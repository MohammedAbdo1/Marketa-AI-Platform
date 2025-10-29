<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Brand;
use App\Services\PythonAIService;
use App\Jobs\GenerateCampaignPosts;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
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

            return response()->json([
                'success' => true,
                'data' => $preview
            ]);
        } catch (Exception $e) {
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
    public function generate(Request $request, $id): JsonResponse
    {
        try {
            $campaign = Campaign::where('id', $id)
                ->where('organization_id', Auth::user()->organization->id)
                ->firstOrFail();

            if ($campaign->generation_status === 'generating') {
                return response()->json([
                    'success' => false,
                    'message' => 'Campaign is already being generated'
                ], 409);
            }

            // Check AI service health
            Log::info("Campaign Generation - Checking AI Service", ['campaign_id' => $campaign->id]);
            
            if (!$this->aiService->testConnection()) {
                $healthCheck = $this->aiService->checkHealth();
                Log::error("Campaign Generation - AI Service Unavailable", [
                    'campaign_id' => $campaign->id,
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
            Log::info("Dispatching GenerateCampaignPosts job", ['campaign_id' => $campaign->id]);
            try {
                GenerateCampaignPosts::dispatch($campaign)
                    ->onConnection('redis')
                    ->onQueue('campaign-generation');
                Log::info("GenerateCampaignPosts job dispatched successfully", ['campaign_id' => $campaign->id]);
            } catch (\Exception $dispatchException) {
                Log::error("Failed to dispatch GenerateCampaignPosts job", [
                    'campaign_id' => $campaign->id,
                    'error' => $dispatchException->getMessage(),
                    'trace' => $dispatchException->getTraceAsString()
                ]);
                throw $dispatchException;
            }

            return response()->json([
                'success' => true,
                'message' => 'Campaign generation started',
                'data' => [
                    'campaign_id' => $campaign->id,
                    'status' => 'generating'
                ]
            ]);
        } catch (Exception $e) {
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
    public function show($id): JsonResponse
    {
        try {
            $campaign = Campaign::where('id', $id)
                ->where('organization_id', Auth::user()->organization->id)
                ->with(['brand', 'posts' => function($query) {
                    $query->orderBy('order_number');
                }])
                ->firstOrFail();

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
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $campaign = Campaign::where('id', $id)
                ->where('organization_id', Auth::user()->organization->id)
                ->firstOrFail();

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
    public function destroy($id): JsonResponse
    {
        try {
            $campaign = Campaign::where('id', $id)
                ->where('organization_id', Auth::user()->organization->id)
                ->firstOrFail();

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
    public function generationStatus($id): JsonResponse
    {
        try {
            $campaign = Campaign::where('id', $id)
                ->where('organization_id', Auth::user()->organization->id)
                ->firstOrFail();

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