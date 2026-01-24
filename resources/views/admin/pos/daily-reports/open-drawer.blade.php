@extends('layouts.app')

@section('title', 'Open Cash Drawer')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Open Cash Drawer</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.pos.index') }}">POS</a></li>
                    <li class="breadcrumb-item active">Open Drawer</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white py-4">
                    <div class="text-center">
                        <i class="fas fa-cash-register fa-3x mb-3"></i>
                        <h4 class="mb-1">Start Your Day</h4>
                        <p class="mb-0 opacity-75">{{ now()->format('l, d M Y') }}</p>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    @if(isset($existingReport) && $existingReport)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            A report already exists for today. The drawer was opened at
                            <strong>{{ $existingReport->opened_at->format('h:i A') }}</strong>
                            with <strong>RM {{ number_format($existingReport->opening_cash, 2) }}</strong>.
                        </div>
                        <div class="text-center">
                            <a href="{{ route('admin.pos.index') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-cart me-2"></i> Continue to POS
                            </a>
                        </div>
                    @else
                        <form action="{{ route('admin.pos.daily-reports.set-opening-cash') }}" method="POST" id="openDrawerForm">
                            @csrf

                            <div class="text-center mb-4">
                                <p class="text-muted">
                                    Enter the starting cash amount in your drawer to begin today's operations.
                                </p>
                            </div>

                            <!-- Last Closing Info -->
                            @if(isset($lastReport) && $lastReport)
                                <div class="bg-light rounded p-3 mb-4">
                                    <h6 class="text-muted mb-2">
                                        <i class="fas fa-history me-1"></i> Last Day's Closing
                                    </h6>
                                    <div class="row text-sm">
                                        <div class="col-6">
                                            <span class="text-muted">Date:</span><br>
                                            <strong>{{ $lastReport->report_date->format('d M Y') }}</strong>
                                        </div>
                                        <div class="col-6 text-end">
                                            <span class="text-muted">Closing Cash:</span><br>
                                            <strong>RM {{ number_format($lastReport->actual_cash ?? $lastReport->expected_cash, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Opening Cash Input -->
                            <div class="mb-4">
                                <label for="opening_cash" class="form-label fw-bold">
                                    <i class="fas fa-coins text-warning me-1"></i>
                                    Opening Cash Amount <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" step="0.01" min="0"
                                           class="form-control form-control-lg @error('opening_cash') is-invalid @enderror"
                                           id="opening_cash" name="opening_cash"
                                           value="{{ old('opening_cash', $suggestedAmount ?? 100) }}"
                                           placeholder="0.00" required autofocus>
                                </div>
                                @error('opening_cash')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    Count all cash in the drawer before starting.
                                </small>
                            </div>

                            <!-- Quick Amount Buttons -->
                            <div class="mb-4">
                                <label class="form-label text-muted small">Quick Select:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach([50, 100, 150, 200, 300, 500] as $amount)
                                        <button type="button" class="btn btn-outline-secondary quick-amount"
                                                data-amount="{{ $amount }}">
                                            RM {{ number_format($amount) }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Notes (Optional) -->
                            <div class="mb-4">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note me-1"></i> Notes (Optional)
                                </label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"
                                          placeholder="Any notes about starting cash...">{{ old('notes') }}</textarea>
                            </div>

                            <!-- Confirmation -->
                            <div class="bg-light rounded p-3 mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="confirmOpen" required>
                                    <label class="form-check-label" for="confirmOpen">
                                        I confirm the opening cash amount is correct.
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                                    <i class="fas fa-unlock me-2"></i> Open Drawer & Start Day
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-lightbulb text-warning me-1"></i> Tips for Cash Management
                    </h6>
                    <ul class="text-muted small mb-0">
                        <li class="mb-2">Count all cash denominations before opening the drawer</li>
                        <li class="mb-2">Keep a consistent opening amount each day</li>
                        <li class="mb-2">Store large bills securely and make regular drops</li>
                        <li>Close the day report before leaving to track discrepancies</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Quick amount buttons
    $('.quick-amount').click(function() {
        const amount = $(this).data('amount');
        $('#opening_cash').val(amount);
        $('.quick-amount').removeClass('btn-primary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
    });

    // Enable submit button when confirmed
    $('#confirmOpen').change(function() {
        $('#submitBtn').prop('disabled', !$(this).is(':checked'));
    });

    // Highlight current amount button on load
    const currentAmount = parseFloat($('#opening_cash').val());
    $(`.quick-amount[data-amount="${currentAmount}"]`).removeClass('btn-outline-secondary').addClass('btn-primary');

    // Validate form
    $('#openDrawerForm').on('submit', function(e) {
        const amount = parseFloat($('#opening_cash').val());
        if (isNaN(amount) || amount < 0) {
            e.preventDefault();
            alert('Please enter a valid opening cash amount.');
            return false;
        }
    });
});
</script>
@endpush
