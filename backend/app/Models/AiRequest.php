<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRequest extends Model
{
    protected $fillable = [
        'organization_id',
        'campaign_id',
        'campaign_post_id',
        'request_type',
        'model_used',
        'prompt',
        'response',
        'tokens_used',
        'cost',
        'status',
        'error_message',
        'processing_time_ms',
    ];

    protected $casts = [
        'cost' => 'decimal:4',
    ];

    /**
     * Get the organization that owns the AI request.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the campaign that owns the AI request.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the campaign post that owns the AI request.
     */
    public function campaignPost(): BelongsTo
    {
        return $this->belongsTo(CampaignPost::class);
    }
}
