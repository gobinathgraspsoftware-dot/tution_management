{{-- Weekly Timetable View for Teachers --}}
<div class="weekly-timetable">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">
            <i class="fas fa-calendar-week me-2 text-primary"></i>
            Weekly Schedule
        </h5>
        @if(isset($timetableData['week_range']))
            <span class="badge bg-secondary">{{ $timetableData['week_range'] }}</span>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-bordered timetable-table">
            <thead class="table-light">
                <tr>
                    <th style="width: 100px;">Time</th>
                    @php
                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    @endphp
                    @foreach($days as $day)
                        <th class="text-center {{ strtolower(now()->format('l')) == $day ? 'bg-primary text-white' : '' }}">
                            {{ ucfirst($day) }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    // Collect all unique time slots
                    $timeSlots = collect();
                    if(isset($timetableData['timetable'])) {
                        foreach($timetableData['timetable'] as $day => $schedules) {
                            foreach($schedules as $schedule) {
                                $timeSlots->push($schedule['start_time']);
                            }
                        }
                    }
                    $timeSlots = $timeSlots->unique()->sort()->values();
                @endphp

                @forelse($timeSlots as $timeSlot)
                    <tr>
                        <td class="align-middle text-center bg-light fw-bold">
                            {{ \Carbon\Carbon::parse($timeSlot)->format('h:i A') }}
                        </td>
                        @foreach($days as $day)
                            <td class="p-1">
                                @php
                                    $daySchedules = isset($timetableData['timetable'][$day]) 
                                        ? collect($timetableData['timetable'][$day])->where('start_time', $timeSlot)
                                        : collect();
                                @endphp
                                
                                @foreach($daySchedules as $schedule)
                                    <div class="schedule-card p-2 rounded mb-1" 
                                         style="background-color: {{ $schedule['color'] ?? '#6c757d' }}20; border-left: 3px solid {{ $schedule['color'] ?? '#6c757d' }};">
                                        <div class="fw-bold small" style="color: {{ $schedule['color'] ?? '#6c757d' }};">
                                            {{ $schedule['class_name'] }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ $schedule['subject'] }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ \Carbon\Carbon::parse($schedule['start_time'])->format('h:i A') }} - 
                                            {{ \Carbon\Carbon::parse($schedule['end_time'])->format('h:i A') }}
                                        </div>
                                        @if($schedule['location'])
                                            <span class="badge bg-light text-dark mt-1 small">
                                                <i class="fas fa-map-marker-alt"></i> {{ $schedule['location'] }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-calendar-check fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Classes Scheduled</h5>
                            <p class="text-muted mb-0">You have no classes scheduled for this week.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Alternative Card View for Mobile --}}
    <div class="d-md-none">
        @foreach($days as $day)
            @php
                $daySchedules = isset($timetableData['timetable'][$day]) 
                    ? $timetableData['timetable'][$day] 
                    : [];
            @endphp
            
            @if(count($daySchedules) > 0)
                <div class="mb-4">
                    <h6 class="fw-bold text-uppercase {{ strtolower(now()->format('l')) == $day ? 'text-primary' : 'text-muted' }}">
                        <i class="fas fa-calendar-day me-1"></i>{{ ucfirst($day) }}
                    </h6>
                    
                    @foreach($daySchedules as $schedule)
                        <div class="card mb-2 border-start border-3" style="border-color: {{ $schedule['color'] ?? '#6c757d' }} !important;">
                            <div class="card-body py-2 px-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-0">{{ $schedule['class_name'] }}</h6>
                                        <small class="text-muted">{{ $schedule['subject'] }}</small>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        {{ \Carbon\Carbon::parse($schedule['start_time'])->format('h:i A') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
</div>

<style>
.timetable-table {
    min-width: 900px;
}
.timetable-table th,
.timetable-table td {
    vertical-align: top;
    min-width: 120px;
}
.schedule-card {
    transition: transform 0.2s ease;
    cursor: pointer;
}
.schedule-card:hover {
    transform: scale(1.02);
}
@media (max-width: 768px) {
    .table-responsive {
        display: none;
    }
}
@media (min-width: 769px) {
    .d-md-none {
        display: none !important;
    }
}
</style>
