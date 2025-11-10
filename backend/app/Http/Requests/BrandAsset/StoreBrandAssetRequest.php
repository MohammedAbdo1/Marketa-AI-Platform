<?php

namespace App\Http\Requests\BrandAsset;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_type' => ['required', 'string', 'max:50'],
            'label' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['nullable', 'string', 'max:50'],
            'is_primary' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'file' => ['nullable', 'file', 'max:8192'],
        ];
    }
}

