<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyUsage extends Model
{
    protected $table = 'daily_usage';

    protected $fillable = [
        'organization_id',
        'user_id',
        'date',
        'requests_count',
        'tokens_used',
        'ai_cost',
    ];

    protected $casts = [
        'date' => 'date',
        'ai_cost' => 'decimal:4',
    ];

    /**
     * Get the organization that owns the daily usage.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user that owns the daily usage.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
