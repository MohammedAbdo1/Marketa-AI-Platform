<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'uuid',
        'organization_id',
        'brand_id',
        'name',
        'business_type',
        'description',
        'goal',
        'mode',
        'seasonal_event',
        'product_description',
        'unique_selling_point',
        'special_offer',
        'target_audience',
        'platforms',
        'duration_days',
        'start_date',
        'end_date',
        'posts_per_week',
        'posting_times',
        'content_types',
        'tone_of_voice',
        'languages',
        'use_hashtags',
        'call_to_actions',
        'paid_ads_budget',
        'ai_auto_filled',
        'ai_generated_plans',
        'selected_plan_index',
        'status',
        'generation_status',
        'generation_progress',
        'generation_task_id',
        'ai_task_id',
        'generation_started_at',
        'generation_completed_at',
        'ai_analysis',
        'wizard_step',
        'wizard_data',
        'is_complete',
        'campaign_strategy',
    ];

    protected $casts = [
        'target_audience' => 'array',
        'platforms' => 'array',
        'posting_times' => 'array',
        'content_types' => 'array',
        'languages' => 'array',
        'call_to_actions' => 'array',
        'ai_auto_filled' => 'array',
        'ai_generated_plans' => 'array',
        'use_hashtags' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'paid_ads_budget' => 'decimal:2',
        'generation_started_at' => 'datetime',
        'generation_completed_at' => 'datetime',
        'ai_analysis' => 'array',
        'wizard_data' => 'array',
        'is_complete' => 'boolean',
        'campaign_strategy' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function creativeAssets(): HasMany
    {
        return $this->hasMany(CreativeAsset::class, 'context_id')
            ->where('context_type', self::class);
    }

    public function postAssets(): HasMany
    {
        return $this->creativeAssets()->where('asset_type', 'campaign_post');
    }

    public function designAssets(): HasMany
    {
        return $this->creativeAssets()->where('asset_type', 'design');
    }

    public function aiRequests(): HasMany
    {
        return $this->hasMany(AiRequest::class);
    }

    public function designs(): BelongsToMany
    {
        return $this->belongsToMany(CreativeAsset::class, 'campaign_creative_asset', 'campaign_id', 'creative_asset_id')
            ->where('creative_assets.asset_type', 'design')
            ->withPivot([
                'platform',
                'scheduled_date',
                'scheduled_time',
                'published_at',
                'status',
                'post_content_ar',
                'post_content_en',
                'hashtags',
                'order',
            ])
            ->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Campaign $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
