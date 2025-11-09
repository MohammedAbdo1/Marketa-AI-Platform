<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\CreativeAsset;

class AiConversation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'design_type',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Boot function - Auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (AiConversation $model) {
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
     * Get the user that owns the conversation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all messages in this conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'conversation_id')
                    ->orderBy('created_at', 'asc');
    }

    /**
     * Get designs created from this conversation.
     */
    public function designs()
    {
        // Custom query since we're matching by UUID string
        return CreativeAsset::designs()
            ->where('source_id', $this->uuid)
            ->where('source_model', 'ai_conversation')
            ->get();
    }

    /**
     * Business Logic Methods
     */

    /**
     * Add a new message to this conversation
     */
    public function addMessage(string $role, string $content, array $generatedDesigns = [], array $suggestions = [], array $metadata = []): AiMessage
    {
        $message = $this->messages()->create([
            'uuid' => (string) Str::uuid(),
            'role' => $role,
            'content' => $content,
            'generated_designs' => $generatedDesigns,
            'suggestions' => $suggestions,
            'metadata' => $metadata,
        ]);

        // Update last_message_at
        $this->update(['last_message_at' => now()]);

        // Auto-generate title from first user message if not set
        if (empty($this->title) && $role === 'user') {
            $this->updateTitle($content);
        }

        return $message;
    }

    /**
     * Auto-generate conversation title from first message
     */
    public function updateTitle(string $firstMessage = null): void
    {
        if ($this->title) {
            return; // Already has a title
        }

        $content = $firstMessage ?? $this->messages()->where('role', 'user')->first()?->content;
        
        if ($content) {
            // Take first 50 characters
            $title = Str::limit($content, 50, '...');
            $this->update(['title' => $title]);
        }
    }

    /**
     * Get conversation summary for list view
     */
    public function getSummary(): array
    {
        $lastMessage = $this->messages()->latest()->first();
        $messageCount = $this->messages()->count();
        $designCount = $this->designs()->count();

        return [
            'uuid' => $this->uuid,
            'title' => $this->title ?? 'New Conversation',
            'design_type' => $this->design_type,
            'last_message' => $lastMessage ? [
                'role' => $lastMessage->role,
                'content' => Str::limit($lastMessage->content, 100),
                'created_at' => $lastMessage->created_at,
            ] : null,
            'message_count' => $messageCount,
            'design_count' => $designCount,
            'last_message_at' => $this->last_message_at,
            'created_at' => $this->created_at,
        ];
    }

    /**
     * Export full conversation for frontend
     */
    public function exportFull(): array
    {
        $designs = $this->designs();
        
        return [
            'uuid' => $this->uuid,
            'title' => $this->title ?? 'New Conversation',
            'design_type' => $this->design_type,
            'messages' => $this->messages->map(function ($message) {
                return $message->exportForFrontend();
            }),
            'designs' => $designs->map(function (CreativeAsset $design) {
                return [
                    'uuid' => $design->uuid,
                    'title' => $design->title,
                    'thumbnail_url' => $design->thumbnail_url,
                    'preview_url' => $design->preview_url,
                    'export_url' => $design->export_url,
                    'design_type' => $design->subtype,
                ];
            }),
            'created_at' => $this->created_at,
            'last_message_at' => $this->last_message_at,
        ];
    }

    /**
     * Scopes
     */

    /**
     * Scope for user's conversations
     */
    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope ordered by recent activity
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('last_message_at', 'desc');
    }
}

