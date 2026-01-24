@extends('layouts.app')

@section('title', 'Close Day - ' . $report->report_date->format('d M Y'))

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Close Day</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.daily-cash-reports.index') }}">Daily Reports</a></li>
                    <li class="breadcrumb-item active">Close Day</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.daily-cash-reports.show', $report) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Report
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Summary Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-day me-2"></i>
                            {{ $report->report_date->format('l, d M Y') }}
                        </h5>
                        <span class="badge bg-light text-primary">End of Day</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Today's Summary -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-chart-line me-1"></i> Day Summary
                            </h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td>Total Transactions</td>
                                    <td class="text-end"><strong>{{ number_format($report->total_transactions) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Cash Sales</td>
                                    <td class="text-end text-success">RM {{ number_format($report->cash_sales, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>QR Sales</td>
                                    <td class="text-end text-primary">RM {{ number_format($report->qr_sales, 2) }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Total Sales</strong></td>
                                    <td class="text-end"><strong>RM {{ number_format($report->total_sales, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Cash Calculation -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-calculator me-1"></i> Cash Calculation
                            </h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td>Opening Cash</td>
                                    <td class="text-end">RM {{ number_format($report->opening_cash, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Cash Sales</td>
                                    <td class="text-end text-success">+ RM {{ number_format($report->cash_sales, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Cash Refunds</td>
                                    <td class="text-end text-danger">- RM {{ number_format($report->cash_refunds ?? 0, 2) }}</td>
                                </tr>
                                <tr class="border-top bg-light">
                                    <td><strong>Expected Cash in Drawer</strong></td>
                                    <td class="text-end"><strong class="text-primary">RM {{ number_format($report->expected_cash, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cash Count Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-coins text-warning me-2"></i>
                        Cash Count & Reconciliation
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.daily-cash-reports.close.store', $report) }}" method="POST" id="closeForm">
                        @csrf

                        <!-- Denomination Count (Optional) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-money-bill-alt me-1"></i>
                                Cash Denomination Count (Optional)
                            </label>
                            <p class="text-muted small mb-3">Count each denomination to help verify total cash amount.</p>

                            <div class="row g-2">
                                @php
                                    $denominations = [100, 50, 20, 10, 5, 1, 0.50, 0.20, 0.10, 0.05];
                                @endphp
                                @foreach($denominations as $denom)
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">
                                                @if($denom >= 1)
                                                    RM{{ number_format($denom, 0) }}
                                                @else
                                                    {{ number_format($denom * 100, 0) }}c
                                                @endif
                                            </span>
                                            <input type="number" class="form-control denomination-input"
                                                   name="denominations[{{ $denom }}]"
                                                   data-value="{{ $denom }}"
                                                   min="0" value="0" placeholder="0">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-2 text-end">
                                <span class="text-muted">Counted Total: </span>
                                <strong id="denominationTotal">RM 0.00</strong>
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="applyCount">
                                    <i class="fas fa-check me-1"></i> Apply to Actual Cash
                                </button>
                            </div>
                        </div>

                        <hr>

                        <!-- Actual Cash Input -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="actual_cash" class="form-label fw-bold">
                                    <i class="fas fa-cash-register me-1"></i>
                                    Actual Cash in Drawer <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" step="0.01" min="0"
                                           class="form-control @error('actual_cash') is-invalid @enderror"
                                           id="actual_cash" name="actual_cash"
                                           value="{{ old('actual_cash') }}"
                                           placeholder="0.00" required>
                                </div>
                                @error('actual_cash')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Variance</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">RM</span>
                                    <input type="text" class="form-control" id="varianceDisplay" readonly>
                                    <span class="input-group-text" id="varianceStatus">
                                        <i class="fas fa-minus-circle text-muted"></i>
                                    </span>
                                </div>
                                <small class="text-muted">Expected: RM {{ number_format($report->expected_cash, 2) }}</small>
                            </div>
                        </div>

                        <!-- Variance Warning -->
                        <div class="alert alert-warning d-none mb-4" id="varianceWarning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Cash Variance Detected!</strong>
                            <span id="varianceMessage"></span>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label fw-bold">
                                <i class="fas fa-sticky-note me-1"></i>
                                Closing Notes
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3"
                                      placeholder="Any notes about today's operations, discrepancies, issues, etc.">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirmation -->
                        <div class="bg-light rounded p-3 mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmClose" required>
                                <label class="form-check-label" for="confirmClose">
                                    I confirm that I have counted all cash and the above information is accurate.
                                    <strong>This action cannot be undone.</strong>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.daily-cash-reports.show', $report) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                                <i class="fas fa-lock me-2"></i> Close Day & Lock Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const expectedCash = {{ $report->expected_cash }};

    // Calculate denomination total
    function calculateDenominationTotal() {
        let total = 0;
        $('.denomination-input').each(function() {
            const value = parseFloat($(this).data('value')) || 0;
            const qty = parseInt($(this).val()) || 0;
            total += value * qty;
        });
        $('#denominationTotal').text('RM ' + total.toFixed(2));
        return total;
    }

    // Apply denomination count to actual cash
    $('#applyCount').click(function() {
        const total = calculateDenominationTotal();
        $('#actual_cash').val(total.toFixed(2)).trigger('input');
    });

    // Update on denomination change
    $('.denomination-input').on('input', function() {
        calculateDenominationTotal();
    });

    // Calculate variance
    $('#actual_cash').on('input', function() {
        const actual = parseFloat($(this).val()) || 0;
        const variance = actual - expectedCash;

        $('#varianceDisplay').val(variance.toFixed(2));

        if (variance === 0) {
            $('#varianceStatus').html('<i class="fas fa-check-circle text-success"></i>');
            $('#varianceWarning').addClass('d-none');
        } else if (variance > 0) {
            $('#varianceStatus').html('<i class="fas fa-arrow-up text-info"></i>');
            $('#varianceWarning').removeClass('d-none alert-warning alert-danger').addClass('alert-info');
            $('#varianceMessage').text(' Cash is RM ' + variance.toFixed(2) + ' OVER expected amount.');
        } else {
            $('#varianceStatus').html('<i class="fas fa-arrow-down text-danger"></i>');
            $('#varianceWarning').removeClass('d-none alert-info').addClass('alert-danger');
            $('#varianceMessage').text(' Cash is RM ' + Math.abs(variance).toFixed(2) + ' SHORT of expected amount.');
        }
    });

    // Enable submit button when confirmed
    $('#confirmClose').change(function() {
        $('#submitBtn').prop('disabled', !$(this).is(':checked'));
    });

    // Confirm before submit
    $('#closeForm').on('submit', function(e) {
        const actual = parseFloat($('#actual_cash').val()) || 0;
        const variance = actual - expectedCash;

        if (Math.abs(variance) > 10) {
            if (!confirm('There is a significant variance of RM ' + Math.abs(variance).toFixed(2) + '. Are you sure you want to close the day?')) {
                e.preventDefault();
                return false;
            }
        }

        if (!confirm('Are you sure you want to close the day? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endpush
