<div class="daily-view">
    <h4 class="mb-4 text-center">
        {{ \Carbon\Carbon::parse($timetableData['date'])->format('l, F j, Y') }}
    </h4>

    @if(isset($timetableData['schedules']) && count($timetableData['schedules']) > 0)
        <div class="timeline">
            @foreach($timetableData['schedules'] as $schedule)
                <div class="class-card mb-3" style="border-left-color: {{ $schedule['color'] }};">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ $schedule['class_name'] }}</h5>
                            <div class="text-muted small mb-2">
                                <i class="fas fa-book"></i> {{ $schedule['subject'] }}
                            </div>
                            <div class="d-flex gap-3 flex-wrap">
                                <span class="text-muted">
                                    <i class="fas fa-clock"></i>
                                    {{ date('h:i A', strtotime($schedule['start_time'])) }} - {{ date('h:i A', strtotime($schedule['end_time'])) }}
                                </span>
                                @if($schedule['teacher'] != 'N/A')
                                    <span class="text-muted">
                                        <i class="fas fa-user"></i> {{ $schedule['teacher'] }}
                                    </span>
                                @endif
                                @if($schedule['location'])
                                    <span class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i> {{ $schedule['location'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="class-type-badge badge {{ $schedule['type'] == 'online' ? 'bg-primary' : 'bg-success' }}">
                                {{ ucfirst($schedule['type']) }}
                            </span>
                        </div>
                    </div>
                    @if($schedule['meeting_link'] && $schedule['type'] == 'online')
                        <div class="mt-2">
                            <a href="{{ $schedule['meeting_link'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-video"></i> Join Meeting
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle"></i> No classes scheduled for this day.
        </div>
    @endif
</div>
