<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignPost;
use App\Models\Design;
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
use Illuminate\Support\Arr;

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
     * Get incomplete drafts
     */
    public function getDrafts(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $organizationId = optional($user->organization)->id;

            $draftsQuery = Campaign::where('user_id', $user->id)
                ->where('status', 'draft')
                ->where('is_complete', false)
                ->orderBy('updated_at', 'desc');

            if ($organizationId) {
                $draftsQuery->where('organization_id', $organizationId);
            }

            $drafts = $draftsQuery->get();

            return response()->json([
                'success' => true,
                'data' => $drafts
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch drafts',
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
                'goal' => 'nullable|string',
                'target_audience' => 'nullable|array',
                'platforms' => 'nullable|array',
                'duration_days' => 'nullable|integer|min:7',
                'duration_weeks' => 'nullable|integer|min:1',
                'posts_per_week' => 'nullable|integer|min:1|max:7',
                'brand_id' => 'nullable|exists:brands,id',
                'mode' => 'nullable|in:quick,advanced',
                'languages' => 'nullable|array',
                'wizard_step' => 'nullable|integer|min:1|max:10',
                'wizard_data' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $organizationId = optional($user->organization)->id;

            $wizardStep = $request->wizard_step ?? 1;
            $durationDays = $request->duration_days
                ?? ($request->duration_weeks ? $request->duration_weeks * 7 : 28);
            $postsPerWeek = $request->posts_per_week ?? 3;
            $mode = $request->mode ?? 'quick';

            $initialWizardData = $request->wizard_data ?? [];
            if (!isset($initialWizardData['step1'])) {
                $initialWizardData['step1'] = [
                    'business_type' => $request->business_type,
                    'product_name' => $request->name,
                    'description' => $request->description,
                ];
            }

            $campaign = Campaign::create([
                'user_id' => $user->id,
                'organization_id' => $organizationId,
                'brand_id' => $request->brand_id,
                'name' => $request->name,
                'business_type' => $request->business_type,
                'description' => $request->description,
                'goal' => $request->goal ?? 'awareness',
                'mode' => $mode,
                'target_audience' => $request->target_audience ?? [],
                'platforms' => $request->platforms ?? [],
                'duration_days' => $durationDays,
                'start_date' => $request->start_date ?? now(),
                'end_date' => $request->start_date ?
                    now()->parse($request->start_date)->addDays($durationDays) :
                    now()->addDays($durationDays),
                'posts_per_week' => $postsPerWeek,
                'languages' => $request->languages ?? ['ar'],
                'generation_status' => 'pending',
                'status' => 'draft',
                'wizard_step' => $wizardStep,
                'wizard_data' => $initialWizardData,
                'is_complete' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Campaign created successfully',
                'data' => $campaign->fresh()->load('brand')
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create campaign',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate comprehensive campaign intelligence preview
     */
    public function generatePreview(Request $request): JsonResponse
    {
        try {
            $rid = $request->header('X-Request-ID', (string) Str::uuid());
            $request->headers->set('X-Request-ID', $rid);
            Log::info('Generate Intelligence Preview - start', ['request_id' => $rid]);
            
            $validator = Validator::make($request->all(), [
                'campaign_uuid' => 'required|exists:campaigns,uuid',
                'mode' => 'nullable|in:quick,advanced',
                'campaign_goal' => 'nullable|string',
                'brand_voice' => 'nullable|string',
                'target_audience' => 'nullable|array',
                'platforms' => 'nullable|array',
                'duration_weeks' => 'nullable|integer|min:1',
                'duration_days' => 'nullable|integer|min:7',
                'posts_per_week' => 'nullable|integer|min:1|max:7'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $campaign = Campaign::where('uuid', $request->campaign_uuid)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            if ($campaign->organization_id && $campaign->organization_id !== optional(Auth::user()->organization)->id) {
                abort(404);
            }

            $mode = $request->mode ?? $campaign->mode ?? 'advanced';
            $mode = in_array($mode, ['quick', 'advanced'], true) ? $mode : 'advanced';

            Log::info('Generate Intelligence Preview - payload', [
                'request_id' => $rid,
                'campaign_uuid' => $campaign->uuid,
                'mode' => $mode,
                'raw_payload' => $request->all(),
            ]);

            $businessType = $this->resolveCampaignField($campaign, 'business_type');
            $productName = $this->resolveCampaignField($campaign, 'name')
                ?? $this->resolveCampaignField($campaign, 'product_name');
            $description = $this->resolveCampaignField($campaign, 'description', '');
            $goal = $request->campaign_goal ?? $campaign->goal ?? 'awareness';

            $targetAudience = $request->target_audience
                ?? $campaign->target_audience
                ?? $this->resolveCampaignField($campaign, 'target_audience', []);

            $targetAudience = $this->normalizeTargetAudience($targetAudience, $campaign);

            $platforms = $request->platforms ?? $campaign->platforms ?? [];
            if (!is_array($platforms)) {
                $decodedPlatforms = json_decode($platforms, true);
                $platforms = is_array($decodedPlatforms) ? $decodedPlatforms : [];
            }
            if (!Arr::isAssoc($platforms)) {
                $platforms = array_values($platforms);
            }

            $durationDays = $request->duration_days
                ?? ($request->duration_weeks ? $request->duration_weeks * 7 : $campaign->duration_days ?? 28);
            $postsPerWeek = $request->posts_per_week ?? $campaign->posts_per_week ?? 3;
            $brandVoice = $request->brand_voice ?? $this->resolveCampaignField($campaign, 'brand_voice', 'friendly');
            $languages = $request->languages ?? $campaign->languages ?? ['ar'];
            if (!is_array($languages)) {
                $decodedLanguages = json_decode($languages, true);
                $languages = is_array($decodedLanguages) ? $decodedLanguages : ['ar'];
            }
            $languages = array_values(array_filter($languages));
            if (empty($languages)) {
                $languages = ['ar'];
            }

            $aiData = [
                'business_type' => $businessType,
                'product_name' => $productName,
                'description' => $description,
                'goal' => $goal,
                'target_audience' => $targetAudience,
                'platforms' => $platforms,
                'duration_days' => $durationDays,
                'posts_per_week' => $postsPerWeek,
                'tone_of_voice' => $brandVoice,
                'mode' => $mode,
                'languages' => $languages,
            ];

            $fallbackUsed = false;
            try {
                $intelligence = $this->aiService->generateCampaignIntelligence($aiData);
            } catch (Exception $aiException) {
                Log::warning('Generate Intelligence Preview - AI failure', [
                    'request_id' => $rid,
                    'error' => $aiException->getMessage()
                ]);
                $intelligence = $this->buildFallbackIntelligence($aiData, $aiException->getMessage());
                $fallbackUsed = true;
            }

            $campaign->update([
                'campaign_strategy' => $intelligence,
                'ai_analysis' => $intelligence['language_analysis'] ?? null,
                'mode' => $mode,
            ]);

            Log::info('Generate Intelligence Preview - done', ['request_id' => $rid]);

            return response()->json([
                'success' => true,
                'data' => $intelligence,
                'fallback' => $fallbackUsed,
                'message' => $fallbackUsed
                    ? 'تم إنشاء خطة بديلة ذكية مؤقتاً، يمكنك المتابعة وإعادة المحاولة لاحقاً.'
                    : 'تم إنشاء المعاينة بنجاح.'
            ]);
        } catch (Exception $e) {
            Log::error('Generate Intelligence Preview - error', [
                'request_id' => isset($rid) ? $rid : null, 
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate intelligence preview',
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

                // Save campaign strategy if available
                if (isset($payload['campaign_strategy'])) {
                    $campaign->update([
                        'campaign_strategy' => $payload['campaign_strategy'],
                        'ai_analysis' => $payload['language_analysis'] ?? null
                    ]);
                }

                foreach ($posts as $index => $postData) {
                    // Extract content (flexible multi-language support)
                    $content = $postData['content'] ?? [];
                    $primaryLanguage = $postData['primary_language'] ?? 'ar';
                    
                    // Extract hashtags (flexible multi-language support)
                    $hashtags = $postData['hashtags'] ?? [];
                    
                    // Extract composition data
                    $compositionLayers = $postData['composition_layers'] ?? null;
                    $baseImageUrl = $postData['base_image_url'] ?? null;
                    $isComposed = $postData['is_composed'] ?? false;
                    
                    CampaignPost::create([
                        'campaign_id' => $campaign->id,
                        'platform' => $postData['platform'] ?? 'instagram',
                        'post_type' => $postData['post_type'] ?? 'image',
                        'content' => $content,
                        'primary_language' => $primaryLanguage,
                        'hashtags' => $hashtags,
                        'media_urls' => isset($postData['image_url']) ? [$postData['image_url']] : [],
                        'media_prompts' => isset($postData['image_prompt']) ? [$postData['image_prompt']] : [],
                        'scheduled_date' => null,
                        'status' => 'pending',
                        'ai_prompt_used' => $postData['ai_prompt_used'] ?? null,
                        'ai_tokens_used' => $postData['tokens_used'] ?? 0,
                        'ai_cost' => $postData['cost'] ?? 0,
                        'order_number' => $index + 1,
                        'week_number' => $postData['week'] ?? 1,
                        'day_of_week' => $postData['day'] ?? null,
                        'day_number' => $postData['day'] ?? null,
                        'day_name' => $postData['day_name'] ?? null,
                        'phase_name' => $postData['phase'] ?? null,
                        'content_brief' => $postData['content_brief'] ?? null,
                        'composition_layers' => $compositionLayers,
                        'base_image_url' => $baseImageUrl,
                        'is_composed' => $isComposed
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
            $user = Auth::user();
            if ($campaign->organization_id && $campaign->organization_id !== optional($user->organization)->id) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'goal' => 'sometimes|string',
                'mode' => 'sometimes|in:quick,advanced',
                'target_audience' => 'sometimes|array',
                'platforms' => 'sometimes|array',
                'duration_days' => 'sometimes|integer|min:7',
                'duration_weeks' => 'sometimes|integer|min:1',
                'posts_per_week' => 'sometimes|integer|min:1|max:7',
                'languages' => 'sometimes|array',
                'brand_id' => 'sometimes|exists:brands,id',
                'wizard_step' => 'sometimes|integer|min:1|max:10',
                'wizard_data' => 'sometimes|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $payload = $request->only([
                'name',
                'description',
                'goal',
                'mode',
                'target_audience',
                'platforms',
                'duration_days',
                'posts_per_week',
                'languages',
                'brand_id'
            ]);

            if ($request->has('duration_weeks')) {
                $payload['duration_days'] = $request->duration_weeks * 7;
            }

            if ($request->has('wizard_step')) {
                $payload['wizard_step'] = $request->wizard_step;
            }

            if ($request->has('wizard_data')) {
                $existingWizardData = $campaign->wizard_data ?? [];
                if (!is_array($existingWizardData)) {
                    $existingWizardData = (array) $existingWizardData;
                }
                $payload['wizard_data'] = array_replace_recursive($existingWizardData, $request->wizard_data);
            }

            if ($request->has('target_audience')) {
                $payload['target_audience'] = $request->target_audience ?? [];
            }

            if ($request->has('platforms')) {
                $payload['platforms'] = $request->platforms ?? [];
            }

            if ($request->has('languages')) {
                $payload['languages'] = $request->languages ?? ['ar'];
            }

            $campaign->fill($payload);

            // Auto-advance campaign status based on wizard progress
            if (array_key_exists('wizard_step', $payload)) {
                $nextStep = $payload['wizard_step'];
                if ($campaign->status === 'draft' && $nextStep >= 2) {
                    $campaign->status = 'building';
                }
                if ($campaign->status === 'building' && $nextStep < 2) {
                    $campaign->status = 'draft';
                }
            }

            $campaign->save();

            return response()->json([
                'success' => true,
                'message' => 'Campaign updated successfully',
                'data' => $campaign->fresh()->load('brand')
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
            $user = Auth::user();
            
            // Check ownership
            if ($campaign->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
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
    protected function resolveCampaignField(Campaign $campaign, string $field, $default = null)
    {
        if (!empty($campaign->{$field})) {
            return $campaign->{$field};
        }

        $wizardData = $campaign->wizard_data ?? [];
        if (!is_array($wizardData)) {
            $wizardData = (array) $wizardData;
        }

        foreach ($wizardData as $step) {
            if (!is_array($step)) {
                continue;
            }

            if (array_key_exists($field, $step) && !empty($step[$field])) {
                return $step[$field];
            }

            if ($field === 'name' && !empty($step['product_name'])) {
                return $step['product_name'];
            }

            if ($field === 'product_name' && !empty($step['name'])) {
                return $step['name'];
            }

            if ($field === 'brand_voice' && !empty($step['brand_voice'])) {
                return $step['brand_voice'];
            }
        }

        return $default;
    }

    protected function normalizeTargetAudience($audience, Campaign $campaign): array
    {
        if (is_string($audience)) {
            $decoded = json_decode($audience, true);
            $audience = is_array($decoded) ? $decoded : ['summary' => trim($audience)];
        }

        if (!is_array($audience) || empty($audience)) {
            $wizardAudience = $this->resolveCampaignField($campaign, 'target_audience', []);
            $audience = is_array($wizardAudience) ? $wizardAudience : [];
        }

        if (!Arr::isAssoc($audience)) {
            $audience = ['segments' => array_values($audience)];
        }

        $normalized = [
            'summary' => $audience['summary'] ?? $audience['description'] ?? null,
            'age_range' => $audience['age_range'] ?? null,
            'gender' => $audience['gender'] ?? null,
            'location' => $audience['location'] ?? null,
            'interests' => [],
            'personas' => [],
            'segments' => [],
        ];

        $interests = $audience['interests'] ?? [];
        if (!is_array($interests)) {
            $interests = [$interests];
        }
        $normalized['interests'] = array_values(array_filter($interests, fn ($interest) => !empty($interest)));

        $segments = $audience['segments'] ?? [];
        if (!is_array($segments)) {
            $segments = [$segments];
        }
        $normalized['segments'] = array_values(array_filter($segments, fn ($segment) => !empty($segment)));

        $personas = $audience['personas'] ?? [];
        if (!is_array($personas)) {
            $personas = [$personas];
        }
        $normalized['personas'] = array_values(array_map(function ($persona) {
            if (!is_array($persona)) {
                return [
                    'name' => null,
                    'description' => $persona,
                ];
            }
            return $persona;
        }, array_filter($personas, fn ($persona) => !empty($persona))));

        if (!$normalized['summary']) {
            $product = $campaign->name ?? $campaign->business_type ?? 'المنتج';
            $normalized['summary'] = "جمهور عام مهتم بـ {$product}";
        }

        return $normalized;
    }

    protected function buildFallbackIntelligence(array $aiData, string $errorMessage = ''): array
    {
        $durationDays = (int) ($aiData['duration_days'] ?? 28);
        $durationWeeks = max(1, (int) ceil($durationDays / 7));
        $postsPerWeek = max(1, (int) ($aiData['posts_per_week'] ?? 3));
        $totalPosts = $durationWeeks * $postsPerWeek;
        $platforms = $aiData['platforms'] ?? ['instagram'];
        if (!is_array($platforms) || count($platforms) === 0) {
            $platforms = ['instagram'];
        }

        $productName = $aiData['product_name'] ?? ($aiData['business_type'] ?? 'الحملة');
        $goal = $aiData['goal'] ?? 'awareness';
        $tone = $aiData['tone_of_voice'] ?? 'ودود وملهم';
        $businessType = $aiData['business_type'] ?? 'brand';

        $phaseTemplates = [
            [
                'name' => 'إطلاق ووعي',
                'objective' => 'جذب الانتباه وتعريف الجمهور بالقيمة المميزة',
                'strategy' => 'استخدم محتوى بصري قوي يبرز الهوية ويخلق ترقب مبكر للحملة.',
                'content_mix' => [
                    'Reels' => '40%',
                    'Stories' => '30%',
                    'Static Posts' => '30%'
                ],
                'key_messages' => [
                    'من نحن وما الذي يجعلنا مختلفين',
                    'لماذا التجربة مختلفة مع ' . $productName,
                    'عروض افتتاحية ومزايا مبكرة'
                ]
            ],
            [
                'name' => 'تجربة وتفاعل',
                'objective' => 'تحفيز الجمهور على التفاعل مع المحتوى ومشاركته.',
                'strategy' => 'قدّم محتوى يتضمن تجارب عملاء، لقطات ما وراء الكواليس، وتحديات تحفّز المشاركة.',
                'content_mix' => [
                    'Stories' => '40%',
                    'Reels' => '35%',
                    'Live' => '25%'
                ],
                'key_messages' => [
                    'قصص عملاء وتجارب حقيقية',
                    'تحديات ومحتوى تفاعلي مع الجمهور',
                    'دعوة لمشاركة المحتوى أو زيارة الفرع'
                ]
            ],
            [
                'name' => 'تحويل وولاء',
                'objective' => 'تحويل المتابعين إلى عملاء حقيقيين وتعزيز الولاء.',
                'strategy' => 'ركّز على إثبات القيمة، التوصيات، والعروض المحدودة لتحفيز الشراء المتكرر.',
                'content_mix' => [
                    'Static Posts' => '35%',
                    'Reels' => '35%',
                    'Stories' => '30%'
                ],
                'key_messages' => [
                    'إثبات اجتماعي ومراجعات العملاء',
                    'عروض حصرية محدودة بزمن',
                    'دعوة لاتخاذ إجراء واضح وسريع'
                ]
            ],
        ];

        $phaseCount = min($durationWeeks, count($phaseTemplates));
        $campaignPhases = [];
        for ($i = 0; $i < $phaseCount; $i++) {
            $template = $phaseTemplates[$i];
            $template['phase'] = $i + 1;
            $template['duration'] = 'الأسبوع ' . ($i + 1);
            $campaignPhases[] = $template;
        }

        $weekThemes = [
            'قصص من ' . $productName,
            'تجارب العملاء الحقيقية',
            'العروض الخاصة والأحداث'
        ];

        $dayNames = ['الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت', 'الأحد'];
        $timeline = [];
        $postCounter = 0;

        for ($week = 1; $week <= $durationWeeks; $week++) {
            $days = [];
            for ($day = 1; $day <= 7; $day++) {
                $days[] = [
                    'day' => $day,
                    'day_name' => $dayNames[($day - 1) % 7],
                    'posts' => []
                ];
            }

            for ($slot = 0; $slot < $postsPerWeek; $slot++) {
                $targetDay = ($slot * 2) % 7;
                $platform = $platforms[$slot % count($platforms)];
                $days[$targetDay]['posts'][] = [
                    'time' => '18:00',
                    'platform' => $platform,
                    'type' => $slot % 2 === 0 ? 'Reel' : 'Story'
                ];
                $postCounter++;
            }

            $timeline[] = [
                'name' => 'الأسبوع ' . $week,
                'theme' => $weekThemes[($week - 1) % count($weekThemes)],
                'days' => $days
            ];
        }

        $samplePosts = [];
        for ($i = 0; $i < min(3, $totalPosts); $i++) {
            $samplePosts[] = [
                'platform' => $platforms[$i % count($platforms)],
                'week' => ($i % $durationWeeks) + 1,
                'day' => ($i % 7) + 1,
                'phase' => $campaignPhases[$i % max(1, count($campaignPhases))]['name'] ?? 'مرحلة الحملة',
                'primary_language' => 'ar',
                'content' => [
                    'ar' => 'منشور نموذجي يبرز ' . $productName . ' ويستخدم نبرة ' . $tone . '.'
                ],
                'hashtags' => ['#' . Str::slug($productName, '_'), '#MarketaAI'],
                'content_brief' => [
                    'instructions' => [
                        'overview' => 'التقط مشهداً يعكس هوية العلامة وركّز على إبراز القيمة المضافة للجمهور.'
                    ],
                    'filming' => [
                        'shots' => ['زاوية علوية للمنتج', 'لقطة قريبة للتفاصيل'],
                        'lighting' => 'إضاءة دافئة تُبرز الألوان الطبيعية.'
                    ],
                    'editing' => [
                        'transitions' => 'استخدم انتقالات سريعة تحافظ على الإيقاع.',
                        'music' => 'موسيقى حماسية بإيقاع متوسط.'
                    ],
                    'engagement_tips' => [
                        'اطرح سؤالاً في نهاية المنشور لتحفيز التفاعل.',
                        'استخدم ملصقات التفاعل (Poll/Quiz) في القصص.'
                    ]
                ],
                'expected_results' => [
                    'reach' => '10k - 15k',
                    'engagement_rate' => '6% - 8%',
                    'saves' => '200+'
                ],
            ];
        }

        return [
            'metadata' => [
                'fallback' => true,
                'fallback_reason' => $errorMessage,
                'fallback_message' => 'تم إنشاء خطة سريعة لضمان استمرارية العمل، يمكنك الاعتماد عليها الآن وإعادة التوليد لاحقاً إذا رغبت.',
                'generated_at' => now()->toIso8601String(),
                'mode' => $aiData['mode'] ?? 'quick',
                'engine' => 'rule-based-fallback'
            ],
            'executive_summary' => [
                'campaign_name' => $productName,
                'objective' => $goal === 'sales' ? 'زيادة المبيعات والتحويلات' : 'تعزيز الوعي والتفاعل',
                'duration' => $durationWeeks . ' أسابيع (' . $durationDays . ' يوم)',
                'total_posts' => $totalPosts,
                'target_kpis' => [
                    'reach' => number_format($totalPosts * 3500) . '+',
                    'engagement_rate' => '5% - 7%',
                    'conversions' => $goal === 'sales' ? number_format($totalPosts * 45) . '+' : null,
                ]
            ],
            'language_analysis' => [
                'detected_languages' => ['ar'],
                'audience_location' => $aiData['target_audience']['location'] ?? 'السعودية',
                'audience_age' => $aiData['target_audience']['age_range'] ?? '18-35',
                'tone' => $tone
            ],
            'campaign_phases' => $campaignPhases,
            'daily_calendar' => $timeline,
            'sample_posts' => $samplePosts,
            'content_guidelines' => [
                'visual_style' => 'استخدم ألوان الهوية مع إضاءة دافئة ولمسات مستقبلية بسيطة لإبراز ' . $businessType,
                'tone_of_voice' => $tone,
                'colors' => ['#0B6E99', '#0F7B6C', '#D9730D']
            ],
            'estimated_metrics' => [
                'total_reach' => number_format($totalPosts * 3000) . ' - ' . number_format($totalPosts * 4500),
                'engagement_rate' => '5% - 8%',
                'estimated_cost' => number_format($totalPosts * 120) . ' SAR',
                'generation_time' => '< 10 ثواني (Fallback)'
            ]
        ];
    }

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

    /**
     * Attach existing design to campaign
     */
    public function attachDesign(Request $request, $uuid): JsonResponse
    {
        try {
            $campaign = Campaign::where('uuid', $uuid)->firstOrFail();
            
            // Check ownership
            if ($campaign->organization_id !== Auth::user()->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'design_uuid' => 'required|exists:designs,uuid',
                'platform' => 'required|string',
                'scheduled_date' => 'nullable|date',
                'scheduled_time' => 'nullable|date_format:H:i',
                'post_content_ar' => 'nullable|string',
                'post_content_en' => 'nullable|string',
                'hashtags' => 'nullable|string',
                'order' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $design = Design::where('uuid', $request->design_uuid)->firstOrFail();

            // Attach with pivot data
            $campaign->designs()->syncWithoutDetaching([
                $design->id => [
                    'platform' => $request->platform,
                    'scheduled_date' => $request->scheduled_date,
                    'scheduled_time' => $request->scheduled_time,
                    'post_content_ar' => $request->post_content_ar,
                    'post_content_en' => $request->post_content_en,
                    'hashtags' => $request->hashtags,
                    'order' => $request->order ?? 0,
                    'status' => 'pending',
                ]
            ]);

            // Increment design usage count
            $design->incrementUsage();

            return response()->json([
                'success' => true,
                'message' => 'Design attached to campaign successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to attach design',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detach design from campaign
     */
    public function detachDesign($campaignUuid, $designUuid): JsonResponse
    {
        try {
            $campaign = Campaign::where('uuid', $campaignUuid)->firstOrFail();
            
            // Check ownership
            if ($campaign->organization_id !== Auth::user()->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $design = Design::where('uuid', $designUuid)->firstOrFail();

            // Detach
            $campaign->designs()->detach($design->id);

            return response()->json([
                'success' => true,
                'message' => 'Design detached from campaign successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to detach design',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}