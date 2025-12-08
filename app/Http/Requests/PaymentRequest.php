<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\PaymentService;

class PaymentRequest extends FormRequest
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
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,qr,bank_transfer,cheque',
            'payment_date' => 'required|date|before_or_equal:today',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ];

        // QR payment specific rules
        if ($this->payment_method === 'qr') {
            $rules['reference_number'] = 'required|string|max:100';
            $rules['screenshot'] = 'nullable|image|mimes:jpeg,png,jpg|max:5120'; // 5MB max
        }

        // Bank transfer specific rules
        if ($this->payment_method === 'bank_transfer') {
            $rules['reference_number'] = 'required|string|max:100';
        }

        // Cheque specific rules
        if ($this->payment_method === 'cheque') {
            $rules['reference_number'] = 'required|string|max:100';
            $rules['cheque_date'] = 'nullable|date';
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'invoice_id' => 'invoice',
            'payment_method' => 'payment method',
            'payment_date' => 'payment date',
            'reference_number' => 'reference number',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'invoice_id.required' => 'Please select an invoice.',
            'invoice_id.exists' => 'The selected invoice is invalid.',
            'amount.required' => 'Payment amount is required.',
            'amount.numeric' => 'Payment amount must be a valid number.',
            'amount.min' => 'Payment amount must be at least RM 0.01.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Please select a valid payment method.',
            'payment_date.required' => 'Payment date is required.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
            'reference_number.required' => 'Reference number is required for this payment method.',
            'screenshot.image' => 'The screenshot must be an image file.',
            'screenshot.mimes' => 'The screenshot must be a JPEG or PNG file.',
            'screenshot.max' => 'The screenshot must not exceed 5MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If student_id is provided instead of invoice_id, we'll handle it in controller
        if ($this->has('student_id') && !$this->has('invoice_id')) {
            // Will be handled in the controller
        }

        // Sanitize amount
        if ($this->has('amount')) {
            $this->merge([
                'amount' => preg_replace('/[^0-9.]/', '', $this->amount),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation: Check if amount exceeds invoice balance
            if (!$validator->errors()->has('amount') && !$validator->errors()->has('invoice_id')) {
                $invoice = \App\Models\Invoice::find($this->invoice_id);

                if ($invoice) {
                    // Check if invoice can receive payment
                    if (!$invoice->canReceivePayment()) {
                        $validator->errors()->add('invoice_id', 'This invoice cannot receive payments. Status: ' . $invoice->status);
                    }

                    // Check amount against balance
                    $balance = $invoice->balance;
                    if ($this->amount > $balance) {
                        $validator->errors()->add('amount', "Payment amount (RM {$this->amount}) exceeds outstanding balance (RM " . number_format($balance, 2) . ")");
                    }
                }
            }
        });
    }
}
