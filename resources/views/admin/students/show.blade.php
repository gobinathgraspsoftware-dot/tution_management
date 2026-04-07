@extends('layouts.app')

@section('title', 'Student Details')
@section('page-title', 'Student Details')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-user-graduate me-2"></i> Student Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.students.index') }}">Students</a></li>
                <li class="breadcrumb-item active">{{ $student->user->name }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @if($whatsappEnabled && $student->user->phone)
            <a href="{{ route('admin.students.resend-whatsapp', $student) }}"
               class="btn btn-success"
               onclick="return confirm('Are you sure you want to send WhatsApp credentials to the student?');">
                <i class="fab fa-whatsapp me-1"></i> Send WhatsApp
            </a>
        @endif
        @can('edit-students')
        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        @endcan
    </div>
</div>

<div class="row">
    <!-- Profile Card -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="user-avatar mx-auto mb-3" style="width:100px;height:100px;font-size:2.5rem;background:linear-gradient(135deg,#2196f3 0%,#1976d2 100%);">
                    {{ substr($student->user->name, 0, 1) }}
                </div>
                <h4>{{ $student->user->name }}</h4>
                {{-- ─── Grade name via relationship ─────────────────── --}}
                <p class="text-muted mb-2">{{ $student->gradeLevel->name ?? 'Student' }}</p>
                {{-- ───────────────────────────────────────────────────── --}}
                <span class="badge bg-primary">{{ $student->student_id }}</span>
                <hr>
                <div class="d-flex justify-content-center gap-2">
                    @if($student->approval_status == 'approved')
                        <span class="badge bg-success">Approved</span>
                    @elseif($student->approval_status == 'pending')
                        <span class="badge bg-warning text-dark">Pending Approval</span>
                    @else
                        <span class="badge bg-danger">Rejected</span>
                    @endif
                    @if($student->user->status == 'active')
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-address-book me-2"></i> Contact Information
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Email</label>
                    <p class="mb-0"><a href="mailto:{{ $student->user->email }}">{{ $student->user->email }}</a></p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Password</label>
                    <div class="input-group input-group-sm">
                        <input type="password" class="form-control" id="passwordView"
                               value="{{ $student->user->password_view ?? '••••••••' }}" disabled>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordView()">
                            <i class="fas fa-eye" id="passwordToggleIcon"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Phone</label>
                    <p class="mb-0">
                        @if($student->user->phone)
                            <a href="tel:{{ $student->user->phone }}">{{ $student->user->phone }}</a>
                            @if($whatsappEnabled)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $student->user->phone) }}"
                                   target="_blank" class="text-success ms-2" title="Open WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            @endif
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Address</label>
                    <p class="mb-0">{{ $student->address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Statistics
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h4 class="text-success mb-0">RM {{ number_format($stats['total_paid'], 2) }}</h4>
                        <small class="text-muted">Total Paid</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-danger mb-0">RM {{ number_format($stats['pending_amount'], 2) }}</h4>
                        <small class="text-muted">Pending</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-primary mb-0">{{ $stats['attendance_rate'] }}%</h4>
                        <small class="text-muted">Attendance</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-info mb-0">{{ $stats['active_enrollments'] }}</h4>
                        <small class="text-muted">Enrollments</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- WhatsApp Quick Actions -->
        @if($whatsappEnabled)
        <div class="card mb-4 border-success">
            <div class="card-header bg-success text-white">
                <i class="fab fa-whatsapp me-2"></i> WhatsApp Actions
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($student->user->phone)
                        <a href="{{ route('admin.students.resend-whatsapp', $student) }}"
                           class="btn btn-outline-success btn-sm"
                           onclick="return confirm('Send registration details with login credentials via WhatsApp to student?');">
                            <i class="fab fa-whatsapp me-1"></i> Send Credentials to Student
                        </a>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Student has no phone number registered.
                        </div>
                    @endif
                </div>
                <small class="text-muted d-block mt-2 text-center">Send login credentials to student's WhatsApp</small>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <!-- Personal Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-id-card me-2"></i> Personal Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">IC Number</label>
                        <p class="mb-0"><strong>
                            @php
                                $ic = $student->ic_number;
                                echo strlen($ic) === 12
                                    ? substr($ic,0,6).'-'.substr($ic,6,2).'-'.substr($ic,8,4)
                                    : $ic;
                            @endphp
                        </strong></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Date of Birth</label>
                        <p class="mb-0">{{ $student->date_of_birth?->format('d M Y') ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Gender</label>
                        <p class="mb-0">{{ ucfirst($student->gender) }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">School</label>
                        <p class="mb-0">{{ $student->school_name ?? 'N/A' }}</p>
                    </div>

                    {{-- ─── Grade name via relationship ─────────────────── --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Grade Level</label>
                        <p class="mb-0">{{ $student->gradeLevel->name ?? 'N/A' }}</p>
                    </div>
                    {{-- ───────────────────────────────────────────────────── --}}

                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Registration Type</label>
                        <p class="mb-0">
                            <span class="badge {{ $student->registration_type == 'online' ? 'bg-info' : 'bg-secondary' }}">
                                {{ ucfirst($student->registration_type) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Referral Code</label>
                        <p class="mb-0"><code>{{ $student->referral_code }}</code></p>
                    </div>
                </div>
                @if($student->medical_conditions)
                <div class="mt-3">
                    <label class="form-label text-muted small mb-1">Medical Conditions</label>
                    <p class="mb-0">{{ $student->medical_conditions }}</p>
                </div>
                @endif
                @if($student->notes)
                <div class="mt-3">
                    <label class="form-label text-muted small mb-1">Notes</label>
                    <p class="mb-0">{{ $student->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Parent Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user-friends me-2"></i> Parent Information
            </div>
            <div class="card-body">
                @if($student->parent)
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Parent Name</label>
                        <p class="mb-0">
                            <a href="{{ route('admin.parents.show', $student->parent) }}">
                                {{ $student->parent->user->name }}
                            </a>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Relationship</label>
                        <p class="mb-0">{{ ucfirst($student->parent->relationship ?? 'N/A') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Phone</label>
                        <p class="mb-0">
                            <a href="tel:{{ $student->parent->user->phone }}">{{ $student->parent->user->phone }}</a>
                            @if($whatsappEnabled && $student->parent->user->phone)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $student->parent->user->phone) }}"
                                   target="_blank" class="text-success ms-2" title="Open WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Email</label>
                        <p class="mb-0"><a href="mailto:{{ $student->parent->user->email }}">{{ $student->parent->user->email }}</a></p>
                    </div>
                </div>
                @else
                <p class="text-muted mb-0">No parent linked.</p>
                @endif
            </div>
        </div>

        <!-- Enrollments -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-graduation-cap me-2"></i> Enrollments</span>
                <span class="badge bg-primary">{{ $student->enrollments->count() }}</span>
            </div>
            <div class="card-body">
                @if($student->enrollments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Package</th><th>Class</th><th>Status</th><th>Start Date</th></tr>
                        </thead>
                        <tbody>
                            @foreach($student->enrollments as $enrollment)
                            <tr>
                                <td>{{ $enrollment->package->name ?? 'N/A' }}</td>
                                <td>{{ $enrollment->class->name ?? 'N/A' }}</td>
                                <td>
                                    @php $sb = ['active'=>'bg-success','suspended'=>'bg-warning text-dark','cancelled'=>'bg-danger','expired'=>'bg-secondary']; @endphp
                                    <span class="badge {{ $sb[$enrollment->status] ?? 'bg-secondary' }}">{{ ucfirst($enrollment->status) }}</span>
                                </td>
                                <td>{{ $enrollment->start_date?->format('d M Y') ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted mb-0 text-center py-3">No enrollments yet.</p>
                @endif
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-money-bill-wave me-2"></i> Recent Payments</div>
            <div class="card-body">
                @if($student->payments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($student->payments as $payment)
                            <tr>
                                <td>{{ $payment->created_at->format('d M Y') }}</td>
                                <td>RM {{ number_format($payment->amount, 2) }}</td>
                                <td>{{ ucfirst($payment->payment_method ?? 'N/A') }}</td>
                                <td>
                                    @php $pb = ['completed'=>'bg-success','pending'=>'bg-warning text-dark','failed'=>'bg-danger']; @endphp
                                    <span class="badge {{ $pb[$payment->status] ?? 'bg-secondary' }}">{{ ucfirst($payment->status) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted mb-0 text-center py-3">No payments yet.</p>
                @endif
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-calendar-check me-2"></i> Recent Attendance</div>
            <div class="card-body">
                @if($student->attendance->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Date</th><th>Class</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($student->attendance as $att)
                            <tr>
                                <td>{{ $att->created_at->format('d M Y') }}</td>
                                <td>{{ $att->classSession->class->name ?? 'N/A' }}</td>
                                <td>
                                    @php $ab = ['present'=>'bg-success','absent'=>'bg-danger','late'=>'bg-warning text-dark','excused'=>'bg-info']; @endphp
                                    <span class="badge {{ $ab[$att->status] ?? 'bg-secondary' }}">{{ ucfirst($att->status) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted mb-0 text-center py-3">No attendance records yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-start gap-2 mb-4">
    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>
@endsection

@push('scripts')
<script>
let pwShowing = false;
const storedPw = '{{ $student->user->password_view ?? "" }}';

function togglePasswordView() {
    const el   = document.getElementById('passwordView');
    const icon = document.getElementById('passwordToggleIcon');
    if (!pwShowing && storedPw) {
        el.type = 'text'; el.value = storedPw;
        icon.classList.replace('fa-eye', 'fa-eye-slash');
        pwShowing = true;
    } else {
        el.type = 'password'; el.value = '••••••••';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
        pwShowing = false;
    }
}
</script>
<style>
.border-success { border-color: #25D366 !important; }
.bg-success     { background-color: #25D366 !important; }
.btn-success    { background-color: #25D366; border-color: #25D366; }
.btn-success:hover { background-color: #1da851; border-color: #1da851; }
.btn-outline-success { color: #25D366; border-color: #25D366; }
.btn-outline-success:hover { background-color: #25D366; color: white; }
.text-success   { color: #25D366 !important; }
.user-avatar    { border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
</style>
@endpush
