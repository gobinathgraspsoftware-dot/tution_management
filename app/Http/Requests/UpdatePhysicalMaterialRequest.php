<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePhysicalMaterialRequest extends FormRequest
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
            'subject_id' => 'required|exists:subjects,id',
            'grade_level' => 'nullable|string|max:50',
            'month' => 'nullable|string|in:January,February,March,April,May,June,July,August,September,October,November,December',
            'year' => 'nullable|integer|min:2020|max:' . (date('Y') + 5),
            'description' => 'nullable|string',
            'quantity_available' => 'required|integer|min:0',
            'status' => 'required|in:available,out_of_stock',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'subject_id' => 'subject',
            'grade_level' => 'grade level',
            'quantity_available' => 'quantity available',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'quantity_available.required' => 'Please enter the available quantity.',
            'quantity_available.min' => 'Quantity cannot be negative.',
        ];
    }
}
