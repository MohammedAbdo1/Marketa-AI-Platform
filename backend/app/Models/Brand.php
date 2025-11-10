<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Brand extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'is_default',
        'status',
        'name',
        'slug',
        'tagline',
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
        'last_synced_at',
    ];

    protected $casts = [
        'reference_images' => 'array',
        'color_palette' => 'array',
        'typography_settings' => 'array',
        'voice_attributes' => 'array',
        'usage_guidelines' => 'array',
        'keywords' => 'array',
        'is_default' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Brand $brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });

        static::deleting(function (Brand $brand) {
            if ($brand->isForceDeleting()) {
                $brand->assets()->withTrashed()->forceDelete();
                $brand->creativeAssets()->withTrashed()->forceDelete();
            } else {
                $brand->assets()->each(function (BrandAsset $asset) {
                    $asset->delete();
                });

                $brand->creativeAssets()->each(function (CreativeAsset $asset) {
                    $asset->delete();
                });
            }
        });
    }

    /**
     * Get the organization that owns the brand.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the campaigns for the brand.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Get the creative assets generated from this brand.
     */
    public function creativeAssets(): HasMany
    {
        return $this->hasMany(CreativeAsset::class);
    }

    /**
     * Brand kit assets (logos, fonts, palettes, etc).
     */
    public function assets(): HasMany
    {
        return $this->hasMany(BrandAsset::class)->orderBy('display_order');
    }

    /**
     * Primary logo asset.
     */
    public function primaryLogo(): HasOne
    {
        return $this->hasOne(BrandAsset::class)
            ->where('asset_type', 'logo')
            ->where('is_primary', true)
            ->latest();
    }
}
