<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\BrandAsset;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BrandAssetService extends BaseService
{
    protected string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.brand_disk', config('filesystems.default', 'public'));
    }

    public function list(Brand $brand): Collection
    {
        return $brand->assets()->orderBy('display_order')->get();
    }

    public function formatAsset(BrandAsset $asset): array
    {
        return [
            'id' => $asset->id,
            'uuid' => $asset->uuid,
            'brand_id' => $asset->brand_id,
            'organization_id' => $asset->organization_id,
            'asset_type' => $asset->asset_type,
            'label' => $asset->label,
            'description' => $asset->description,
            'storage_path' => $asset->storage_path,
            'url' => $asset->storage_path ? $this->resolveAssetUrl($asset->storage_path) : null,
            'original_filename' => $asset->original_filename,
            'mime_type' => $asset->mime_type,
            'file_size' => $asset->file_size,
            'metadata' => $asset->metadata ?? [],
            'tags' => $asset->tags ?? [],
            'is_primary' => (bool) $asset->is_primary,
            'display_order' => $asset->display_order,
            'version' => $asset->version ?? 1,
            'checksum' => $asset->checksum,
            'created_at' => $asset->created_at,
            'updated_at' => $asset->updated_at,
        ];
    }

    public function store(Brand $brand, array $data, ?UploadedFile $file = null): BrandAsset
    {
        return DB::transaction(function () use ($brand, $data, $file) {
            $attributes = $this->prepareAttributes($brand, $data, $file);

            $asset = $brand->assets()->create($attributes);

            if ($asset->is_primary) {
                $this->markAsPrimary($asset);
            }

            return $asset->fresh();
        });
    }

    public function update(BrandAsset $asset, array $data, ?UploadedFile $file = null): BrandAsset
    {
        return DB::transaction(function () use ($asset, $data, $file) {
            $attributes = $this->prepareAttributes($asset->brand, $data, $file, $asset);

            if (!empty($attributes)) {
                $asset->fill($attributes);
                $asset->save();
            }

            if (array_key_exists('is_primary', $attributes) && $attributes['is_primary']) {
                $this->markAsPrimary($asset);
            }

            return $asset->fresh();
        });
    }

    public function delete(BrandAsset $asset): void
    {
        DB::transaction(function () use ($asset) {
            if ($asset->storage_path) {
                Storage::disk($this->disk)->delete($asset->storage_path);
            }

            $asset->delete();
        });
    }

    public function markAsPrimary(BrandAsset $asset): void
    {
        BrandAsset::where('brand_id', $asset->brand_id)
            ->where('asset_type', $asset->asset_type)
            ->where('id', '!=', $asset->id)
            ->update(['is_primary' => false]);

        $asset->update([
            'is_primary' => true,
        ]);
    }

    public function reorder(Brand $brand, array $orderPayload): void
    {
        DB::transaction(function () use ($brand, $orderPayload) {
            foreach ($orderPayload as $item) {
                BrandAsset::where('brand_id', $brand->id)
                    ->where('id', $item['id'])
                    ->update(['display_order' => (int) $item['display_order']]);
            }
        });
    }

    protected function prepareAttributes(Brand $brand, array $data, ?UploadedFile $file = null, ?BrandAsset $existing = null): array
    {
        $attributes = [];

        foreach ([
            'asset_type',
            'label',
            'description',
            'metadata',
            'tags',
            'is_primary',
            'display_order',
        ] as $field) {
            if (array_key_exists($field, $data)) {
                $attributes[$field] = $data[$field];
            }
        }

        $attributes['organization_id'] = $brand->organization_id;

        if (array_key_exists('is_primary', $attributes)) {
            $attributes['is_primary'] = (bool) $attributes['is_primary'];
        }

        if (!array_key_exists('asset_type', $attributes)) {
            $attributes['asset_type'] = $existing?->asset_type ?? 'file';
        }

        $attributes['metadata'] = Arr::get($attributes, 'metadata', []);
        if (!is_array($attributes['metadata'])) {
            $attributes['metadata'] = [];
        }

        if (array_key_exists('tags', $attributes)) {
            $attributes['tags'] = $this->normalizeTags($attributes['tags']);
        }

        if (!array_key_exists('display_order', $attributes)) {
            $attributes['display_order'] = $brand->assets()->max('display_order') + 1;
        }

        if ($file instanceof UploadedFile) {
            if ($existing && $existing->storage_path) {
                Storage::disk($this->disk)->delete($existing->storage_path);
            }

            $stored = $this->storeFile($brand, $file, $attributes['asset_type']);
            $attributes = array_merge($attributes, $stored);
            $attributes['checksum'] = $this->calculateChecksum($file);
        }

        if (!array_key_exists('label', $attributes) && $file) {
            $attributes['label'] = $file->getClientOriginalName();
        }

        $attributes['version'] = $this->determineNextVersion($brand, $attributes, $existing);

        if (!array_key_exists('checksum', $attributes) && $existing) {
            $attributes['checksum'] = $existing->checksum;
        }

        return $attributes;
    }

    protected function storeFile(Brand $brand, UploadedFile $file, string $assetType): array
    {
        $path = $file->store(
            "brands/{$brand->organization_id}/{$brand->id}/{$assetType}",
            $this->disk
        );

        return [
            'storage_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    protected function normalizeTags($value): array
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

    protected function determineNextVersion(Brand $brand, array $attributes, ?BrandAsset $existing = null): int
    {
        if ($existing) {
            $current = $existing->version ?? 1;
            $hasNewChecksum = array_key_exists('checksum', $attributes)
                && $attributes['checksum']
                && $attributes['checksum'] !== $existing->checksum;

            return $hasNewChecksum ? $current + 1 : $current;
        }

        $query = BrandAsset::where('brand_id', $brand->id)
            ->where('asset_type', $attributes['asset_type']);

        if (!empty($attributes['label'])) {
            $query->where('label', $attributes['label']);
        }

        $max = (int) $query->max('version');

        return $max + 1;
    }

    protected function calculateChecksum(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        $path = $file->getRealPath();

        if (!$path || !is_readable($path)) {
            return null;
        }

        return hash_file('sha256', $path);
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

