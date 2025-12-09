<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment gateway that will be used when
    | processing online payments. You may set this to any of the gateways
    | defined in the "gateways" array below.
    |
    */

    'default' => env('PAYMENT_GATEWAY', 'toyyibpay'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Currency
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default currency for all payment gateways.
    |
    */

    'currency' => env('PAYMENT_CURRENCY', 'MYR'),

    /*
    |--------------------------------------------------------------------------
    | Online Payment Fee
    |--------------------------------------------------------------------------
    |
    | This is the additional fee charged for online payments (RM130 as per requirements)
    |
    */

    'online_fee' => env('ONLINE_PAYMENT_FEE', 130.00),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the payment gateways supported by your
    | application. Supported gateways: toyyibpay, senangpay, billplz
    |
    */

    'gateways' => [

        'toyyibpay' => [
            'name' => 'ToyyibPay',
            'description' => 'Malaysian FPX & Card Payment Gateway',
            'class' => \App\Services\Gateways\ToyyibPayGateway::class,
            'sandbox_url' => 'https://dev.toyyibpay.com',
            'production_url' => 'https://toyyibpay.com',
            'supported_currencies' => ['MYR'],
            'supported_methods' => ['fpx', 'card'],
            'fee_percentage' => env('TOYYIBPAY_FEE_PERCENT', 1.5),
            'fee_fixed' => env('TOYYIBPAY_FEE_FIXED', 0.00),
        ],

        'senangpay' => [
            'name' => 'SenangPay',
            'description' => 'Easy Malaysian Payment Gateway',
            'class' => \App\Services\Gateways\SenangPayGateway::class,
            'sandbox_url' => 'https://sandbox.senangpay.my',
            'production_url' => 'https://app.senangpay.my',
            'supported_currencies' => ['MYR'],
            'supported_methods' => ['fpx', 'card'],
            'fee_percentage' => env('SENANGPAY_FEE_PERCENT', 2.0),
            'fee_fixed' => env('SENANGPAY_FEE_FIXED', 0.20),
        ],

        'billplz' => [
            'name' => 'Billplz',
            'description' => 'Simple Malaysian Payment Platform',
            'class' => \App\Services\Gateways\BillplzGateway::class,
            'sandbox_url' => 'https://www.billplz-sandbox.com/api/v3',
            'production_url' => 'https://www.billplz.com/api/v3',
            'supported_currencies' => ['MYR'],
            'supported_methods' => ['fpx'],
            'fee_percentage' => env('BILLPLZ_FEE_PERCENT', 1.0),
            'fee_fixed' => env('BILLPLZ_FEE_FIXED', 0.00),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    |
    | These URLs will be used for payment gateway callbacks
    |
    */

    'callback' => [
        'success' => env('PAYMENT_SUCCESS_URL', '/payment/success'),
        'failed' => env('PAYMENT_FAILED_URL', '/payment/failed'),
        'webhook' => env('PAYMENT_WEBHOOK_URL', '/api/payment/webhook'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Settings
    |--------------------------------------------------------------------------
    */

    'transaction' => [
        'prefix' => 'TXN',
        'expiry_minutes' => 30,
        'auto_cancel_pending' => true,
    ],

];
