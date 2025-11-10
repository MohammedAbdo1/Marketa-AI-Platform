<?php

namespace App\Http\Requests\BrandAsset;

use Illuminate\Foundation\Http\FormRequest;

class ReorderBrandAssetsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order' => ['required', 'array', 'min:1'],
            'order.*.id' => ['required', 'integer', 'exists:brand_assets,id'],
            'order.*.display_order' => ['required', 'integer', 'min:0'],
        ];
    }
}

