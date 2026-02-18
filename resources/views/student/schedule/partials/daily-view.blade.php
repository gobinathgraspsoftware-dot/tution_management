{{-- Daily View Partial --}}
@if(isset($timetableData['classes']) && count($timetableData['classes']) > 0)
    <div class="schedule-daily-view">
        <h5 class="mb-3 text-center">
            {{ \Carbon\Carbon::parse($timetableData['date'])->format('l, d F Y') }}
        </h5>
        
        <div class="list-group">
            @foreach($timetableData['classes'] as $class)
                <div class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="time-block text-center p-2 bg-primary bg-opacity-10 rounded">
                                <strong class="d-block">{{ \Carbon\Carbon::parse($class['start_time'])->format('h:i A') }}</strong>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($class['end_time'])->format('h:i A') }}</small>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h6 class="mb-1">{{ $class['class_name'] }}</h6>
                            <p class="mb-1 text-muted small">
                                <i class="fas fa-book me-1"></i> {{ $class['subject'] ?? 'N/A' }}
                            </p>
                            <p class="mb-0 text-muted small">
                                <i class="fas fa-user me-1"></i> {{ $class['teacher'] ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-3 text-end">
                            @if($class['type'] === 'online' && $class['meeting_link'])
                                <a href="{{ $class['meeting_link'] }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-video me-1"></i> Join Class
                                </a>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-map-marker-alt me-1"></i> {{ $class['location'] ?? 'Physical Class' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No classes scheduled for this day</h5>
    </div>
@endif
