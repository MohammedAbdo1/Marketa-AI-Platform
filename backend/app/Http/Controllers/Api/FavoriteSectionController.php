<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FavoriteSection;
use App\Models\UserFavorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FavoriteSectionController extends Controller
{
    /**
     * Get user's sections with favorites count
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $sections = FavoriteSection::where('user_id', $userId)
            ->withCount('favorites')
            ->orderBy('order')
            ->get();

        return response()->json($sections);
    }

    /**
     * Create new section
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'emoji' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::id();

        // Get next order number
        $maxOrder = FavoriteSection::where('user_id', $userId)
            ->max('order') ?? -1;

        $section = FavoriteSection::create([
            'user_id' => $userId,
            'organization_id' => Auth::user()->current_organization_id ?? null,
            'name' => $request->name ?? 'قسم بدون عنوان',
            'emoji' => $request->emoji ?? '📁',
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'message' => 'Section created successfully',
            'section' => $section
        ], 201);
    }

    /**
     * Update section
     */
    public function update(Request $request, $uuid)
    {
        $section = FavoriteSection::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if ($section->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'emoji' => 'nullable|string|max:10',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('name')) {
            $section->name = $request->name;
        }

        if ($request->has('emoji')) {
            $section->emoji = $request->emoji;
        }

        if ($request->has('order')) {
            $section->order = $request->order;
        }

        $section->save();

        return response()->json([
            'message' => 'Section updated successfully',
            'section' => $section
        ]);
    }

    /**
     * Delete section (set favorites section_id to null)
     */
    public function destroy($uuid)
    {
        $section = FavoriteSection::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if ($section->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Move all favorites to unsectioned
        UserFavorite::where('section_id', $section->id)
            ->update(['section_id' => null]);

        $section->delete();

        return response()->json([
            'message' => 'Section deleted successfully'
        ]);
    }

    /**
     * Reorder multiple sections
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sections' => 'required|array',
            'sections.*.uuid' => 'required|exists:favorite_sections,uuid',
            'sections.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::id();

        foreach ($request->sections as $sectionData) {
            FavoriteSection::where('uuid', $sectionData['uuid'])
                ->where('user_id', $userId)
                ->update(['order' => $sectionData['order']]);
        }

        return response()->json([
            'message' => 'Sections reordered successfully'
        ]);
    }
}

