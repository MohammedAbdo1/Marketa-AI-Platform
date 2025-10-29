<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Support\Facades\Storage;

class BrandService extends BaseService
{
    /**
     * Get all brands for an organization
     */
    public function getBrandsForOrganization(int $organizationId)
    {
        return Brand::where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a new brand
     */
    public function createBrand(array $data, int $organizationId)
    {
        $data['organization_id'] = $organizationId;
        
        return Brand::create($data);
    }

    /**
     * Update a brand
     */
    public function updateBrand(int $id, array $data, int $organizationId)
    {
        $brand = Brand::where('id', $id)
            ->where('organization_id', $organizationId)
            ->firstOrFail();
        
        $brand->update($data);
        
        return $brand;
    }

    /**
     * Delete a brand
     */
    public function deleteBrand(int $id, int $organizationId)
    {
        $brand = Brand::where('id', $id)
            ->where('organization_id', $organizationId)
            ->firstOrFail();
        
        // Delete logo file if exists
        if ($brand->logo_url) {
            Storage::disk('public')->delete($brand->logo_url);
        }
        
        return $brand->delete();
    }

    /**
     * Upload logo and return the path
     */
    public function uploadLogo($file, int $organizationId)
    {
        if (!$file) {
            return null;
        }
        
        $path = $file->store("brands/logos/{$organizationId}", 'public');
        
        return $path;
    }

    /**
     * Extract colors from logo image
     * This is a placeholder - would need image processing library like Intervention Image
     */
    public function extractColorsFromLogo(string $logoPath)
    {
        // Placeholder implementation
        // In reality, you'd use a library to analyze the image and extract dominant colors
        
        return [
            'primary_color' => '#000000',
            'secondary_color' => '#FFFFFF',
            'accent_color' => '#FF0000',
        ];
    }

    /**
     * Get a single brand
     */
    public function getBrand(int $id, int $organizationId)
    {
        return Brand::where('id', $id)
            ->where('organization_id', $organizationId)
            ->with('campaigns')
            ->firstOrFail();
    }
}

