<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    protected BrandService $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    /**
     * Display a listing of brands for the authenticated user's organization.
     */
    public function index(Request $request)
    {
        $organizationId = $request->user()->organization_id;
        $brands = $this->brandService->getBrandsForOrganization($organizationId);

        return response()->json([
            'success' => true,
            'data' => $brands,
        ]);
    }

    /**
     * Store a newly created brand.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo_url' => 'nullable|string',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'font_arabic' => 'nullable|string|max:100',
            'font_english' => 'nullable|string|max:100',
            'design_style' => 'nullable|string|max:50',
            'reference_images' => 'nullable|array',
            'brand_voice' => 'nullable|string',
        ]);

        $organizationId = $request->user()->organization_id;
        $brand = $this->brandService->createBrand($validated, $organizationId);

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully',
            'data' => $brand,
        ], 201);
    }

    /**
     * Display the specified brand.
     */
    public function show(Request $request, $id)
    {
        $organizationId = $request->user()->organization_id;
        $brand = $this->brandService->getBrand($id, $organizationId);

        return response()->json([
            'success' => true,
            'data' => $brand,
        ]);
    }

    /**
     * Update the specified brand.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'logo_url' => 'nullable|string',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'font_arabic' => 'nullable|string|max:100',
            'font_english' => 'nullable|string|max:100',
            'design_style' => 'nullable|string|max:50',
            'reference_images' => 'nullable|array',
            'brand_voice' => 'nullable|string',
        ]);

        $organizationId = $request->user()->organization_id;
        $brand = $this->brandService->updateBrand($id, $validated, $organizationId);

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully',
            'data' => $brand,
        ]);
    }

    /**
     * Remove the specified brand.
     */
    public function destroy(Request $request, $id)
    {
        $organizationId = $request->user()->organization_id;
        $this->brandService->deleteBrand($id, $organizationId);

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully',
        ]);
    }

    /**
     * Upload brand logo.
     */
    public function uploadLogo(Request $request, $id)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $organizationId = $request->user()->organization_id;
        $logoPath = $this->brandService->uploadLogo($request->file('logo'), $organizationId);

        // Update brand with new logo
        $brand = $this->brandService->updateBrand($id, ['logo_url' => $logoPath], $organizationId);

        // Extract colors from logo
        $colors = $this->brandService->extractColorsFromLogo($logoPath);

        return response()->json([
            'success' => true,
            'message' => 'Logo uploaded successfully',
            'data' => [
                'logo_url' => $logoPath,
                'suggested_colors' => $colors,
                'brand' => $brand,
            ],
        ]);
    }
}
