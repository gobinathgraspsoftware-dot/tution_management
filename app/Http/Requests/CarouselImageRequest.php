<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarouselImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by route middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $imageRule = $this->isMethod('PUT') || $this->isMethod('PATCH')
            ? 'nullable'
            : 'required';

        return [
            'image' => [
                $imageRule,
                'image',
                'mimes:jpeg,jpg,png,webp,gif',
                'max:2048',            // 2 MB max
                'dimensions:min_width=600,min_height=200,max_width=3840,max_height=2160',
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:255'],
            'is_active'  => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom attribute names for error messages.
     */
    public function attributes(): array
    {
        return [
            'image'      => 'carousel image',
            'sort_order' => 'display order',
            'is_active'  => 'active status',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'image.required'     => 'Please upload a carousel image.',
            'image.image'        => 'The file must be a valid image (JPEG, PNG, WebP, GIF).',
            'image.mimes'        => 'Allowed formats: JPEG, JPG, PNG, WebP, GIF.',
            'image.max'          => 'Image size must not exceed 2 MB.',
            'image.dimensions'   => 'Image must be between 600×200 and 3840×2160 pixels.',
            'sort_order.integer' => 'Display order must be a whole number.',
            'sort_order.min'     => 'Display order cannot be negative.',
        ];
    }
}
