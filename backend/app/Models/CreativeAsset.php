<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\UserFavorite;

class CreativeAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'organization_id',
        'brand_id',
        'brand_asset_id',
        'asset_type',
        'subtype',
        'title',
        'description',
        'status',
        'is_template',
        'is_public',
        'source_type',
        'source_id',
        'source_model',
        'context_type',
        'context_id',
        'storage_path',
        'thumbnail_url',
        'preview_url',
        'export_url',
        'width',
        'height',
        'legacy_post_id',
        'content',
        'settings',
        'metadata',
        'tags',
        'views_count',
        'used_count',
        'trashed_at',
    ];

    protected $casts = [
        'is_template' => 'boolean',
        'is_public' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
        'content' => 'array',
        'settings' => 'array',
        'metadata' => 'array',
        'tags' => 'array',
        'views_count' => 'integer',
        'used_count' => 'integer',
        'trashed_at' => 'datetime',
    ];

    /**
     * Auto-generate UUID on creation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (CreativeAsset $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Use uuid for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'context_id')
            ->where('context_type', Campaign::class);
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_creative_asset', 'creative_asset_id', 'campaign_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function brandAsset(): BelongsTo
    {
        return $this->belongsTo(BrandAsset::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class, 'creative_asset_id');
    }

    /**
     * Scopes
     */
    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('asset_type', $type);
    }

    public function scopeForBrand(Builder $query, int $brandId): Builder
    {
        return $query->where('brand_id', $brandId);
    }

    public function scopeCampaignPosts(Builder $query): Builder
    {
        return $query->type('campaign_post');
    }

    public function scopeDesigns(Builder $query): Builder
    {
        return $query->type('design');
    }

    public function scopeForCampaign(Builder $query, int $campaignId): Builder
    {
        return $query->campaignPosts()
            ->where(function (Builder $inner) use ($campaignId) {
                $inner->where('context_type', Campaign::class)
                    ->where('context_id', $campaignId);
            });
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where(function (Builder $inner) {
            $inner->where('is_public', true)
                ->orWhereNull('deleted_at');
        });
    }

    public function scopeNotTrashed(Builder $query): Builder
    {
        return $query->whereNull('trashed_at');
    }

    public function scopeTrashed(Builder $query): Builder
    {
        return $query->whereNotNull('trashed_at')->whereNull('deleted_at');
    }

    /**
     * Helpers
     */
    public function isCampaignPost(): bool
    {
        return $this->asset_type === 'campaign_post';
    }

    public function isBrandAsset(): bool
    {
        return $this->asset_type === 'brand_asset';
    }

    public function moveToTrash(): void
    {
        $this->trashed_at = now();
        $this->save();
    }

    public function restoreFromTrash(): void
    {
        $this->trashed_at = null;
        $this->save();
    }
}

