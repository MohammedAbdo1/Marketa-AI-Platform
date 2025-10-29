<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampaignPost;
use Illuminate\Http\Request;

class CampaignPostController extends Controller
{
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
}
