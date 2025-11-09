<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Services\CreativeAssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiConversationController extends Controller
{
    public function __construct(protected CreativeAssetService $creativeAssetService)
    {
    }
    /**
     * Display a listing of the user's conversations.
     */
    public function index(Request $request)
    {
        $query = AiConversation::ownedBy(Auth::id())
                              ->with('messages:id,conversation_id,role,content,created_at')
                              ->recent();

        // Filter by design type
        if ($request->has('design_type')) {
            $query->where('design_type', $request->design_type);
        }

        // Search
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where('title', 'like', "%{$searchTerm}%");
        }

        $perPage = $request->get('per_page', 20);
        $conversations = $query->paginate($perPage);

        // Transform to summaries
        $conversations->getCollection()->transform(function ($conversation) {
            return $conversation->getSummary();
        });

        return response()->json($conversations);
    }

    /**
     * Store a newly created conversation.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'design_type' => 'required|in:social_post,story,presentation,banner,custom',
            'initial_message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $conversation = AiConversation::create([
            'user_id' => Auth::id(),
            'design_type' => $request->design_type,
            'last_message_at' => now(),
        ]);

        // Add system message in Arabic
        $conversation->addMessage(
            'system',
            'مرحباً! سأساعدك في إنشاء تصاميم رائعة. ماذا تريد أن تُنشئ اليوم؟',
            [],
            [
                'أضف صورة ماكينة خياطة حديثة',
                'استخدم ألوان عصرية',
                'المزيد من التصاميم',
            ]
        );

        // Add initial user message if provided and generate AI response
        if ($request->initial_message) {
            $userMessage = $conversation->addMessage('user', $request->initial_message);
            
            // Generate AI response
            $aiResponse = $this->generateAiResponse($conversation, $request->initial_message);
            $conversation->addMessage(
                'assistant',
                $aiResponse['content'],
                $aiResponse['designs'],
                $aiResponse['suggestions']
            );
        }

        return response()->json([
            'message' => 'Conversation created successfully',
            'conversation' => $conversation->exportFull()
        ], 201);
    }

    /**
     * Display the specified conversation.
     */
    public function show($uuid)
    {
        $conversation = AiConversation::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($conversation->exportFull());
    }

    /**
     * Update the specified conversation.
     */
    public function update(Request $request, $uuid)
    {
        $conversation = AiConversation::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $conversation->update($request->only(['title']));

        return response()->json([
            'message' => 'Conversation updated successfully',
            'conversation' => $conversation
        ]);
    }

    /**
     * Remove the specified conversation.
     */
    public function destroy($uuid)
    {
        $conversation = AiConversation::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $conversation->delete();

        return response()->json([
            'message' => 'Conversation deleted successfully'
        ]);
    }

    /**
     * Send a message to the conversation and get AI response.
     */
    public function sendMessage(Request $request, $uuid)
    {
        $conversation = AiConversation::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Add user message
        $userMessage = $conversation->addMessage('user', $request->content);

        // TODO: Call AI service to generate response
        // For now, return a simple response
        $aiResponse = $this->generateAiResponse($conversation, $request->content);

        // Add assistant message
        $assistantMessage = $conversation->addMessage(
            'assistant',
            $aiResponse['content'],
            $aiResponse['designs'] ?? [],
            $aiResponse['suggestions'] ?? []
        );

        return response()->json([
            'message' => 'Message sent successfully',
            'user_message' => $userMessage->exportForFrontend(),
            'assistant_message' => $assistantMessage->exportForFrontend(),
        ]);
    }

    /**
     * Generate AI response with design generation.
     */
    private function generateAiResponse($conversation, $userMessage)
    {
        try {
            // Call Python AI Service to generate designs
            $aiServiceUrl = env('AI_SERVICE_URL', 'http://api:8001');
            
            $response = Http::timeout(120)->post("{$aiServiceUrl}/api/ai/conversation/message", [
                'content' => $userMessage,
                'design_type' => $conversation->design_type,
                'conversation_id' => $conversation->uuid,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Create Design objects for each generated image
                $createdDesigns = [];
                if (!empty($data['images'])) {
                    $conversation->loadMissing('user');
                    $organizationId = $conversation->user?->organization_id;

                    foreach ($data['images'] as $index => $imageData) {
                        $design = $this->creativeAssetService->createDesignAsset([
                            'title' => $imageData['title'] ?? 'Design ' . ($index + 1),
                            'description' => $userMessage,
                            'design_type' => $conversation->design_type,
                            'source_type' => 'ai',
                            'source_id' => $conversation->uuid,
                            'source_type_model' => 'ai_conversation',
                            'composition_data' => [
                                'layers' => [
                                    [
                                        'type' => 'image',
                                        'url' => $imageData['url'],
                                        'x' => 0,
                                        'y' => 0,
                                        'left' => 0,
                                        'top' => 0,
                                        'width' => 1080,
                                        'height' => 1080,
                                        'scaleX' => 1,
                                        'scaleY' => 1,
                                    ],
                                ],
                                'dimensions' => ['width' => 1080, 'height' => 1080],
                            ],
                            'thumbnail_url' => $imageData['url'],
                            'preview_url' => $imageData['url'],
                            'export_url' => $imageData['url'],
                            'width' => 1080,
                            'height' => 1080,
                            'metadata' => [
                                'prompt' => $userMessage,
                                'provider' => $imageData['provider'] ?? 'unknown',
                            ],
                            'tags' => $imageData['tags'] ?? [],
                        ], $conversation->user_id, $organizationId);

                        $createdDesigns[] = $design->uuid;
                    }
                }

                return [
                    'content' => $data['response'] ?? 'تم! إليك بعض التصاميم لك 🎨',
                    'designs' => $createdDesigns,
                    'suggestions' => $data['suggestions'] ?? [
                        'أضف المزيد من التفاصيل',
                        'غير الألوان',
                        'جرب نمط مختلف',
                        'أضف صورة ماكينة خياطة حديثة',
                        'بألوان عصرية',
                    ]
                ];
            }
        } catch (\Exception $e) {
            logger()->error('AI Service error: ' . $e->getMessage());
        }

        // Fallback response if AI service fails
        return [
            'content' => 'تمام! سأجهز لك تصاميم السوشيال ميديا الآن. انتظر لحظة معي...',
            'designs' => [],
            'suggestions' => [
                'أضف المزيد من التفاصيل',
                'غير الألوان',
                'جرب نمط مختلف',
            ]
        ];
    }
}

