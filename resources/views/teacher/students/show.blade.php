@extends('layouts.app')

@section('title', 'Student Details')
@section('page-title', 'Student Details')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-user-graduate me-2"></i> Student Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teacher.students.index') }}">My Students</a></li>
                <li class="breadcrumb-item active">{{ $student->user->name }}</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('teacher.students.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Students
    </a>
</div>

<div class="row">
    <!-- Student Profile Card -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="user-avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                    {{ substr($student->user->name, 0, 1) }}
                </div>
                <h4>{{ $student->user->name }}</h4>
                <span class="badge bg-info mb-3">{{ $student->student_id }}</span>

                <hr>

                <div class="row text-center">
                    <div class="col-4">
                        <h5 class="text-primary mb-0">{{ $enrollments->count() }}</h5>
                        <small class="text-muted">Classes</small>
                    </div>
                    <div class="col-4">
                        <h5 class="text-success mb-0">{{ $attendanceStats['attendance_rate'] }}%</h5>
                        <small class="text-muted">Attendance</small>
                    </div>
                    <div class="col-4">
                        <h5 class="text-info mb-0">{{ $examResults->count() }}</h5>
                        <small class="text-muted">Exams</small>
                    </div>
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
                    <p class="mb-0">
                        <a href="mailto:{{ $student->user->email }}">{{ $student->user->email }}</a>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Phone</label>
                    <p class="mb-0">
                        @if($student->user->phone)
                            <a href="tel:{{ $student->user->phone }}">{{ $student->user->phone }}</a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </p>
                </div>
                <div class="mb-0">
                    <label class="form-label text-muted small mb-1">School</label>
                    <p class="mb-0">{{ $student->school ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Parent Information -->
        @if($student->parent)
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user-friends me-2"></i> Parent Information
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Name</label>
                    <p class="mb-0"><strong>{{ $student->parent->user->name }}</strong></p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Email</label>
                    <p class="mb-0">
                        <a href="mailto:{{ $student->parent->user->email }}">{{ $student->parent->user->email }}</a>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Phone</label>
                    <p class="mb-0">
                        @if($student->parent->user->phone)
                            <a href="tel:{{ $student->parent->user->phone }}">{{ $student->parent->user->phone }}</a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </p>
                </div>
                <div class="mb-0">
                    <label class="form-label text-muted small mb-1">Relationship</label>
                    <p class="mb-0">{{ ucfirst($student->parent->relationship ?? 'Parent') }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <!-- Enrolled Classes -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-school me-2"></i> Enrolled Classes (My Classes)
            </div>
            <div class="card-body">
                @if($enrollments->count() > 0)
                <div class="row">
                    @foreach($enrollments as $enrollment)
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">{{ $enrollment->class->name }}</h6>
                                    <span class="badge bg-success">{{ ucfirst($enrollment->status) }}</span>
                                </div>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-book me-1"></i> {{ $enrollment->class->subject->name ?? 'N/A' }}
                                </p>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-box me-1"></i> {{ $enrollment->package->name ?? 'N/A' }}
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-calendar me-1"></i> Enrolled: {{ $enrollment->created_at->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-3 text-muted">
                    <i class="fas fa-info-circle me-1"></i> No active enrollments in your classes.
                </div>
                @endif
            </div>
        </div>

        <!-- Attendance Statistics -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-pie me-2"></i> Attendance Overview</span>
                <a href="{{ route('teacher.students.attendance', $student) }}" class="btn btn-sm btn-outline-primary">
                    View Full History
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="attendance-chart">
                            <div class="progress mb-3" style="height: 25px;">
                                @php
                                    $presentRate = $attendanceStats['total'] > 0
                                        ? round(($attendanceStats['present'] / $attendanceStats['total']) * 100)
                                        : 0;
                                    $lateRate = $attendanceStats['total'] > 0
                                        ? round(($attendanceStats['late'] / $attendanceStats['total']) * 100)
                                        : 0;
                                    $absentRate = $attendanceStats['total'] > 0
                                        ? round(($attendanceStats['absent'] / $attendanceStats['total']) * 100)
                                        : 0;
                                    $excusedRate = $attendanceStats['total'] > 0
                                        ? round(($attendanceStats['excused'] / $attendanceStats['total']) * 100)
                                        : 0;
                                @endphp
                                <div class="progress-bar bg-success" style="width: {{ $presentRate }}%">
                                    {{ $presentRate }}%
                                </div>
                                <div class="progress-bar bg-warning" style="width: {{ $lateRate }}%">
                                    {{ $lateRate }}%
                                </div>
                                <div class="progress-bar bg-danger" style="width: {{ $absentRate }}%">
                                    {{ $absentRate }}%
                                </div>
                                <div class="progress-bar bg-info" style="width: {{ $excusedRate }}%">
                                    {{ $excusedRate }}%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row text-center">
                            <div class="col-3">
                                <h5 class="text-success mb-0">{{ $attendanceStats['present'] }}</h5>
                                <small class="text-muted">Present</small>
                            </div>
                            <div class="col-3">
                                <h5 class="text-warning mb-0">{{ $attendanceStats['late'] }}</h5>
                                <small class="text-muted">Late</small>
                            </div>
                            <div class="col-3">
                                <h5 class="text-danger mb-0">{{ $attendanceStats['absent'] }}</h5>
                                <small class="text-muted">Absent</small>
                            </div>
                            <div class="col-3">
                                <h5 class="text-info mb-0">{{ $attendanceStats['excused'] }}</h5>
                                <small class="text-muted">Excused</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Attendance Records -->
                @if($attendanceRecords->count() > 0)
                <hr>
                <h6 class="mb-3">Recent Attendance</h6>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Class</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendanceRecords->take(5) as $record)
                            <tr>
                                <td>{{ $record->classSession->session_date->format('d M Y') }}</td>
                                <td>{{ $record->classSession->class->name }}</td>
                                <td>
                                    @php
                                        $statusBadge = [
                                            'present' => 'bg-success',
                                            'absent' => 'bg-danger',
                                            'late' => 'bg-warning text-dark',
                                            'excused' => 'bg-info',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusBadge[$record->status] ?? 'bg-secondary' }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        <!-- Exam Results -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-alt me-2"></i> Recent Exam Results</span>
                <a href="{{ route('teacher.students.results', $student) }}" class="btn btn-sm btn-outline-primary">
                    View All Results
                </a>
            </div>
            <div class="card-body">
                @if($examResults->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Exam</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Score</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($examResults as $result)
                            <tr>
                                <td>{{ $result->exam->title }}</td>
                                <td>{{ $result->exam->subject->name ?? $result->exam->class->subject->name ?? 'N/A' }}</td>
                                <td>{{ $result->exam->exam_date->format('d M Y') }}</td>
                                <td>
                                    <strong>{{ $result->marks_obtained }}</strong> / {{ $result->exam->total_marks }}
                                    <small class="text-muted">
                                        ({{ round(($result->marks_obtained / $result->exam->total_marks) * 100) }}%)
                                    </small>
                                </td>
                                <td>
                                    @php
                                        $percentage = ($result->marks_obtained / $result->exam->total_marks) * 100;
                                        $gradeClass = $percentage >= 80 ? 'bg-success' :
                                                     ($percentage >= 60 ? 'bg-primary' :
                                                     ($percentage >= 40 ? 'bg-warning text-dark' : 'bg-danger'));
                                    @endphp
                                    <span class="badge {{ $gradeClass }}">
                                        {{ $result->grade ?? 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-3 text-muted">
                    <i class="fas fa-info-circle me-1"></i> No exam results available yet.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.user-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: bold;
}
</style>
@endpush
