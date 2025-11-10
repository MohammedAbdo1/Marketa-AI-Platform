<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandAsset\ReorderBrandAssetsRequest;
use App\Http\Requests\BrandAsset\StoreBrandAssetRequest;
use App\Http\Requests\BrandAsset\UpdateBrandAssetRequest;
use App\Models\Brand;
use App\Models\BrandAsset;
use App\Services\BrandAssetService;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandAssetController extends Controller
{
    public function __construct(
        protected BrandAssetService $brandAssetService,
        protected BrandService $brandService
    ) {
    }

    public function index(Request $request, Brand $brand): JsonResponse
    {
        $this->authorize('view', $brand);
        $organizationId = $request->user()->organization_id;
        abort_if($brand->organization_id !== $organizationId, 404);

        $assets = $this->brandAssetService->list($brand)
            ->map(fn (BrandAsset $asset) => $this->brandAssetService->formatAsset($asset))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $assets,
        ]);
    }

    public function store(StoreBrandAssetRequest $request, Brand $brand): JsonResponse
    {
        $this->authorize('update', $brand);
        $organizationId = $request->user()->organization_id;
        abort_if($brand->organization_id !== $organizationId, 404);

        $asset = $this->brandAssetService->store($brand, $request->validated(), $request->file('file'));

        if ($asset->asset_type === 'logo' && $asset->is_primary) {
            $this->brandService->applyLogoAsset($brand, $asset);
        }

        $brandPayload = $this->brandService->getBrand($brand->id, $organizationId);

        return response()->json([
            'success' => true,
            'message' => 'Brand asset created successfully',
            'data' => [
                'asset' => $this->brandAssetService->formatAsset($asset),
                'brand' => $brandPayload,
            ],
        ], 201);
    }

    public function update(UpdateBrandAssetRequest $request, Brand $brand, BrandAsset $asset): JsonResponse
    {
        $this->authorize('update', $brand);
        $organizationId = $request->user()->organization_id;
        abort_if($brand->organization_id !== $organizationId, 404);
        abort_if($asset->brand_id !== $brand->id, 404);

        $updated = $this->brandAssetService->update($asset, $request->validated(), $request->file('file'));

        if ($updated->asset_type === 'logo' && $updated->is_primary) {
            $this->brandService->applyLogoAsset($brand, $updated);
        }

        $brandPayload = $this->brandService->getBrand($brand->id, $organizationId);

        return response()->json([
            'success' => true,
            'message' => 'Brand asset updated successfully',
            'data' => [
                'asset' => $this->brandAssetService->formatAsset($updated),
                'brand' => $brandPayload,
            ],
        ]);
    }

    public function destroy(Request $request, Brand $brand, BrandAsset $asset): JsonResponse
    {
        $this->authorize('delete', $brand);
        $organizationId = $request->user()->organization_id;
        abort_if($brand->organization_id !== $organizationId, 404);
        abort_if($asset->brand_id !== $brand->id, 404);

        $wasPrimaryLogo = $asset->asset_type === 'logo' && $asset->is_primary;

        $this->brandAssetService->delete($asset);

        if ($wasPrimaryLogo) {
            $nextLogo = $brand->assets()->where('asset_type', 'logo')->orderByDesc('is_primary')->orderBy('display_order')->first();
            if ($nextLogo) {
                $this->brandAssetService->markAsPrimary($nextLogo);
                $this->brandService->applyLogoAsset($brand, $nextLogo);
            } else {
                $brand->logo_url = null;
                $brand->save();
            }
        }

        $brandPayload = $this->brandService->getBrand($brand->id, $organizationId);

        return response()->json([
            'success' => true,
            'message' => 'Brand asset removed',
            'data' => $brandPayload,
        ]);
    }

    public function makePrimary(Request $request, Brand $brand, BrandAsset $asset): JsonResponse
    {
        $this->authorize('update', $brand);
        $organizationId = $request->user()->organization_id;
        abort_if($brand->organization_id !== $organizationId, 404);
        abort_if($asset->brand_id !== $brand->id, 404);

        $this->brandAssetService->markAsPrimary($asset);

        if ($asset->asset_type === 'logo') {
            $this->brandService->applyLogoAsset($brand, $asset->fresh());
        }

        $brandPayload = $this->brandService->getBrand($brand->id, $organizationId);

        return response()->json([
            'success' => true,
            'message' => 'Brand asset marked as primary',
            'data' => $brandPayload,
        ]);
    }

    public function reorder(ReorderBrandAssetsRequest $request, Brand $brand): JsonResponse
    {
        $this->authorize('update', $brand);
        $organizationId = $request->user()->organization_id;
        abort_if($brand->organization_id !== $organizationId, 404);

        $this->brandAssetService->reorder($brand, $request->validated()['order']);

        $brandPayload = $this->brandService->getBrand($brand->id, $organizationId);

        return response()->json([
            'success' => true,
            'message' => 'Brand assets reordered successfully',
            'data' => $brandPayload,
        ]);
    }
}

