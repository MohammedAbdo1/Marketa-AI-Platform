<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignPost;
use App\Models\Brand;
use App\Services\PythonAIService;
use App\Jobs\GenerateCampaignPosts;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class CampaignController extends Controller
{
    protected $aiService;

    public function __construct(PythonAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Get user's campaigns
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $organization = $user->organization;

            $campaigns = Campaign::where('organization_id', $organization->id)
                ->with(['brand', 'posts'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $campaigns
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch campaigns',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new campaign
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'business_type' => 'required|string',
                'description' => 'required|string',
                'goal' => 'required|string',
                'target_audience' => 'required|array',
                'platforms' => 'required|array',
                'duration_days' => 'required|integer|min:7',
                'posts_per_week' => 'required|integer|min:1|max:7',
                'brand_id' => 'nullable|exists:brands,id',
                'mode' => 'required|in:quick,advanced'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $organization = $user->organization;

            // Create campaign
            $campaign = Campaign::create([
                'organization_id' => $organization->id,
                'brand_id' => $request->brand_id,
                'name' => $request->name,
                'business_type' => $request->business_type,
                'description' => $request->description,
                'goal' => $request->goal,
                'mode' => $request->mode,
                'target_audience' => $request->target_audience,
                'platforms' => $request->platforms,
                'duration_days' => $request->duration_days,
                'start_date' => $request->start_date ?? now(),
                'end_date' => $request->start_date ? 
                    now()->parse($request->start_date)->addDays($request->duration_days) : 
                    now()->addDays($request->duration_days),
                'posts_per_week' => $request->posts_per_week,
                'languages' => $request->languages ?? ['ar', 'en'],
                'generation_status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Campaign created successfully',
                'data' => $campaign->load('brand')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create campaign',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate campaign preview
     */
    public function generatePreview(Request $request): JsonResponse
    {
        try {
            $rid = $request->header('X-Request-ID', (string) Str::uuid());
            $request->headers->set('X-Request-ID', $rid);
            Log::info('Generate Preview - start', ['request_id' => $rid]);
            $validator = Validator::make($request->all(), [
                'business_type' => 'required|string',
                'product_name' => 'required|string',
                'description' => 'required|string',
                'campaign_goal' => 'required|string',
                'target_audience' => 'required|array',
                'platforms' => 'required|array',
                'duration_weeks' => 'required|integer|min:1',
                'posts_per_week' => 'required|integer|min:1|max:7',
                'brand_colors' => 'nullable|array',
                'brand_voice' => 'nullable|string',
                'mode' => 'required|in:quick,advanced'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check AI service health - Temporarily disabled
            // if (!$this->aiService->testConnection()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'AI service is currently unavailable'
            //     ], 503);
            // }

            // Prepare data for AI service
            $aiData = [
                'business_type' => $request->business_type,
                'product_name' => $request->product_name,
                'description' => $request->description,
                'goal' => $request->campaign_goal,
                'target_audience' => $request->target_audience,
                'platforms' => $request->platforms,
                'duration_days' => $request->duration_weeks * 7,
                'posts_per_week' => $request->posts_per_week,
                'tone_of_voice' => $request->brand_voice ?? 'friendly',
                'languages' => ['ar', 'en']
            ];

            // Generate preview using AI service
            $preview = $this->aiService->generateCampaignPreview($aiData);
            Log::info('Generate Preview - done', ['request_id' => $rid]);

            return response()->json([
                'success' => true,
                'data' => $preview
            ]);
        } catch (Exception $e) {
            Log::error('Generate Preview - error', ['request_id' => isset($rid) ? $rid : null, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate preview',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start campaign generation
     */
    public function generate(Request $request, Campaign $campaign): JsonResponse
    {
        try {
            $rid = $request->header('X-Request-ID', (string) Str::uuid());
            $request->headers->set('X-Request-ID', $rid);
            Log::info('Generate Campaign - start', ['request_id' => $rid, 'campaign_uuid' => $campaign->uuid]);

            if ($campaign->organization_id !== Auth::user()->organization->id) {
                abort(404);
            }

            if ($campaign->generation_status === 'generating') {
                return response()->json([
                    'success' => false,
                    'message' => 'Campaign is already being generated'
                ], 409);
            }

        // Optional rebuild: clear existing posts and reset state
        $rebuild = filter_var($request->input('rebuild', false), FILTER_VALIDATE_BOOLEAN);
        if ($rebuild === true) {
            CampaignPost::where('campaign_id', $campaign->id)->delete();
            $campaign->update([
                'generation_status' => 'pending',
                'generation_progress' => 0,
                'generation_started_at' => null,
                'generation_completed_at' => null,
                'ai_generated_plans' => null,
            ]);
            Log::info('Generate Campaign - rebuild requested', ['request_id' => $rid, 'campaign_uuid' => $campaign->uuid]);
        }

            // If simple mode is enabled, generate inline and persist posts directly
            if (config('services.python_ai.use_simple_ai')) {
                Log::info("Inline generation mode enabled", ['campaign_uuid' => $campaign->uuid, 'request_id' => $rid]);

                // Build AI data (reuse logic similar to job)
                $targetAudience = $campaign->target_audience;
                if (is_string($targetAudience)) {
                    $decoded = json_decode($targetAudience, true);
                    $targetAudience = is_array($decoded) ? $decoded : [];
                }
                if (!is_array($targetAudience)) { $targetAudience = []; }

                $brand = $campaign->brand;

                $aiData = [
                    'campaign_id' => $campaign->id,
                    'business_type' => $campaign->business_type,
                    'product_name' => $campaign->name,
                    'description' => $campaign->description,
                    'goal' => $campaign->goal,
                    'target_audience' => $targetAudience,
                    'duration_days' => $campaign->duration_days,
                    'platforms' => $campaign->platforms,
                    'posts_per_week' => $campaign->posts_per_week,
                    'brand_colors' => $brand ? [
                        'primary_color' => $brand->primary_color,
                        'secondary_color' => $brand->secondary_color,
                        'accent_color' => $brand->accent_color
                    ] : null,
                    'brand_voice' => $brand ? $brand->brand_voice : null,
                    'languages' => $campaign->languages ?? ['ar', 'en'],
                    'mode' => $campaign->mode ?? 'quick'
                ];

                // Mark as generating (must match DB enum)
                $campaign->update([
                    'generation_status' => 'generating',
                    'generation_progress' => 0,
                    'generation_started_at' => now()
                ]);

                $result = $this->aiService->generateCampaignInline($aiData);

                // Expect posts under result['posts'] or top-level 'posts'
                $payload = $result['result'] ?? $result;
                $posts = $payload['posts'] ?? [];

                foreach ($posts as $index => $postData) {
                    CampaignPost::create([
                        'campaign_id' => $campaign->id,
                        'platform' => $postData['platform'] ?? 'instagram',
                        'post_type' => $postData['post_type'] ?? 'text',
                        'content_ar' => $postData['content_ar'] ?? '',
                        'content_en' => $postData['content_en'] ?? '',
                        'hashtags' => isset($postData['hashtags']) ? (is_array($postData['hashtags']) ? json_encode($postData['hashtags']) : $postData['hashtags']) : '[]',
                        'media_urls' => isset($postData['image_url']) ? [$postData['image_url']] : [],
                        'media_prompts' => isset($postData['image_prompt']) ? [$postData['image_prompt']] : [],
                        'scheduled_date' => null,
                        'status' => 'pending',
                        'ai_prompt_used' => $postData['ai_prompt_used'] ?? null,
                        'ai_tokens_used' => $postData['tokens_used'] ?? 0,
                        'ai_cost' => $postData['cost'] ?? 0,
                        'order_number' => $index + 1,
                        'week_number' => $postData['week'] ?? 1,
                        'day_of_week' => $postData['day'] ?? 1
                    ]);
                }

                $campaign->update([
                    'generation_status' => 'completed',
                    'generation_progress' => 100,
                    'generation_completed_at' => now(),
                    'ai_generated_plans' => $payload
                ]);

                Log::info('Generate Campaign - inline done', ['request_id' => $rid, 'campaign_uuid' => $campaign->uuid]);
                return response()->json([
                    'success' => true,
                    'message' => 'Campaign generated successfully (inline)',
                    'data' => [
                        'campaign_uuid' => $campaign->uuid,
                        'status' => 'completed'
                    ]
                ]);
            }

            // Check AI service health
            Log::info("Campaign Generation - Checking AI Service", ['campaign_uuid' => $campaign->uuid, 'request_id' => $rid]);
            
            if (!$this->aiService->testConnection()) {
                $healthCheck = $this->aiService->checkHealth();
                Log::error("Campaign Generation - AI Service Unavailable", [
                    'campaign_uuid' => $campaign->uuid,
                    'request_id' => $rid,
                    'health_check' => $healthCheck
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'AI service is currently unavailable',
                    'error_details' => $healthCheck,
                    'debug_info' => [
                        'base_url' => config('services.python_ai.url'),
                        'timeout' => config('services.python_ai.timeout')
                    ]
                ], 503);
            }

            // Dispatch background job
            Log::info("Dispatching GenerateCampaignPosts job", ['campaign_uuid' => $campaign->uuid, 'request_id' => $rid]);
            try {
                GenerateCampaignPosts::dispatch($campaign)
                    ->onConnection('redis')
                    ->onQueue('campaign-generation');
                Log::info("GenerateCampaignPosts job dispatched successfully", ['campaign_uuid' => $campaign->uuid, 'request_id' => $rid]);
            } catch (\Exception $dispatchException) {
                Log::error("Failed to dispatch GenerateCampaignPosts job", [
                    'campaign_uuid' => $campaign->uuid,
                    'request_id' => $rid,
                    'error' => $dispatchException->getMessage(),
                    'trace' => $dispatchException->getTraceAsString()
                ]);
                throw $dispatchException;
            }

            Log::info('Generate Campaign - queued', ['request_id' => $rid, 'campaign_uuid' => $campaign->uuid]);
            return response()->json([
                'success' => true,
                'message' => 'Campaign generation started',
                'data' => [
                    'campaign_uuid' => $campaign->uuid,
                    'status' => 'generating'
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Generate Campaign - error', ['request_id' => isset($rid) ? $rid : null, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to start generation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get campaign details
     */
    public function show(Campaign $campaign): JsonResponse
    {
        try {
            if ($campaign->organization_id !== Auth::user()->organization->id) {
                abort(404);
            }

            $campaign->load(['brand', 'posts' => function($query) {
                $query->orderBy('order_number');
            }]);

            return response()->json([
                'success' => true,
                'data' => $campaign
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update campaign
     */
    public function update(Request $request, Campaign $campaign): JsonResponse
    {
        try {
            if ($campaign->organization_id !== Auth::user()->organization->id) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'goal' => 'sometimes|string',
                'target_audience' => 'sometimes|array',
                'platforms' => 'sometimes|array',
                'posts_per_week' => 'sometimes|integer|min:1|max:7'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $campaign->update($request->only([
                'name', 'description', 'goal', 'target_audience', 
                'platforms', 'posts_per_week'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Campaign updated successfully',
                'data' => $campaign->load('brand')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update campaign',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete campaign
     */
    public function destroy(Campaign $campaign): JsonResponse
    {
        try {
            if ($campaign->organization_id !== Auth::user()->organization->id) {
                abort(404);
            }

            $campaign->delete();

            return response()->json([
                'success' => true,
                'message' => 'Campaign deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete campaign',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get campaign generation status
     */
    public function generationStatus(Campaign $campaign): JsonResponse
    {
        try {
            if ($campaign->organization_id !== Auth::user()->organization->id) {
                abort(404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $campaign->generation_status,
                    'progress' => $campaign->generation_progress,
                    'posts_count' => $campaign->posts()->count()
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Suggest brand colors
     */
    public function suggestColors(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'description' => 'required|string|min:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $colors = $this->aiService->suggestBrandColors($request->description);

            return response()->json([
                'success' => true,
                'data' => $colors
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to suggest colors',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}