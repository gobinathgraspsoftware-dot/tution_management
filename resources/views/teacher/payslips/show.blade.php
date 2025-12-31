@extends('layouts.teacher')

@section('title', 'Payslip Details - ' . $payslip->payslip_number)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Payslip Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.payslips.index') }}">Payslips</a></li>
                    <li class="breadcrumb-item active">{{ $payslip->payslip_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('teacher.payslips.print', $payslip) }}" class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-print me-1"></i> Print
            </a>
            <a href="{{ route('teacher.payslips.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

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
                            <h6 class="text-muted mb-2">Period Information</h6>
                            <p class="mb-1"><strong>Period Start:</strong> {{ $payslip->period_start->format('d M Y') }}</p>
                            <p class="mb-1"><strong>Period End:</strong> {{ $payslip->period_end->format('d M Y') }}</p>
                            <p class="mb-1"><strong>Total Hours:</strong> {{ number_format($payslip->total_hours, 2) }} hours</p>
                            <p class="mb-0"><strong>Total Classes:</strong> {{ $payslip->total_classes }} classes</p>
                        </div>
                        <div class="col-md-6">
                            @if($payslip->status == 'paid')
                            <h6 class="text-muted mb-2">Payment Information</h6>
                            <p class="mb-1"><strong>Payment Date:</strong> {{ $payslip->payment_date?->format('d M Y') ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $payslip->payment_method ?? 'N/A')) }}</p>
                            @if($payslip->reference_number)
                            <p class="mb-0"><strong>Reference:</strong> {{ $payslip->reference_number }}</p>
                            @endif
                            @endif
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
                                        EPF (Employee 11%)
                                        @if($payslip->epf_employee == 0)
                                            <span class="badge bg-secondary ms-1">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-end">RM {{ number_format($payslip->epf_employee, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        SOCSO (Employee)
                                        @if($payslip->socso_employee == 0)
                                            <span class="badge bg-secondary ms-1">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-end">RM {{ number_format($payslip->socso_employee, 2) }}</td>
                                </tr>
                                <tr class="border-top fw-bold">
                                    <td>Total Deductions</td>
                                    <td class="text-end text-danger">RM {{ number_format($payslip->deductions + $payslip->epf_employee + $payslip->socso_employee, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Net Pay -->
                    <div class="alert alert-success mt-3 mb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i> Net Pay</h5>
                            <h3 class="mb-0">RM {{ number_format($payslip->net_pay, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employer Contributions (For Reference) -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-building me-2"></i> Employer Contributions (For Reference)
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <h6 class="text-muted">EPF (Employer 13%)</h6>
                                <h4 class="mb-0 {{ $payslip->epf_employer > 0 ? 'text-info' : 'text-muted' }}">
                                    RM {{ number_format($payslip->epf_employer, 2) }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <h6 class="text-muted">SOCSO (Employer)</h6>
                                <h4 class="mb-0 {{ $payslip->socso_employer > 0 ? 'text-info' : 'text-muted' }}">
                                    RM {{ number_format($payslip->socso_employer, 2) }}
                                </h4>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted small mt-3 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Employer contributions are paid by the centre on your behalf and are not part of your take-home pay.
                    </p>
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

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Payment Status -->
            @if($payslip->status == 'paid')
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i> Payment Completed
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-check-circle text-success fa-4x"></i>
                    </div>
                    <p class="mb-2"><strong>Payment Date:</strong><br>{{ $payslip->payment_date?->format('d M Y') ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Payment Method:</strong><br>{{ ucfirst(str_replace('_', ' ', $payslip->payment_method ?? 'N/A')) }}</p>
                    @if($payslip->reference_number)
                    <p class="mb-0"><strong>Reference:</strong><br>{{ $payslip->reference_number }}</p>
                    @endif
                </div>
            </div>
            @elseif($payslip->status == 'approved')
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-clock me-2"></i> Pending Payment
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-hourglass-half text-info fa-4x mb-3"></i>
                    <p class="mb-0">Your payslip has been approved and is pending payment processing.</p>
                </div>
            </div>
            @else
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-edit me-2"></i> Draft
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-file-alt text-warning fa-4x mb-3"></i>
                    <p class="mb-0">This payslip is still being prepared and has not been finalized yet.</p>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-bolt me-2"></i> Quick Actions
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('teacher.payslips.print', $payslip) }}" class="btn btn-outline-primary" target="_blank">
                        <i class="fas fa-print me-1"></i> Print Payslip
                    </a>
                    <a href="{{ route('teacher.payslips.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-1"></i> View All Payslips
                    </a>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> Summary
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Gross Pay</span>
                        <span>RM {{ number_format($payslip->basic_pay + $payslip->allowances, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Deductions</span>
                        <span class="text-danger">- RM {{ number_format($payslip->deductions + $payslip->epf_employee + $payslip->socso_employee, 2) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Net Pay</span>
                        <span class="fw-bold text-success fs-5">RM {{ number_format($payslip->net_pay, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
