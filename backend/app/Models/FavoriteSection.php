<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FavoriteSection extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'organization_id',
        'name',
        'emoji',
        'order',
    ];

    protected $casts = [
        'uuid' => 'string',
        'order' => 'integer',
    ];

    /**
     * Boot function - Auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (FavoriteSection $model) {
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

    /**
     * Get the user that owns the section
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization that owns the section
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the favorites in this section
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class, 'section_id');
    }
}

