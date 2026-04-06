@extends('layouts.app')

@section('title', 'Payslip Details - ' . $payslip->payslip_number)

@php
    // Resolve teacher/user safely once — reuse everywhere
    $teacherUser   = $payslip->teacher?->user;
    $teacherName   = $teacherUser?->name   ?? 'Deleted Teacher';
    $teacherId     = $payslip->teacher?->teacher_id ?? 'N/A';
    $teacherIc     = $payslip->teacher?->formatted_ic_number ?? 'N/A';
    $teacherPayType = $payslip->teacher ? ucfirst(str_replace('_', ' ', $payslip->teacher->pay_type)) : 'N/A';
    $isDeletedTeacher = !$payslip->teacher || !$teacherUser;
@endphp

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Payslip Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.teacher-payslips.index') }}">Payslips</a></li>
                    <li class="breadcrumb-item active">{{ $payslip->payslip_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.teacher-payslips.print', $payslip) }}" class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-print me-1"></i> Print
            </a>
            <a href="{{ route('admin.teacher-payslips.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Deleted teacher warning --}}
    @if($isDeletedTeacher)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> The teacher associated with this payslip has been deleted.
            Some details may be unavailable. Teacher ID on record: <strong>{{ $payslip->teacher_id }}</strong>
        </div>
    @endif

    <div class="row">
        <!-- Main Payslip Details -->
        <div class="col-lg-8">
            <!-- Payslip Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $payslip->payslip_number }}</h5>
                        <small class="text-muted">Generated on {{ $payslip->created_at->format('d M Y, h:i A') }}</small>
                    </div>
                    <div>
                        @if($payslip->status == 'draft')
                            <span class="badge bg-warning fs-6">Draft</span>
                        @elseif($payslip->status == 'approved')
                            <span class="badge bg-info fs-6">Approved</span>
                        @elseif($payslip->status == 'paid')
                            <span class="badge bg-success fs-6">Paid</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Teacher Information</h6>
                            <p class="mb-1"><strong>Name:</strong> {{ $teacherName }}</p>
                            <p class="mb-1"><strong>Teacher ID:</strong> {{ $teacherId }}</p>
                            <p class="mb-1"><strong>Pay Type:</strong> {{ $teacherPayType }}</p>
                            <p class="mb-0"><strong>IC Number:</strong> {{ $teacherIc }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Period Information</h6>
                            <p class="mb-1"><strong>Period Start:</strong> {{ $payslip->period_start->format('d M Y') }}</p>
                            <p class="mb-1"><strong>Period End:</strong> {{ $payslip->period_end->format('d M Y') }}</p>
                            <p class="mb-1"><strong>Total Hours:</strong> {{ number_format($payslip->total_hours, 2) }} hours</p>
                            <p class="mb-0"><strong>Total Classes:</strong> {{ $payslip->total_classes }} classes</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings & Deductions -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calculator me-2"></i> Salary Breakdown
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Earnings -->
                        <div class="col-md-6">
                            <h6 class="text-success mb-3"><i class="fas fa-plus-circle me-1"></i> Earnings</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td>Basic Pay</td>
                                    <td class="text-end">RM {{ number_format($payslip->basic_pay, 2) }}</td>
                                </tr>
                                @if($payslip->allowances > 0)
                                <tr>
                                    <td>Allowances</td>
                                    <td class="text-end">RM {{ number_format($payslip->allowances, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="border-top fw-bold">
                                    <td>Gross Pay</td>
                                    <td class="text-end">RM {{ number_format($payslip->basic_pay + $payslip->allowances, 2) }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Deductions -->
                        <div class="col-md-6">
                            <h6 class="text-danger mb-3"><i class="fas fa-minus-circle me-1"></i> Deductions</h6>
                            <table class="table table-sm table-borderless">
                                @if($payslip->deductions > 0)
                                <tr>
                                    <td>Other Deductions</td>
                                    <td class="text-end">RM {{ number_format($payslip->deductions, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td>
                                        EPF (Employee)
                                        @if($payslip->epf_employee == 0)
                                            <span class="badge bg-secondary ms-1">Disabled</span>
                                        @endif
                                    </td>
                                    <td class="text-end">RM {{ number_format($payslip->epf_employee, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        SOCSO (Employee)
                                        @if($payslip->socso_employee == 0)
                                            <span class="badge bg-secondary ms-1">Disabled</span>
                                        @endif
                                    </td>
                                    <td class="text-end">RM {{ number_format($payslip->socso_employee, 2) }}</td>
                                </tr>
                                <tr class="border-top fw-bold">
                                    <td>Total Deductions</td>
                                    <td class="text-end">RM {{ number_format($payslip->deductions + $payslip->epf_employee + $payslip->socso_employee, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Net Pay -->
                    <div class="alert alert-success mt-3 mb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Net Pay</h5>
                            <h3 class="mb-0">RM {{ number_format($payslip->net_pay, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employer Contributions -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-building me-2"></i> Employer Contributions
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h6 class="text-muted">EPF (Employer)</h6>
                                <h4 class="mb-0 {{ $payslip->epf_employer > 0 ? 'text-info' : 'text-muted' }}">
                                    RM {{ number_format($payslip->epf_employer, 2) }}
                                </h4>
                                @if($payslip->epf_employer == 0)
                                    <small class="text-muted">Disabled</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h6 class="text-muted">SOCSO (Employer)</h6>
                                <h4 class="mb-0 {{ $payslip->socso_employer > 0 ? 'text-info' : 'text-muted' }}">
                                    RM {{ number_format($payslip->socso_employer, 2) }}
                                </h4>
                                @if($payslip->socso_employer == 0)
                                    <small class="text-muted">Disabled</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="text-muted">Total Employer Cost</h6>
                                <h4 class="mb-0 text-primary">
                                    RM {{ number_format($payslip->net_pay + $payslip->epf_employer + $payslip->socso_employer, 2) }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($payslip->notes)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-sticky-note me-2"></i> Notes
                </div>
                <div class="card-body">
                    {{ $payslip->notes }}
                </div>
            </div>
            @endif
        </div>

        <!-- Status & Actions Sidebar -->
        <div class="col-lg-4">
            <!-- Update Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tasks me-2"></i> Update Status
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.teacher-payslips.update-status', $payslip) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" id="statusSelect">
                                <option value="draft" {{ $payslip->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="approved" {{ $payslip->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="paid" {{ $payslip->status == 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>

                        <div id="paymentFields" style="{{ $payslip->status == 'paid' ? '' : 'display:none;' }}">
                            <div class="mb-3">
                                <label class="form-label">Payment Date</label>
                                <input type="date" name="payment_date" class="form-control"
                                       value="{{ $payslip->payment_date?->format('Y-m-d') ?? date('Y-m-d') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="">Select Method</option>
                                    <option value="bank_transfer" {{ $payslip->payment_method == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="cheque" {{ $payslip->payment_method == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="cash" {{ $payslip->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reference Number</label>
                                <input type="text" name="reference_number" class="form-control"
                                       value="{{ $payslip->reference_number }}" placeholder="Transaction/Cheque No.">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-1"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>

            <!-- Payment Info -->
            @if($payslip->status == 'paid')
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i> Payment Completed
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Payment Date:</strong><br>{{ $payslip->payment_date?->format('d M Y') ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Payment Method:</strong><br>{{ ucfirst(str_replace('_', ' ', $payslip->payment_method ?? 'N/A')) }}</p>
                    <p class="mb-0"><strong>Reference:</strong><br>{{ $payslip->reference_number ?? 'N/A' }}</p>
                </div>
            </div>
            @endif

            <!-- Bank Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-university me-2"></i> Bank Details
                </div>
                <div class="card-body">
                    @if(!$isDeletedTeacher)
                        <p class="mb-2"><strong>Bank:</strong> {{ $payslip->teacher->bank_name ?? 'Not provided' }}</p>
                        <p class="mb-2"><strong>Account:</strong> {{ $payslip->teacher->bank_account ?? 'Not provided' }}</p>
                        <hr>
                        <p class="mb-2"><strong>EPF No:</strong> {{ $payslip->teacher->epf_number ?? 'Not provided' }}</p>
                        <p class="mb-0"><strong>SOCSO No:</strong> {{ $payslip->teacher->socso_number ?? 'Not provided' }}</p>
                    @else
                        <p class="text-muted mb-0"><i class="fas fa-exclamation-circle me-1"></i> Teacher record unavailable.</p>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bolt me-2"></i> Quick Actions
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('admin.teacher-payslips.print', $payslip) }}" class="btn btn-outline-primary" target="_blank">
                        <i class="fas fa-print me-1"></i> Print Payslip
                    </a>
                    @if(!$isDeletedTeacher)
                        <a href="{{ route('admin.teachers.show', $payslip->teacher) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-user me-1"></i> View Teacher Profile
                        </a>
                    @endif
                    @if($payslip->status == 'draft')
                    <form action="{{ route('admin.teacher-payslips.destroy', $payslip) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this draft payslip?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-1"></i> Delete Draft
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('statusSelect').addEventListener('change', function() {
    var paymentFields = document.getElementById('paymentFields');
    paymentFields.style.display = this.value === 'paid' ? 'block' : 'none';
});
</script>
@endpush
