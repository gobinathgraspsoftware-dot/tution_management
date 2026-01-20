{{-- Monthly Timetable View for Teachers --}}
<div class="monthly-timetable">
    @php
        $currentMonth = isset($timetableData['month']) ? \Carbon\Carbon::parse($timetableData['month']) : now();
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();
        $startDayOfWeek = $startOfMonth->dayOfWeekIso; // 1 = Monday, 7 = Sunday
        $daysInMonth = $currentMonth->daysInMonth;
        
        // Get all schedules for this month
        $monthSchedules = isset($timetableData['schedules']) ? $timetableData['schedules'] : [];
        
        // Get the recurring weekly schedule
        $weeklySchedule = isset($timetableData['timetable']) ? $timetableData['timetable'] : [];
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">
            <i class="fas fa-calendar-alt me-2 text-primary"></i>
            {{ $currentMonth->format('F Y') }}
        </h5>
        <div class="d-flex gap-2">
            <span class="badge bg-primary"><i class="fas fa-circle me-1"></i> Teaching Days</span>
            <span class="badge bg-secondary"><i class="fas fa-circle me-1"></i> Free Days</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered monthly-calendar">
            <thead class="table-light">
                <tr>
                    <th class="text-center">Mon</th>
                    <th class="text-center">Tue</th>
                    <th class="text-center">Wed</th>
                    <th class="text-center">Thu</th>
                    <th class="text-center">Fri</th>
                    <th class="text-center bg-light">Sat</th>
                    <th class="text-center bg-light">Sun</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $dayCounter = 1;
                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                @endphp

                @for($week = 0; $week < 6; $week++)
                    @if($dayCounter <= $daysInMonth)
                        <tr>
                            @for($dayOfWeek = 1; $dayOfWeek <= 7; $dayOfWeek++)
                                @php
                                    $showDay = false;
                                    $currentDate = null;
                                    $dayName = $days[$dayOfWeek - 1];
                                    
                                    if ($week == 0 && $dayOfWeek < $startDayOfWeek) {
                                        $showDay = false;
                                    } elseif ($dayCounter <= $daysInMonth) {
                                        $showDay = true;
                                        $currentDate = $startOfMonth->copy()->addDays($dayCounter - 1);
                                        $dayCounter++;
                                    }
                                    
                                    $daySchedules = $weeklySchedule[$dayName] ?? [];
                                    $isToday = $currentDate && $currentDate->isToday();
                                    $isWeekend = $dayOfWeek >= 6;
                                @endphp

                                <td class="calendar-cell {{ $isWeekend ? 'bg-light' : '' }} {{ $isToday ? 'today' : '' }}">
                                    @if($showDay)
                                        <div class="day-number {{ $isToday ? 'bg-primary text-white rounded-circle' : '' }}">
                                            {{ $currentDate->day }}
                                        </div>
                                        
                                        @if(count($daySchedules) > 0)
                                            <div class="day-schedules mt-1">
                                                @foreach($daySchedules as $index => $schedule)
                                                    @if($index < 2)
                                                        <div class="schedule-item small" 
                                                             style="background-color: {{ $schedule['color'] ?? '#6c757d' }}20; border-left: 2px solid {{ $schedule['color'] ?? '#6c757d' }};"
                                                             title="{{ $schedule['class_name'] }} - {{ $schedule['subject'] }} ({{ \Carbon\Carbon::parse($schedule['start_time'])->format('h:i A') }})">
                                                            <span class="schedule-time">{{ \Carbon\Carbon::parse($schedule['start_time'])->format('h:i') }}</span>
                                                            <span class="schedule-name text-truncate">{{ $schedule['class_name'] }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                                
                                                @if(count($daySchedules) > 2)
                                                    <div class="more-schedules small text-muted">
                                                        +{{ count($daySchedules) - 2 }} more
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endif
                @endfor
            </tbody>
        </table>
    </div>

    {{-- Legend --}}
    <div class="mt-4 p-3 bg-light rounded">
        <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Monthly Summary</h6>
        <div class="row">
            <div class="col-md-6">
                <p class="mb-2">
                    <strong>Teaching Days:</strong> 
                    @php
                        $teachingDays = 0;
                        foreach($days as $day) {
                            if(isset($weeklySchedule[$day]) && count($weeklySchedule[$day]) > 0) {
                                // Count occurrences of this day in the month
                                $tempDate = $startOfMonth->copy();
                                while($tempDate->lte($endOfMonth)) {
                                    if(strtolower($tempDate->format('l')) == $day) {
                                        $teachingDays++;
                                    }
                                    $tempDate->addDay();
                                }
                            }
                        }
                    @endphp
                    <span class="badge bg-primary">{{ $teachingDays }} days</span>
                </p>
            </div>
            <div class="col-md-6">
                <p class="mb-2">
                    <strong>Total Classes:</strong> 
                    @php
                        $totalClasses = 0;
                        foreach($weeklySchedule as $day => $schedules) {
                            $tempDate = $startOfMonth->copy();
                            while($tempDate->lte($endOfMonth)) {
                                if(strtolower($tempDate->format('l')) == $day) {
                                    $totalClasses += count($schedules);
                                }
                                $tempDate->addDay();
                            }
                        }
                    @endphp
                    <span class="badge bg-success">{{ $totalClasses }} classes</span>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.monthly-calendar {
    table-layout: fixed;
}
.monthly-calendar th,
.monthly-calendar td {
    width: calc(100% / 7);
}
.calendar-cell {
    height: 100px;
    vertical-align: top;
    padding: 5px;
    position: relative;
}
.calendar-cell.today {
    background-color: #e3f2fd !important;
}
.day-number {
    font-weight: bold;
    font-size: 0.9rem;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.day-schedules {
    max-height: 60px;
    overflow: hidden;
}
.schedule-item {
    padding: 2px 4px;
    margin-bottom: 2px;
    border-radius: 2px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.schedule-time {
    font-weight: 600;
    font-size: 0.7rem;
    flex-shrink: 0;
}
.schedule-name {
    font-size: 0.7rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.more-schedules {
    font-style: italic;
    text-align: center;
}

@media (max-width: 768px) {
    .calendar-cell {
        height: 60px;
        padding: 2px;
    }
    .day-schedules {
        display: none;
    }
    .day-number {
        font-size: 0.8rem;
        width: 22px;
        height: 22px;
    }
}
</style>
