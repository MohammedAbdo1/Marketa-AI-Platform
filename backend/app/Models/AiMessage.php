<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiMessage extends Model
{
    protected $fillable = [
        'uuid',
        'conversation_id',
        'role',
        'content',
        'generated_designs',
        'suggestions',
        'metadata',
    ];

    protected $casts = [
        'generated_designs' => 'array',
        'suggestions' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Boot function - Auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (AiMessage $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the conversation this message belongs to.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }

    /**
     * Business Logic Methods
     */

    /**
     * Attach design UUIDs to this message
     */
    public function attachDesigns(array $designUuids): void
    {
        $currentDesigns = $this->generated_designs ?? [];
        $this->update([
            'generated_designs' => array_unique(array_merge($currentDesigns, $designUuids))
        ]);
    }

    /**
     * Get designs generated in this message
     */
    public function getDesigns()
    {
        if (empty($this->generated_designs)) {
            return collect([]);
        }

        return Design::whereIn('uuid', $this->generated_designs)->get();
    }

    /**
     * Export for frontend
     */
    public function exportForFrontend(): array
    {
        $designs = $this->getDesigns();

        return [
            'uuid' => $this->uuid,
            'role' => $this->role,
            'content' => $this->content,
            'generated_designs' => $designs->map(function ($design) {
                return [
                    'uuid' => $design->uuid,
                    'title' => $design->title,
                    'thumbnail_url' => $design->thumbnail_url,
                    'export_url' => $design->export_url,
                    'design_type' => $design->design_type,
                ];
            }),
            'suggestions' => $this->suggestions ?? [],
            'metadata' => $this->metadata ?? [],
            'created_at' => $this->created_at,
        ];
    }

    /**
     * Check if this is a user message
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if this is an assistant message
     */
    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    /**
     * Check if this is a system message
     */
    public function isSystem(): bool
    {
        return $this->role === 'system';
    }

    /**
     * Scopes
     */

    /**
     * Scope for user messages only
     */
    public function scopeUserMessages($query)
    {
        return $query->where('role', 'user');
    }

    /**
     * Scope for assistant messages only
     */
    public function scopeAssistantMessages($query)
    {
        return $query->where('role', 'assistant');
    }
}

