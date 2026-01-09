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
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'expense_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,online',
            'reference_number' => 'nullable|string|max:100',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'is_recurring' => 'nullable|boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,1|in:monthly,quarterly,yearly',
            'notes' => 'nullable|string|max:1000',
            'budget_amount' => 'nullable|numeric|min:0|max:9999999.99',
            'vendor_name' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'expense category',
            'description' => 'description',
            'amount' => 'amount',
            'expense_date' => 'expense date',
            'payment_method' => 'payment method',
            'reference_number' => 'reference number',
            'receipt' => 'receipt file',
            'is_recurring' => 'recurring option',
            'recurring_frequency' => 'recurring frequency',
            'notes' => 'notes',
            'budget_amount' => 'budget amount',
            'vendor_name' => 'payee/vendor name',
            'invoice_number' => 'invoice number',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select an expense category.',
            'category_id.exists' => 'The selected category is invalid.',
            'description.required' => 'Please provide a description for this expense.',
            'description.max' => 'Description cannot exceed 500 characters.',
            'amount.required' => 'Please enter the expense amount.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least RM 0.01.',
            'amount.max' => 'Amount cannot exceed RM 9,999,999.99.',
            'expense_date.required' => 'Please select the expense date.',
            'expense_date.date' => 'Please enter a valid date.',
            'expense_date.before_or_equal' => 'Expense date cannot be in the future.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
            'receipt.file' => 'Receipt must be a valid file.',
            'receipt.mimes' => 'Receipt must be a PDF, JPG, JPEG, or PNG file.',
            'receipt.max' => 'Receipt file size cannot exceed 5MB.',
            'recurring_frequency.required_if' => 'Please select recurring frequency when marking as recurring.',
            'recurring_frequency.in' => 'Invalid recurring frequency selected.',
            'budget_amount.numeric' => 'Budget amount must be a valid number.',
            'budget_amount.min' => 'Budget amount cannot be negative.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert is_recurring to boolean
        if ($this->has('is_recurring')) {
            $this->merge([
                'is_recurring' => filter_var($this->is_recurring, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Clear recurring_frequency if not recurring
        if (!$this->is_recurring) {
            $this->merge([
                'recurring_frequency' => null,
            ]);
        }
    }
}
