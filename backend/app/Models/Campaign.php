<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
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
        // Generation state fields
        'generation_status',
        'generation_progress',
        'generation_task_id',
        'ai_task_id',
        'generation_started_at',
        'generation_completed_at',
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
    ];

    /**
     * Get the organization that owns the campaign.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the brand that owns the campaign.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the posts for the campaign.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(CampaignPost::class);
    }

    /**
     * Get the AI requests for the campaign.
     */
    public function aiRequests(): HasMany
    {
        return $this->hasMany(AiRequest::class);
    }

    /**
     * Auto-generate UUID and bind routes by UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Campaign $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Use uuid for route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
