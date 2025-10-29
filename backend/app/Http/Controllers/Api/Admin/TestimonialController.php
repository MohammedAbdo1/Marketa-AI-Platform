<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Display a listing of testimonials.
     */
    public function index()
    {
        $testimonials = Testimonial::orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $testimonials,
        ]);
    }

    /**
     * Store a newly created testimonial.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'position_ar' => 'nullable|string',
            'position_en' => 'nullable|string',
            'company_ar' => 'nullable|string',
            'company_en' => 'nullable|string',
            'content_ar' => 'required|string',
            'content_en' => 'required|string',
            'avatar_url' => 'nullable|string',
            'rating' => 'required|integer|min:1|max:5',
            'is_featured' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $testimonial = Testimonial::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial created successfully',
            'data' => $testimonial,
        ], 201);
    }

    /**
     * Display the specified testimonial.
     */
    public function show($id)
    {
        $testimonial = Testimonial::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $testimonial,
        ]);
    }

    /**
     * Update the specified testimonial.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes|string',
            'name_en' => 'sometimes|string',
            'position_ar' => 'nullable|string',
            'position_en' => 'nullable|string',
            'company_ar' => 'nullable|string',
            'company_en' => 'nullable|string',
            'content_ar' => 'sometimes|string',
            'content_en' => 'sometimes|string',
            'avatar_url' => 'nullable|string',
            'rating' => 'sometimes|integer|min:1|max:5',
            'is_featured' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial updated successfully',
            'data' => $testimonial,
        ]);
    }

    /**
     * Remove the specified testimonial.
     */
    public function destroy($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();

        return response()->json([
            'success' => true,
            'message' => 'Testimonial deleted successfully',
        ]);
    }
}
