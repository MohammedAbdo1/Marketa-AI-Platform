<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampaignPost;
use App\Services\PythonAIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class CampaignPostController extends Controller
{
    protected $aiService;

    public function __construct(PythonAIService $aiService)
    {
        $this->aiService = $aiService;
    }
    /**
     * Display the specified post.
     */
    public function show(Request $request, $id)
    {
        $post = CampaignPost::with('campaign')
            ->whereHas('campaign', function ($query) use ($request) {
                $query->where('organization_id', $request->user()->organization_id);
            })
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'content_ar' => 'nullable|string',
            'content_en' => 'nullable|string',
            'hashtags' => 'nullable|string',
            'media_urls' => 'nullable|array',
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable|date_format:H:i',
        ]);

        $post = CampaignPost::whereHas('campaign', function ($query) use ($request) {
            $query->where('organization_id', $request->user()->organization_id);
        })->findOrFail($id);

        $post->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => $post,
        ]);
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Request $request, $id)
    {
        $post = CampaignPost::whereHas('campaign', function ($query) use ($request) {
            $query->where('organization_id', $request->user()->organization_id);
        })->findOrFail($id);

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }

    /**
     * Approve a post.
     */
    public function approve(Request $request, $id)
    {
        $post = CampaignPost::whereHas('campaign', function ($query) use ($request) {
            $query->where('organization_id', $request->user()->organization_id);
        })->findOrFail($id);

        $post->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'Post approved successfully',
            'data' => $post,
        ]);
    }

    /**
     * Reject a post.
     */
    public function reject(Request $request, $id)
    {
        $post = CampaignPost::whereHas('campaign', function ($query) use ($request) {
            $query->where('organization_id', $request->user()->organization_id);
        })->findOrFail($id);

        $post->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Post rejected successfully',
            'data' => $post,
        ]);
    }

    /**
     * Regenerate a post using AI.
     */
    public function regenerate(Request $request, $id)
    {
        // TODO: Implement AI regeneration

        return response()->json([
            'success' => true,
            'message' => 'Post regeneration started',
        ]);
    }

    /**
     * Generate media for a post.
     */
    public function generateMedia(Request $request, $id)
    {
        $validated = $request->validate([
            'prompt' => 'required|string',
        ]);

        // TODO: Implement AI image generation

        return response()->json([
            'success' => true,
            'message' => 'Media generation started',
        ]);
    }

    /**
     * Schedule a post.
     */
    public function schedule(Request $request, $id)
    {
        $validated = $request->validate([
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|date_format:H:i',
        ]);

        $post = CampaignPost::whereHas('campaign', function ($query) use ($request) {
            $query->where('organization_id', $request->user()->organization_id);
        })->findOrFail($id);

        $post->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Post scheduled successfully',
            'data' => $post,
        ]);
    }
    
    // ========================================
    // Layer Management & Composition Endpoints
    // ========================================
    
    /**
     * Export layers for editor
     */
    public function exportLayers(Request $request, $id): JsonResponse
    {
        try {
            $post = CampaignPost::whereHas('campaign', function ($query) use ($request) {
                $query->where('organization_id', $request->user()->organization_id);
            })->findOrFail($id);

            $editorData = $post->exportForEditor();

            return response()->json([
                'success' => true,
                'data' => $editorData
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export layers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Add new layer to post
     */
    public function addLayer(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:text,image',
                'content' => 'nullable|string',
                'position' => 'required|array',
                'style' => 'nullable|array'
            ]);

            $post = CampaignPost::whereHas('campaign', function ($query) use ($request) {
                $query->where('organization_id', $request->user()->organization_id);
            })->findOrFail($id);

            $post->addLayer($validated);

            return response()->json([
                'success' => true,
                'message' => 'Layer added successfully',
                'data' => $post->exportForEditor()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add layer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update specific layer
     */
    public function updateLayer(Request $request, $id, $layerIndex): JsonResponse
    {
        try {
            $validated = $request->validate([
                'content' => 'nullable|string',
                'position' => 'nullable|array',
                'style' => 'nullable|array'
            ]);

            $post = CampaignPost::whereHas('campaign', function ($query) use ($request) {
                $query->where('organization_id', $request->user()->organization_id);
            })->findOrFail($id);

            $post->updateLayer((int)$layerIndex, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Layer updated successfully',
                'data' => $post->exportForEditor()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update layer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove layer from post
     */
    public function removeLayer(Request $request, $id, $layerIndex): JsonResponse
    {
        try {
            $post = CampaignPost::whereHas('campaign', function ($query) use ($request) {
                $query->where('organization_id', $request->user()->organization_id);
            })->findOrFail($id);

            $post->removeLayer((int)$layerIndex);

            return response()->json([
                'success' => true,
                'message' => 'Layer removed successfully',
                'data' => $post->exportForEditor()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove layer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Import layers from editor (save edited composition)
     */
    public function importLayers(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'layers' => 'required|array',
                'dimensions' => 'nullable|array'
            ]);

            $post = CampaignPost::whereHas('campaign', function ($query) use ($request) {
                $query->where('organization_id', $request->user()->organization_id);
            })->findOrFail($id);

            // Update composition layers
            $post->composition_layers = [
                'layers' => $validated['layers'],
                'dimensions' => $validated['dimensions'] ?? $post->composition_layers['dimensions'] ?? []
            ];
            $post->save();

            return response()->json([
                'success' => true,
                'message' => 'Layers imported successfully',
                'data' => $post->exportForEditor()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import layers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Regenerate specific layer using AI
     */
    public function regenerateLayer(Request $request, $id, $layerIndex): JsonResponse
    {
        try {
            $validated = $request->validate([
                'changes' => 'nullable|array'
            ]);

            $post = CampaignPost::whereHas('campaign', function ($query) use ($request) {
                $query->where('organization_id', $request->user()->organization_id);
            })->findOrFail($id);

            // Call AI service to regenerate layer
            $result = $this->aiService->regenerateLayer(
                $post->id,
                (int)$layerIndex,
                $validated['changes'] ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Layer regenerated successfully',
                'data' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate layer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
