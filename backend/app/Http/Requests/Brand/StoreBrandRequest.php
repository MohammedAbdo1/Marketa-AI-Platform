<?php

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive,archived'],
            'logo_url' => ['nullable', 'string', 'max:2048'],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'secondary_color' => ['nullable', 'string', 'max:7'],
            'accent_color' => ['nullable', 'string', 'max:7'],
            'color_palette' => ['nullable', 'array'],
            'color_palette.*' => ['nullable', 'string', 'max:7'],
            'font_arabic' => ['nullable', 'string', 'max:100'],
            'font_english' => ['nullable', 'string', 'max:100'],
            'typography_settings' => ['nullable', 'array'],
            'typography_settings.primary_font' => ['nullable', 'string', 'max:100'],
            'typography_settings.secondary_font' => ['nullable', 'string', 'max:100'],
            'design_style' => ['nullable', 'string', 'max:50'],
            'brand_voice' => ['nullable', 'string'],
            'voice_attributes' => ['nullable', 'array'],
            'voice_attributes.*' => ['nullable', 'string', 'max:100'],
            'usage_guidelines' => ['nullable', 'array'],
            'usage_guidelines.*.title' => ['nullable', 'string', 'max:255'],
            'usage_guidelines.*.description' => ['nullable', 'string'],
            'guideline_url' => ['nullable', 'string', 'max:2048'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['nullable', 'string', 'max:50'],
            'reference_images' => ['nullable', 'array'],
            'reference_images.*' => ['nullable', 'string', 'max:2048'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}

