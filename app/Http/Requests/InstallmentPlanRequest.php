<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstallmentPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage-installments');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_id' => [
                'required',
                'exists:invoices,id',
                Rule::exists('invoices', 'id')->where(function ($query) {
                    $query->whereIn('status', ['pending', 'partial', 'overdue'])
                          ->where('is_installment', false);
                }),
            ],
            'number_of_installments' => [
                'required',
                'integer',
                'min:2',
                'max:12',
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'interval_days' => [
                'nullable',
                'integer',
                'min:7',
                'max:90',
            ],
            'custom_amounts' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    if ($value && count($value) !== (int) $this->input('number_of_installments')) {
                        $fail('The number of custom amounts must match the number of installments.');
                    }

                    // Validate each amount is numeric and positive
                    if ($value) {
                        foreach ($value as $index => $amount) {
                            if (!is_numeric($amount) || $amount <= 0) {
                                $fail("Custom amount at position " . ($index + 1) . " must be a positive number.");
                            }
                        }
                    }
                },
            ],
            'custom_amounts.*' => [
                'nullable',
                'numeric',
                'min:0.01',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'invoice_id.required' => 'Please select an invoice.',
            'invoice_id.exists' => 'The selected invoice is not valid or already has an installment plan.',
            'number_of_installments.required' => 'Please specify the number of installments.',
            'number_of_installments.min' => 'Minimum 2 installments are required.',
            'number_of_installments.max' => 'Maximum 12 installments are allowed.',
            'start_date.required' => 'Please specify the start date.',
            'start_date.after_or_equal' => 'Start date must be today or later.',
            'interval_days.min' => 'Minimum interval between installments is 7 days.',
            'interval_days.max' => 'Maximum interval between installments is 90 days.',
            'custom_amounts.array' => 'Custom amounts must be provided as an array.',
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
            'interval_days' => 'interval days',
            'custom_amounts' => 'custom amounts',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default interval if not provided
        if (!$this->has('interval_days')) {
            $this->merge(['interval_days' => 30]);
        }

        // Clean up custom amounts
        if ($this->has('custom_amounts') && is_array($this->custom_amounts)) {
            $cleanAmounts = array_map(function ($amount) {
                return is_numeric($amount) ? floatval($amount) : null;
            }, $this->custom_amounts);

            // Remove null values and reindex
            $cleanAmounts = array_values(array_filter($cleanAmounts, fn($v) => $v !== null));

            if (empty($cleanAmounts)) {
                $this->merge(['custom_amounts' => null]);
            } else {
                $this->merge(['custom_amounts' => $cleanAmounts]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$validator->errors()->any() && $this->invoice_id) {
                $invoice = \App\Models\Invoice::find($this->invoice_id);

                if ($invoice) {
                    // Check if invoice balance is sufficient for installments
                    $balance = $invoice->total_amount - $invoice->paid_amount;

                    if ($balance <= 0) {
                        $validator->errors()->add('invoice_id', 'This invoice has no outstanding balance.');
                    }

                    // Check if custom amounts total matches balance
                    if ($this->custom_amounts && is_array($this->custom_amounts)) {
                        $totalCustom = array_sum($this->custom_amounts);
                        if (abs($totalCustom - $balance) > 0.01) {
                            $validator->errors()->add(
                                'custom_amounts',
                                "Custom amounts total (RM" . number_format($totalCustom, 2) .
                                ") must equal invoice balance (RM" . number_format($balance, 2) . ")."
                            );
                        }
                    }
                }
            }
        });
    }
}
