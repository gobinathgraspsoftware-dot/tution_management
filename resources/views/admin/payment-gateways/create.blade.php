@extends('layouts.app')

@section('title', 'Add Payment Gateway')

@section('page-title', 'Add Payment Gateway')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.payment-gateways.index') }}">Payment Gateways</a></li>
        <li class="breadcrumb-item active">Add Gateway</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form action="{{ route('admin.payment-gateways.store') }}" method="POST" id="gatewayForm">
                @csrf

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Gateway Selection</h5>
                    </div>
                    <div class="card-body">
                        <!-- Gateway Selection -->
                        @if($gatewayType)
                            <input type="hidden" name="gateway_name" value="{{ $gatewayType }}">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Configuring <strong>{{ $availableGateways[$gatewayType]['name'] ?? ucfirst($gatewayType) }}</strong>
                            </div>
                        @else
                            <div class="mb-3">
                                <label for="gateway_name" class="form-label">Select Payment Gateway <span class="text-danger">*</span></label>
                                <select class="form-select @error('gateway_name') is-invalid @enderror"
                                        id="gateway_name" name="gateway_name" required>
                                    <option value="">-- Select Gateway --</option>
                                    @foreach($unconfiguredGateways as $name => $info)
                                        <option value="{{ $name }}" {{ old('gateway_name') == $name ? 'selected' : '' }}>
                                            {{ $info['name'] ?? ucfirst($name) }} - {{ $info['description'] ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('gateway_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>API Credentials</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="api_key" class="form-label">API Key / User Secret Key <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('api_key') is-invalid @enderror"
                                       id="api_key" name="api_key" value="{{ old('api_key') }}" required>
                                <div class="form-text">Your gateway API key or user secret key</div>
                                @error('api_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="api_secret" class="form-label">
                                    <span id="api_secret_label">API Secret</span> <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control @error('api_secret') is-invalid @enderror"
                                       id="api_secret" name="api_secret" value="{{ old('api_secret') }}" required>
                                <div class="form-text" id="api_secret_help">Your gateway API secret or secret key</div>
                                @error('api_secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="merchant_id" class="form-label">
                                    <span id="merchant_id_label">Merchant ID</span>
                                </label>
                                <input type="text" class="form-control @error('merchant_id') is-invalid @enderror"
                                       id="merchant_id" name="merchant_id" value="{{ old('merchant_id') }}">
                                <div class="form-text" id="merchant_id_help">Required for SenangPay and EGHL</div>
                                @error('merchant_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="webhook_secret" class="form-label">Webhook Secret</label>
                                <input type="password" class="form-control @error('webhook_secret') is-invalid @enderror"
                                       id="webhook_secret" name="webhook_secret" value="{{ old('webhook_secret') }}">
                                <div class="form-text">For verifying webhook signatures</div>
                                @error('webhook_secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ToyyibPay Specific Settings -->
                <div class="card mb-4 gateway-config" id="toyyibpay-config" style="display: none;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>ToyyibPay Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="toyyibpay_category_code" class="form-label">Category Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('configuration.category_code') is-invalid @enderror"
                                       id="toyyibpay_category_code" name="configuration[category_code]"
                                       value="{{ old('configuration.category_code') }}">
                                <div class="form-text">Get this from your ToyyibPay dashboard</div>
                                @error('configuration.category_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="toyyibpay_payment_channel" class="form-label">Payment Channel</label>
                                <select class="form-select @error('configuration.payment_channel') is-invalid @enderror"
                                        id="toyyibpay_payment_channel" name="configuration[payment_channel]">
                                    <option value="0" {{ old('configuration.payment_channel') == '0' ? 'selected' : '' }}>FPX Only</option>
                                    <option value="1" {{ old('configuration.payment_channel') == '1' ? 'selected' : '' }}>Credit/Debit Card Only</option>
                                    <option value="2" {{ old('configuration.payment_channel') == '2' ? 'selected' : '' }}>Both FPX & Card</option>
                                </select>
                                @error('configuration.payment_channel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="toyyibpay_charge_to_customer" class="form-label">Charge Fee To</label>
                                <select class="form-select @error('configuration.charge_to_customer') is-invalid @enderror"
                                        id="toyyibpay_charge_to_customer" name="configuration[charge_to_customer]">
                                    <option value="0" {{ old('configuration.charge_to_customer', '1') == '0' ? 'selected' : '' }}>Include in Bill Amount</option>
                                    <option value="1" {{ old('configuration.charge_to_customer', '1') == '1' ? 'selected' : '' }}>Charge to Customer (on top)</option>
                                    <option value="2" {{ old('configuration.charge_to_customer', '1') == '2' ? 'selected' : '' }}>Deduct from Bill Amount</option>
                                </select>
                                @error('configuration.charge_to_customer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billplz Specific Settings -->
                <div class="card mb-4 gateway-config" id="billplz-config" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Billplz Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="billplz_collection_id" class="form-label">Collection ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('configuration.collection_id') is-invalid @enderror"
                                       id="billplz_collection_id" name="configuration[collection_id]"
                                       value="{{ old('configuration.collection_id') }}">
                                <div class="form-text">Get this from your Billplz dashboard</div>
                                @error('configuration.collection_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EGHL Specific Settings -->
                <div class="card mb-4 gateway-config" id="eghl-config" style="display: none;">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-globe me-2"></i>eGHL Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> For EGHL, the API Secret field should contain your <strong>Service ID</strong> (also called Password),
                            and Merchant ID is required.
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="eghl_currencies" class="form-label">Supported Currencies</label>
                                <select class="form-select @error('supported_currencies') is-invalid @enderror"
                                        id="eghl_currencies" name="supported_currencies[]" multiple>
                                    <option value="MYR" selected>MYR - Malaysian Ringgit</option>
                                    <option value="USD">USD - US Dollar</option>
                                    <option value="SGD">SGD - Singapore Dollar</option>
                                    <option value="THB">THB - Thai Baht</option>
                                    <option value="IDR">IDR - Indonesian Rupiah</option>
                                    <option value="CNY">CNY - Chinese Yuan</option>
                                </select>
                                <div class="form-text">Hold Ctrl/Cmd to select multiple currencies. MYR is selected by default.</div>
                                @error('supported_currencies')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Available Payment Methods</label>
                                <div class="form-text mb-2">EGHL supports:</div>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Credit/Debit Cards (Visa, Mastercard, Amex, UnionPay)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>FPX Online Banking</li>
                                    <li><i class="fas fa-check text-success me-2"></i>E-Wallets (Touch 'n Go, Boost, GrabPay, etc.)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Alipay (for Chinese customers)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-percentage me-2"></i>Fee Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="transaction_fee_percentage" class="form-label">Transaction Fee (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100"
                                           class="form-control @error('transaction_fee_percentage') is-invalid @enderror"
                                           id="transaction_fee_percentage" name="transaction_fee_percentage"
                                           value="{{ old('transaction_fee_percentage', '0') }}">
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('transaction_fee_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="transaction_fee_fixed" class="form-label">Fixed Fee (RM)</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" step="0.01" min="0"
                                           class="form-control @error('transaction_fee_fixed') is-invalid @enderror"
                                           id="transaction_fee_fixed" name="transaction_fee_fixed"
                                           value="{{ old('transaction_fee_fixed', '0') }}">
                                </div>
                                @error('transaction_fee_fixed')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-toggle-on me-2"></i>Status Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           id="is_sandbox" name="is_sandbox" value="1"
                                           {{ old('is_sandbox', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_sandbox">
                                        <strong>Sandbox Mode</strong>
                                        <div class="text-muted small">Enable for testing with sandbox environment</div>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           id="is_active" name="is_active" value="1"
                                           {{ old('is_active') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Active</strong>
                                        <div class="text-muted small">Enable this gateway for accepting payments</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> Make sure to test with sandbox mode first before enabling production mode.
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Gateway Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    function showGatewayConfig() {
        var selectedGateway = $('#gateway_name').val() || '{{ $gatewayType }}';

        // Hide all gateway configs
        $('.gateway-config').hide();

        // Show selected gateway config
        if (selectedGateway) {
            $('#' + selectedGateway + '-config').show();

            // Update labels based on gateway
            if (selectedGateway === 'eghl') {
                $('#api_secret_label').text('Service ID (Password)');
                $('#api_secret_help').text('Your EGHL Service ID - this is different from your login password');
                $('#merchant_id_label').text('Merchant ID');
                $('#merchant_id_help').text('Required - Your EGHL Merchant ID');
                $('#merchant_id').prop('required', true);
            } else {
                $('#api_secret_label').text('API Secret');
                $('#api_secret_help').text('Your gateway API secret or secret key');
                $('#merchant_id_label').text('Merchant ID');

                if (selectedGateway === 'senangpay') {
                    $('#merchant_id_help').text('Required - Your SenangPay Merchant ID');
                    $('#merchant_id').prop('required', true);
                } else {
                    $('#merchant_id_help').text('Optional - Required for some gateways');
                    $('#merchant_id').prop('required', false);
                }
            }
        }
    }

    // Initial load
    showGatewayConfig();

    // On change
    $('#gateway_name').change(showGatewayConfig);
});
</script>
@endpush
@endsection
