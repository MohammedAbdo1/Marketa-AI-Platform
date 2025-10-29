<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageContent extends Model
{
    protected $table = 'page_content';

    protected $fillable = [
        'section_id',
        'content_type',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'image_url',
        'icon_class',
        'video_url',
        'button_text_ar',
        'button_text_en',
        'button_url',
        'sort_order',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the section that owns the content.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(PageSection::class, 'section_id');
    }
}
