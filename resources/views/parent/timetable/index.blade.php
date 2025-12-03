@extends('layouts.app')

@section('title', 'Children Timetable')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Children's Class Schedule</h1>
            <p class="text-muted mb-0">View your children's timetable</p>
        </div>
        <button type="button" class="btn btn-outline-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Select Child</label>
                        <select name="student_id" class="form-select" onchange="this.form.submit()">
                            @foreach($children as $child)
                                <option value="{{ $child->id }}" {{ $selectedStudent && $selectedStudent->id == $child->id ? 'selected' : '' }}>
                                    {{ $child->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">View</label>
                        <select name="view" class="form-select" onchange="this.form.submit()">
                            <option value="daily" {{ $view == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ $view == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ $view == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedStudent)
        <div class="card">
            <div class="card-header">
                <strong>{{ $selectedStudent->user->name }}'s Schedule</strong>
            </div>
            <div class="card-body">
                @if($view == 'daily')
                    @include('admin.timetable._daily', ['timetableData' => $timetableData])
                @elseif($view == 'weekly')
                    @include('admin.timetable._weekly', ['timetableData' => $timetableData])
                @else
                    @include('admin.timetable._monthly', ['timetableData' => $timetableData])
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Please select a child to view their timetable.
        </div>
    @endif
</div>
@endsection
