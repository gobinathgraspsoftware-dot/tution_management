@extends('layouts.app')

@section('title', 'Payslip Details')
@section('page-title', 'Payslip Details')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-file-invoice-dollar me-2"></i> Payslip Details
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teacher.payslips.index') }}">My Payslips</a></li>
            <li class="breadcrumb-item active">{{ $payslip->payslip_number }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <!-- Payslip Information -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $payslip->payslip_number }}</h5>
                @if($payslip->status == 'draft')
                    <span class="badge bg-warning text-dark">Draft</span>
                @elseif($payslip->status == 'approved')
                    <span class="badge bg-info">Approved</span>
                @else
                    <span class="badge bg-success">Paid</span>
                @endif
            </div>
            <div class="card-body">
                <!-- Period Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Period Information</h6>
                        <p class="mb-1"><strong>Period:</strong> {{ $payslip->period_start->format('d M Y') }} to {{ $payslip->period_end->format('d M Y') }}</p>
                        <p class="mb-1"><strong>Total Hours Worked:</strong> {{ number_format($payslip->total_hours, 2) }} hours</p>
                        <p class="mb-1"><strong>Total Classes Conducted:</strong> {{ $payslip->total_classes }} classes</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Pay Information</h6>
                        <p class="mb-1"><strong>Pay Type:</strong>
                            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $payslip->teacher->pay_type)) }}</span>
                        </p>
                        @if($payslip->teacher->pay_type == 'hourly')
                            <p class="mb-1"><strong>Hourly Rate:</strong> RM {{ number_format($payslip->teacher->hourly_rate, 2) }}/hour</p>
                        @elseif($payslip->teacher->pay_type == 'monthly')
                            <p class="mb-1"><strong>Monthly Salary:</strong> RM {{ number_format($payslip->teacher->monthly_salary, 2) }}</p>
                        @else
                            <p class="mb-1"><strong>Per Class Rate:</strong> RM {{ number_format($payslip->teacher->per_class_rate, 2) }}/class</p>
                        @endif
                    </div>
                </div>

                <hr>

                <!-- Salary Breakdown -->
                <h6 class="text-muted mb-3">Salary Breakdown</h6>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td><strong>Basic Pay</strong></td>
                            <td class="text-end">RM {{ number_format($payslip->basic_pay, 2) }}</td>
                        </tr>
                        @if($payslip->allowances > 0)
                        <tr>
                            <td>Allowances</td>
                            <td class="text-end text-success">+ RM {{ number_format($payslip->allowances, 2) }}</td>
                        </tr>
                        @endif
                        @if($payslip->deductions > 0)
                        <tr>
                            <td>Deductions</td>
                            <td class="text-end text-danger">- RM {{ number_format($payslip->deductions, 2) }}</td>
                        </tr>
                        @endif
                        @if($payslip->epf_employee > 0)
                        <tr>
                            <td>EPF Employee Contribution (11%)</td>
                            <td class="text-end text-danger">- RM {{ number_format($payslip->epf_employee, 2) }}</td>
                        </tr>
                        @endif
                        @if($payslip->socso_employee > 0)
                        <tr>
                            <td>SOCSO Employee Contribution</td>
                            <td class="text-end text-danger">- RM {{ number_format($payslip->socso_employee, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="table-success">
                            <td><strong>Net Pay</strong></td>
                            <td class="text-end"><strong class="text-success fs-5">RM {{ number_format($payslip->net_pay, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>

                @if($payslip->epf_employer > 0 || $payslip->socso_employer > 0)
                <div class="alert alert-info mt-3">
                    <h6 class="mb-2"><i class="fas fa-info-circle"></i> Employer Contributions</h6>
                    @if($payslip->epf_employer > 0)
                    <p class="mb-1"><strong>EPF Employer (13%):</strong> RM {{ number_format($payslip->epf_employer, 2) }}</p>
                    @endif
                    @if($payslip->socso_employer > 0)
                    <p class="mb-0"><strong>SOCSO Employer:</strong> RM {{ number_format($payslip->socso_employer, 2) }}</p>
                    @endif
                </div>
                @endif

                <!-- Payment Information -->
                @if($payslip->status == 'paid' && $payslip->payment_date)
                <hr>
                <h6 class="text-muted mb-3">Payment Information</h6>
                <div class="row">
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Payment Date:</strong></p>
                        <p>{{ $payslip->payment_date->format('d M Y') }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Payment Method:</strong></p>
                        <p>{{ ucfirst($payslip->payment_method) }}</p>
                    </div>
                    @if($payslip->reference_number)
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Reference Number:</strong></p>
                        <p>{{ $payslip->reference_number }}</p>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Notes -->
                @if($payslip->notes)
                <hr>
                <h6 class="text-muted mb-2">Notes</h6>
                <p>{{ $payslip->notes }}</p>
                @endif

                <!-- Metadata -->
                <hr>
                <div class="row text-muted small">
                    <div class="col-md-6">
                        <strong>Generated:</strong> {{ $payslip->created_at->format('d M Y, g:i A') }}
                    </div>
                    <div class="col-md-6 text-end">
                        <strong>Last Updated:</strong> {{ $payslip->updated_at->format('d M Y, g:i A') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Sidebar -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('teacher.payslips.print', $payslip) }}" class="btn btn-primary w-100 mb-2" target="_blank">
                    <i class="fas fa-print"></i> Print Payslip
                </a>

                <a href="{{ route('teacher.payslips.index') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-arrow-left"></i> Back to My Payslips
                </a>
            </div>
        </div>

        <!-- Bank Details -->
        @if($payslip->teacher->bank_name)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">My Bank Details</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Bank:</strong> {{ $payslip->teacher->bank_name }}</p>
                <p class="mb-1"><strong>Account:</strong> {{ $payslip->teacher->bank_account }}</p>
                @if($payslip->teacher->epf_number)
                <p class="mb-1"><strong>EPF:</strong> {{ $payslip->teacher->epf_number }}</p>
                @endif
                @if($payslip->teacher->socso_number)
                <p class="mb-0"><strong>SOCSO:</strong> {{ $payslip->teacher->socso_number }}</p>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
