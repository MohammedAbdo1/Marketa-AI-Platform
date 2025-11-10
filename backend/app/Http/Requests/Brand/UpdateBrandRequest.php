<?php

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'tagline' => ['sometimes', 'nullable', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'in:active,inactive,archived'],
            'logo_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'primary_color' => ['sometimes', 'nullable', 'string', 'max:7'],
            'secondary_color' => ['sometimes', 'nullable', 'string', 'max:7'],
            'accent_color' => ['sometimes', 'nullable', 'string', 'max:7'],
            'color_palette' => ['sometimes', 'nullable', 'array'],
            'color_palette.*' => ['nullable', 'string', 'max:7'],
            'font_arabic' => ['sometimes', 'nullable', 'string', 'max:100'],
            'font_english' => ['sometimes', 'nullable', 'string', 'max:100'],
            'typography_settings' => ['sometimes', 'nullable', 'array'],
            'typography_settings.primary_font' => ['nullable', 'string', 'max:100'],
            'typography_settings.secondary_font' => ['nullable', 'string', 'max:100'],
            'design_style' => ['sometimes', 'nullable', 'string', 'max:50'],
            'brand_voice' => ['sometimes', 'nullable', 'string'],
            'voice_attributes' => ['sometimes', 'nullable', 'array'],
            'voice_attributes.*' => ['nullable', 'string', 'max:100'],
            'usage_guidelines' => ['sometimes', 'nullable', 'array'],
            'usage_guidelines.*.title' => ['nullable', 'string', 'max:255'],
            'usage_guidelines.*.description' => ['nullable', 'string'],
            'guideline_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'keywords' => ['sometimes', 'nullable', 'array'],
            'keywords.*' => ['nullable', 'string', 'max:50'],
            'reference_images' => ['sometimes', 'nullable', 'array'],
            'reference_images.*' => ['nullable', 'string', 'max:2048'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}

