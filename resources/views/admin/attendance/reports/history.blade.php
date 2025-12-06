@extends('layouts.app')

@section('title', 'Attendance History')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Attendance History</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.attendance.reports.index') }}">Attendance Reports</a></li>
                    <li class="breadcrumb-item active">History</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success" onclick="exportHistory('csv')">
                <i class="fas fa-file-csv me-2"></i>Export CSV
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="exportHistory('xlsx')">
                <i class="fas fa-file-excel me-2"></i>Export Excel
            </button>
            <a href="{{ route('admin.attendance.reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Reports
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter Records</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.attendance.reports.history') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="student_id" class="form-label">Student</label>
                        <select name="student_id" id="student_id" class="form-select">
                            <option value="">All Students</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->user->name }} ({{ $student->student_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="class_id" class="form-label">Class</label>
                        <select name="class_id" id="class_id" class="form-select">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                            <option value="excused" {{ request('status') == 'excused' ? 'selected' : '' }}>Excused</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" name="date_from" id="date_from" class="form-control"
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" name="date_to" id="date_to" class="form-control"
                               value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                        <a href="{{ route('admin.attendance.reports.history') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Reset Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    @php
        $totalRecords = $records->total();
        $presentCount = $records->where('status', 'present')->count();
        $absentCount = $records->where('status', 'absent')->count();
        $lateCount = $records->where('status', 'late')->count();
    @endphp
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($totalRecords) }}</h3>
                    <small>Total Records</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $records->where('status', 'present')->count() }}</h3>
                    <small>Present (This Page)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $records->where('status', 'absent')->count() }}</h3>
                    <small>Absent (This Page)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $records->where('status', 'late')->count() }}</h3>
                    <small>Late (This Page)</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance Records Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-history me-2 text-primary"></i>Attendance Records
            </h5>
            <span class="badge bg-secondary">{{ number_format($records->total()) }} Total Records</span>
        </div>
        <div class="card-body p-0">
            @if($records->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Date & Time</th>
                                <th>Student</th>
                                <th>Class / Subject</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Notified</th>
                                <th>Marked By</th>
                                <th>Remarks</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $index => $record)
                                <tr>
                                    <td class="text-muted">{{ $records->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $record->classSession->session_date->format('d/m/Y') }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $record->classSession->start_time->format('H:i') }} -
                                            {{ $record->classSession->end_time->format('H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-light rounded-circle me-2">
                                                <span class="avatar-text text-dark">
                                                    {{ substr($record->student->user->name ?? 'N', 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $record->student->user->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $record->student->student_id ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $record->classSession->class->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $record->classSession->class->subject->name ?? '' }}</small>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusColors = [
                                                'present' => 'success',
                                                'absent' => 'danger',
                                                'late' => 'warning',
                                                'excused' => 'info'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$record->status] ?? 'secondary' }}">
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($record->parent_notified)
                                            <span class="text-success" title="Notified at {{ $record->notified_at ? $record->notified_at->format('d/m/Y H:i') : 'N/A' }}">
                                                <i class="fas fa-check-circle"></i>
                                            </span>
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-times-circle"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $record->markedBy->name ?? 'System' }}</small>
                                    </td>
                                    <td>
                                        @if($record->remarks)
                                            <span class="text-truncate d-inline-block" style="max-width: 150px;"
                                                  title="{{ $record->remarks }}">
                                                {{ $record->remarks }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if(!$record->parent_notified && $record->student->parent)
                                                <form method="POST" action="{{ route('admin.attendance.reports.resend-notification', $record->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary"
                                                            title="Send notification to parent"
                                                            onclick="return confirm('Send notification to parent?')">
                                                        <i class="fas fa-bell"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('admin.attendance.reports.student', ['student_id' => $record->student_id]) }}"
                                               class="btn btn-sm btn-outline-info" title="View student report">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    {{ $records->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Attendance Records Found</h5>
                    <p class="text-muted">Try adjusting your filters to find records.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-text {
    font-weight: 600;
    font-size: 14px;
}
</style>
@endpush

@push('scripts')
<script>
function exportHistory(format) {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    formData.append('format', format);

    // Build query string
    const params = new URLSearchParams(formData);

    // Redirect to export URL (you would need to create this route)
    window.location.href = '{{ route("admin.attendance.reports.export-history") }}?' + params.toString();
}
</script>
@endpush
