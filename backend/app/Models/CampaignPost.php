<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'platform',
        'post_type',
        'content_ar',
        'content_en',
        'hashtags',
        'media_urls',
        'media_prompts',
        'scheduled_date',
        'scheduled_time',
        'published_at',
        'status',
        'ai_prompt_used',
        'ai_tokens_used',
        'ai_cost',
        'order_number',
        'week_number',
        'day_of_week',
    ];

    protected $casts = [
        'media_urls' => 'array',
        'media_prompts' => 'array',
        'scheduled_date' => 'date',
        'published_at' => 'datetime',
        'ai_cost' => 'decimal:4',
    ];

    /**
     * Get the campaign that owns the post.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the AI requests for the post.
     */
    public function aiRequests(): HasMany
    {
        return $this->hasMany(AiRequest::class);
    }
}
