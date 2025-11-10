<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\BrandAssetService;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    protected BrandService $brandService;
    protected BrandAssetService $brandAssetService;

    public function __construct(BrandService $brandService, BrandAssetService $brandAssetService)
    {
        $this->brandService = $brandService;
        $this->brandAssetService = $brandAssetService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Brand::class);

        $organizationId = $request->user()->organization_id;
        $brands = $this->brandService->getBrandsForOrganization($organizationId);

        return response()->json([
            'success' => true,
            'data' => $brands,
        ]);
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $this->authorize('create', Brand::class);

        $organizationId = $request->user()->organization_id;
        abort_if(is_null($organizationId), 403, 'Organization context is required.');

        $brand = $this->brandService->createBrand($request->validated(), $organizationId);

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully',
            'data' => $brand,
        ], 201);
    }

    public function show(Request $request, Brand $brand): JsonResponse
    {
        $this->authorize('view', $brand);

        $organizationId = $request->user()->organization_id;
        abort_if($brand->organization_id !== $organizationId, 404);

        $payload = $this->brandService->getBrand($brand->id, $organizationId);

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        $this->authorize('update', $brand);

        $organizationId = $request->user()->organization_id;
        abort_if($brand->organization_id !== $organizationId, 404);

        $payload = $this->brandService->updateBrand($brand->id, $request->validated(), $organizationId);

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully',
            'data' => $payload,
        ]);
    }

    public function destroy(Request $request, Brand $brand): JsonResponse
    {
        $this->authorize('delete', $brand);

        $organizationId = $request->user()->organization_id;
        abort_if($brand->organization_id !== $organizationId, 404);

        $this->brandService->deleteBrand($brand->id, $organizationId);

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully',
        ]);
    }

    public function uploadLogo(Request $request, Brand $brand): JsonResponse
    {
        $this->authorize('update', $brand);

        $validated = $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg,svg', 'max:4096'],
            'label' => ['nullable', 'string', 'max:255'],
        ]);

        $organizationId = $request->user()->organization_id;
        abort_if($brand->organization_id !== $organizationId, 404);

        $asset = $this->brandAssetService->store(
            $brand,
            [
                'asset_type' => 'logo',
                'label' => $validated['label'] ?? 'Primary Logo',
                'is_primary' => true,
            ],
            $request->file('logo')
        );

        $this->brandService->applyLogoAsset($brand, $asset);

        $colors = $this->brandService->extractColorsFromLogo($asset->storage_path ?? '');
        $brandPayload = $this->brandService->getBrand($brand->id, $organizationId);

        return response()->json([
            'success' => true,
            'message' => 'Logo uploaded successfully',
            'data' => [
                'brand' => $brandPayload,
                'asset' => $this->brandAssetService->formatAsset($asset),
                'suggested_colors' => $colors,
            ],
        ]);
    }
}
