<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BrandAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'brand_id',
        'organization_id',
        'asset_type',
        'label',
        'description',
        'storage_path',
        'original_filename',
        'mime_type',
        'file_size',
        'metadata',
        'tags',
        'is_primary',
        'display_order',
        'version',
        'checksum',
    ];

    protected $casts = [
        'metadata' => 'array',
        'tags' => 'array',
        'is_primary' => 'boolean',
        'version' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (BrandAsset $asset) {
            if (empty($asset->uuid)) {
                $asset->uuid = (string) Str::uuid();
            }
        });
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creativeAssets(): HasMany
    {
        return $this->hasMany(CreativeAsset::class, 'brand_asset_id');
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('asset_type', $type);
    }
}

