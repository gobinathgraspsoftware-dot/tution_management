@extends('layouts.app')

@section('title', 'Bulk Generate Invoices')
@section('page-title', 'Bulk Generate Invoices')

@push('styles')
<style>
    .generate-summary {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
    }
    .enrollment-card {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    .enrollment-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .enrollment-card.selected {
        border-color: #28a745;
        background-color: #f8fff9;
    }
    .enrollment-card.has-invoice {
        opacity: 0.6;
        background-color: #f5f5f5;
    }
    .month-selector {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .fee-badge {
        font-size: 1.1em;
        font-weight: 600;
    }
    .select-all-container {
        background: #e3f2fd;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-file-invoice-dollar me-2"></i> Bulk Generate Invoices
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">Invoices</a></li>
            <li class="breadcrumb-item active">Bulk Generate</li>
        </ol>
    </nav>
</div>

<!-- Month Selection -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="month-selector">
            <h5 class="mb-3"><i class="fas fa-calendar-alt me-2"></i> Select Billing Month</h5>
            <form action="{{ route('admin.invoices.bulk-generate') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="month" class="form-label">Billing Month</label>
                    <input type="month" name="month" id="month" class="form-control form-control-lg"
                           value="{{ $month->format('Y-m') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-search me-2"></i> Load
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="generate-summary h-100 d-flex flex-column justify-content-center">
            <h4 class="mb-3">
                <i class="fas fa-chart-pie me-2"></i> Generation Summary for {{ $month->format('F Y') }}
            </h4>
            <div class="row text-center">
                <div class="col-4">
                    <h2 class="mb-0">{{ $enrollments->count() }}</h2>
                    <small>Total Enrollments</small>
                </div>
                <div class="col-4">
                    <h2 class="mb-0">{{ $enrollments->where('has_invoice', false)->count() }}</h2>
                    <small>Can Generate</small>
                </div>
                <div class="col-4">
                    <h2 class="mb-0">{{ $enrollments->where('has_invoice', true)->count() }}</h2>
                    <small>Already Have Invoice</small>
                </div>
            </div>
        </div>
    </div>
</div>

@if($enrollments->isEmpty())
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        No active enrollments found. Please ensure there are approved students with active enrollments.
    </div>
@else
<form action="{{ route('admin.invoices.bulk-generate') }}" method="POST" id="bulkGenerateForm">
    @csrf
    <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">

    <!-- Selection Controls -->
    <div class="select-all-container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="selectAll">
                    <label class="form-check-label fw-bold" for="selectAll">
                        Select All Available Enrollments
                    </label>
                </div>
                <small class="text-muted">
                    Only enrollments without existing invoices for {{ $month->format('F Y') }} can be selected.
                </small>
            </div>
            <div class="col-md-6 text-end">
                <span class="badge bg-primary fs-6" id="selectedCount">0 selected</span>
                <span class="badge bg-success fs-6" id="totalAmount">RM 0.00</span>
            </div>
        </div>
    </div>

    <!-- Enrollments List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> Active Enrollments</span>
            <div>
                <input type="text" id="searchEnrollment" class="form-control form-control-sm"
                       placeholder="Search student..." style="width: 250px;">
            </div>
        </div>
        <div class="card-body">
            <div class="row" id="enrollmentsList">
                @foreach($enrollments as $item)
                    @php
                        $enrollment = $item['enrollment'];
                        $hasInvoice = $item['has_invoice'];
                        $monthlyFee = $item['monthly_fee'];
                    @endphp
                    <div class="col-lg-6 enrollment-item"
                         data-student="{{ strtolower($enrollment->student->user->name ?? '') }}"
                         data-fee="{{ $monthlyFee }}">
                        <div class="enrollment-card {{ $hasInvoice ? 'has-invoice' : '' }}">
                            <div class="d-flex align-items-start">
                                @if(!$hasInvoice)
                                    <div class="form-check me-3">
                                        <input type="checkbox" class="form-check-input enrollment-checkbox"
                                               name="enrollment_ids[]" value="{{ $enrollment->id }}"
                                               id="enrollment_{{ $enrollment->id }}"
                                               data-fee="{{ $monthlyFee }}">
                                    </div>
                                @else
                                    <div class="me-3">
                                        <i class="fas fa-check-circle text-success fs-4" title="Invoice already exists"></i>
                                    </div>
                                @endif

                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                {{ $enrollment->student->user->name ?? 'Unknown Student' }}
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-id-card me-1"></i>
                                                {{ $enrollment->student->student_id ?? 'No ID' }}
                                            </small>
                                        </div>
                                        <span class="badge bg-{{ $hasInvoice ? 'success' : 'primary' }} fee-badge">
                                            RM {{ number_format($monthlyFee, 2) }}
                                        </span>
                                    </div>

                                    <div class="mt-2">
                                        <span class="badge bg-info">{{ $enrollment->package->name ?? 'N/A' }}</span>
                                        @if($hasInvoice)
                                            <span class="badge bg-secondary">Invoice Exists</span>
                                        @endif
                                    </div>

                                    @if($enrollment->start_date || $enrollment->end_date)
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $enrollment->start_date?->format('d M Y') ?? 'N/A' }} -
                                            {{ $enrollment->end_date?->format('d M Y') ?? 'Ongoing' }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Generate Button -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1">Ready to Generate Invoices?</h5>
                    <p class="text-muted mb-0">
                        This will create monthly invoices for {{ $month->format('F Y') }} for all selected enrollments.
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <button type="submit" class="btn btn-success btn-lg" id="generateBtn" disabled>
                        <i class="fas fa-magic me-2"></i> Generate Selected
                    </button>
                    <button type="submit" name="generate_all" value="1" class="btn btn-primary btn-lg ms-2"
                            onclick="return confirm('Generate invoices for ALL enrollments without existing invoices?')">
                        <i class="fas fa-bolt me-2"></i> Generate All
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select All functionality
    $('#selectAll').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.enrollment-checkbox').prop('checked', isChecked);
        updateSummary();
    });

    // Individual checkbox change
    $('.enrollment-checkbox').on('change', function() {
        updateSummary();

        // Update select all checkbox state
        var total = $('.enrollment-checkbox').length;
        var checked = $('.enrollment-checkbox:checked').length;
        $('#selectAll').prop('checked', total === checked);
        $('#selectAll').prop('indeterminate', checked > 0 && checked < total);
    });

    // Update summary counts
    function updateSummary() {
        var total = 0;
        var count = 0;

        $('.enrollment-checkbox:checked').each(function() {
            count++;
            total += parseFloat($(this).data('fee')) || 0;
        });

        $('#selectedCount').text(count + ' selected');
        $('#totalAmount').text('RM ' + total.toFixed(2));
        $('#generateBtn').prop('disabled', count === 0);

        // Update card styling
        $('.enrollment-checkbox').each(function() {
            var card = $(this).closest('.enrollment-card');
            if ($(this).prop('checked')) {
                card.addClass('selected');
            } else {
                card.removeClass('selected');
            }
        });
    }

    // Search functionality
    $('#searchEnrollment').on('input', function() {
        var search = $(this).val().toLowerCase();

        $('.enrollment-item').each(function() {
            var studentName = $(this).data('student');
            if (studentName.indexOf(search) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>
@endpush
