<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DesignController extends Controller
{
    /**
     * Display a listing of the user's designs.
     */
    public function index(Request $request)
    {
        $query = Design::ownedBy(Auth::id())
                      ->with('user:id,name,email');

        // Filters
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        if ($request->has('source_type')) {
            $query->fromSource($request->source_type);
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
        $designs = $query->paginate($perPage);

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
            'composition_data' => 'required|array',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'canvas_settings' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $design = Design::create([
            'user_id' => Auth::id(),
            'title' => $request->title ?? 'Untitled Design',
            'description' => $request->description,
            'design_type' => $request->design_type,
            'source_type' => $request->source_type,
            'source_id' => $request->source_id,
            'source_type_model' => $request->source_type_model,
            'composition_data' => $request->composition_data,
            'width' => $request->width ?? 1080,
            'height' => $request->height ?? 1080,
            'canvas_settings' => $request->canvas_settings,
            'metadata' => $request->metadata,
            'context_type' => $request->context_type,
            'context_id' => $request->context_id,
        ]);

        return response()->json([
            'message' => 'Design created successfully',
            'design' => $design
        ], 201);
    }

    /**
     * Display the specified design.
     */
    public function show($uuid)
    {
        $design = Design::where('uuid', $uuid)
                       ->with(['user:id,name,email', 'campaigns'])
                       ->firstOrFail();

        // Check ownership or public
        if ($design->user_id !== Auth::id() && !$design->is_public) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Increment views
        if ($design->user_id !== Auth::id()) {
            $design->incrementViews();
        }

        return response()->json($design);
    }

    /**
     * Update the specified design.
     */
    public function update(Request $request, $uuid)
    {
        $design = Design::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if ($design->user_id !== Auth::id()) {
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
            'thumbnail_url' => 'nullable|string',
            'export_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $design->update($request->only([
            'title',
            'description',
            'composition_data',
            'width',
            'height',
            'canvas_settings',
            'metadata',
            'thumbnail_url',
            'export_url',
        ]));

        return response()->json([
            'message' => 'Design updated successfully',
            'design' => $design
        ]);
    }

    /**
     * Remove the specified design.
     */
    public function destroy($uuid)
    {
        $design = Design::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if ($design->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if used in active campaigns
        $campaignCount = $design->campaigns()->count();
        if ($campaignCount > 0) {
            return response()->json([
                'message' => 'Cannot delete design used in campaigns',
                'campaign_count' => $campaignCount
            ], 409);
        }

        $design->delete();

        return response()->json([
            'message' => 'Design deleted successfully'
        ]);
    }

    /**
     * Duplicate the specified design.
     */
    public function duplicate($uuid)
    {
        $design = Design::where('uuid', $uuid)->firstOrFail();

        // Check ownership or public
        if ($design->user_id !== Auth::id() && !$design->is_public) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $newDesign = $design->duplicate(Auth::user());

        return response()->json([
            'message' => 'Design duplicated successfully',
            'design' => $newDesign
        ], 201);
    }

    /**
     * Export design as image.
     */
    public function export($uuid)
    {
        $design = Design::where('uuid', $uuid)->firstOrFail();

        // Check ownership or public
        if ($design->user_id !== Auth::id() && !$design->is_public) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // TODO: Implement image export logic
        // This would call the AI service to render the composition_data to an image

        return response()->json([
            'message' => 'Export initiated',
            'export_url' => $design->export_url
        ]);
    }

    /**
     * Convert design to public template.
     */
    public function toTemplate($uuid)
    {
        $design = Design::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if ($design->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $design->toTemplate();

        return response()->json([
            'message' => 'Design converted to template successfully',
            'design' => $design
        ]);
    }

    /**
     * Get public templates.
     */
    public function templates(Request $request)
    {
        $query = Design::templates()
                      ->with('user:id,name');

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->type);
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

        return response()->json($templates);
    }
}

