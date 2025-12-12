<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Expense;

class ExpenseRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0|max:999999.99',
            'expense_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:' . implode(',', array_keys(Expense::getPaymentMethods())),
            'reference_number' => 'nullable|string|max:100',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'is_recurring' => 'nullable|boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,1|in:' . implode(',', array_keys(Expense::getRecurringFrequencies())),
            'budget_amount' => 'nullable|numeric|min:0|max:999999.99',
            'vendor_name' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select an expense category.',
            'category_id.exists' => 'The selected category is invalid.',
            'description.required' => 'Please provide a description for the expense.',
            'amount.required' => 'Please enter the expense amount.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be greater than 0.',
            'expense_date.required' => 'Please select the expense date.',
            'expense_date.before_or_equal' => 'The expense date cannot be in the future.',
            'payment_method.required' => 'Please select a payment method.',
            'receipt.mimes' => 'Receipt must be a file of type: jpg, jpeg, png, or pdf.',
            'receipt.max' => 'Receipt file size must not exceed 5MB.',
            'recurring_frequency.required_if' => 'Please select recurring frequency.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'expense category',
            'expense_date' => 'date',
            'payment_method' => 'payment method',
            'is_recurring' => 'recurring expense',
            'recurring_frequency' => 'frequency',
            'budget_amount' => 'budget',
            'vendor_name' => 'vendor',
            'invoice_number' => 'invoice number',
        ];
    }
}
