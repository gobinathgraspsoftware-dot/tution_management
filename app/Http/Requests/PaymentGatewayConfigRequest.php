<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentGatewayConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('manage-payment-gateway');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'is_active' => 'boolean',
            'is_sandbox' => 'boolean',
            'merchant_id' => 'nullable|string|max:255',
            'transaction_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'transaction_fee_fixed' => 'nullable|numeric|min:0',
            'supported_currencies' => 'nullable|array',
            'supported_currencies.*' => 'string|size:3',
            'configuration' => 'nullable|array',
        ];

        // For create, require gateway_name and credentials
        if ($this->isMethod('POST')) {
            $rules['gateway_name'] = [
                'required',
                'string',
                'max:50',
                Rule::in(array_keys(config('payment_gateways.gateways', []))),
                Rule::unique('payment_gateway_configs', 'gateway_name'),
            ];
            $rules['api_key'] = 'required|string|max:500';
            $rules['api_secret'] = 'required|string|max:500';
            $rules['webhook_secret'] = 'nullable|string|max:500';
        } else {
            // For update, credentials are optional
            $rules['api_key'] = 'nullable|string|max:500';
            $rules['api_secret'] = 'nullable|string|max:500';
            $rules['webhook_secret'] = 'nullable|string|max:500';
        }

        // Gateway-specific rules
        $gatewayName = $this->input('gateway_name') ?? $this->route('paymentGateway')?->gateway_name;

        if ($gatewayName === 'toyyibpay') {
            $rules['configuration.category_code'] = $this->isMethod('POST')
                ? 'required|string|max:50'
                : 'nullable|string|max:50';
            $rules['configuration.payment_channel'] = 'nullable|in:0,1,2';
            $rules['configuration.charge_to_customer'] = 'nullable|in:0,1,2';
        }

        if ($gatewayName === 'billplz') {
            $rules['configuration.collection_id'] = $this->isMethod('POST')
                ? 'required|string|max:50'
                : 'nullable|string|max:50';
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'gateway_name' => 'payment gateway',
            'api_key' => 'API key',
            'api_secret' => 'API secret',
            'webhook_secret' => 'webhook secret',
            'merchant_id' => 'merchant ID',
            'is_active' => 'active status',
            'is_sandbox' => 'sandbox mode',
            'transaction_fee_percentage' => 'fee percentage',
            'transaction_fee_fixed' => 'fixed fee',
            'configuration.category_code' => 'category code',
            'configuration.collection_id' => 'collection ID',
            'configuration.payment_channel' => 'payment channel',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'gateway_name.required' => 'Please select a payment gateway.',
            'gateway_name.in' => 'The selected payment gateway is not supported.',
            'gateway_name.unique' => 'This payment gateway is already configured.',
            'api_key.required' => 'The API key is required to configure this gateway.',
            'api_secret.required' => 'The API secret is required to configure this gateway.',
            'configuration.category_code.required' => 'Category code is required for ToyyibPay.',
            'configuration.collection_id.required' => 'Collection ID is required for Billplz.',
            'transaction_fee_percentage.max' => 'Fee percentage cannot exceed 100%.',
            'supported_currencies.*.size' => 'Currency code must be exactly 3 characters (e.g., MYR).',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('is_sandbox')) {
            $this->merge([
                'is_sandbox' => filter_var($this->is_sandbox, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Ensure supported_currencies is an array
        if ($this->has('supported_currencies') && is_string($this->supported_currencies)) {
            $this->merge([
                'supported_currencies' => array_filter(
                    array_map('trim', explode(',', $this->supported_currencies))
                ),
            ]);
        }

        // Set default supported currency if not provided
        if (!$this->has('supported_currencies') || empty($this->supported_currencies)) {
            $this->merge([
                'supported_currencies' => ['MYR'],
            ]);
        }
    }
}
