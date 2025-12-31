@extends('layouts.app')

@section('title', 'Generate Payslip')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Generate Payslip</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.teacher-payslips.index') }}">Payslips</a></li>
                    <li class="breadcrumb-item active">Generate</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.teacher-payslips.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Selection Form -->
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-tie me-2"></i> Select Teacher & Period
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.teacher-payslips.create') }}" id="previewForm">
                        <div class="mb-3">
                            <label class="form-label">Teacher <span class="text-danger">*</span></label>
                            <select name="teacher_id" id="teacherSelect" class="form-select" required>
                                <option value="">-- Select Teacher --</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}"
                                            data-pay-type="{{ $t->pay_type }}"
                                            data-epf-enabled="{{ $t->epf_enabled ? '1' : '0' }}"
                                            data-socso-enabled="{{ $t->socso_enabled ? '1' : '0' }}"
                                            data-socso-type="{{ $t->socso_type ?? 'regular' }}"
                                            {{ isset($teacher) && $teacher->id == $t->id ? 'selected' : '' }}>
                                        {{ $t->user->name }} ({{ ucfirst(str_replace('_', ' ', $t->pay_type)) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Period Start <span class="text-danger">*</span></label>
                                <input type="date" name="period_start" id="periodStart" class="form-control"
                                       value="{{ $periodStart ?? request('period_start') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Period End <span class="text-danger">*</span></label>
                                <input type="date" name="period_end" id="periodEnd" class="form-control"
                                       value="{{ $periodEnd ?? request('period_end') }}" required>
                            </div>
                        </div>

                        <!-- Statutory Contribution Options -->
                        <div class="card mb-3 bg-light" id="statutoryOptions">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-file-invoice-dollar me-2"></i>Statutory Contributions
                                </h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="epfEnabled" name="epf_enabled" value="1"
                                                   {{ ($statutorySettings['epf_enabled'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="epfEnabled">
                                                EPF Deduction
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="socsoEnabled" name="socso_enabled" value="1"
                                                   {{ ($statutorySettings['socso_enabled'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="socsoEnabled">
                                                SOCSO Deduction
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div id="socsoTypeWrapper" class="mt-2" style="{{ ($statutorySettings['socso_enabled'] ?? true) ? '' : 'display:none;' }}">
                                    <label class="form-label small">SOCSO Type</label>
                                    <select name="socso_type" id="socsoType" class="form-select form-select-sm">
                                        <option value="regular" {{ ($statutorySettings['socso_type'] ?? 'regular') == 'regular' ? 'selected' : '' }}>
                                            Regular
                                        </option>
                                        <option value="insurance_only" {{ ($statutorySettings['socso_type'] ?? '') == 'insurance_only' ? 'selected' : '' }}>
                                            Insurance Only (60+/Foreign)
                                        </option>
                                    </select>
                                </div>

                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    These settings are loaded from teacher profile but can be adjusted for this payslip.
                                </small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-calculator me-1"></i> Calculate Salary
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <i class="fas fa-bolt me-2"></i> Quick Period Selection
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setCurrentMonth()">
                            Current Month
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setPreviousMonth()">
                            Previous Month
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calculation Result & Submit -->
        <div class="col-lg-7">
            @if($teacher && $calculation)
            <form method="POST" action="{{ route('admin.teacher-payslips.store') }}" id="payslipForm">
                @csrf
                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                <input type="hidden" name="period_start" value="{{ $periodStart }}">
                <input type="hidden" name="period_end" value="{{ $periodEnd }}">
                <input type="hidden" name="epf_enabled" value="{{ $statutorySettings['epf_enabled'] ?? true ? '1' : '0' }}">
                <input type="hidden" name="socso_enabled" value="{{ $statutorySettings['socso_enabled'] ?? true ? '1' : '0' }}">
                <input type="hidden" name="socso_type" value="{{ $statutorySettings['socso_type'] ?? 'regular' }}">

                <!-- Teacher Info -->
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fas fa-user me-2"></i> Teacher Information
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Name:</strong> {{ $teacher->user->name }}<br>
                                <strong>ID:</strong> {{ $teacher->teacher_id }}<br>
                                <strong>Pay Type:</strong> {{ ucfirst(str_replace('_', ' ', $teacher->pay_type)) }}
                            </div>
                            <div class="col-md-6">
                                <strong>Period:</strong> {{ \Carbon\Carbon::parse($periodStart)->format('d M Y') }} -
                                                         {{ \Carbon\Carbon::parse($periodEnd)->format('d M Y') }}<br>
                                @if($teacher->pay_type == 'hourly')
                                    <strong>Rate:</strong> RM {{ number_format($teacher->hourly_rate, 2) }}/hour
                                @elseif($teacher->pay_type == 'monthly')
                                    <strong>Salary:</strong> RM {{ number_format($teacher->monthly_salary, 2) }}/month
                                @else
                                    <strong>Rate:</strong> RM {{ number_format($teacher->per_class_rate, 2) }}/class
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Salary Breakdown -->
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fas fa-calculator me-2"></i> Salary Breakdown
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td>Total Hours Worked</td>
                                    <td class="text-end">{{ number_format($calculation['total_hours'], 2) }} hours</td>
                                </tr>
                                <tr>
                                    <td>Total Classes</td>
                                    <td class="text-end">{{ $calculation['total_classes'] }} classes</td>
                                </tr>
                                <tr class="table-light">
                                    <td><strong>Basic Pay</strong></td>
                                    <td class="text-end"><strong>RM {{ number_format($calculation['basic_pay'], 2) }}</strong></td>
                                </tr>
                                @if($calculation['allowances'] > 0)
                                <tr class="text-success">
                                    <td>(+) Allowances</td>
                                    <td class="text-end">RM {{ number_format($calculation['allowances'], 2) }}</td>
                                </tr>
                                @endif
                                @if($calculation['deductions'] > 0)
                                <tr class="text-danger">
                                    <td>(-) Deductions</td>
                                    <td class="text-end">RM {{ number_format($calculation['deductions'], 2) }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Statutory Deductions -->
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fas fa-file-invoice me-2"></i> Statutory Deductions
                        @if(!($statutorySettings['epf_enabled'] ?? true) && !($statutorySettings['socso_enabled'] ?? true))
                            <span class="badge bg-warning ms-2">Disabled</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th class="text-end">Employee</th>
                                    <th class="text-end">Employer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        EPF (KWSP)
                                        @if(!($statutorySettings['epf_enabled'] ?? true))
                                            <span class="badge bg-secondary">Disabled</span>
                                        @endif
                                    </td>
                                    <td class="text-end {{ ($statutorySettings['epf_enabled'] ?? true) ? 'text-danger' : 'text-muted' }}">
                                        RM {{ number_format($calculation['epf_employee'], 2) }}
                                    </td>
                                    <td class="text-end text-info">
                                        RM {{ number_format($calculation['epf_employer'], 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        SOCSO (PERKESO)
                                        @if(!($statutorySettings['socso_enabled'] ?? true))
                                            <span class="badge bg-secondary">Disabled</span>
                                        @elseif(($statutorySettings['socso_type'] ?? 'regular') == 'insurance_only')
                                            <span class="badge bg-info">Insurance Only</span>
                                        @endif
                                    </td>
                                    <td class="text-end {{ ($statutorySettings['socso_enabled'] ?? true) ? 'text-danger' : 'text-muted' }}">
                                        RM {{ number_format($calculation['socso_employee'], 2) }}
                                    </td>
                                    <td class="text-end text-info">
                                        RM {{ number_format($calculation['socso_employer'], 2) }}
                                    </td>
                                </tr>
                                <tr class="table-light">
                                    <td><strong>Total Statutory</strong></td>
                                    <td class="text-end text-danger">
                                        <strong>RM {{ number_format($calculation['epf_employee'] + $calculation['socso_employee'], 2) }}</strong>
                                    </td>
                                    <td class="text-end text-info">
                                        <strong>RM {{ number_format($calculation['epf_employer'] + $calculation['socso_employer'], 2) }}</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Additional Adjustments -->
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fas fa-edit me-2"></i> Additional Adjustments
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Additional Allowances (RM)</label>
                                <input type="number" name="allowances" class="form-control"
                                       value="{{ old('allowances', 0) }}" step="0.01" min="0"
                                       id="additionalAllowances" onchange="recalculateNet()">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Additional Deductions (RM)</label>
                                <input type="number" name="deductions" class="form-control"
                                       value="{{ old('deductions', 0) }}" step="0.01" min="0"
                                       id="additionalDeductions" onchange="recalculateNet()">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"
                                      placeholder="Optional notes for this payslip">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Net Pay Summary -->
                <div class="card mb-3 border-success">
                    <div class="card-body bg-light">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0">Net Pay</h5>
                                <small class="text-muted">Basic + Allowances - Deductions - EPF - SOCSO</small>
                            </div>
                            <div class="col-auto">
                                <h3 class="mb-0 text-success" id="netPayDisplay">
                                    RM {{ number_format($calculation['net_pay'], 2) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-between">
                    <div>
                        <select name="status" class="form-select" style="width: auto;">
                            <option value="draft">Save as Draft</option>
                            <option value="approved">Save as Approved</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Generate Payslip
                    </button>
                </div>
            </form>
            @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calculator fa-3x text-muted mb-3"></i>
                    <h5>Select Teacher & Period</h5>
                    <p class="text-muted">Choose a teacher and date range to calculate salary</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Store calculation values for recalculation
const baseCalculation = @json($calculation ?? null);

// Set current month period
function setCurrentMonth() {
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

    document.getElementById('periodStart').value = formatDate(firstDay);
    document.getElementById('periodEnd').value = formatDate(lastDay);
}

// Set previous month period
function setPreviousMonth() {
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1);
    const lastDay = new Date(now.getFullYear(), now.getMonth(), 0);

    document.getElementById('periodStart').value = formatDate(firstDay);
    document.getElementById('periodEnd').value = formatDate(lastDay);
}

// Format date as YYYY-MM-DD
function formatDate(date) {
    return date.toISOString().split('T')[0];
}

// Recalculate net pay when adjustments change
function recalculateNet() {
    if (!baseCalculation) return;

    const additionalAllowances = parseFloat(document.getElementById('additionalAllowances').value) || 0;
    const additionalDeductions = parseFloat(document.getElementById('additionalDeductions').value) || 0;

    const netPay = baseCalculation.basic_pay
                 + baseCalculation.allowances
                 + additionalAllowances
                 - baseCalculation.deductions
                 - additionalDeductions
                 - baseCalculation.epf_employee
                 - baseCalculation.socso_employee;

    document.getElementById('netPayDisplay').textContent = 'RM ' + Math.max(0, netPay).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Load teacher statutory settings when teacher changes
document.getElementById('teacherSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const epfEnabled = selectedOption.dataset.epfEnabled === '1';
        const socsoEnabled = selectedOption.dataset.socsoEnabled === '1';
        const socsoType = selectedOption.dataset.socsoType || 'regular';

        document.getElementById('epfEnabled').checked = epfEnabled;
        document.getElementById('socsoEnabled').checked = socsoEnabled;
        document.getElementById('socsoType').value = socsoType;

        toggleSocsoType();
    }
});

// Toggle SOCSO type visibility
function toggleSocsoType() {
    const socsoEnabled = document.getElementById('socsoEnabled').checked;
    document.getElementById('socsoTypeWrapper').style.display = socsoEnabled ? 'block' : 'none';
}

document.getElementById('socsoEnabled').addEventListener('change', toggleSocsoType);

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    toggleSocsoType();
});
</script>
@endpush
