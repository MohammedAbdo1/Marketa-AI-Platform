<?php

namespace App\Http\Requests\BrandAsset;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_type' => ['sometimes', 'string', 'max:50'],
            'label' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'metadata' => ['sometimes', 'nullable', 'array'],
            'tags' => ['sometimes', 'nullable', 'array'],
            'tags.*' => ['nullable', 'string', 'max:50'],
            'is_primary' => ['sometimes', 'boolean'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
            'file' => ['sometimes', 'nullable', 'file', 'max:8192'],
        ];
    }
}

