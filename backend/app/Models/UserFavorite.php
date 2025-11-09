<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFavorite extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'creative_asset_id',
        'section_id',
        'order',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'order' => 'integer',
    ];

    /**
     * Get the user that owns the favorite
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the design that is favorited
     */
    public function design(): BelongsTo
    {
        return $this->belongsTo(CreativeAsset::class, 'creative_asset_id')
            ->where('asset_type', 'design');
    }

    /**
     * Get the section this favorite belongs to
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(FavoriteSection::class, 'section_id');
    }
}

