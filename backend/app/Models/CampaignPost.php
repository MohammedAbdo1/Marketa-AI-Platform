<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CampaignPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'campaign_id',
        'design_id',
        'platform',
        'post_type',
        'content',
        'primary_language',
        'hashtags',
        'media_urls',
        'media_prompts',
        'scheduled_date',
        'scheduled_time',
        'published_at',
        'status',
        'ai_prompt_used',
        'ai_tokens_used',
        'ai_cost',
        'order_number',
        'week_number',
        'day_of_week',
        // Composition fields
        'composition_layers',
        'base_image_url',
        'is_composed',
        'composition_analysis',
        // Intelligence System fields
        'content_brief',
        'day_number',
        'day_name',
        'phase_name',
    ];

    protected $casts = [
        'content' => 'array',
        'hashtags' => 'array',
        'media_urls' => 'array',
        'media_prompts' => 'array',
        'scheduled_date' => 'date',
        'published_at' => 'datetime',
        'ai_cost' => 'decimal:4',
        // Composition casts
        'composition_layers' => 'array',
        'composition_analysis' => 'array',
        'is_composed' => 'boolean',
        // Intelligence System casts
        'content_brief' => 'array',
    ];

    /**
     * Get the campaign that owns the post.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the design linked to this post.
     */
    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }

    /**
     * Get the AI requests for the post.
     */
    public function aiRequests(): HasMany
    {
        return $this->hasMany(AiRequest::class);
    }
    
    /**
     * Get editable layers for frontend editor
     */
    public function getEditableLayers(): array
    {
        if (!$this->is_composed || !$this->composition_layers) {
            return [];
        }
        
        return [
            'base_image_url' => $this->base_image_url,
            'layers' => $this->composition_layers['layers'] ?? [],
            'dimensions' => $this->composition_layers['dimensions'] ?? [
                'width' => 1024,
                'height' => 1024
            ]
        ];
    }
    
    /**
     * Update specific layer
     */
    public function updateLayer(int $layerIndex, array $newData): void
    {
        $layers = $this->composition_layers;
        
        if (!isset($layers['layers'][$layerIndex])) {
            throw new \Exception("Layer {$layerIndex} not found");
        }
        
        // Merge new data
        $layers['layers'][$layerIndex] = array_merge(
            $layers['layers'][$layerIndex],
            $newData
        );
        
        $this->composition_layers = $layers;
        $this->save();
    }
    
    /**
     * Add new layer
     */
    public function addLayer(array $layerData): void
    {
        $layers = $this->composition_layers ?? ['layers' => []];
        $layers['layers'][] = $layerData;
        
        $this->composition_layers = $layers;
        $this->save();
    }
    
    /**
     * Remove layer
     */
    public function removeLayer(int $layerIndex): void
    {
        $layers = $this->composition_layers;
        
        if (isset($layers['layers'][$layerIndex])) {
            array_splice($layers['layers'], $layerIndex, 1);
            $this->composition_layers = $layers;
            $this->save();
        }
    }
    
    /**
     * Export for editor
     */
    public function exportForEditor(): array
    {
        // If linked to a design, use design data
        if ($this->design_id && $this->design) {
            return $this->design->exportForEditor();
        }

        // Otherwise use post's own composition data (backward compatibility)
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'base_image_url' => $this->base_image_url,
            'final_image_url' => $this->media_urls[0] ?? null,
            'layers' => $this->composition_layers['layers'] ?? [],
            'dimensions' => $this->composition_layers['dimensions'] ?? [],
            'is_composed' => $this->is_composed,
        ];
    }

    /**
     * Boot function - Auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (CampaignPost $model) {
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
}
