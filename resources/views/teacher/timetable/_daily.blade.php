{{-- Daily Timetable View for Teachers --}}
<div class="daily-timetable">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">
            <i class="fas fa-calendar-day me-2 text-primary"></i>
            {{ \Carbon\Carbon::parse($timetableData['date'])->format('l, d F Y') }}
        </h5>
        <span class="badge bg-primary fs-6">{{ $timetableData['day'] }}</span>
    </div>

    @if(isset($timetableData['schedules']) && count($timetableData['schedules']) > 0)
        <div class="timeline">
            @foreach($timetableData['schedules'] as $schedule)
                <div class="card mb-3 border-start border-4" style="border-color: {{ $schedule['color'] ?? '#6c757d' }} !important;">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="time-block text-center p-3 rounded" style="background-color: {{ $schedule['color'] ?? '#6c757d' }}20;">
                                    <div class="fw-bold" style="color: {{ $schedule['color'] ?? '#6c757d' }};">
                                        {{ \Carbon\Carbon::parse($schedule['start_time'])->format('h:i A') }}
                                    </div>
                                    <small class="text-muted">to</small>
                                    <div class="fw-bold" style="color: {{ $schedule['color'] ?? '#6c757d' }};">
                                        {{ \Carbon\Carbon::parse($schedule['end_time'])->format('h:i A') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="card-title mb-1">{{ $schedule['class_name'] }}</h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-book me-1"></i> {{ $schedule['subject'] }}
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    @if($schedule['location'])
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-map-marker-alt me-1"></i>{{ $schedule['location'] }}
                                        </span>
                                    @endif
                                    @if($schedule['type'])
                                        <span class="badge {{ $schedule['type'] == 'online' ? 'bg-info' : 'bg-success' }}">
                                            <i class="fas {{ $schedule['type'] == 'online' ? 'fa-video' : 'fa-building' }} me-1"></i>
                                            {{ ucfirst($schedule['type']) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto">
                                @if($schedule['meeting_link'])
                                    <a href="{{ $schedule['meeting_link'] }}" target="_blank" class="btn btn-primary btn-sm">
                                        <i class="fas fa-video me-1"></i> Join Class
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-calendar-check fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">No Classes Scheduled</h5>
            <p class="text-muted">You have no classes scheduled for this day.</p>
        </div>
    @endif
</div>

<style>
.daily-timetable .time-block {
    min-width: 100px;
}
.daily-timetable .card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.daily-timetable .card:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>
