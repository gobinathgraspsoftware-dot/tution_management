{{-- Weekly View Partial --}}
@if(isset($timetableData['days']) && count($timetableData['days']) > 0)
    <div class="schedule-weekly-view">
        <h5 class="mb-3 text-center">
            Week: {{ \Carbon\Carbon::parse($timetableData['week_start'])->format('d M') }} - 
            {{ \Carbon\Carbon::parse($timetableData['week_end'])->format('d M Y') }}
        </h5>
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th width="15%">Time</th>
                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                            <th class="text-center">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Get all unique time slots
                        $timeSlots = [];
                        foreach($timetableData['days'] as $classes) {
                            foreach($classes as $class) {
                                $timeKey = $class['start_time'] . '-' . $class['end_time'];
                                if (!in_array($timeKey, $timeSlots)) {
                                    $timeSlots[] = $timeKey;
                                }
                            }
                        }
                        sort($timeSlots);
                    @endphp
                    
                    @forelse($timeSlots as $timeSlot)
                        @php
                            list($startTime, $endTime) = explode('-', $timeSlot);
                        @endphp
                        <tr>
                            <td class="align-middle text-center bg-light">
                                <strong>{{ \Carbon\Carbon::parse($startTime)->format('h:i A') }}</strong><br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($endTime)->format('h:i A') }}</small>
                            </td>
                            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                <td class="align-middle" style="min-width: 150px;">
                                    @php
                                        $dayClasses = collect($timetableData['days'][$day] ?? [])
                                            ->filter(function($class) use ($startTime, $endTime) {
                                                return $class['start_time'] === $startTime && $class['end_time'] === $endTime;
                                            });
                                    @endphp
                                    
                                    @foreach($dayClasses as $class)
                                        <div class="p-2 mb-1 rounded {{ $class['type'] === 'online' ? 'bg-primary' : 'bg-success' }} bg-opacity-10 border border-{{ $class['type'] === 'online' ? 'primary' : 'success' }}">
                                            <div class="fw-bold small">{{ $class['class_name'] }}</div>
                                            <div class="text-muted small">{{ $class['subject'] ?? '' }}</div>
                                            <div class="text-muted small">
                                                <i class="fas fa-user"></i> {{ \Str::limit($class['teacher'] ?? 'N/A', 15) }}
                                            </div>
                                            @if($class['type'] === 'online')
                                                <div class="mt-1">
                                                    <i class="fas fa-video text-primary"></i> <small>Online</small>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No classes scheduled for this week</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No classes scheduled for this week</h5>
    </div>
@endif
