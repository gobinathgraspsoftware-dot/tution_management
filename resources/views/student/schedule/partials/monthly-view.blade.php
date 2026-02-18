{{-- Monthly View Partial --}}
@if(isset($timetableData['days']))
    <div class="schedule-monthly-view">
        <h5 class="mb-3 text-center">
            {{ \Carbon\Carbon::parse($timetableData['month_start'])->format('F Y') }}
        </h5>
        
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Sun</th>
                        <th class="text-center">Mon</th>
                        <th class="text-center">Tue</th>
                        <th class="text-center">Wed</th>
                        <th class="text-center">Thu</th>
                        <th class="text-center">Fri</th>
                        <th class="text-center">Sat</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $monthStart = \Carbon\Carbon::parse($timetableData['month_start']);
                        $monthEnd = \Carbon\Carbon::parse($timetableData['month_end']);
                        $calendarStart = $monthStart->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                        $calendarEnd = $monthEnd->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                        $currentDate = $calendarStart->copy();
                    @endphp
                    
                    @while($currentDate <= $calendarEnd)
                        <tr>
                            @for($i = 0; $i < 7; $i++)
                                @php
                                    $dateKey = $currentDate->format('Y-m-d');
                                    $dayClasses = $timetableData['days'][$dateKey] ?? [];
                                    $isCurrentMonth = $currentDate->month === $monthStart->month;
                                    $isToday = $currentDate->isToday();
                                @endphp
                                <td class="align-top p-2 {{ !$isCurrentMonth ? 'bg-light text-muted' : '' }} {{ $isToday ? 'border-primary border-2' : '' }}" 
                                    style="height: 100px; width: 14.28%;">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="badge {{ $isToday ? 'bg-primary' : 'bg-secondary' }}">
                                            {{ $currentDate->format('d') }}
                                        </span>
                                        @if(count($dayClasses) > 0)
                                            <span class="badge bg-success">{{ count($dayClasses) }}</span>
                                        @endif
                                    </div>
                                    
                                    @if(count($dayClasses) > 0)
                                        <div class="small">
                                            @foreach($dayClasses as $index => $class)
                                                @if($index < 2)
                                                    <div class="mb-1 p-1 rounded bg-primary bg-opacity-10 border border-primary" style="font-size: 0.7rem;">
                                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($class['start_time'])->format('h:i A') }}</div>
                                                        <div class="text-truncate">{{ $class['class_name'] }}</div>
                                                    </div>
                                                @endif
                                            @endforeach
                                            @if(count($dayClasses) > 2)
                                                <div class="text-primary small">+{{ count($dayClasses) - 2 }} more</div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                @php
                                    $currentDate->addDay();
                                @endphp
                            @endfor
                        </tr>
                    @endwhile
                </tbody>
            </table>
        </div>
        
        <!-- Legend -->
        <div class="mt-3">
            <small class="text-muted">
                <span class="badge bg-primary me-2">Today</span>
                <span class="badge bg-success me-2">Has Classes</span>
            </small>
        </div>
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No classes scheduled for this month</h5>
    </div>
@endif
