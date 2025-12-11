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
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.teacher-payslips.index') }}">Teacher Payslips</a></li>
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
                <!-- Teacher Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Teacher Information</h6>
                        <p class="mb-1"><strong>Name:</strong> {{ $payslip->teacher->user->name }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $payslip->teacher->user->email }}</p>
                        <p class="mb-1"><strong>Phone:</strong> {{ $payslip->teacher->user->phone }}</p>
                        <p class="mb-1"><strong>Employment Type:</strong>
                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $payslip->teacher->employment_type)) }}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Period Information</h6>
                        <p class="mb-1"><strong>Period:</strong> {{ $payslip->period_start->format('d M Y') }} to {{ $payslip->period_end->format('d M Y') }}</p>
                        <p class="mb-1"><strong>Pay Type:</strong>
                            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $payslip->teacher->pay_type)) }}</span>
                        </p>
                        <p class="mb-1"><strong>Total Hours:</strong> {{ number_format($payslip->total_hours, 2) }}</p>
                        <p class="mb-1"><strong>Total Classes:</strong> {{ $payslip->total_classes }}</p>
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
                            <td>EPF (Employee 11%)</td>
                            <td class="text-end text-danger">- RM {{ number_format($payslip->epf_employee, 2) }}</td>
                        </tr>
                        <tr>
                            <td>EPF (Employer 13%)</td>
                            <td class="text-end text-muted"><small>RM {{ number_format($payslip->epf_employer, 2) }}</small></td>
                        </tr>
                        @endif
                        @if($payslip->socso_employee > 0)
                        <tr>
                            <td>SOCSO (Employee)</td>
                            <td class="text-end text-danger">- RM {{ number_format($payslip->socso_employee, 2) }}</td>
                        </tr>
                        <tr>
                            <td>SOCSO (Employer)</td>
                            <td class="text-end text-muted"><small>RM {{ number_format($payslip->socso_employer, 2) }}</small></td>
                        </tr>
                        @endif
                        <tr class="table-success">
                            <td><strong>Net Pay</strong></td>
                            <td class="text-end"><strong class="text-success fs-5">RM {{ number_format($payslip->net_pay, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Payment Information -->
                @if($payslip->status == 'paid')
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
                <a href="{{ route('admin.teacher-payslips.print', $payslip) }}" class="btn btn-primary w-100 mb-2" target="_blank">
                    <i class="fas fa-print"></i> Print Payslip
                </a>

                @can('manage-teacher-salary')
                @if($payslip->status != 'paid')
                <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                    <i class="fas fa-edit"></i> Update Status
                </button>
                @endif

                @if($payslip->status == 'draft')
                <button type="button" class="btn btn-danger w-100" onclick="deletePayslip()">
                    <i class="fas fa-trash"></i> Delete Draft
                </button>
                @endif
                @endcan

                <a href="{{ route('admin.teacher-payslips.index') }}" class="btn btn-secondary w-100 mt-2">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Bank Details -->
        @if($payslip->teacher->bank_name)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Bank Details</h5>
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

<!-- Update Status Modal -->
@can('manage-teacher-salary')
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.teacher-payslips.update-status', $payslip) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Update Payslip Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="draft" {{ $payslip->status == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="approved" {{ $payslip->status == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="paid" {{ $payslip->status == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ $payslip->payment_date ? $payslip->payment_date->format('Y-m-d') : '' }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Select Method</option>
                            <option value="bank_transfer" {{ $payslip->payment_method == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="cash" {{ $payslip->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="cheque" {{ $payslip->payment_method == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference_number" class="form-control" value="{{ $payslip->reference_number }}" placeholder="Transaction reference...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- Delete Form -->
<form id="deleteForm" method="POST" action="{{ route('admin.teacher-payslips.destroy', $payslip) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function deletePayslip() {
    if (confirm('Are you sure you want to delete this draft payslip?')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush
