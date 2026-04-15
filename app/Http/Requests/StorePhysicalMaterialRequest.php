<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePhysicalMaterialRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'grade_level_id' => 'required|exists:grade_levels,id',          // CHANGED: was 'grade_level' string
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'nullable|exists:classes,id',                     // ADDED: class dropdown
            'month' => 'nullable|string|in:January,February,March,April,May,June,July,August,September,October,November,December',
            'year' => 'nullable|integer|min:2020|max:' . (date('Y') + 5),
            'description' => 'nullable|string',
            'quantity_total' => 'required|integer|min:0',
            'quantity_available' => 'required|integer|min:0',
            'minimum_quantity' => 'required|integer|min:0',
            'status' => 'required|in:available,low_stock,out_of_stock',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'grade_level_id' => 'grade level',
            'subject_id' => 'subject',
            'class_id' => 'class',
            'quantity_total' => 'total quantity',
            'quantity_available' => 'available quantity',
            'minimum_quantity' => 'minimum quantity',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'grade_level_id.required' => 'Please select a grade level.',
            'subject_id.required' => 'Please select a subject.',
            'quantity_total.required' => 'Please enter the total quantity.',
            'quantity_available.required' => 'Please enter the available quantity.',
            'quantity_available.min' => 'Quantity cannot be negative.',
        ];
    }
}
