@extends('layouts.app')

@section('title', 'Child Profile - ' . ($student->user->name ?? 'Student'))

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Child Profile</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('parent.children.index') }}">My Children</a></li>
                    <li class="breadcrumb-item active">{{ $student->user->name ?? 'Profile' }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('parent.children.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Children
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Profile Info -->
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="avatar-xl mx-auto" style="width: 100px; height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <span class="text-white" style="font-size: 2.5rem; font-weight: bold;">
                                {{ strtoupper(substr($student->user->name ?? 'S', 0, 1)) }}
                            </span>
                        </div>
                    </div>
                    <h4 class="mb-1">{{ $student->user->name ?? 'N/A' }}</h4>
                    <p class="text-muted mb-2">
                        <code>{{ $student->student_id }}</code>
                    </p>
                    <span class="badge bg-{{ ($student->user->status ?? 'active') === 'active' ? 'success' : 'secondary' }} mb-3">
                        {{ ucfirst($student->user->status ?? 'Active') }}
                    </span>

                    <hr>

                    <div class="text-start">
                        <p class="mb-2">
                            <i class="fas fa-envelope text-muted me-2"></i>
                            {{ $student->user->email ?? 'N/A' }}
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-phone text-muted me-2"></i>
                            {{ $student->user->phone ?? 'N/A' }}
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-birthday-cake text-muted me-2"></i>
                            {{ $student->date_of_birth ? $student->date_of_birth->format('d M Y') : 'N/A' }}
                            @if($student->date_of_birth)
                            <small class="text-muted">({{ $student->date_of_birth->age }} years old)</small>
                            @endif
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-venus-mars text-muted me-2"></i>
                            {{ ucfirst($student->gender ?? 'N/A') }}
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-id-card text-muted me-2"></i>
                            {{ $student->ic_number ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- School Info Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-school me-2"></i>School Information</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>School:</strong><br>
                        {{ $student->school_name ?? 'N/A' }}
                    </p>
                    <p class="mb-0">
                        <strong>Grade Level:</strong><br>
                        {{ $student->grade_level ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <!-- Quick Stats Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h4 class="mb-0 text-primary">{{ $student->enrollments->where('status', 'active')->count() }}</h4>
                            <small class="text-muted">Active Classes</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="mb-0 text-success">{{ $student->attendance->where('status', 'present')->count() }}</h4>
                            <small class="text-muted">Present Days</small>
                        </div>
                        <div class="col-6">
                            <h4 class="mb-0 text-info">{{ $student->examResults->count() }}</h4>
                            <small class="text-muted">Exams Taken</small>
                        </div>
                        <div class="col-6">
                            <h4 class="mb-0 text-warning">{{ $student->invoices->where('status', '!=', 'paid')->count() }}</h4>
                            <small class="text-muted">Pending Invoices</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Details -->
        <div class="col-lg-8">
            <!-- Enrolled Classes -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-book me-2"></i>Enrolled Classes</h6>
                    <span class="badge bg-primary">{{ $student->enrollments->count() }} Total</span>
                </div>
                <div class="card-body">
                    @forelse($student->enrollments as $enrollment)
                    <div class="d-flex justify-content-between align-items-start mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="bg-primary bg-opacity-10 rounded p-2">
                                    <i class="fas fa-chalkboard text-primary"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">{{ $enrollment->class->name ?? 'Class Not Assigned' }}</h6>
                                <p class="text-muted mb-1 small">
                                    <i class="fas fa-box me-1"></i> {{ $enrollment->package->name ?? 'N/A' }}
                                </p>
                                <p class="text-muted mb-1 small">
                                    <i class="fas fa-book me-1"></i> {{ $enrollment->class->subject->name ?? 'N/A' }}
                                </p>
                                <p class="text-muted mb-0 small">
                                    <i class="fas fa-chalkboard-teacher me-1"></i> {{ $enrollment->class->teacher->user->name ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $enrollment->status === 'active' ? 'success' : ($enrollment->status === 'pending' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($enrollment->status) }}
                            </span>
                            <p class="text-muted small mb-0 mt-1">
                                Since {{ $enrollment->start_date ? $enrollment->start_date->format('d M Y') : 'N/A' }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No enrollments yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Attendance -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Recent Attendance</h6>
                    @if(Route::has('parent.attendance.child'))
                    <a href="{{ route('parent.attendance.child', $student) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    @php
                        $recentAttendance = $student->attendance()->with('classSession.class')->latest()->take(5)->get();
                    @endphp
                    @forelse($recentAttendance as $attendance)
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <span class="fw-bold">{{ $attendance->classSession->class->name ?? 'Class' }}</span>
                            <br>
                            <small class="text-muted">{{ $attendance->created_at->format('d M Y, h:i A') }}</small>
                        </div>
                        <span class="badge bg-{{
                            $attendance->status === 'present' ? 'success' :
                            ($attendance->status === 'absent' ? 'danger' :
                            ($attendance->status === 'late' ? 'warning' : 'info'))
                        }}">
                            {{ ucfirst($attendance->status) }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No attendance records yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Recent Invoices</h6>
                    @if(Route::has('parent.invoices.index'))
                    <a href="{{ route('parent.invoices.index') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    @php
                        $recentInvoices = $student->invoices()->latest()->take(5)->get();
                    @endphp
                    @forelse($recentInvoices as $invoice)
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <span class="fw-bold">#{{ $invoice->invoice_number }}</span>
                            <br>
                            <small class="text-muted">Due: {{ $invoice->due_date->format('d M Y') }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{
                                $invoice->status === 'paid' ? 'success' :
                                ($invoice->status === 'overdue' ? 'danger' :
                                ($invoice->status === 'partial' ? 'info' : 'warning'))
                            }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                            <br>
                            <small class="text-muted">RM {{ number_format($invoice->total_amount, 2) }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No invoices yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Exam Results -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Recent Exam Results</h6>
                </div>
                <div class="card-body">
                    @php
                        $recentResults = $student->examResults()->with('exam.subject')->latest()->take(5)->get();
                    @endphp
                    @forelse($recentResults as $result)
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <span class="fw-bold">{{ $result->exam->title ?? 'Exam' }}</span>
                            <br>
                            <small class="text-muted">{{ $result->exam->subject->name ?? 'Subject' }} - {{ $result->exam->exam_date ? $result->exam->exam_date->format('d M Y') : 'N/A' }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $result->marks_obtained >= ($result->exam->total_marks * 0.5) ? 'success' : 'danger' }}">
                                {{ $result->marks_obtained }} / {{ $result->exam->total_marks ?? '100' }}
                            </span>
                            @if($result->grade)
                            <br>
                            <small class="fw-bold">Grade: {{ $result->grade }}</small>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-award fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No exam results yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
