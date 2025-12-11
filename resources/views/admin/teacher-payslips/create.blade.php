@extends('layouts.app')

@section('title', 'Generate Payslip')
@section('page-title', 'Generate Payslip')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-file-invoice-dollar me-2"></i> Generate Payslip
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.teacher-payslips.index') }}">Teacher Payslips</a></li>
            <li class="breadcrumb-item active">Generate</li>
        </ol>
    </nav>
</div>

<div class="row">
    <!-- Selection Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Select Period</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.teacher-payslips.create') }}">
                    <div class="mb-3">
                        <label class="form-label">Teacher <span class="text-danger">*</span></label>
                        <select name="teacher_id" class="form-select" required>
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>
                                    {{ $t->user->name }} - {{ ucfirst(str_replace('_', ' ', $t->pay_type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Period Start <span class="text-danger">*</span></label>
                        <input type="date" name="period_start" class="form-control" value="{{ request('period_start', now()->startOfMonth()->format('Y-m-d')) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Period End <span class="text-danger">*</span></label>
                        <input type="date" name="period_end" class="form-control" value="{{ request('period_end', now()->endOfMonth()->format('Y-m-d')) }}" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-calculator"></i> Calculate Salary
                    </button>
                </form>
            </div>
        </div>

        @if($teacher)
        <!-- Teacher Info -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Teacher Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Name:</strong><br>
                    {{ $teacher->user->name }}
                </div>
                <div class="mb-2">
                    <strong>Email:</strong><br>
                    {{ $teacher->user->email }}
                </div>
                <div class="mb-2">
                    <strong>Employment Type:</strong><br>
                    <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $teacher->employment_type)) }}</span>
                </div>
                <div class="mb-2">
                    <strong>Pay Type:</strong><br>
                    <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $teacher->pay_type)) }}</span>
                </div>
                <div class="mb-2">
                    <strong>Rate:</strong><br>
                    @if($teacher->pay_type == 'hourly')
                        RM {{ number_format($teacher->hourly_rate, 2) }}/hour
                    @elseif($teacher->pay_type == 'monthly')
                        RM {{ number_format($teacher->monthly_salary, 2) }}/month
                    @else
                        RM {{ number_format($teacher->per_class_rate, 2) }}/class
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Calculation Results and Form -->
    <div class="col-md-8">
        @if($calculation)
        <form method="POST" action="{{ route('admin.teacher-payslips.store') }}">
            @csrf
            <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
            <input type="hidden" name="period_start" value="{{ $periodStart }}">
            <input type="hidden" name="period_end" value="{{ $periodEnd }}">

            <!-- Salary Breakdown -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Salary Calculation</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Period</label>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($periodStart)->format('d M Y') }} to {{ \Carbon\Carbon::parse($periodEnd)->format('d M Y') }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Total Hours</label>
                            <p class="mb-0"><strong>{{ number_format($calculation['total_hours'], 2) }}</strong></p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Total Classes</label>
                            <p class="mb-0"><strong>{{ $calculation['total_classes'] }}</strong></p>
                        </div>
                    </div>

                    <hr>

                    <table class="table">
                        <tr>
                            <td>Basic Pay</td>
                            <td class="text-end"><strong>RM {{ number_format($calculation['basic_pay'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>
                                Allowances
                                <input type="number" name="allowances" class="form-control form-control-sm mt-1" placeholder="Additional allowances" step="0.01" min="0" value="{{ old('allowances', 0) }}">
                            </td>
                            <td class="text-end">RM {{ number_format($calculation['allowances'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>
                                Deductions
                                <input type="number" name="deductions" class="form-control form-control-sm mt-1" placeholder="Additional deductions" step="0.01" min="0" value="{{ old('deductions', 0) }}">
                            </td>
                            <td class="text-end text-danger">- RM {{ number_format($calculation['deductions'], 2) }}</td>
                        </tr>
                        @if($calculation['epf_employee'] > 0)
                        <tr>
                            <td>EPF (Employee 11%)</td>
                            <td class="text-end text-danger">- RM {{ number_format($calculation['epf_employee'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>EPF (Employer 13%)</td>
                            <td class="text-end text-muted"><small>RM {{ number_format($calculation['epf_employer'], 2) }}</small></td>
                        </tr>
                        @endif
                        @if($calculation['socso_employee'] > 0)
                        <tr>
                            <td>SOCSO (Employee)</td>
                            <td class="text-end text-danger">- RM {{ number_format($calculation['socso_employee'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>SOCSO (Employer)</td>
                            <td class="text-end text-muted"><small>RM {{ number_format($calculation['socso_employer'], 2) }}</small></td>
                        </tr>
                        @endif
                        <tr class="table-primary">
                            <td><strong>Net Pay</strong></td>
                            <td class="text-end"><strong class="text-success">RM {{ number_format($calculation['net_pay'], 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Payslip Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft">Draft</option>
                            <option value="approved">Approved</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Generate Payslip
                    </button>
                    <a href="{{ route('admin.teacher-payslips.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
        @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-calculator fa-3x text-muted mb-3"></i>
                <p class="text-muted">Select teacher and period to calculate salary</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
