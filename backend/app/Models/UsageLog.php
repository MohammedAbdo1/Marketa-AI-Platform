<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'organization_id',
        'subscription_id',
        'billing_cycle_start',
        'billing_cycle_end',
        'total_tokens_used',
        'total_requests',
        'total_cost_usd',
    ];

    protected $hidden = [
        'id',
    ];

    protected $casts = [
        'billing_cycle_start' => 'datetime',
        'billing_cycle_end' => 'datetime',
        'total_cost_usd' => 'decimal:4',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
