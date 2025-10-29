<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class PythonAIService
{
    protected $baseUrl;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.python_ai.url', 'http://api:8001/api');
        $this->timeout = config('services.python_ai.timeout', 180);  // 180 seconds for Google API calls
    }

    /**
     * Generate campaign preview structure
     */
    public function generateCampaignPreview(array $data): array
    {
        try {
            // Get user ID from auth if available
            $userId = Auth::check() ? Auth::id() : request()->ip() . '_anonymous';
            
            // Start preview generation task
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-User-ID' => (string) $userId
                ])
                ->post("{$this->baseUrl}/campaign/preview", $data);

            if (!$response->successful()) {
                $errorBody = $response->body();
                $statusCode = $response->status();
                
                // Handle rate limit errors specifically
                if ($statusCode === 429) {
                    Log::warning("Rate limit exceeded for preview generation", [
                        'user_id' => $userId,
                        'status' => $statusCode
                    ]);
                    throw new Exception("Rate limit exceeded. Please wait a moment and try again.");
                }
                
                throw new Exception("AI Service error (HTTP {$statusCode}): " . $errorBody);
            }

            $taskResponse = $response->json();
            
            // If response contains task_id, wait for result
            if (isset($taskResponse['task_id'])) {
                $taskId = $taskResponse['task_id'];
                $maxWaitTime = $this->timeout - 10; // Reserve 10 seconds for initial request and final result retrieval
                $startTime = time();
                $checkInterval = 2; // Check every 2 seconds (reduced frequency)
                
                Log::info("Campaign preview task started", [
                    'task_id' => $taskId,
                    'max_wait_time' => $maxWaitTime,
                    'timeout' => $this->timeout
                ]);
                
                // Poll for task completion
                while (true) {
                    $elapsed = time() - $startTime;
                    if ($elapsed >= $maxWaitTime) {
                        throw new Exception("Preview generation timeout after {$maxWaitTime} seconds");
                    }
                    
                    $statusResponse = Http::timeout(10)
                        ->withHeaders([
                            'X-User-ID' => (string) $userId
                        ])
                        ->get("{$this->baseUrl}/task/status/{$taskId}");
                    
                    if (!$statusResponse->successful()) {
                        throw new Exception("Failed to check task status: " . $statusResponse->body());
                    }
                    
                    $status = $statusResponse->json();
                    
                    if ($status['status'] === 'completed') {
                        // Get the result
                        $resultResponse = Http::timeout(10)
                            ->withHeaders([
                                'X-User-ID' => (string) $userId
                            ])
                            ->get("{$this->baseUrl}/task/result/{$taskId}");
                        
                        if (!$resultResponse->successful()) {
                            throw new Exception("Failed to get task result: " . $resultResponse->body());
                        }
                        
                        $result = $resultResponse->json();
                        Log::info("Campaign preview completed", [
                            'task_id' => $taskId,
                            'elapsed_seconds' => $elapsed
                        ]);
                        
                        return $result['result'] ?? $result;
                    } elseif ($status['status'] === 'failed') {
                        throw new Exception("Preview generation failed: " . ($status['message'] ?? 'Unknown error'));
                    }
                    
                    // Wait before next check
                    sleep($checkInterval);
                }
            }
            
            // If no task_id, return response directly (for backward compatibility)
            return $taskResponse;
        } catch (Exception $e) {
            Log::error("Campaign preview generation failed", [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Generate full campaign with posts (async)
     */
    public function generateCampaignAsync(array $data): array
    {
        try {
            // Get user ID from auth if available
            $userId = Auth::check() ? Auth::id() : request()->ip() . '_anonymous';
            
            Log::info("Calling AI service for campaign generation", [
                'url' => "{$this->baseUrl}/campaign/generate",
                'user_id' => $userId,
                'campaign_id' => $data['campaign_id'] ?? null
            ]);
            
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-User-ID' => (string) $userId
                ])
                ->post("{$this->baseUrl}/campaign/generate", $data);

            if ($response->successful()) {
                $result = $response->json();
                Log::info("AI service campaign generation response", [
                    'campaign_id' => $data['campaign_id'] ?? null,
                    'task_id' => $result['task_id'] ?? null
                ]);
                return $result;
            }

            $errorBody = $response->body();
            $statusCode = $response->status();
            
            Log::error("AI Service campaign generation failed", [
                'status_code' => $statusCode,
                'error_body' => $errorBody,
                'campaign_id' => $data['campaign_id'] ?? null,
                'url' => "{$this->baseUrl}/campaign/generate"
            ]);

            throw new Exception("AI Service error (HTTP {$statusCode}): " . $errorBody);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("AI Service connection failed", [
                'error' => $e->getMessage(),
                'base_url' => $this->baseUrl,
                'campaign_id' => $data['campaign_id'] ?? null
            ]);
            throw new Exception("Failed to connect to AI service: " . $e->getMessage());
        } catch (Exception $e) {
            Log::error("Campaign generation failed", [
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Generate full campaign with posts (sync - deprecated)
     */
    public function generateCampaign(array $data): array
    {
        return $this->generateCampaignAsync($data);
    }

    /**
     * Get task status from AI service
     */
    public function getTaskStatus(string $taskId): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/task/status/{$taskId}");

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("AI Service error: " . $response->body());
        } catch (Exception $e) {
            Log::error("Failed to get task status", [
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get task result from AI service
     */
    public function getTaskResult(string $taskId): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/task/result/{$taskId}");

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("AI Service error: " . $response->body());
        } catch (Exception $e) {
            Log::error("Failed to get task result", [
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Regenerate text for a specific post
     */
    public function regeneratePostText(string $postId, array $settings): array
    {
        try {
            $data = array_merge(['post_id' => $postId], $settings);
            
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/post/regenerate-text", $data);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("AI Service error: " . $response->body());
        } catch (Exception $e) {
            Log::error("Post text regeneration failed", [
                'error' => $e->getMessage(),
                'post_id' => $postId,
                'settings' => $settings
            ]);
            throw $e;
        }
    }

    /**
     * Regenerate image for a specific post
     */
    public function regeneratePostImage(string $postId, string $imagePrompt): array
    {
        try {
            $data = [
                'post_id' => $postId,
                'image_prompt' => $imagePrompt
            ];
            
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/post/regenerate-image", $data);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("AI Service error: " . $response->body());
        } catch (Exception $e) {
            Log::error("Post image regeneration failed", [
                'error' => $e->getMessage(),
                'post_id' => $postId,
                'image_prompt' => $imagePrompt
            ]);
            throw $e;
        }
    }

    /**
     * Suggest brand colors based on description
     */
    public function suggestBrandColors(string $description): array
    {
        try {
            $data = ['description' => $description];
            
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/brand/suggest-colors", $data);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("AI Service error: " . $response->body());
        } catch (Exception $e) {
            Log::error("Brand color suggestion failed", [
                'error' => $e->getMessage(),
                'description' => $description
            ]);
            
            // Return default color palettes on error
            return [
                'color_palettes' => [
                    [
                        'name' => 'Professional Blue',
                        'primary_color' => '#2563eb',
                        'secondary_color' => '#64748b',
                        'accent_color' => '#f59e0b',
                        'reasoning' => 'Professional and trustworthy'
                    ],
                    [
                        'name' => 'Warm Orange',
                        'primary_color' => '#ea580c',
                        'secondary_color' => '#dc2626',
                        'accent_color' => '#fbbf24',
                        'reasoning' => 'Energetic and engaging'
                    ],
                    [
                        'name' => 'Nature Green',
                        'primary_color' => '#16a34a',
                        'secondary_color' => '#059669',
                        'accent_color' => '#84cc16',
                        'reasoning' => 'Fresh and natural'
                    ]
                ]
            ];
        }
    }

    /**
     * Check AI service health
     */
    public function checkHealth(): array
    {
        try {
            // Remove /api from baseUrl for health check since health endpoint is at root level
            // Use rtrim to safely remove trailing /api
            $healthUrl = rtrim($this->baseUrl, '/api') . '/health';
            
            Log::info("AI Service Health Check", [
                'base_url' => $this->baseUrl,
                'health_url' => $healthUrl,
                'timeout' => $this->timeout
            ]);
            
            $response = Http::timeout(10)
                ->get($healthUrl);

            Log::info("AI Service Health Response", [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("AI Service Health Check Failed", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return ['status' => 'unhealthy', 'error' => 'Service unavailable'];
        } catch (Exception $e) {
            Log::error("AI Service Health Check Exception", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    /**
     * Test AI service connection
     */
    public function testConnection(): bool
    {
        $maxRetries = 3;
        $retryDelay = 1; // seconds
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                Log::info("AI Service Test Connection Attempt $attempt/$maxRetries");
                $health = $this->checkHealth();
                $isHealthy = isset($health['status']) && $health['status'] === 'healthy';
                
                if ($isHealthy) {
                    Log::info("AI Service Test Connection Success on attempt $attempt", [
                        'health_status' => $health['status'],
                        'health_data' => $health
                    ]);
                    return true;
                }
                
                Log::warning("AI Service Test Connection Failed on attempt $attempt", [
                    'health_status' => $health['status'] ?? 'unknown',
                    'health_data' => $health
                ]);
                
                if ($attempt < $maxRetries) {
                    Log::info("Retrying AI Service connection in {$retryDelay} seconds...");
                    sleep($retryDelay);
                }
                
            } catch (Exception $e) {
                Log::error("AI Service Test Connection Exception on attempt $attempt", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                if ($attempt < $maxRetries) {
                    Log::info("Retrying AI Service connection in {$retryDelay} seconds...");
                    sleep($retryDelay);
                }
            }
        }
        
        Log::error("AI Service Test Connection Failed after $maxRetries attempts");
        return false;
    }

    /**
     * Get service statistics
     */
    public function getServiceStats(): array
    {
        try {
            // Remove /api from baseUrl for health check since health endpoint is at root level
            // Use rtrim to safely remove trailing /api
            $healthUrl = rtrim($this->baseUrl, '/api') . '/health';
            $response = Http::timeout(10)
                ->get($healthUrl);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => $data['status'] ?? 'unknown',
                    'agents' => $data['agents'] ?? [],
                    'uptime' => $data['uptime'] ?? null,
                    'last_check' => now()
                ];
            }

            return ['status' => 'unavailable'];
        } catch (Exception $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }
}
