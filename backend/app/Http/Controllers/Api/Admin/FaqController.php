<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of FAQs.
     */
    public function index()
    {
        $faqs = Faq::orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $faqs,
        ]);
    }

    /**
     * Store a newly created FAQ.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_ar' => 'nullable|string',
            'category_en' => 'nullable|string',
            'question_ar' => 'required|string',
            'question_en' => 'required|string',
            'answer_ar' => 'required|string',
            'answer_en' => 'required|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $faq = Faq::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'FAQ created successfully',
            'data' => $faq,
        ], 201);
    }

    /**
     * Display the specified FAQ.
     */
    public function show($id)
    {
        $faq = Faq::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $faq,
        ]);
    }

    /**
     * Update the specified FAQ.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category_ar' => 'nullable|string',
            'category_en' => 'nullable|string',
            'question_ar' => 'sometimes|string',
            'question_en' => 'sometimes|string',
            'answer_ar' => 'sometimes|string',
            'answer_en' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $faq = Faq::findOrFail($id);
        $faq->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'FAQ updated successfully',
            'data' => $faq,
        ]);
    }

    /**
     * Remove the specified FAQ.
     */
    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return response()->json([
            'success' => true,
            'message' => 'FAQ deleted successfully',
        ]);
    }
}
