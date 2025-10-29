<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'logo_url',
        'primary_color',
        'secondary_color',
        'accent_color',
        'font_arabic',
        'font_english',
        'design_style',
        'reference_images',
        'brand_voice',
    ];

    protected $casts = [
        'reference_images' => 'array',
    ];

    /**
     * Get the organization that owns the brand.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the campaigns for the brand.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
}
