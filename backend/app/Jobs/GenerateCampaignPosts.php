<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\CreativeAssetService;
use App\Services\PythonAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class GenerateCampaignPosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;

    /**
     * Create a new job instance.
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     */
    public function handle(PythonAIService $aiService): void
    {
        try {
            // Update campaign status to processing
            $this->campaign->update([
                'generation_status' => 'processing',
                'generation_progress' => 0,
                'generation_started_at' => now(),
                'status' => 'generating',
                'is_complete' => false,
            ]);

            Log::info("Starting campaign generation", ['campaign_id' => $this->campaign->id]);

            // Prepare data for AI service
            $campaignData = $this->prepareCampaignData();

            // Generate campaign using AI service (async)
            $aiResponse = $aiService->generateCampaignAsync($campaignData);

            if (!isset($aiResponse['task_id'])) {
                throw new Exception("AI service did not return task ID");
            }

            // Store AI task ID for tracking
            $this->campaign->update([
                'ai_task_id' => $aiResponse['task_id'],
                'generation_status' => 'processing'
            ]);

            Log::info("Campaign generation task started", [
                'campaign_id' => $this->campaign->id,
                'ai_task_id' => $aiResponse['task_id']
            ]);

            // Poll for completion (with timeout)
            $this->pollForCompletion($aiResponse['task_id'], $aiService);
        } catch (Exception $e) {
            // Update campaign status to failed
            $this->campaign->update([
                'generation_status' => 'failed',
                'generation_progress' => 0,
                'generation_completed_at' => now(),
                'status' => 'draft',
                'is_complete' => false,
            ]);

            Log::error("Campaign generation failed", [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Prepare campaign data for AI service
     */
    protected function prepareCampaignData(): array
    {
        $brand = $this->campaign->brand;

        // Convert target_audience from JSON string to array if needed
        $targetAudience = $this->campaign->target_audience;
        if (is_string($targetAudience)) {
            $targetAudience = json_decode($targetAudience, true) ?? [];
        }
        if (!is_array($targetAudience)) {
            $targetAudience = [];
        }

        $platforms = $this->campaign->platforms;
        if (is_string($platforms)) {
            $decodedPlatforms = json_decode($platforms, true);
            if (is_array($decodedPlatforms)) {
                $platforms = $decodedPlatforms;
            } else {
                $platforms = array_filter(array_map('trim', explode(',', $platforms)));
            }
        } elseif (!is_array($platforms)) {
            $platforms = [];
        }
        $platforms = array_values(array_filter($platforms, fn ($platform) => !empty($platform)));
        if (empty($platforms)) {
            $platforms = ['instagram'];
        }

        $languages = $this->campaign->languages ?? ['ar'];
        if (is_string($languages)) {
            $decodedLanguages = json_decode($languages, true);
            $languages = is_array($decodedLanguages) ? $decodedLanguages : [$languages];
        }
        if (!is_array($languages) || empty($languages)) {
            $languages = ['ar', 'en'];
        }

        $durationDays = (int) ($this->campaign->duration_days ?? 28);
        $durationDays = max(7, $durationDays);
        $postsPerWeek = (int) ($this->campaign->posts_per_week ?? 3);
        $postsPerWeek = max(1, min($postsPerWeek, 14));

        return [
            'campaign_id' => $this->campaign->id,
            'business_type' => $this->campaign->business_type,
            'product_name' => $this->campaign->name,
            'description' => $this->campaign->description,
            'goal' => $this->campaign->goal,
            'target_audience' => $targetAudience,
            'duration_days' => $durationDays,
            'platforms' => $platforms,
            'posts_per_week' => $postsPerWeek,
            'brand_colors' => $brand ? [
                'primary_color' => $brand->primary_color,
                'secondary_color' => $brand->secondary_color,
                'accent_color' => $brand->accent_color,
            ] : null,
            'brand_voice' => $brand ? $brand->brand_voice : null,
            'languages' => array_values($languages),
            'mode' => $this->campaign->mode ?? 'quick',
        ];
    }

    /**
     * Save generated posts to database
     */
    protected function saveGeneratedPosts(array $posts): void
    {
        /** @var CreativeAssetService $creativeAssetService */
        $creativeAssetService = app(CreativeAssetService::class);

        foreach ($posts as $index => $postData) {
            if (!isset($postData['scheduled_date'])) {
                $postData['scheduled_date'] = $this->calculateScheduledDate($postData);
            }

            $payload = $creativeAssetService->buildPayloadFromArray(
                $this->campaign,
                $postData,
                $index + 1
            );

            $creativeAssetService->create($payload);
        }
    }

    /**
     * Calculate scheduled date for post
     */
    protected function calculateScheduledDate(array $postData): ?string
    {
        if (!isset($postData['week']) || !isset($postData['day'])) {
            return $postData['scheduled_date'] ?? null;
        }

        $startDate = $this->campaign->start_date;
        if (!$startDate) {
            return $postData['scheduled_date'] ?? null;
        }

        $weekNumber = $postData['week'] - 1; // Convert to 0-based
        $dayOfWeek = $postData['day'] - 1; // Convert to 0-based

        $scheduledDate = $startDate->copy()
            ->addWeeks($weekNumber)
            ->addDays($dayOfWeek);

        return $scheduledDate->format('Y-m-d');
    }

    /**
     * Poll AI service for task completion
     */
    protected function pollForCompletion(string $taskId, PythonAIService $aiService): void
    {
        $maxAttempts = 12; // 1 minute with 5-second intervals
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            try {
                $status = $aiService->getTaskStatus($taskId);

                Log::info("Task status check", [
                    'task_id' => $taskId,
                    'attempt' => $attempt,
                    'status' => $status['status'] ?? 'unknown',
                ]);

                if ($status['status'] === 'completed') {
                    $this->handleTaskCompletion($taskId, $aiService);
                    return;
                } elseif ($status['status'] === 'failed') {
                    throw new Exception("AI task failed: " . ($status['message'] ?? 'Unknown error'));
                } elseif ($status['status'] === 'processing') {
                    // Update progress
                    $progress = $status['progress'] ?? 0;
                    $this->campaign->update(['generation_progress' => $progress]);
                }

                sleep(5); // Wait 5 seconds before next check
                $attempt++;
            } catch (Exception $e) {
                Log::warning("Failed to check task status", [
                    'task_id' => $taskId,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt >= $maxAttempts - 1) {
                    throw new Exception("Task polling timeout after {$maxAttempts} attempts");
                }

                sleep(5);
                $attempt++;
            }
        }

        throw new Exception("Task polling timeout");
    }

    /**
     * Handle successful task completion
     */
    protected function handleTaskCompletion(string $taskId, PythonAIService $aiService): void
    {
        try {
            $result = $aiService->getTaskResult($taskId);

            if (!isset($result['result']['posts']) || empty($result['result']['posts'])) {
                throw new Exception("No posts in AI service result");
            }

            // Save generated posts
            $this->saveGeneratedPosts($result['result']['posts']);

            // Update campaign status to completed
            $this->campaign->update([
                'generation_status' => 'completed',
                'generation_progress' => 100,
                'generation_completed_at' => now(),
                'ai_generated_plans' => $result['result'],
                'status' => 'completed',
                'is_complete' => true,
            ]);

            Log::info("Campaign generation completed successfully", [
                'campaign_id' => $this->campaign->id,
                'ai_task_id' => $taskId,
            ]);
        } catch (Exception $e) {
            throw new Exception("Failed to process completed task: " . $e->getMessage());
        }
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception): void
    {
        $this->campaign->update([
            'generation_status' => 'failed',
            'generation_progress' => 0,
            'generation_completed_at' => now(),
            'status' => 'draft',
            'is_complete' => false,
        ]);

        Log::error("Campaign generation job failed", [
            'campaign_id' => $this->campaign->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
