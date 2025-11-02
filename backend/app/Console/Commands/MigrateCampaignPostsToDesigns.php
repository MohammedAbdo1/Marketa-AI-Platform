<?php

namespace App\Console\Commands;

use App\Models\CampaignPost;
use App\Models\Design;
use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MigrateCampaignPostsToDesigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'designs:migrate-posts 
                            {--dry-run : Run without making changes}
                            {--chunk=500 : Number of records to process at once}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing campaign posts to the new unified designs system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');

        $this->info('Starting campaign posts to designs migration...');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE: No changes will be made');
        }

        // Get total posts with composition layers
        $totalPosts = CampaignPost::whereNotNull('composition_layers')
                                  ->whereNull('design_id')
                                  ->count();

        if ($totalPosts === 0) {
            $this->info('No posts found to migrate.');
            return 0;
        }

        $this->info("Found {$totalPosts} posts to migrate");
        
        $progressBar = $this->output->createProgressBar($totalPosts);
        $progressBar->start();

        $migrated = 0;
        $errors = 0;
        $skipped = 0;

        CampaignPost::whereNotNull('composition_layers')
                    ->whereNull('design_id')
                    ->with('campaign')
                    ->chunkById($chunkSize, function ($posts) use (&$migrated, &$errors, &$skipped, $progressBar, $dryRun) {
                        foreach ($posts as $post) {
                            try {
                                if ($dryRun) {
                                    $this->line("\nWould migrate post ID: {$post->id} from campaign: {$post->campaign->name}");
                                    $skipped++;
                                } else {
                                    $this->migratePost($post);
                                    $migrated++;
                                }
                                $progressBar->advance();
                            } catch (\Exception $e) {
                                $errors++;
                                $this->error("\nError migrating post ID {$post->id}: " . $e->getMessage());
                                $progressBar->advance();
                            }
                        }
                    });

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Migration complete!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Migrated', $migrated],
                ['Errors', $errors],
                ['Skipped (dry-run)', $skipped],
                ['Total Processed', $migrated + $errors + $skipped],
            ]
        );

        return $errors > 0 ? 1 : 0;
    }

    /**
     * Migrate a single campaign post to a design
     */
    private function migratePost(CampaignPost $post)
    {
        // Create design from post
        $design = Design::create([
            'uuid' => Str::uuid(),
            'user_id' => $post->campaign->organization->user_id ?? 1, // Fallback to admin
            'title' => $this->generateTitle($post),
            'description' => $post->content_ar ?? $post->content_en,
            'design_type' => $this->mapPostTypeToDesignType($post->post_type),
            'source_type' => $post->generation_method ?? 'ai',
            'source_type_model' => 'campaign_post',
            'source_id' => $post->uuid,
            'composition_data' => $post->composition_layers,
            'thumbnail_url' => $post->media_urls[0] ?? null,
            'export_url' => $post->media_urls[0] ?? null,
            'width' => $post->composition_layers['dimensions']['width'] ?? 1080,
            'height' => $post->composition_layers['dimensions']['height'] ?? 1080,
            'canvas_settings' => [
                'background' => $post->composition_analysis['background'] ?? null,
            ],
            'metadata' => [
                'original_post_id' => $post->id,
                'campaign_id' => $post->campaign_id,
                'platform' => $post->platform,
                'migrated_at' => now()->toISOString(),
            ],
            'context_type' => 'campaign',
            'context_id' => $post->campaign_id,
        ]);

        // Update post to link to design
        $post->update(['design_id' => $design->id]);

        // Link design to campaign via pivot table
        $post->campaign->designs()->syncWithoutDetaching([
            $design->id => [
                'platform' => $post->platform,
                'scheduled_date' => $post->scheduled_date,
                'scheduled_time' => $post->scheduled_time,
                'published_at' => $post->published_at,
                'status' => $post->status,
                'post_content_ar' => $post->content_ar,
                'post_content_en' => $post->content_en,
                'hashtags' => $post->hashtags,
                'order' => $post->order_number,
            ]
        ]);

        return $design;
    }

    /**
     * Generate a title for the design
     */
    private function generateTitle(CampaignPost $post): string
    {
        $campaignName = $post->campaign->name ?? 'Campaign';
        $platform = ucfirst($post->platform);
        $date = $post->scheduled_date ? $post->scheduled_date->format('M d') : 'Day ' . $post->order_number;
        
        return "{$campaignName} - {$platform} - {$date}";
    }

    /**
     * Map post type to design type
     */
    private function mapPostTypeToDesignType(string $postType): string
    {
        $mapping = [
            'image' => 'social_post',
            'video' => 'social_post',
            'story' => 'story',
            'carousel' => 'social_post',
            'text' => 'social_post',
        ];

        return $mapping[$postType] ?? 'social_post';
    }
}

