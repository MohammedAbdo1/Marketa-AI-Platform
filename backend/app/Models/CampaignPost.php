<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'platform',
        'post_type',
        'content_ar',
        'content_en',
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
    ];

    protected $casts = [
        'media_urls' => 'array',
        'media_prompts' => 'array',
        'scheduled_date' => 'date',
        'published_at' => 'datetime',
        'ai_cost' => 'decimal:4',
        // Composition casts
        'composition_layers' => 'array',
        'composition_analysis' => 'array',
        'is_composed' => 'boolean',
    ];

    /**
     * Get the campaign that owns the post.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
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
        return [
            'id' => $this->id,
            'base_image_url' => $this->base_image_url,
            'final_image_url' => $this->media_urls[0] ?? null,
            'layers' => $this->composition_layers['layers'] ?? [],
            'dimensions' => $this->composition_layers['dimensions'] ?? [],
            'is_composed' => $this->is_composed,
        ];
    }
}
