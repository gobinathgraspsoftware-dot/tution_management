<div class="weekly-view">
    <h4 class="mb-3 text-center">
        Week {{ $timetableData['week_number'] }}
        ({{ \Carbon\Carbon::parse($timetableData['start_date'])->format('M j') }} -
        {{ \Carbon\Carbon::parse($timetableData['end_date'])->format('M j, Y') }})
    </h4>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th style="width: 120px;">Time</th>
                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                        <th class="text-center">{{ $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    // Get all unique time slots
                    $timeSlots = [];

                    foreach($timetableData['timetable'] as $day => $schedules) {
                        foreach($schedules as $schedule) {

                            // Convert Carbon / DateTime into string format
                            $start = is_object($schedule['start_time'])
                                        ? $schedule['start_time']->format('H:i')
                                        : (string) $schedule['start_time'];

                            $end = is_object($schedule['end_time'])
                                        ? $schedule['end_time']->format('H:i')
                                        : (string) $schedule['end_time'];

                            // Use string-based array key to avoid "Illegal offset type"
                            $timeSlots[$start] = [
                                'start' => $start,
                                'end'   => $end,
                            ];
                        }
                    }

                    // Sort by time
                    ksort($timeSlots);
                @endphp

                @forelse($timeSlots as $timeSlot)
                    <tr>
                        <td class="text-center align-middle bg-light">
                            <small>
                                {{ date('h:i A', strtotime($timeSlot['start'])) }}<br>
                                <i class="fas fa-arrow-down" style="font-size: 0.7rem;"></i><br>
                                {{ date('h:i A', strtotime($timeSlot['end'])) }}
                            </small>
                        </td>

                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                            <td class="time-slot p-2">
                                @php
                                    $daySchedules = collect($timetableData['timetable'][$day] ?? [])
                                        ->where('start_time', $timeSlot['start']);
                                @endphp

                                @foreach($daySchedules as $schedule)
                                    <div class="class-card p-2 small" style="border-left-color: {{ $schedule['color'] }};">
                                        <strong>{{ $schedule['class_name'] }}</strong>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            {{ $schedule['subject'] }}
                                        </div>
                                        @if($schedule['teacher_name'] != 'N/A')
                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                <i class="fas fa-user"></i> {{ $schedule['teacher_name'] }}
                                            </div>
                                        @endif
                                        @if($schedule['location'])
                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                <i class="fas fa-map-marker-alt"></i> {{ $schedule['location'] }}
                                            </div>
                                        @endif
                                        <span class="class-type-badge badge {{ $schedule['type'] == 'online' ? 'bg-primary' : 'bg-success' }}" style="font-size: 0.65rem;">
                                            {{ ucfirst($schedule['type']) }}
                                        </span>
                                    </div>
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-info-circle"></i> No classes scheduled for this week.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
