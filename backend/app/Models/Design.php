<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Design extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'description',
        'design_type',
        'source_type',
        'source_id',
        'source_type_model',
        'composition_data',
        'thumbnail_url',
        'export_url',
        'width',
        'height',
        'canvas_settings',
        'metadata',
        'is_template',
        'is_public',
        'context_type',
        'context_id',
        'views_count',
        'used_count',
    ];

    protected $casts = [
        'composition_data' => 'array',
        'canvas_settings' => 'array',
        'metadata' => 'array',
        'is_template' => 'boolean',
        'is_public' => 'boolean',
        'views_count' => 'integer',
        'used_count' => 'integer',
        'trashed_at' => 'datetime',
    ];

    /**
     * Boot function - Auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Design $model) {
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
     * Relationships
     */

    /**
     * Get the user that owns the design.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get campaigns using this design (many-to-many).
     */
    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_design')
                    ->withPivot([
                        'platform', 
                        'scheduled_date', 
                        'scheduled_time',
                        'published_at',
                        'status',
                        'post_content_ar',
                        'post_content_en',
                        'hashtags',
                        'order'
                    ])
                    ->withTimestamps();
    }

    /**
     * Get the AI conversation that created this design.
     */
    public function aiConversation()
    {
        // This is a custom relationship since source_id is UUID string
        if ($this->source_type_model !== 'ai_conversation' || !$this->source_id) {
            return null;
        }
        
        return AiConversation::where('uuid', $this->source_id)->first();
    }

    /**
     * Get designs created from this template.
     */
    public function derivedDesigns(): HasMany
    {
        return $this->hasMany(Design::class, 'source_id')
                    ->where('source_type_model', 'template');
    }

    /**
     * Business Logic Methods
     */

    /**
     * Export design for Fabric.js editor
     */
    public function exportForEditor(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'composition_data' => $this->composition_data,
            'canvas_settings' => $this->canvas_settings,
            'width' => $this->width,
            'height' => $this->height,
            'thumbnail_url' => $this->thumbnail_url,
            'export_url' => $this->export_url,
            'source_type' => $this->source_type,
            'is_template' => $this->is_template,
        ];
    }

    /**
     * Duplicate this design for the user
     */
    public function duplicate(User $user = null): Design
    {
        $user = $user ?? $this->user;
        
        $newDesign = $this->replicate();
        $newDesign->uuid = (string) Str::uuid();
        $newDesign->user_id = $user->id;
        $newDesign->title = $this->title . ' (Copy)';
        $newDesign->source_id = $this->uuid;
        $newDesign->source_type_model = 'design';
        $newDesign->is_template = false;
        $newDesign->is_public = false;
        $newDesign->views_count = 0;
        $newDesign->used_count = 0;
        $newDesign->save();
        
        return $newDesign;
    }

    /**
     * Convert this design to a public template
     */
    public function toTemplate(): void
    {
        $this->is_template = true;
        $this->is_public = true;
        $this->save();
    }

    /**
     * Increment usage counter
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /**
     * Increment view counter
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Scopes
     */

    /**
     * Scope to filter by design type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('design_type', $type);
    }

    /**
     * Scope to filter by source type
     */
    public function scopeFromSource($query, string $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    /**
     * Scope for templates only
     */
    public function scopeTemplates($query)
    {
        return $query->where('is_template', true)
                     ->where('is_public', true);
    }

    /**
     * Scope for user's own designs
     */
    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for AI-generated designs
     */
    public function scopeAiGenerated($query)
    {
        return $query->where('source_type', 'ai');
    }

    /**
     * Scope for not trashed designs
     */
    public function scopeNotTrashed($query)
    {
        return $query->whereNull('trashed_at');
    }

    /**
     * Scope for trashed designs
     */
    public function scopeTrashed($query)
    {
        return $query->whereNotNull('trashed_at')->whereNull('deleted_at');
    }

    /**
     * Move design to trash
     */
    public function moveToTrash(): void
    {
        $this->trashed_at = now();
        $this->save();
    }

    /**
     * Restore design from trash
     */
    public function restoreFromTrash(): void
    {
        $this->trashed_at = null;
        $this->save();
    }

    /**
     * Get favorites relationship
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    /**
     * Check if design is favorited by a user
     */
    public function isFavoritedBy(int $userId): bool
    {
        return $this->favorites()->where('user_id', $userId)->exists();
    }
}

