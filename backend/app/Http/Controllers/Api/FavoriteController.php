<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Design;
use App\Models\UserFavorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    /**
     * Get user's favorites grouped by sections
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Get favorites with designs
        $favorites = UserFavorite::where('user_id', $userId)
            ->with(['design.user:id,name,email', 'section'])
            ->orderBy('order')
            ->get();

        // Group by sections
        $sections = DB::table('favorite_sections')
            ->where('user_id', $userId)
            ->orderBy('order')
            ->get();

        // Transform data
        $sectionsWithDesigns = $sections->map(function ($section) use ($favorites) {
            return [
                'uuid' => $section->uuid,
                'name' => $section->name,
                'emoji' => $section->emoji,
                'order' => $section->order,
                'designs' => $favorites->where('section_id', $section->id)
                    ->map(function ($favorite) {
                        $design = $favorite->design;
                        $design->is_favorited = true;
                        $design->favorite_order = $favorite->order;
                        return $design;
                    })
                    ->values()
            ];
        });

        // Get unsectioned favorites
        $unsectioned = $favorites->whereNull('section_id')
            ->map(function ($favorite) {
                $design = $favorite->design;
                $design->is_favorited = true;
                $design->favorite_order = $favorite->order;
                return $design;
            })
            ->values();

        return response()->json([
            'sections' => $sectionsWithDesigns,
            'unsectioned' => $unsectioned
        ]);
    }

    /**
     * Add design to favorites
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'design_id' => 'required|exists:designs,id',
            'section_id' => 'nullable|exists:favorite_sections,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::id();

        // Check if already favorited
        $existing = UserFavorite::where('user_id', $userId)
            ->where('design_id', $request->design_id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Design already in favorites'
            ], 409);
        }

        // Get next order number
        $maxOrder = UserFavorite::where('user_id', $userId)
            ->where('section_id', $request->section_id)
            ->max('order') ?? -1;

        $favorite = UserFavorite::create([
            'user_id' => $userId,
            'design_id' => $request->design_id,
            'section_id' => $request->section_id,
            'order' => $maxOrder + 1,
        ]);

        $favorite->load('design', 'section');

        return response()->json([
            'message' => 'Design added to favorites',
            'favorite' => $favorite
        ], 201);
    }

    /**
     * Remove design from favorites
     */
    public function destroy($designId)
    {
        $userId = Auth::id();

        $favorite = UserFavorite::where('user_id', $userId)
            ->where('design_id', $designId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'message' => 'Favorite not found'
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'message' => 'Design removed from favorites'
        ]);
    }

    /**
     * Update favorite (move to different section or reorder)
     */
    public function update(Request $request, $designId)
    {
        $validator = Validator::make($request->all(), [
            'section_id' => 'nullable|exists:favorite_sections,id',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::id();

        $favorite = UserFavorite::where('user_id', $userId)
            ->where('design_id', $designId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'message' => 'Favorite not found'
            ], 404);
        }

        if ($request->has('section_id')) {
            $favorite->section_id = $request->section_id;
        }

        if ($request->has('order')) {
            $favorite->order = $request->order;
        }

        $favorite->save();
        $favorite->load('design', 'section');

        return response()->json([
            'message' => 'Favorite updated successfully',
            'favorite' => $favorite
        ]);
    }
}

