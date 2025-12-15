@extends('layouts.app')

@section('title', 'Edit Payment Gateway')

@section('page-title', 'Edit Payment Gateway')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.payment-gateways.index') }}">Payment Gateways</a></li>
        <li class="breadcrumb-item active">Edit {{ $gatewayInfo['name'] ?? ucfirst($paymentGateway->gateway_name) }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form action="{{ route('admin.payment-gateways.update', $paymentGateway) }}" method="POST" id="gatewayForm">
                @csrf
                @method('PUT')

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            @php
                                $icons = [
                                    'toyyibpay' => 'fas fa-money-bill-wave text-primary',
                                    'senangpay' => 'fas fa-credit-card text-success',
                                    'billplz' => 'fas fa-file-invoice-dollar text-info',
                                    'eghl' => 'fas fa-globe text-warning',
                                ];
                                $icon = $icons[$paymentGateway->gateway_name] ?? 'fas fa-credit-card';
                            @endphp
                            <i class="{{ $icon }} me-2"></i>
                            {{ $gatewayInfo['name'] ?? ucfirst($paymentGateway->gateway_name) }}
                        </h5>
                        <span class="badge {{ $paymentGateway->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $paymentGateway->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ $gatewayInfo['description'] ?? 'Malaysian Payment Gateway' }}
                        </div>
                    </div>
                </div>

                @if($paymentGateway->gateway_name !== 'eghl')
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>API Credentials</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Leave credential fields empty to keep existing values.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="api_key" class="form-label">API Key / User Secret Key</label>
                                <input type="password" class="form-control @error('api_key') is-invalid @enderror"
                                       id="api_key" name="api_key" placeholder="••••••••">
                                <div class="form-text">Leave empty to keep current value</div>
                                @error('api_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="api_secret" class="form-label">API Secret</label>
                                <input type="password" class="form-control @error('api_secret') is-invalid @enderror"
                                       id="api_secret" name="api_secret" placeholder="••••••••">
                                <div class="form-text">Leave empty to keep current value</div>
                                @error('api_secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="merchant_id" class="form-label">
                                    Merchant ID
                                    @if($paymentGateway->gateway_name === 'senangpay')
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <input type="text" class="form-control @error('merchant_id') is-invalid @enderror"
                                       id="merchant_id" name="merchant_id"
                                       value="{{ old('merchant_id', $paymentGateway->merchant_id) }}"
                                       @if($paymentGateway->gateway_name === 'senangpay') required @endif>
                                <div class="form-text">
                                    @if($paymentGateway->gateway_name === 'senangpay')
                                        Required - Your SenangPay Merchant ID
                                    @else
                                        Optional
                                    @endif
                                </div>
                                @error('merchant_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="webhook_secret" class="form-label">Webhook Secret</label>
                                <input type="password" class="form-control @error('webhook_secret') is-invalid @enderror"
                                       id="webhook_secret" name="webhook_secret" placeholder="••••••••">
                                <div class="form-text">Leave empty to keep current value</div>
                                @error('webhook_secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($paymentGateway->gateway_name === 'toyyibpay')
                <!-- ToyyibPay Specific Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>ToyyibPay Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="toyyibpay_category_code" class="form-label">Category Code</label>
                                <input type="text" class="form-control @error('configuration.category_code') is-invalid @enderror"
                                       id="toyyibpay_category_code" name="configuration[category_code]"
                                       value="{{ old('configuration.category_code', $paymentGateway->configuration['category_code'] ?? '') }}">
                                @error('configuration.category_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="toyyibpay_payment_channel" class="form-label">Payment Channel</label>
                                <select class="form-select @error('configuration.payment_channel') is-invalid @enderror"
                                        id="toyyibpay_payment_channel" name="configuration[payment_channel]">
                                    <option value="0" {{ old('configuration.payment_channel', $paymentGateway->configuration['payment_channel'] ?? '0') == '0' ? 'selected' : '' }}>FPX Only</option>
                                    <option value="1" {{ old('configuration.payment_channel', $paymentGateway->configuration['payment_channel'] ?? '0') == '1' ? 'selected' : '' }}>Credit/Debit Card Only</option>
                                    <option value="2" {{ old('configuration.payment_channel', $paymentGateway->configuration['payment_channel'] ?? '0') == '2' ? 'selected' : '' }}>Both FPX & Card</option>
                                </select>
                                @error('configuration.payment_channel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="toyyibpay_charge_to_customer" class="form-label">Charge Fee To</label>
                                <select class="form-select @error('configuration.charge_to_customer') is-invalid @enderror"
                                        id="toyyibpay_charge_to_customer" name="configuration[charge_to_customer]">
                                    <option value="0" {{ old('configuration.charge_to_customer', $paymentGateway->configuration['charge_to_customer'] ?? '1') == '0' ? 'selected' : '' }}>Include in Bill Amount</option>
                                    <option value="1" {{ old('configuration.charge_to_customer', $paymentGateway->configuration['charge_to_customer'] ?? '1') == '1' ? 'selected' : '' }}>Charge to Customer (on top)</option>
                                    <option value="2" {{ old('configuration.charge_to_customer', $paymentGateway->configuration['charge_to_customer'] ?? '1') == '2' ? 'selected' : '' }}>Deduct from Bill Amount</option>
                                </select>
                                @error('configuration.charge_to_customer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($paymentGateway->gateway_name === 'billplz')
                <!-- Billplz Specific Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Billplz Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="billplz_collection_id" class="form-label">Collection ID</label>
                                <input type="text" class="form-control @error('configuration.collection_id') is-invalid @enderror"
                                       id="billplz_collection_id" name="configuration[collection_id]"
                                       value="{{ old('configuration.collection_id', $paymentGateway->configuration['collection_id'] ?? '') }}">
                                @error('configuration.collection_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($paymentGateway->gateway_name === 'eghl')
                <!-- EGHL Specific Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-globe me-2"></i>eGHL Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="eghl_merchant_id" class="form-label">Merchant ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('configuration.merchant_id') is-invalid @enderror"
                                       id="eghl_merchant_id" name="configuration[merchant_id]"
                                       value="{{ old('configuration.merchant_id', $paymentGateway->configuration['merchant_id'] ?? '') }}"
                                       placeholder="e.g., DEMO0001">
                                <div class="form-text">Your EGHL Merchant ID</div>
                                @error('configuration.merchant_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="eghl_merchant_password" class="form-label">Merchant Password</label>
                                <input type="password" class="form-control @error('configuration.merchant_password') is-invalid @enderror"
                                       id="eghl_merchant_password" name="configuration[merchant_password]"
                                       placeholder="••••••••">
                                <div class="form-text">Your EGHL Service ID / Password (leave empty to keep current)</div>
                                @error('configuration.merchant_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="eghl_merchant_registered_name" class="form-label">Merchant Registered Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('configuration.merchant_registered_name') is-invalid @enderror"
                                       id="eghl_merchant_registered_name" name="configuration[merchant_registered_name]"
                                       value="{{ old('configuration.merchant_registered_name', $paymentGateway->configuration['merchant_registered_name'] ?? '') }}"
                                       placeholder="Your Company Name">
                                <div class="form-text">Your registered business name with EGHL</div>
                                @error('configuration.merchant_registered_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="eghl_sandbox_url" class="form-label">Sandbox URL <span class="text-danger">*</span></label>
                                <input type="url" class="form-control @error('configuration.sandbox_url') is-invalid @enderror"
                                       id="eghl_sandbox_url" name="configuration[sandbox_url]"
                                       value="{{ old('configuration.sandbox_url', $paymentGateway->configuration['sandbox_url'] ?? 'https://test2pay.ghl.com/IPGSG/Payment.aspx') }}"
                                       required>
                                <div class="form-text">EGHL sandbox/development payment URL</div>
                                @error('configuration.sandbox_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="eghl_production_url" class="form-label">Production URL <span class="text-danger">*</span></label>
                                <input type="url" class="form-control @error('configuration.production_url') is-invalid @enderror"
                                       id="eghl_production_url" name="configuration[production_url]"
                                       value="{{ old('configuration.production_url', $paymentGateway->configuration['production_url'] ?? 'https://pay.ghl.com/IPGSG/Payment.aspx') }}"
                                       required>
                                <div class="form-text">EGHL production/live payment URL</div>
                                @error('configuration.production_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="eghl_currencies" class="form-label">Supported Currencies</label>
                                @php
                                    $supportedCurrencies = $paymentGateway->supported_currencies ?? ['MYR'];
                                @endphp
                                <select class="form-select @error('supported_currencies') is-invalid @enderror"
                                        id="eghl_currencies" name="supported_currencies[]" multiple>
                                    <option value="MYR" {{ in_array('MYR', $supportedCurrencies) ? 'selected' : '' }}>MYR - Malaysian Ringgit</option>
                                    <option value="USD" {{ in_array('USD', $supportedCurrencies) ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="SGD" {{ in_array('SGD', $supportedCurrencies) ? 'selected' : '' }}>SGD - Singapore Dollar</option>
                                    <option value="THB" {{ in_array('THB', $supportedCurrencies) ? 'selected' : '' }}>THB - Thai Baht</option>
                                    <option value="IDR" {{ in_array('IDR', $supportedCurrencies) ? 'selected' : '' }}>IDR - Indonesian Rupiah</option>
                                    <option value="CNY" {{ in_array('CNY', $supportedCurrencies) ? 'selected' : '' }}>CNY - Chinese Yuan</option>
                                </select>
                                <div class="form-text">Hold Ctrl/Cmd to select multiple currencies</div>
                                @error('supported_currencies')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Available Payment Methods</label>
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
                @endif

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
                                           value="{{ old('transaction_fee_percentage', $paymentGateway->transaction_fee_percentage) }}">
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
                                           value="{{ old('transaction_fee_fixed', $paymentGateway->transaction_fee_fixed) }}">
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
                                           {{ old('is_sandbox', $paymentGateway->is_sandbox) ? 'checked' : '' }}>
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
                                           {{ old('is_active', $paymentGateway->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Active</strong>
                                        <div class="text-muted small">Enable this gateway for accepting payments</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        @if(!$paymentGateway->is_sandbox && $paymentGateway->is_active)
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle me-2"></i>
                            This gateway is currently in <strong>production mode</strong> and accepting real payments.
                        </div>
                        @elseif($paymentGateway->is_sandbox && $paymentGateway->is_active)
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This gateway is in <strong>sandbox mode</strong>. Payments will use test environment.
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-vial me-2"></i>Connection Test</h5>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-outline-primary" id="testConnection">
                            <i class="fas fa-plug me-1"></i> Test Connection
                        </button>
                        <div id="testResult" class="mt-3" style="display: none;"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Cancel
                    </a>
                    <div>
                        <button type="button" class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-1"></i> Delete
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Payment Gateway</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this payment gateway configuration?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This action cannot be undone. If there are existing transactions, you cannot delete this gateway.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.payment-gateways.destroy', $paymentGateway) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Gateway
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#testConnection').click(function() {
        var btn = $(this);
        var resultDiv = $('#testResult');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Testing...');
        resultDiv.hide();

        $.ajax({
            url: '{{ route("admin.payment-gateways.test-connection", $paymentGateway) }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    resultDiv.html('<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>' + response.message + '</div>');
                } else {
                    resultDiv.html('<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>' + response.message + '</div>');
                }
                resultDiv.show();
            },
            error: function(xhr) {
                resultDiv.html('<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Connection test failed</div>');
                resultDiv.show();
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-plug me-1"></i> Test Connection');
            }
        });
    });
});
</script>
@endpush
@endsection
