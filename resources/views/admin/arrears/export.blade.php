@extends('layouts.admin')

@section('title', 'Export Arrears Report')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Export Arrears Report</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.arrears.index') }}">Arrears</a></li>
                    <li class="breadcrumb-item active">Export</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.arrears.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Arrears
        </a>
    </div>

    <div class="row">
        {{-- Export Options --}}
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-file-export me-2"></i>Export Options</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.arrears.export') }}" method="GET" id="exportForm">
                        {{-- Date Range --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date From</label>
                                <input type="date" name="date_from" class="form-control" 
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date To</label>
                                <input type="date" name="date_to" class="form-control" 
                                       value="{{ request('date_to', date('Y-m-d')) }}">
                            </div>
                        </div>

                        {{-- Filters --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Class</label>
                                <select name="class_id" class="form-select">
                                    <option value="">All Classes</option>
                                    @foreach($classes ?? [] as $class)
                                        <option value="{{ $class->id }}" 
                                            {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Subject</label>
                                <select name="subject_id" class="form-select">
                                    <option value="">All Subjects</option>
                                    @foreach($subjects ?? [] as $subject)
                                        <option value="{{ $subject->id }}"
                                            {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                        Pending
                                    </option>
                                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>
                                        Overdue
                                    </option>
                                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>
                                        Partial
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{-- Arrears Criteria --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Minimum Days Overdue</label>
                                <input type="number" name="days_overdue_min" class="form-control" 
                                       placeholder="e.g., 30" min="0"
                                       value="{{ request('days_overdue_min') }}">
                                <small class="text-muted">Only include invoices overdue by at least this many days</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Minimum Amount (RM)</label>
                                <input type="number" name="amount_min" class="form-control" 
                                       placeholder="e.g., 100" min="0" step="0.01"
                                       value="{{ request('amount_min') }}">
                                <small class="text-muted">Only include invoices with at least this amount due</small>
                            </div>
                        </div>

                        {{-- Export Format --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Export Format</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check form-check-custom p-3 border rounded bg-light">
                                        <input class="form-check-input" type="radio" name="format" 
                                               id="formatCsv" value="csv" checked>
                                        <label class="form-check-label ms-2" for="formatCsv">
                                            <i class="fas fa-file-csv text-success me-2"></i>
                                            <strong>CSV</strong>
                                            <small class="d-block text-muted">Comma-separated values</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-custom p-3 border rounded bg-light">
                                        <input class="form-check-input" type="radio" name="format" 
                                               id="formatExcel" value="excel">
                                        <label class="form-check-label ms-2" for="formatExcel">
                                            <i class="fas fa-file-excel text-success me-2"></i>
                                            <strong>Excel</strong>
                                            <small class="d-block text-muted">Microsoft Excel (.xlsx)</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-custom p-3 border rounded bg-light">
                                        <input class="form-check-input" type="radio" name="format" 
                                               id="formatPdf" value="pdf">
                                        <label class="form-check-label ms-2" for="formatPdf">
                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                            <strong>PDF</strong>
                                            <small class="d-block text-muted">Portable Document Format</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Include Options --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Include in Report</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="include_summary" 
                                               id="includeSummary" value="1" checked>
                                        <label class="form-check-label" for="includeSummary">
                                            Summary Statistics
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="include_aging" 
                                               id="includeAging" value="1" checked>
                                        <label class="form-check-label" for="includeAging">
                                            Aging Analysis
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="include_contact" 
                                               id="includeContact" value="1" checked>
                                        <label class="form-check-label" for="includeContact">
                                            Contact Information
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="include_reminders" 
                                               id="includeReminders" value="1">
                                        <label class="form-check-label" for="includeReminders">
                                            Reminder History
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="include_payments" 
                                               id="includePayments" value="1">
                                        <label class="form-check-label" for="includePayments">
                                            Payment History
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="include_parent" 
                                               id="includeParent" value="1">
                                        <label class="form-check-label" for="includeParent">
                                            Parent Details
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <div>
                                <a href="{{ route('admin.arrears.print', request()->query()) }}" 
                                   class="btn btn-outline-primary me-2" target="_blank">
                                    <i class="fas fa-print me-1"></i> Preview & Print
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-download me-1"></i> Export Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="col-md-4">
            {{-- Current Stats --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>Current Arrears Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-muted">Total Arrears</span>
                        <span class="fw-bold text-danger fs-5">
                            RM {{ number_format($totalArrears ?? 0, 2) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-muted">Overdue Invoices</span>
                        <span class="fw-bold">{{ number_format($overdueCount ?? 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-muted">Students Affected</span>
                        <span class="fw-bold">{{ number_format($studentsWithArrears ?? 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Critical (90+ days)</span>
                        <span class="fw-bold text-danger">{{ number_format($criticalCount ?? 0) }}</span>
                    </div>
                </div>
            </div>

            {{-- Quick Export Templates --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-bookmark me-2"></i>Quick Export Templates</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.arrears.export', ['status' => 'overdue', 'format' => 'csv']) }}" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-clock text-warning me-2"></i> All Overdue</span>
                            <span class="badge bg-warning">CSV</span>
                        </a>
                        <a href="{{ route('admin.arrears.export', ['days_overdue_min' => 90, 'format' => 'csv']) }}" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-exclamation-triangle text-danger me-2"></i> Critical (90+ days)</span>
                            <span class="badge bg-danger">CSV</span>
                        </a>
                        <a href="{{ route('admin.arrears.export', ['days_overdue_min' => 30, 'days_overdue_max' => 60, 'format' => 'csv']) }}" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-hourglass-half text-info me-2"></i> 30-60 Days Overdue</span>
                            <span class="badge bg-info">CSV</span>
                        </a>
                        <a href="{{ route('admin.arrears.export', ['date_from' => now()->startOfMonth()->format('Y-m-d'), 'format' => 'csv']) }}" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar text-primary me-2"></i> This Month</span>
                            <span class="badge bg-primary">CSV</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Export Tips --}}
            <div class="card shadow-sm border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Export Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3">
                        <li class="mb-2">
                            <small>Use <strong>CSV</strong> for importing into other systems or Excel.</small>
                        </li>
                        <li class="mb-2">
                            <small>Use <strong>PDF</strong> for printing or sharing as a document.</small>
                        </li>
                        <li class="mb-2">
                            <small>Filter by <strong>days overdue</strong> to focus on critical cases.</small>
                        </li>
                        <li>
                            <small>Include <strong>contact info</strong> for follow-up actions.</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-check-custom {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .form-check-custom:hover {
        background-color: #e9ecef !important;
        border-color: #007bff !important;
    }
    
    .form-check-custom input:checked + label {
        color: #007bff;
    }
</style>
@endpush

@push('scripts')
<script>
    function resetForm() {
        document.getElementById('exportForm').reset();
        // Reset select elements
        document.querySelectorAll('select').forEach(function(select) {
            select.selectedIndex = 0;
        });
        // Reset date inputs
        document.querySelectorAll('input[type="date"]').forEach(function(input) {
            input.value = '';
        });
        // Set date_to to today
        document.querySelector('input[name="date_to"]').value = '{{ date("Y-m-d") }}';
    }
    
    // Form validation before submit
    document.getElementById('exportForm').addEventListener('submit', function(e) {
        var dateFrom = document.querySelector('input[name="date_from"]').value;
        var dateTo = document.querySelector('input[name="date_to"]').value;
        
        if (dateFrom && dateTo && dateFrom > dateTo) {
            e.preventDefault();
            alert('Date From cannot be after Date To');
            return false;
        }
    });
</script>
@endpush
