<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstallmentPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('manage-installments');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'invoice_id' => 'required|exists:invoices,id',
            'number_of_installments' => 'required|integer|min:2|max:12',
            'start_date' => 'required|date|after_or_equal:today',
            'interval_days' => 'nullable|integer|min:1|max:90',
            'custom_amounts' => 'nullable|array',
            'custom_amounts.*' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'invoice_id.required' => 'Please select an invoice.',
            'invoice_id.exists' => 'The selected invoice does not exist.',
            'number_of_installments.required' => 'Please specify the number of installments.',
            'number_of_installments.min' => 'Minimum 2 installments are required.',
            'number_of_installments.max' => 'Maximum 12 installments are allowed.',
            'start_date.required' => 'Please specify the start date.',
            'start_date.after_or_equal' => 'Start date must be today or in the future.',
            'interval_days.min' => 'Interval must be at least 1 day.',
            'interval_days.max' => 'Interval cannot exceed 90 days.',
            'custom_amounts.*.numeric' => 'All custom amounts must be numeric values.',
            'custom_amounts.*.min' => 'Custom amounts cannot be negative.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'invoice_id' => 'invoice',
            'number_of_installments' => 'number of installments',
            'start_date' => 'start date',
            'interval_days' => 'interval',
            'custom_amounts' => 'custom amounts',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If custom interval is set and interval_days is 'custom', use custom value
        if ($this->interval_days === 'custom' && $this->custom_interval) {
            $this->merge([
                'interval_days' => (int) $this->custom_interval,
            ]);
        }

        // Default interval to 30 days if not set
        if (!$this->interval_days) {
            $this->merge([
                'interval_days' => 30,
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate custom amounts total if provided
            if ($this->has('custom_amounts') && is_array($this->custom_amounts)) {
                $invoice = \App\Models\Invoice::find($this->invoice_id);
                
                if ($invoice) {
                    $customTotal = array_sum(array_map('floatval', $this->custom_amounts));
                    $balance = $invoice->balance;

                    if (abs($customTotal - $balance) > 0.01) {
                        $validator->errors()->add(
                            'custom_amounts',
                            "Custom amounts total (RM " . number_format($customTotal, 2) . 
                            ") must equal the invoice balance (RM " . number_format($balance, 2) . ")."
                        );
                    }
                }

                // Validate number of custom amounts matches number of installments
                if (count($this->custom_amounts) != $this->number_of_installments) {
                    $validator->errors()->add(
                        'custom_amounts',
                        'Number of custom amounts must match the number of installments.'
                    );
                }
            }
        });
    }
}
