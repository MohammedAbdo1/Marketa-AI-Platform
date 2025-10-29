<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\CMSService;
use Illuminate\Http\Request;

class CMSController extends Controller
{
    protected CMSService $cmsService;

    public function __construct(CMSService $cmsService)
    {
        $this->cmsService = $cmsService;
    }

    /**
     * Get all pages.
     */
    public function getPages()
    {
        $pages = Page::with('sections.content')->get();

        return response()->json([
            'success' => true,
            'data' => $pages,
        ]);
    }

    /**
     * Update a page.
     */
    public function updatePage(Request $request, $id)
    {
        $validated = $request->validate([
            'title_ar' => 'sometimes|string',
            'title_en' => 'sometimes|string',
            'meta_description_ar' => 'nullable|string',
            'meta_description_en' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $page = Page::findOrFail($id);
        $page->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully',
            'data' => $page,
        ]);
    }

    /**
     * Get all sections.
     */
    public function getSections()
    {
        $sections = $this->cmsService->getSections();

        return response()->json([
            'success' => true,
            'data' => $sections,
        ]);
    }

    /**
     * Create a new section.
     */
    public function createSection(Request $request)
    {
        $validated = $request->validate([
            'page_id' => 'required|exists:pages,id',
            'section_type' => 'required|string',
            'title_ar' => 'nullable|string',
            'title_en' => 'nullable|string',
            'subtitle_ar' => 'nullable|string',
            'subtitle_en' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
            'settings' => 'nullable|array',
        ]);

        $section = $this->cmsService->createSection($validated);

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully',
            'data' => $section,
        ], 201);
    }

    /**
     * Update a section.
     */
    public function updateSection(Request $request, $id)
    {
        $validated = $request->validate([
            'section_type' => 'sometimes|string',
            'title_ar' => 'nullable|string',
            'title_en' => 'nullable|string',
            'subtitle_ar' => 'nullable|string',
            'subtitle_en' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
            'settings' => 'nullable|array',
        ]);

        $section = $this->cmsService->updateSection($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully',
            'data' => $section,
        ]);
    }

    /**
     * Delete a section.
     */
    public function deleteSection($id)
    {
        $this->cmsService->deleteSection($id);

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully',
        ]);
    }

    /**
     * Create content.
     */
    public function createContent(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:page_sections,id',
            'content_type' => 'required|string',
            'title_ar' => 'nullable|string',
            'title_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image_url' => 'nullable|string',
            'icon_class' => 'nullable|string',
            'video_url' => 'nullable|string',
            'button_text_ar' => 'nullable|string',
            'button_text_en' => 'nullable|string',
            'button_url' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
            'metadata' => 'nullable|array',
        ]);

        $content = $this->cmsService->createContent($validated);

        return response()->json([
            'success' => true,
            'message' => 'Content created successfully',
            'data' => $content,
        ], 201);
    }

    /**
     * Update content.
     */
    public function updateContent(Request $request, $id)
    {
        $validated = $request->validate([
            'content_type' => 'sometimes|string',
            'title_ar' => 'nullable|string',
            'title_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image_url' => 'nullable|string',
            'icon_class' => 'nullable|string',
            'video_url' => 'nullable|string',
            'button_text_ar' => 'nullable|string',
            'button_text_en' => 'nullable|string',
            'button_url' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
            'metadata' => 'nullable|array',
        ]);

        $content = $this->cmsService->updateContent($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Content updated successfully',
            'data' => $content,
        ]);
    }

    /**
     * Delete content.
     */
    public function deleteContent($id)
    {
        $this->cmsService->deleteContent($id);

        return response()->json([
            'success' => true,
            'message' => 'Content deleted successfully',
        ]);
    }
}
