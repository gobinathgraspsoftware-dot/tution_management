<div class="monthly-view">
    <h4 class="mb-4 text-center">{{ $timetableData['month'] }}</h4>

    @php
        $startDate = \Carbon\Carbon::parse($timetableData['start_date']);
        $endDate = \Carbon\Carbon::parse($timetableData['end_date']);
        $currentDate = $startDate->copy()->startOfWeek();
        $weeks = [];

        while ($currentDate <= $endDate->endOfWeek()) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $currentDate->copy();
                $currentDate->addDay();
            }
            $weeks[] = $week;
        }
    @endphp

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th class="text-center">Mon</th>
                    <th class="text-center">Tue</th>
                    <th class="text-center">Wed</th>
                    <th class="text-center">Thu</th>
                    <th class="text-center">Fri</th>
                    <th class="text-center">Sat</th>
                    <th class="text-center">Sun</th>
                </tr>
            </thead>
            <tbody>
                @foreach($weeks as $week)
                    <tr>
                        @foreach($week as $date)
                            @php
                                $dateKey = $date->format('Y-m-d');
                                $schedules = $timetableData['schedules'][$dateKey] ?? [];
                                $isCurrentMonth = $date->month == $startDate->month;
                                $isToday = $date->isToday();
                            @endphp
                            <td class="time-slot p-2 {{ !$isCurrentMonth ? 'bg-light' : '' }}" style="height: 120px; vertical-align: top;">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <strong class="{{ $isToday ? 'text-primary' : ($isCurrentMonth ? '' : 'text-muted') }}">
                                        {{ $date->format('j') }}
                                    </strong>
                                    @if(count($schedules) > 0)
                                        <span class="badge bg-primary rounded-pill" style="font-size: 0.65rem;">
                                            {{ count($schedules) }}
                                        </span>
                                    @endif
                                </div>

                                @if($isCurrentMonth && count($schedules) > 0)
                                    @foreach($schedules->take(2) as $schedule)
                                        <div class="small mb-1 p-1" style="background: {{ $schedule['color'] }}15; border-left: 3px solid {{ $schedule['color'] }}; border-radius: 3px;">
                                            <div style="font-size: 0.7rem;">
                                                <strong>{{ date('h:i A', strtotime($schedule['start_time'])) }}</strong>
                                            </div>
                                            <div style="font-size: 0.7rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $schedule['class_name'] }}
                                            </div>
                                        </div>
                                    @endforeach

                                    @if(count($schedules) > 2)
                                        <div class="text-muted small text-center" style="font-size: 0.65rem;">
                                            +{{ count($schedules) - 2 }} more
                                        </div>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
