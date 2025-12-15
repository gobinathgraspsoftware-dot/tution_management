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
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $gatewayName = $this->input('gateway_name');
        $isEghl = $gatewayName === 'eghl';

        return [
            // Gateway basic info
            'gateway_name' => ['required', 'string', Rule::in(['toyyibpay', 'senangpay', 'billplz', 'eghl'])],
            
            // API Credentials - NOT required for EGHL (uses configuration instead)
            'api_key' => $isEghl ? 'nullable|string' : 'required|string|max:255',
            'api_secret' => $isEghl ? 'nullable|string' : 'required|string|max:255',
            'merchant_id' => [
                Rule::requiredIf(function () use ($gatewayName) {
                    return in_array($gatewayName, ['senangpay']);
                }),
                'nullable',
                'string',
                'max:255'
            ],
            'webhook_secret' => 'nullable|string|max:255',
            
            // Status flags
            'is_active' => 'nullable|boolean',
            'is_sandbox' => 'nullable|boolean',
            
            // Currencies
            'supported_currencies' => 'nullable|array',
            'supported_currencies.*' => 'string|max:3',
            
            // Fees
            'transaction_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'transaction_fee_fixed' => 'nullable|numeric|min:0',
            
            // Configuration (for EGHL and other gateway-specific settings)
            'configuration' => 'nullable|array',
            
            // EGHL-specific configuration fields (required when gateway is eghl)
            'configuration.merchant_id' => $isEghl ? 'required|string|max:255' : 'nullable|string|max:255',
            'configuration.merchant_password' => [
                Rule::requiredIf(function () use ($isEghl) {
                    return $isEghl && $this->isMethod('POST'); // Required on create, optional on update
                }),
                'nullable',
                'string',
                'max:255'
            ],
            'configuration.merchant_registered_name' => $isEghl ? 'required|string|max:255' : 'nullable|string|max:255',
            'configuration.sandbox_url' => $isEghl ? 'required|url|max:500' : 'nullable|url|max:500',
            'configuration.production_url' => $isEghl ? 'required|url|max:500' : 'nullable|url|max:500',
            
            // ToyyibPay-specific configuration
            'configuration.category_code' => [
                Rule::requiredIf($gatewayName === 'toyyibpay'),
                'nullable',
                'string'
            ],
            'configuration.payment_channel' => 'nullable|in:0,1,2',
            'configuration.charge_to_customer' => 'nullable|in:0,1,2',
            
            // Billplz-specific configuration
            'configuration.collection_id' => [
                Rule::requiredIf($gatewayName === 'billplz'),
                'nullable',
                'string'
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'gateway_name.required' => 'Please select a payment gateway.',
            'gateway_name.in' => 'Invalid payment gateway selected.',
            
            'api_key.required' => 'The API key is required to configure this gateway.',
            'api_secret.required' => 'The API secret is required to configure this gateway.',
            
            'configuration.merchant_id.required' => 'Merchant ID is required for EGHL.',
            'configuration.merchant_password.required' => 'Merchant Password is required for EGHL.',
            'configuration.merchant_registered_name.required' => 'Merchant Registered Name is required for EGHL.',
            'configuration.sandbox_url.required' => 'Sandbox URL is required for EGHL.',
            'configuration.sandbox_url.url' => 'Sandbox URL must be a valid URL.',
            'configuration.production_url.required' => 'Production URL is required for EGHL.',
            'configuration.production_url.url' => 'Production URL must be a valid URL.',
            
            'configuration.category_code.required' => 'Category code is required for ToyyibPay.',
            'configuration.collection_id.required' => 'Collection ID is required for Billplz.',
            
            'merchant_id.required' => 'Merchant ID is required for this gateway.',
            
            'supported_currencies.*.max' => 'Currency code must be 3 characters.',
            'transaction_fee_percentage.numeric' => 'Transaction fee percentage must be a number.',
            'transaction_fee_percentage.min' => 'Transaction fee percentage cannot be negative.',
            'transaction_fee_percentage.max' => 'Transaction fee percentage cannot exceed 100.',
            'transaction_fee_fixed.numeric' => 'Fixed transaction fee must be a number.',
            'transaction_fee_fixed.min' => 'Fixed transaction fee cannot be negative.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'gateway_name' => 'payment gateway',
            'api_key' => 'API key',
            'api_secret' => 'API secret',
            'merchant_id' => 'merchant ID',
            'webhook_secret' => 'webhook secret',
            'is_active' => 'active status',
            'is_sandbox' => 'sandbox mode',
            'supported_currencies' => 'supported currencies',
            'transaction_fee_percentage' => 'transaction fee percentage',
            'transaction_fee_fixed' => 'fixed transaction fee',
            'configuration.merchant_id' => 'merchant ID',
            'configuration.merchant_password' => 'merchant password',
            'configuration.merchant_registered_name' => 'merchant registered name',
            'configuration.sandbox_url' => 'sandbox URL',
            'configuration.production_url' => 'production URL',
            'configuration.category_code' => 'category code',
            'configuration.payment_channel' => 'payment channel',
            'configuration.charge_to_customer' => 'charge to customer',
            'configuration.collection_id' => 'collection ID',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to boolean
        $this->merge([
            'is_active' => $this->has('is_active'),
            'is_sandbox' => $this->has('is_sandbox'),
        ]);

        // If EGHL, encrypt the password in configuration before storing
        if ($this->input('gateway_name') === 'eghl' && $this->has('configuration.merchant_password')) {
            $config = $this->input('configuration', []);
            if (!empty($config['merchant_password'])) {
                $config['merchant_password'] = encrypt($config['merchant_password']);
                $this->merge(['configuration' => $config]);
            }
        }
    }
}
