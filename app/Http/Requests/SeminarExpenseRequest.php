<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SeminarExpenseRequest extends FormRequest
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
            'seminar_id' => 'required|exists:seminars,id',
            'category' => 'required|in:venue,materials,food,facilitator_fees,marketing,transportation,equipment,miscellaneous',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0|max:999999.99',
            'expense_date' => 'required|date',
            'payment_method' => 'nullable|in:cash,bank_transfer,online,cheque,card',
            'reference_number' => 'nullable|string|max:100',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string|max:1000',
            'approval_status' => 'nullable|in:pending,approved,rejected',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'seminar_id' => 'seminar',
            'expense_date' => 'date of expense',
            'reference_number' => 'reference/receipt number',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category.in' => 'The selected category is invalid. Please choose from: Venue, Materials, Food, Facilitator Fees, Marketing, Transportation, Equipment, or Miscellaneous.',
            'amount.max' => 'The amount cannot exceed RM 999,999.99',
            'receipt.max' => 'The receipt file size cannot exceed 5MB.',
        ];
    }
}
