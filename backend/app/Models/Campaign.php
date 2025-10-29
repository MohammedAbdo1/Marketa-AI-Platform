<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
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
}
