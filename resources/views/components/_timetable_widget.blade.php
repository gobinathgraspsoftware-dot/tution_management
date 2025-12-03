<div class="card timetable-widget">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-calendar-alt"></i> Today's Schedule
        </h5>
        <a href="{{ route(auth()->user()->hasRole('student') ? 'student.timetable.index' : (auth()->user()->hasRole('teacher') ? 'teacher.timetable.index' : 'admin.timetable.index')) }}" class="btn btn-sm btn-outline-primary">
            View Full
        </a>
    </div>
    <div class="card-body">
        @php
            $today = now()->format('Y-m-d');
            $user = auth()->user();

            // Get today's schedule based on role
            if($user->hasRole('teacher')) {
                $schedules = App\Models\ClassSchedule::with(['class.subject'])
                    ->whereHas('class', fn($q) => $q->where('teacher_id', $user->teacher->id)->where('status', 'active'))
                    ->where('day_of_week', strtolower(now()->format('l')))
                    ->where('is_active', true)
                    ->orderBy('start_time')
                    ->get();
            } elseif($user->hasRole('student')) {
                $enrolledClassIds = App\Models\Enrollment::where('student_id', $user->student->id)
                    ->where('status', 'active')
                    ->pluck('class_id');
                $schedules = App\Models\ClassSchedule::with(['class.subject'])
                    ->whereIn('class_id', $enrolledClassIds)
                    ->where('day_of_week', strtolower(now()->format('l')))
                    ->where('is_active', true)
                    ->orderBy('start_time')
                    ->get();
            } else {
                $schedules = collect();
            }
        @endphp

        @forelse($schedules as $schedule)
            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                <div class="me-3">
                    <div class="text-center">
                        <div class="badge bg-primary">{{ date('h:i', strtotime($schedule->start_time)) }}</div>
                        <div class="text-muted small">{{ date('A', strtotime($schedule->start_time)) }}</div>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">{{ $schedule->class->name }}</h6>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-book"></i> {{ $schedule->class->subject->name }}
                        @if($schedule->location)
                            | <i class="fas fa-map-marker-alt"></i> {{ $schedule->location }}
                        @endif
                    </p>
                </div>
                <div>
                    <span class="badge {{ $schedule->class->type == 'online' ? 'bg-primary' : 'bg-success' }}">
                        {{ ucfirst($schedule->class->type) }}
                    </span>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-3">
                <i class="fas fa-calendar-times"></i>
                <p class="mb-0 mt-2">No classes scheduled for today</p>
            </div>
        @endforelse
    </div>
</div>
