<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $inventoryId = $this->route('inventory') ? $this->route('inventory')->id : null;

        $rules = [
            'category_id' => ['required', 'exists:inventory_categories,id'],
            'name' => ['required', 'string', 'max:100'],
            'sku' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('inventory', 'sku')->ignore($inventoryId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'cost_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'current_stock' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'reorder_level' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'unit' => ['nullable', 'string', 'max:20'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'out_of_stock'])],
        ];

        // Make current_stock required for new items
        if ($this->isMethod('POST')) {
            $rules['current_stock'] = ['required', 'integer', 'min:0', 'max:999999'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'name.required' => 'Item name is required.',
            'name.max' => 'Item name cannot exceed 100 characters.',
            'sku.unique' => 'This SKU is already in use.',
            'sku.max' => 'SKU cannot exceed 50 characters.',
            'cost_price.numeric' => 'Cost price must be a valid number.',
            'cost_price.min' => 'Cost price cannot be negative.',
            'selling_price.required' => 'Selling price is required.',
            'selling_price.numeric' => 'Selling price must be a valid number.',
            'selling_price.min' => 'Selling price cannot be negative.',
            'current_stock.required' => 'Current stock quantity is required.',
            'current_stock.integer' => 'Stock quantity must be a whole number.',
            'current_stock.min' => 'Stock quantity cannot be negative.',
            'reorder_level.integer' => 'Reorder level must be a whole number.',
            'reorder_level.min' => 'Reorder level cannot be negative.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a JPEG, PNG, JPG, GIF, or WebP file.',
            'image.max' => 'The image size cannot exceed 2MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'cost_price' => 'cost price',
            'selling_price' => 'selling price',
            'current_stock' => 'current stock',
            'reorder_level' => 'reorder level',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'unit' => $this->unit ?? 'pcs',
            'reorder_level' => $this->reorder_level ?? 10,
            'status' => $this->status ?? 'active',
        ]);
    }
}
