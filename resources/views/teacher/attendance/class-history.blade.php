@extends('layouts.app')

@section('title', 'Attendance History - ' . $class->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $class->name }} - Attendance History</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active">{{ $class->name }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('teacher.attendance.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('teacher.attendance.class-history', $class) }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        <option value="">All Months</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('teacher.attendance.class-history', $class) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Student Summary -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Student Attendance Summary</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Present</th>
                            <th class="text-center">Absent</th>
                            <th class="text-center">Late</th>
                            <th class="text-center">Excused</th>
                            <th class="text-center">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentSummary as $summary)
                            <tr>
                                <td>
                                    <strong>{{ $summary['student']->user->name ?? 'N/A' }}</strong>
                                </td>
                                <td class="text-center">{{ $summary['total'] }}</td>
                                <td class="text-center"><span class="badge bg-success">{{ $summary['present'] }}</span></td>
                                <td class="text-center"><span class="badge bg-danger">{{ $summary['absent'] }}</span></td>
                                <td class="text-center"><span class="badge bg-warning text-dark">{{ $summary['late'] }}</span></td>
                                <td class="text-center"><span class="badge bg-info">{{ $summary['excused'] }}</span></td>
                                <td class="text-center">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar {{ $summary['rate'] >= 75 ? 'bg-success' : ($summary['rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                             role="progressbar" 
                                             style="width: {{ $summary['rate'] }}%">
                                            {{ $summary['rate'] }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sessions List -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Session History</h5>
        </div>
        <div class="card-body">
            @if($sessions->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No sessions found</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th class="text-center">Present</th>
                                <th class="text-center">Absent</th>
                                <th class="text-center">Late</th>
                                <th class="text-center">Excused</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                                @php
                                    $attendances = $session->attendances;
                                    $present = $attendances->where('status', 'present')->count();
                                    $absent = $attendances->where('status', 'absent')->count();
                                    $late = $attendances->where('status', 'late')->count();
                                    $excused = $attendances->where('status', 'excused')->count();
                                @endphp
                                <tr>
                                    <td>{{ $session->session_date->format('M d, Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}</td>
                                    <td class="text-center"><span class="badge bg-success">{{ $present }}</span></td>
                                    <td class="text-center"><span class="badge bg-danger">{{ $absent }}</span></td>
                                    <td class="text-center"><span class="badge bg-warning text-dark">{{ $late }}</span></td>
                                    <td class="text-center"><span class="badge bg-info">{{ $excused }}</span></td>
                                    <td>
                                        <a href="{{ route('teacher.attendance.session-details', $session) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $sessions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
