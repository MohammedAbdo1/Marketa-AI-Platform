<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\BrandAsset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandService extends BaseService
{
    protected string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.brand_disk', config('filesystems.default', 'public'));
    }

    /**
     * Retrieve all brands for an organization with related assets and counts.
     */
    public function getBrandsForOrganization(int $organizationId): Collection
    {
        return $this->queryForOrganization($organizationId)
            ->with([
                'assets' => fn ($query) => $query->orderBy('display_order'),
                'primaryLogo',
            ])
            ->withCount(['campaigns', 'creativeAssets'])
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get()
            ->map(fn (Brand $brand) => $this->formatBrand($brand));
    }

    /**
     * Fetch a single brand for an organization.
     */
    public function getBrand(int $id, int $organizationId): array
    {
        $brand = $this->queryForOrganization($organizationId)
            ->with([
                'assets' => fn ($query) => $query->orderBy('display_order'),
                'primaryLogo',
            ])
            ->withCount(['campaigns', 'creativeAssets'])
            ->findOrFail($id);

        return $this->formatBrand($brand);
    }

    /**
     * Create a brand with full payload support.
     */
    public function createBrand(array $data, int $organizationId): array
    {
        return DB::transaction(function () use ($data, $organizationId) {
            $attributes = $this->prepareBrandAttributes($data, $organizationId);

            $brand = Brand::create($attributes);

            if ($brand->is_default) {
                $this->enforceSingleDefault($brand);
            }

            $brand->loadMissing([
                'assets' => fn ($query) => $query->orderBy('display_order'),
                'primaryLogo',
            ])->loadCount(['campaigns', 'creativeAssets']);

            return $this->formatBrand($brand);
        });
    }

    /**
     * Update brand data.
     */
    public function updateBrand(int $id, array $data, int $organizationId): array
    {
        return DB::transaction(function () use ($id, $data, $organizationId) {
            $brand = $this->queryForOrganization($organizationId)->findOrFail($id);

            $attributes = $this->prepareBrandAttributes($data, $organizationId, $brand);

            $brand->fill($attributes);
            $brand->save();

            if (array_key_exists('is_default', $attributes) && $attributes['is_default']) {
                $this->enforceSingleDefault($brand);
            }

            $brand->load([
                'assets' => fn ($query) => $query->orderBy('display_order'),
                'primaryLogo',
            ])->loadCount(['campaigns', 'creativeAssets']);

            return $this->formatBrand($brand);
        });
    }

    /**
     * Remove a brand while preserving organizational defaults.
     */
    public function deleteBrand(int $id, int $organizationId): void
    {
        DB::transaction(function () use ($id, $organizationId) {
            $brand = $this->queryForOrganization($organizationId)->findOrFail($id);
            $wasDefault = $brand->is_default;

            $brand->delete();

            if ($wasDefault) {
                $replacement = $this->queryForOrganization($organizationId)
                    ->where('id', '!=', $brand->id)
                    ->orderByDesc('is_default')
                    ->orderByDesc('updated_at')
                    ->first();

                if ($replacement && !$replacement->is_default) {
                    $replacement->is_default = true;
                    $replacement->save();
                    $this->enforceSingleDefault($replacement);
                }
            }
        });
    }

    /**
     * Sync the logo URL for a brand from an associated asset.
     */
    public function applyLogoAsset(Brand $brand, BrandAsset $asset): void
    {
        $brand->logo_url = $asset->storage_path ?? Arr::get($asset->metadata, 'url');
        $brand->save();
    }

    /**
     * Placeholder color extraction method for future AI-powered palette detection.
     */
    public function extractColorsFromLogo(string $logoPath): array
    {
        return [
            'primary_color' => '#0B6E99',
            'secondary_color' => '#0F7B6C',
            'accent_color' => '#D9730D',
        ];
    }

    /**
     * Normalize and prepare brand attributes.
     */
    protected function prepareBrandAttributes(array $data, int $organizationId, ?Brand $brand = null): array
    {
        $allowed = [
            'name',
            'tagline',
            'slug',
            'status',
            'logo_url',
            'primary_color',
            'secondary_color',
            'accent_color',
            'color_palette',
            'font_arabic',
            'font_english',
            'typography_settings',
            'design_style',
            'brand_voice',
            'voice_attributes',
            'usage_guidelines',
            'guideline_url',
            'keywords',
            'reference_images',
            'is_default',
        ];

        $attributes = Arr::only($data, $allowed);
        $attributes['organization_id'] = $organizationId;

        if (array_key_exists('is_default', $attributes)) {
            $attributes['is_default'] = (bool) $attributes['is_default'];
        }

        if (!array_key_exists('status', $attributes)) {
            $attributes['status'] = $brand?->status ?? 'active';
        }

        foreach (['color_palette', 'keywords', 'reference_images', 'voice_attributes'] as $arrayField) {
            if (array_key_exists($arrayField, $attributes)) {
                $attributes[$arrayField] = $this->normalizeArray($attributes[$arrayField]);
            }
        }

        if (array_key_exists('usage_guidelines', $attributes)) {
            $attributes['usage_guidelines'] = $this->normalizeGuidelines($attributes['usage_guidelines']);
        }

        if (array_key_exists('typography_settings', $attributes)) {
            $attributes['typography_settings'] = $this->normalizeTypography($attributes['typography_settings']);
        }

        if (array_key_exists('slug', $attributes)) {
            $attributes['slug'] = $this->generateUniqueSlug(
                $organizationId,
                $attributes['slug'] ?: ($attributes['name'] ?? $brand?->name ?? Str::random(6)),
                $brand?->id
            );
        } elseif (!$brand && isset($attributes['name'])) {
            $attributes['slug'] = $this->generateUniqueSlug(
                $organizationId,
                $attributes['name'],
                null
            );
        } elseif ($brand && isset($attributes['name']) && $brand->name !== $attributes['name']) {
            $attributes['slug'] = $this->generateUniqueSlug(
                $organizationId,
                $attributes['name'],
                $brand->id
            );
        }

        foreach (['primary_color', 'secondary_color', 'accent_color'] as $colorField) {
            if (array_key_exists($colorField, $attributes)) {
                $attributes[$colorField] = $this->normalizeColor($attributes[$colorField]);
            }
        }

        return $attributes;
    }

    /**
     * Shape a brand into API response structure.
     */
    protected function formatBrand(Brand $brand): array
    {
        $brand->loadMissing([
            'assets' => fn ($query) => $query->orderBy('display_order'),
            'primaryLogo',
        ]);

        /** @var BrandAssetService $assetService */
        $assetService = app(BrandAssetService::class);

        $assets = $brand->assets
            ->map(fn (BrandAsset $asset) => $assetService->formatAsset($asset))
            ->values();

        return [
            'id' => $brand->id,
            'name' => $brand->name,
            'slug' => $brand->slug,
            'tagline' => $brand->tagline,
            'status' => $brand->status,
            'logo_url' => $brand->logo_url ? $this->resolveAssetUrl($brand->logo_url) : null,
            'primary_color' => $brand->primary_color,
            'secondary_color' => $brand->secondary_color,
            'accent_color' => $brand->accent_color,
            'color_palette' => $brand->color_palette ?? [],
            'font_arabic' => $brand->font_arabic,
            'font_english' => $brand->font_english,
            'typography_settings' => $brand->typography_settings ?? [],
            'design_style' => $brand->design_style,
            'brand_voice' => $brand->brand_voice,
            'voice_attributes' => $brand->voice_attributes ?? [],
            'usage_guidelines' => $brand->usage_guidelines ?? [],
            'guideline_url' => $brand->guideline_url,
            'keywords' => $brand->keywords ?? [],
            'reference_images' => $brand->reference_images ?? [],
            'is_default' => (bool) $brand->is_default,
            'campaigns_count' => $brand->campaigns_count ?? $brand->campaigns()->count(),
            'creative_assets_count' => $brand->creative_assets_count ?? $brand->creativeAssets()->count(),
            'assets' => $assets,
            'primary_logo_asset' => $brand->primaryLogo ? $assetService->formatAsset($brand->primaryLogo) : null,
            'created_at' => $brand->created_at,
            'updated_at' => $brand->updated_at,
        ];
    }

    protected function normalizeGuidelines($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn ($item) => is_array($item) || is_string($item))
            ->map(function ($item) {
                if (is_string($item)) {
                    return [
                        'title' => $item,
                        'description' => null,
                    ];
                }

                return [
                    'title' => Arr::get($item, 'title'),
                    'description' => Arr::get($item, 'description'),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function normalizeTypography($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return [
            'primary_font' => Arr::get($value, 'primary_font'),
            'secondary_font' => Arr::get($value, 'secondary_font'),
            'weights' => $this->normalizeArray(Arr::get($value, 'weights', [])),
            'line_heights' => $this->normalizeArray(Arr::get($value, 'line_heights', [])),
        ];
    }

    protected function normalizeArray($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return array_values(array_filter($decoded));
            }

            return array_filter(array_map('trim', explode(',', $value)));
        }

        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, fn ($item) => !is_null($item) && $item !== ''));
    }

    protected function normalizeColor(?string $color): ?string
    {
        if (empty($color)) {
            return null;
        }

        $color = trim($color);

        if (!str_starts_with($color, '#')) {
            $color = "#{$color}";
        }

        return Str::upper($color);
    }

    protected function enforceSingleDefault(Brand $brand): void
    {
        Brand::where('organization_id', $brand->organization_id)
            ->where('id', '!=', $brand->id)
            ->update(['is_default' => false]);
    }

    protected function generateUniqueSlug(int $organizationId, string $value, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);
        if (empty($baseSlug)) {
            $baseSlug = Str::random(6);
        }

        $slug = $baseSlug;
        $counter = 1;

        while ($this->queryForOrganization($organizationId)
            ->withTrashed()
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected function queryForOrganization(int $organizationId): Builder
    {
        return Brand::query()->where('organization_id', $organizationId);
    }

    protected function resolveAssetUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        try {
            return Storage::disk($this->disk)->url($path);
        } catch (\Throwable $e) {
            return $path;
        }
    }
}

