<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CreativeAsset;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class CreativeAssetService extends BaseService
{
    /**
     * Base query helper.
     */
    public function query(): Builder
    {
        return CreativeAsset::query();
    }

    /**
     * Base query for design assets.
     */
    public function designQuery(): Builder
    {
        return CreativeAsset::designs();
    }

    /**
     * Format a design asset payload similar to legacy Design model.
     */
    public function formatDesignAsset(CreativeAsset $asset, ?int $currentUserId = null): array
    {
        $content = $asset->content ?? [];
        $settings = $asset->settings ?? [];

        return [
            'id' => $asset->id,
            'uuid' => $asset->uuid,
            'user_id' => $asset->user_id,
            'organization_id' => $asset->organization_id,
            'title' => $asset->title,
            'description' => $asset->description,
            'design_type' => $asset->subtype ?? Arr::get($settings, 'design_type'),
            'source_type' => $asset->source_type,
            'source_id' => $asset->source_id,
            'source_type_model' => $asset->source_model,
            'composition_data' => Arr::get($content, 'composition_data', []),
            'canvas_settings' => Arr::get($content, 'canvas_settings', []),
            'thumbnail_url' => $asset->thumbnail_url,
            'preview_url' => $asset->preview_url,
            'export_url' => $asset->export_url,
            'width' => $asset->width ?? 1080,
            'height' => $asset->height ?? 1080,
            'metadata' => $asset->metadata ?? [],
            'tags' => $asset->tags ?? [],
            'is_template' => (bool) $asset->is_template,
            'is_public' => (bool) $asset->is_public,
            'status' => $asset->status,
            'context_type' => $asset->context_type,
            'context_id' => $asset->context_id,
            'views_count' => $asset->views_count ?? 0,
            'used_count' => $asset->used_count ?? 0,
            'content' => $content,
            'settings' => $settings,
            'created_at' => $asset->created_at,
            'updated_at' => $asset->updated_at,
            'trashed_at' => $asset->trashed_at,
        ];
    }

    /**
     * Create a new design asset.
     */
    public function createDesignAsset(array $data, int $userId, ?int $organizationId = null): CreativeAsset
    {
        $attributes = $this->buildDesignAttributes($data, $userId, $organizationId);
        return CreativeAsset::create($attributes);
    }

    /**
     * Update an existing design asset.
     */
    public function updateDesignAsset(CreativeAsset $asset, array $data): CreativeAsset
    {
        $attributes = $this->buildDesignAttributes($data, $asset->user_id, $asset->organization_id, $asset);
        if (!empty($attributes)) {
            $asset->fill($attributes);
            $asset->save();
        }

        return $asset;
    }

    /**
     * Duplicate a design asset for a user.
     */
    public function duplicateDesignAsset(CreativeAsset $asset, int $userId, ?int $organizationId = null): CreativeAsset
    {
        $copyAttributes = Arr::except($asset->toArray(), [
            'id',
            'uuid',
            'created_at',
            'updated_at',
        ]);

        $copyAttributes['user_id'] = $userId;
        $copyAttributes['organization_id'] = $organizationId ?? $asset->organization_id;
        $copyAttributes['uuid'] = (string) Str::uuid();
        $copyAttributes['title'] = trim(($asset->title ?? 'Untitled Design') . ' (Copy)');
        $copyAttributes['source_id'] = $asset->uuid;
        $copyAttributes['source_model'] = 'design';
        $copyAttributes['is_template'] = false;
        $copyAttributes['is_public'] = false;
        $copyAttributes['views_count'] = 0;
        $copyAttributes['used_count'] = 0;
        $copyAttributes['status'] = 'draft';
        $copyAttributes['trashed_at'] = null;

        return CreativeAsset::create($copyAttributes);
    }

    /**
     * Increment design usage counter.
     */
    public function incrementDesignUsage(CreativeAsset $asset): void
    {
        $asset->increment('used_count');
    }

    /**
     * Increment design views for non-owner access.
     */
    public function incrementDesignViews(CreativeAsset $asset, int $viewerId): void
    {
        if ($asset->user_id !== $viewerId) {
            $asset->increment('views_count');
        }
    }

    /**
     * Move design asset to trash.
     */
    public function trashDesignAsset(CreativeAsset $asset): CreativeAsset
    {
        $asset->moveToTrash();
        return $asset;
    }

    /**
     * Restore design asset from trash.
     */
    public function restoreDesignAsset(CreativeAsset $asset): CreativeAsset
    {
        $asset->restoreFromTrash();
        return $asset;
    }

    /**
     * Build attribute array for design asset creation/update.
     */
    protected function buildDesignAttributes(array $data, int $userId, ?int $organizationId = null, ?CreativeAsset $existing = null): array
    {
        $attributes = [];

        if (!$existing) {
            $attributes['user_id'] = $userId;
            $attributes['organization_id'] = $organizationId;
            $attributes['asset_type'] = 'design';
            $attributes['views_count'] = 0;
            $attributes['used_count'] = 0;
            $attributes['status'] = $data['status'] ?? 'draft';
        }

        if (array_key_exists('title', $data)) {
            $attributes['title'] = $data['title'] ?? 'Untitled Design';
        } elseif (!$existing) {
            $attributes['title'] = $data['title'] ?? 'Untitled Design';
        }

        foreach ([
            'description',
            'status',
            'thumbnail_url',
            'preview_url',
            'export_url',
            'source_type',
            'source_id',
            'source_type_model',
            'context_type',
            'context_id',
        ] as $field) {
            if (array_key_exists($field, $data)) {
                $attributes[$field === 'source_type_model' ? 'source_model' : $field] = $data[$field];
            }
        }

        if (
            !$existing
            && empty($attributes['preview_url'] ?? null)
            && !empty($attributes['thumbnail_url'] ?? null)
        ) {
            $attributes['preview_url'] = $attributes['thumbnail_url'];
        }

        if (array_key_exists('design_type', $data)) {
            $attributes['subtype'] = $data['design_type'];
        }

        if (array_key_exists('is_template', $data)) {
            $attributes['is_template'] = (bool) $data['is_template'];
        }

        if (array_key_exists('is_public', $data)) {
            $attributes['is_public'] = (bool) $data['is_public'];
        }

        if (array_key_exists('width', $data)) {
            $attributes['width'] = $data['width'];
        }

        if (array_key_exists('height', $data)) {
            $attributes['height'] = $data['height'];
        }

        if (array_key_exists('metadata', $data)) {
            $attributes['metadata'] = $data['metadata'] ?? [];
        }

        if (array_key_exists('tags', $data)) {
            $attributes['tags'] = $data['tags'] ?? [];
        }

        $content = $existing?->content ?? [];
        $contentChanged = false;

        if (array_key_exists('composition_data', $data)) {
            $content['composition_data'] = $data['composition_data'] ?? [];
            $contentChanged = true;
        }

        if (array_key_exists('canvas_settings', $data)) {
            $content['canvas_settings'] = $data['canvas_settings'] ?? [];
            $contentChanged = true;
        }

        if ($contentChanged) {
            $attributes['content'] = $content;
        }

        $settings = $existing?->settings ?? [];
        $settingsChanged = false;

        if (array_key_exists('design_type', $data)) {
            $settings['design_type'] = $data['design_type'];
            $settingsChanged = true;
        }

        if (array_key_exists('canvas_settings', $data)) {
            $settings['canvas_settings'] = $data['canvas_settings'];
            $settingsChanged = true;
        }

        if ($settingsChanged) {
            $attributes['settings'] = $settings;
        }

        return $attributes;
    }

    /**
     * Create a new creative asset.
     */
    public function create(array $attributes): CreativeAsset
    {
        return CreativeAsset::create($attributes);
    }

    /**
     * Update an existing creative asset.
     */
    public function update(CreativeAsset $asset, array $attributes): CreativeAsset
    {
        $asset->fill($attributes);
        $asset->save();

        return $asset;
    }

    /**
     * Fetch creative asset by UUID.
     */
    public function findByUuid(string $uuid): ?CreativeAsset
    {
        return CreativeAsset::where('uuid', $uuid)->first();
    }

    /**
     * Fetch campaign posts via creative assets.
     */
    public function getCampaignPosts(int $campaignId): Collection
    {
        return CreativeAsset::forCampaign($campaignId)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Count campaign post assets.
     */
    public function countCampaignPosts(int $campaignId): int
    {
        return CreativeAsset::forCampaign($campaignId)->count();
    }

    /**
     * Delete campaign post assets.
     */
    public function deleteCampaignPosts(int $campaignId): int
    {
        return CreativeAsset::forCampaign($campaignId)->delete();
    }

    /**
     * Soft delete.
     */
    public function delete(CreativeAsset $asset): bool
    {
        return (bool) $asset->delete();
    }

    /**
     * Build a creative asset payload directly from AI generation array data.
     */
    public function buildPayloadFromArray(Campaign $campaign, array $postData, int $orderNumber = 1): array
    {
        $compositionResult = $postData['composition_result'] ?? [];

        $mediaUrls = Arr::wrap($postData['media_urls'] ?? []);
        if (!empty($postData['image_url'])) {
            array_unshift($mediaUrls, $postData['image_url']);
        }
        if ($finalImage = Arr::get($compositionResult, 'final_image_url')) {
            array_unshift($mediaUrls, $finalImage);
        }
        $mediaUrls = array_values(array_filter(array_unique($mediaUrls)));

        $thumbnail = Arr::first($mediaUrls);

        $compositionLayers = $postData['composition_layers'] ?? Arr::get($compositionResult, 'layers', []);
        if (!is_array($compositionLayers)) {
            $compositionLayers = [];
        }

        $dimensions = Arr::get($compositionResult, 'dimensions', []);
        if (!is_array($dimensions)) {
            $dimensions = [];
        }

        $hashtags = $this->normalizeHashtags($postData['hashtags'] ?? []);

        $scheduledDate = $this->calculateScheduledDateFromArray($campaign, $postData);

        return [
            'uuid' => Str::uuid(),
            'user_id' => $campaign->user_id ?? optional($campaign->organization)->user_id ?? 1,
            'organization_id' => $campaign->organization_id,
            'asset_type' => 'campaign_post',
            'title' => $this->generateTitleFromArray($campaign, $postData, $orderNumber),
            'description' => $this->generateDescriptionFromArray($postData),
            'status' => $postData['status'] ?? 'pending',
            'is_template' => false,
            'is_public' => false,
            'source_type' => $postData['generation_method'] ?? 'ai_generation',
            'source_id' => $postData['uuid'] ?? null,
            'source_model' => null,
            'context_type' => Campaign::class,
            'context_id' => $campaign->id,
            'thumbnail_url' => $thumbnail,
            'preview_url' => $thumbnail,
            'width' => Arr::get($dimensions, 'width'),
            'height' => Arr::get($dimensions, 'height'),
            'content' => [
                'languages' => $postData['content'] ?? [],
                'composition_layers' => $compositionLayers,
                'base_image_url' => $postData['base_image_url'] ?? Arr::get($compositionResult, 'base_image_url'),
                'final_image_url' => $thumbnail,
            ],
            'settings' => [
                'platform' => $postData['platform'] ?? null,
                'post_type' => $postData['post_type'] ?? null,
                'primary_language' => $postData['primary_language'] ?? 'ar',
                'media_urls' => $mediaUrls,
                'media_prompts' => Arr::wrap($postData['image_prompt'] ?? Arr::get($postData, 'media_prompts', [])),
                'scheduled_date' => $scheduledDate,
                'scheduled_time' => $postData['scheduled_time'] ?? null,
                'published_at' => $postData['published_at'] ?? null,
                'order_number' => $orderNumber,
                'week_number' => $postData['week'] ?? 1,
                'day_of_week' => $postData['day'] ?? null,
                'day_number' => $postData['day'] ?? null,
                'day_name' => $postData['day_name'] ?? null,
                'phase_name' => $postData['phase'] ?? null,
                'generation_method' => $postData['generation_method'] ?? 'ai',
                'image_prompt' => $postData['image_prompt'] ?? null,
                'content_brief' => $postData['content_brief'] ?? null,
                'composition_analysis' => $postData['composition_analysis'] ?? null,
                'is_composed' => $postData['is_composed'] ?? (!empty($compositionLayers)),
            ],
            'metadata' => [
                'hashtags' => $hashtags,
                'ai_prompt_used' => $postData['ai_prompt_used'] ?? null,
                'ai_tokens_used' => $postData['tokens_used'] ?? 0,
                'ai_cost' => $postData['cost'] ?? 0,
            ],
            'tags' => array_filter(array_map('strval', Arr::flatten([$hashtags]))),
        ];
    }


    protected function normalizeHashtags($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            return preg_split('/[\s,]+/', $value, -1, PREG_SPLIT_NO_EMPTY);
        }

        return [];
    }

    protected function calculateScheduledDateFromArray(Campaign $campaign, array $postData): ?string
    {
        if (!$campaign->start_date instanceof Carbon) {
            return null;
        }

        if (!isset($postData['week'], $postData['day'])) {
            return null;
        }

        try {
            $weekNumber = max(0, (int) $postData['week'] - 1);
            $dayOfWeek = max(0, (int) $postData['day'] - 1);

            return $campaign->start_date
                ->copy()
                ->addWeeks($weekNumber)
                ->addDays($dayOfWeek)
                ->format('Y-m-d');
        } catch (\Throwable $exception) {
            return null;
        }
    }

    protected function generateTitleFromArray(Campaign $campaign, array $postData, int $orderNumber): string
    {
        $campaignName = $campaign->name ?? 'Campaign';
        $platform = ucfirst($postData['platform'] ?? 'post');

        $slot = null;
        if (!empty($postData['day_name'])) {
            $slot = $postData['day_name'];
        } elseif (!empty($postData['week']) && !empty($postData['day'])) {
            $slot = "Week {$postData['week']} - Day {$postData['day']}";
        }

        $slot = $slot ?: ('Post #' . $orderNumber);

        return "{$campaignName} - {$platform} - {$slot}";
    }

    protected function generateDescriptionFromArray(array $postData): ?string
    {
        $content = $postData['content'] ?? null;
        if (is_array($content)) {
            $first = Arr::first($content);
            if (is_string($first)) {
                return Str::of($first)->limit(200);
            }
            if (is_array($first)) {
                $firstLang = Arr::first($first);
                if (is_string($firstLang)) {
                    return Str::of($firstLang)->limit(200);
                }
            }
        } elseif (is_string($content)) {
            return Str::of($content)->limit(200);
        }

        if (!empty($postData['content_brief']['summary']) && is_string($postData['content_brief']['summary'])) {
            return Str::of($postData['content_brief']['summary'])->limit(200);
        }

        return null;
    }
}

