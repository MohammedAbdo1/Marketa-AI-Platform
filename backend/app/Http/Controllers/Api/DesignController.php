<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CreativeAsset;
use App\Models\UserFavorite;
use App\Services\CreativeAssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DesignController extends Controller
{
    public function __construct(protected CreativeAssetService $creativeAssetService)
    {
    }
    /**
     * Display a listing of the user's designs.
     */
    public function index(Request $request)
    {
        $query = $this->creativeAssetService
            ->designQuery()
            ->ownedBy(Auth::id())
                      ->notTrashed()
            ->with('user:id,name,email');

        // Filters
        if ($request->has('type')) {
            $query->where('subtype', $request->type);
        }

        if ($request->has('source_type')) {
            $query->where('source_type', $request->source_type);
        }

        if ($request->has('is_template')) {
            $query->where('is_template', $request->boolean('is_template'));
        }

        // Search
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);
        $designs = $query->paginate($perPage, ['*'], 'page', $page);

        // Add is_favorited flag and ensure composition_data is included
        $userId = Auth::id();
        $designs->getCollection()->transform(function (CreativeAsset $asset) use ($userId) {
            $formatted = $this->creativeAssetService->formatDesignAsset($asset, $userId);
            $formatted['is_favorited'] = $asset->favorites()
                ->where('user_id', $userId)
                ->exists();
            return $formatted;
        });

        return response()->json($designs);
    }

    /**
     * Store a newly created design.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'design_type' => 'required|in:social_post,story,presentation,banner,custom',
            'source_type' => 'required|in:ai,manual,template,imported',
            'source_id' => 'nullable|string',
            'source_type_model' => 'nullable|string',
            'composition_data' => 'required|array',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'canvas_settings' => 'nullable|array',
            'metadata' => 'nullable|array',
            'tags' => 'nullable|array',
            'thumbnail_url' => 'nullable|string',
            'export_url' => 'nullable|string',
            'context_type' => 'nullable|string',
            'context_id' => 'nullable|integer',
            'is_template' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $data = $validator->validated();
        $asset = $this->creativeAssetService->createDesignAsset(
            $data,
            $user->id,
            $user->organization_id
        );

        return response()->json([
            'message' => 'Design created successfully',
            'design' => $this->creativeAssetService->formatDesignAsset($asset, $user->id)
        ], 201);
    }

    /**
     * Display the specified design.
     */
    public function show($uuid)
    {
        $asset = $this->creativeAssetService
            ->designQuery()
            ->where('uuid', $uuid)
            ->with(['user:id,name,email', 'campaigns:id,uuid,name'])
                       ->firstOrFail();

        if ($asset->user_id !== Auth::id() && !$asset->is_public) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->creativeAssetService->incrementDesignViews($asset, Auth::id());

        $payload = $this->creativeAssetService->formatDesignAsset($asset, Auth::id());
        $payload['campaigns'] = $asset->campaigns->map(function (Campaign $campaign) {
            return [
                'id' => $campaign->id,
                'uuid' => $campaign->uuid,
                'name' => $campaign->name,
            ];
        })->values();

        return response()->json($payload);
    }

    /**
     * Update the specified design.
     */
    public function update(Request $request, $uuid)
    {
        $asset = $this->creativeAssetService
            ->designQuery()
            ->where('uuid', $uuid)
            ->firstOrFail();

        if ($asset->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'composition_data' => 'nullable|array',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'canvas_settings' => 'nullable|array',
            'metadata' => 'nullable|array',
            'tags' => 'nullable|array',
            'thumbnail_url' => 'nullable|string',
            'preview_url' => 'nullable|string',
            'export_url' => 'nullable|string',
            'design_type' => 'nullable|in:social_post,story,presentation,banner,custom',
            'is_template' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $this->creativeAssetService->updateDesignAsset($asset, $validator->validated());

        return response()->json([
            'message' => 'Design updated successfully',
            'design' => $this->creativeAssetService->formatDesignAsset($asset, Auth::id())
        ]);
    }

    /**
     * Remove the specified design.
     */
    public function destroy($uuid)
    {
        $asset = $this->creativeAssetService
            ->designQuery()
            ->where('uuid', $uuid)
            ->firstOrFail();

        if ($asset->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $campaignCount = $asset->campaigns()->count();
        if ($campaignCount > 0) {
            return response()->json([
                'message' => 'Cannot delete design used in campaigns',
                'campaign_count' => $campaignCount
            ], 409);
        }

        $this->creativeAssetService->delete($asset);

        return response()->json([
            'message' => 'Design deleted successfully'
        ]);
    }

    /**
     * Duplicate the specified design.
     */
    public function duplicate($uuid)
    {
        $asset = $this->creativeAssetService
            ->designQuery()
            ->where('uuid', $uuid)
            ->firstOrFail();

        if ($asset->user_id !== Auth::id() && !$asset->is_public) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = Auth::user();
        $duplicate = $this->creativeAssetService->duplicateDesignAsset(
            $asset,
            $user->id,
            $user->organization_id
        );

        return response()->json([
            'message' => 'Design duplicated successfully',
            'design' => $this->creativeAssetService->formatDesignAsset($duplicate, $user->id)
        ], 201);
    }

    /**
     * Export design as image.
     */
    public function export($uuid)
    {
        $asset = $this->creativeAssetService
            ->designQuery()
            ->where('uuid', $uuid)
            ->firstOrFail();

        if ($asset->user_id !== Auth::id() && !$asset->is_public) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // TODO: Implement image export logic via AI service

        return response()->json([
            'message' => 'Export initiated',
            'export_url' => $asset->export_url
        ]);
    }

    /**
     * Convert design to public template.
     */
    public function toTemplate($uuid)
    {
        $asset = $this->creativeAssetService
            ->designQuery()
            ->where('uuid', $uuid)
            ->firstOrFail();

        if ($asset->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->creativeAssetService->updateDesignAsset($asset, [
            'is_template' => true,
            'is_public' => true,
        ]);

        return response()->json([
            'message' => 'Design converted to template successfully',
            'design' => $this->creativeAssetService->formatDesignAsset($asset, Auth::id())
        ]);
    }

    /**
     * Update design title only
     */
    public function updateTitle(Request $request, $uuid)
    {
        $asset = $this->creativeAssetService
            ->designQuery()
            ->where('uuid', $uuid)
            ->firstOrFail();

        if ($asset->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $this->creativeAssetService->updateDesignAsset($asset, [
            'title' => $request->title,
        ]);

        return response()->json([
            'message' => 'Title updated successfully',
            'design' => $this->creativeAssetService->formatDesignAsset($asset, Auth::id())
        ]);
    }

    /**
     * Get trashed designs
     */
    public function trashed(Request $request)
    {
        $query = $this->creativeAssetService
            ->designQuery()
            ->ownedBy(Auth::id())
                      ->trashed()
            ->with('user:id,name,email')
            ->orderBy('trashed_at', 'desc');

        $perPage = $request->get('per_page', 20);
        $designs = $query->paginate($perPage);

        $userId = Auth::id();
        $designs->getCollection()->transform(function (CreativeAsset $asset) use ($userId) {
            return $this->creativeAssetService->formatDesignAsset($asset, $userId);
        });

        return response()->json($designs);
    }

    /**
     * Move design to trash
     */
    public function moveToTrash($uuid)
    {
        $asset = $this->creativeAssetService
            ->designQuery()
            ->where('uuid', $uuid)
            ->firstOrFail();

        if ($asset->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->creativeAssetService->trashDesignAsset($asset);

        return response()->json([
            'message' => 'Design moved to trash successfully',
            'design' => $this->creativeAssetService->formatDesignAsset($asset, Auth::id())
        ]);
    }

    /**
     * Restore design from trash
     */
    public function restore($uuid)
    {
        $asset = $this->creativeAssetService
            ->designQuery()
            ->where('uuid', $uuid)
            ->firstOrFail();

        if ($asset->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->creativeAssetService->restoreDesignAsset($asset);

        return response()->json([
            'message' => 'Design restored successfully',
            'design' => $this->creativeAssetService->formatDesignAsset($asset, Auth::id())
        ]);
    }

    /**
     * Force delete design permanently
     */
    public function forceDelete($uuid)
    {
        $asset = $this->creativeAssetService
            ->designQuery()
            ->where('uuid', $uuid)
            ->withTrashed()
            ->firstOrFail();

        if ($asset->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $asset->forceDelete();

        return response()->json([
            'message' => 'Design permanently deleted'
        ]);
    }

    /**
     * Get public templates.
     */
    public function templates(Request $request)
    {
        $query = $this->creativeAssetService
            ->designQuery()
            ->where('is_template', true)
            ->where('is_public', true)
                      ->with('user:id,name');

        // Filter by type
        if ($request->has('type')) {
            $query->where('subtype', $request->type);
        }

        // Search
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Sort by popularity
        $query->orderBy('used_count', 'desc')
              ->orderBy('created_at', 'desc');

        $templates = $query->paginate(20);
        $userId = Auth::id();
        $templates->getCollection()->transform(function (CreativeAsset $asset) use ($userId) {
            return $this->creativeAssetService->formatDesignAsset($asset, $userId);
        });

        return response()->json($templates);
    }
}

