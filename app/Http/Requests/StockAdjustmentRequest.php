<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockAdjustmentRequest extends FormRequest
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
        $rules = [
            'type' => ['required', Rule::in(['add', 'remove', 'adjust'])],
            'quantity' => ['required', 'integer', 'min:1', 'max:999999'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        // For bulk adjustments
        if ($this->has('adjustments')) {
            $rules = [
                'adjustments' => ['required', 'array', 'min:1'],
                'adjustments.*.inventory_id' => ['required', 'exists:inventory,id'],
                'adjustments.*.type' => ['required', Rule::in(['add', 'remove', 'adjust'])],
                'adjustments.*.quantity' => ['required', 'integer', 'min:1', 'max:999999'],
                'adjustments.*.reference' => ['nullable', 'string', 'max:100'],
                'adjustments.*.notes' => ['nullable', 'string', 'max:500'],
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Please select an adjustment type.',
            'type.in' => 'Invalid adjustment type selected.',
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Quantity cannot exceed 999,999.',
            'reference.max' => 'Reference cannot exceed 100 characters.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
            'adjustments.required' => 'At least one adjustment is required.',
            'adjustments.*.inventory_id.required' => 'Please select an item.',
            'adjustments.*.inventory_id.exists' => 'Selected item does not exist.',
            'adjustments.*.type.required' => 'Please select an adjustment type for each item.',
            'adjustments.*.quantity.required' => 'Quantity is required for each item.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'adjustments.*.inventory_id' => 'item',
            'adjustments.*.type' => 'adjustment type',
            'adjustments.*.quantity' => 'quantity',
        ];
    }
}
