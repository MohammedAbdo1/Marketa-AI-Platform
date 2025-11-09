<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CreativeAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CreativeAssetController extends Controller
{
    /**
     * Display a creative asset by UUID in a design-friendly format.
     */
    public function show(string $uuid): JsonResponse
    {
        $user = Auth::user();

        $asset = CreativeAsset::where('uuid', $uuid)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);

                if ($user->organization_id) {
                    $query->orWhere('organization_id', $user->organization_id);
                }
            })
            ->first();

        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' => 'Creative asset not found',
            ], 404);
        }

        return response()->json($this->formatAssetResponse($asset));
    }

    /**
     * Update a creative asset payload (used by the editor).
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $user = Auth::user();

        $asset = CreativeAsset::where('uuid', $uuid)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);

                if ($user->organization_id) {
                    $query->orWhere('organization_id', $user->organization_id);
                }
            })
            ->first();

        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' => 'Creative asset not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:100',
            'thumbnail_url' => 'nullable|string',
            'preview_url' => 'nullable|string',
            'export_url' => 'nullable|string',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'content' => 'nullable|array',
            'settings' => 'nullable|array',
            'metadata' => 'nullable|array',
            'tags' => 'nullable|array',
            'composition_data' => 'nullable|array',
            'composition_data.layers' => 'nullable|array',
            'composition_data.dimensions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $updates = Arr::only($data, [
            'title',
            'description',
            'status',
            'thumbnail_url',
            'preview_url',
            'export_url',
            'width',
            'height',
            'metadata',
        ]);

        // Merge content updates
        $content = $asset->content ?? [];
        $contentChanged = false;

        if (array_key_exists('content', $data)) {
            $content = array_replace_recursive($content, $data['content'] ?? []);
            $contentChanged = true;
        }

        if (array_key_exists('composition_data', $data)) {
            $composition = $data['composition_data'] ?? [];
            if (array_key_exists('layers', $composition)) {
                $content['composition_layers'] = $composition['layers'];
                $contentChanged = true;
            }

            if (array_key_exists('dimensions', $composition)) {
                $dimensions = $composition['dimensions'] ?? [];
                $content['dimensions'] = array_merge($content['dimensions'] ?? [], $dimensions);
                $contentChanged = true;
            }
        }

        // Keep dimensions in sync with width/height if provided
        if (array_key_exists('width', $updates)) {
            $content['dimensions']['width'] = $updates['width'];
            $contentChanged = true;
        }
        if (array_key_exists('height', $updates)) {
            $content['dimensions']['height'] = $updates['height'];
            $contentChanged = true;
        }

        if ($contentChanged) {
            $updates['content'] = $content;
        }

        // Merge settings updates
        if (array_key_exists('settings', $data)) {
            $currentSettings = $asset->settings ?? [];
            $updates['settings'] = array_replace_recursive($currentSettings, $data['settings'] ?? []);
        }

        // Normalise metadata updates
        if (array_key_exists('metadata', $updates) && !is_array($updates['metadata'])) {
            $updates['metadata'] = (array) $updates['metadata'];
        }

        if (array_key_exists('tags', $data)) {
            $updates['tags'] = array_values(array_filter(array_map('strval', $data['tags'] ?? [])));
        }

        app(\App\Services\CreativeAssetService::class)->update($asset, $updates);

        $asset->refresh();

        return response()->json([
            'message' => 'Creative asset updated successfully',
            'design' => $this->formatAssetResponse($asset),
        ]);
    }

    protected function formatAssetResponse(CreativeAsset $asset): array
    {
        $content = $asset->content ?? [];
        $settings = $asset->settings ?? [];
        $metadata = $asset->metadata ?? [];

        $width = $asset->width
            ?? Arr::get($content, 'dimensions.width')
            ?? Arr::get($settings, 'dimensions.width')
            ?? 1080;

        $height = $asset->height
            ?? Arr::get($content, 'dimensions.height')
            ?? Arr::get($settings, 'dimensions.height')
            ?? 1080;

        $composition = [
            'layers' => Arr::get($content, 'composition_layers', []),
            'dimensions' => [
                'width' => $width,
                'height' => $height,
            ],
        ];

        if (
            (empty($composition['layers']) || count($composition['layers']) === 0)
            && ($asset->thumbnail_url || $asset->preview_url || Arr::get($content, 'final_image_url'))
        ) {
            $imageUrl = $asset->thumbnail_url
                ?? $asset->preview_url
                ?? Arr::get($content, 'final_image_url');

            if ($imageUrl) {
                $composition['layers'] = [[
                    'type' => 'image',
                    'url' => $imageUrl,
                    'x' => 0,
                    'y' => 0,
                    'left' => 0,
                    'top' => 0,
                    'width' => $width,
                    'height' => $height,
                    'scaleX' => 1,
                    'scaleY' => 1,
                ]];
            }
        }

        return [
            'id' => $asset->id,
            'uuid' => $asset->uuid,
            'title' => $asset->title ?? 'تصميم بدون عنوان',
            'description' => $asset->description,
            'status' => $asset->status,
            'asset_type' => $asset->asset_type,
            'source_type' => $asset->source_type,
            'source_id' => $asset->source_id,
            'source_model' => $asset->source_model,
            'context_type' => $asset->context_type,
            'context_id' => $asset->context_id,
            'width' => $width,
            'height' => $height,
            'composition_data' => $composition,
            'thumbnail_url' => $asset->thumbnail_url,
            'preview_url' => $asset->preview_url,
            'export_url' => Arr::get($content, 'final_image_url') ?: $asset->preview_url ?: $asset->thumbnail_url,
            'content' => $content,
            'settings' => $settings,
            'metadata' => $metadata,
            'tags' => $asset->tags,
            'is_template' => $asset->is_template,
            'is_public' => $asset->is_public,
            'created_at' => $asset->created_at,
            'updated_at' => $asset->updated_at,
        ];
    }
}

