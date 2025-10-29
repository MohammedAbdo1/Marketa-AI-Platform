<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignPost;
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
                'generation_started_at' => now()
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
                'generation_completed_at' => now()
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
        
        return [
            'campaign_id' => $this->campaign->id,
            'business_type' => $this->campaign->business_type,
            'product_name' => $this->campaign->name,
            'description' => $this->campaign->description,
            'goal' => $this->campaign->goal, // Changed from campaign_goal to goal
            'target_audience' => $targetAudience,
            'duration_days' => $this->campaign->duration_days, // Changed from duration_weeks
            'platforms' => $this->campaign->platforms,
            'posts_per_week' => $this->campaign->posts_per_week,
            'brand_colors' => $brand ? [
                'primary_color' => $brand->primary_color,
                'secondary_color' => $brand->secondary_color,
                'accent_color' => $brand->accent_color
            ] : null,
            'brand_voice' => $brand ? $brand->brand_voice : null,
            'languages' => $this->campaign->languages ?? ['ar', 'en'],
            'mode' => $this->campaign->mode ?? 'quick'
        ];
    }

    /**
     * Save generated posts to database
     */
    protected function saveGeneratedPosts(array $posts): void
    {
        foreach ($posts as $index => $postData) {
            CampaignPost::create([
                'campaign_id' => $this->campaign->id,
                'platform' => $postData['platform'] ?? 'instagram',
                'post_type' => $postData['post_type'] ?? 'text',
                'content_ar' => $postData['content_ar'] ?? '',
                'content_en' => $postData['content_en'] ?? '',
                'hashtags' => is_array($postData['hashtags'] ?? []) ? json_encode($postData['hashtags']) : ($postData['hashtags'] ?? '[]'),
                'media_urls' => isset($postData['image_url']) ? [$postData['image_url']] : [],
                'media_prompts' => isset($postData['image_prompt']) ? [$postData['image_prompt']] : [],
                'scheduled_date' => $this->calculateScheduledDate($postData),
                'status' => 'pending',
                'ai_prompt_used' => $postData['ai_prompt_used'] ?? null,
                'ai_tokens_used' => $postData['tokens_used'] ?? 0,
                'ai_cost' => $postData['cost'] ?? 0,
                'order_number' => $index + 1,
                'week_number' => $postData['week'] ?? 1,
                'day_of_week' => $postData['day'] ?? 1
            ]);
        }
    }

    /**
     * Calculate scheduled date for post
     */
    protected function calculateScheduledDate(array $postData): ?string
    {
        if (!isset($postData['week']) || !isset($postData['day'])) {
            return null;
        }

        $startDate = $this->campaign->start_date;
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
                    'status' => $status['status'] ?? 'unknown'
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
                    'error' => $e->getMessage()
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
                'ai_generated_plans' => $result['result']
            ]);

            Log::info("Campaign generation completed successfully", [
                'campaign_id' => $this->campaign->id,
                'ai_task_id' => $taskId
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
            'generation_completed_at' => now()
        ]);

        Log::error("Campaign generation job failed", [
            'campaign_id' => $this->campaign->id,
            'error' => $exception->getMessage()
        ]);
    }
}
