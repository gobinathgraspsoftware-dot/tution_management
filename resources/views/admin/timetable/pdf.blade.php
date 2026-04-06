<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Timetable - Arena Matriks Edu Group</title>
    <style>
        /* ── Base ──────────────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }

        /* ── Header ────────────────────────────────────── */
        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            color: #1e40af;
            margin-bottom: 2px;
        }
        .header h2 {
            font-size: 14px;
            font-weight: normal;
            color: #555;
            margin-bottom: 4px;
        }
        .header .period {
            font-size: 12px;
            color: #2563eb;
            font-weight: bold;
        }
        .header .meta {
            font-size: 8px;
            color: #999;
            margin-top: 4px;
        }

        /* ── Tables ────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background-color: #2563eb;
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 6px 4px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            border: 1px solid #d1d5db;
            padding: 4px;
            vertical-align: top;
            font-size: 9px;
        }
        tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        /* ── Time column ───────────────────────────────── */
        .time-cell {
            text-align: center;
            font-weight: bold;
            background-color: #f3f4f6 !important;
            width: 80px;
            white-space: nowrap;
            font-size: 8px;
            vertical-align: middle;
        }

        /* ── Class block inside cell ───────────────────── */
        .class-block {
            border-left: 3px solid;
            padding: 3px 5px;
            margin-bottom: 3px;
            border-radius: 2px;
            background-color: #f0f9ff;
        }
        .class-block:last-child { margin-bottom: 0; }
        .class-name {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 1px;
        }
        .class-detail {
            font-size: 7.5px;
            color: #666;
        }
        .badge-online {
            display: inline-block;
            background: #2563eb;
            color: #fff;
            font-size: 7px;
            padding: 1px 4px;
            border-radius: 3px;
        }
        .badge-offline {
            display: inline-block;
            background: #16a34a;
            color: #fff;
            font-size: 7px;
            padding: 1px 4px;
            border-radius: 3px;
        }

        /* ── Daily view ────────────────────────────────── */
        .daily-table th { text-align: left; }
        .daily-table td { padding: 8px 6px; }

        /* ── Monthly calendar ──────────────────────────── */
        .month-cell {
            height: 60px;
            width: 14.28%;
        }
        .month-cell .day-num {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 2px;
        }
        .month-cell.other-month {
            background-color: #f3f4f6;
            color: #aaa;
        }
        .month-event {
            font-size: 7px;
            border-left: 2px solid;
            padding-left: 2px;
            margin-bottom: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── Empty state ───────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #999;
            font-size: 12px;
        }

        /* ── Footer ────────────────────────────────────── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
        }

        /* ── Page break control ────────────────────────── */
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    {{-- ── PDF Header ───────────────────────────────── --}}
    <div class="header">
        <h1>Arena Matriks Edu Group</h1>
        <h2>Class Timetable</h2>
        <div class="period">
            @if($view == 'daily')
                {{ \Carbon\Carbon::parse($timetableData['date'])->format('l, F j, Y') }}
            @elseif($view == 'weekly')
                Week {{ $timetableData['week_number'] }}
                ({{ \Carbon\Carbon::parse($timetableData['start_date'])->format('M j') }} &ndash;
                {{ \Carbon\Carbon::parse($timetableData['end_date'])->format('M j, Y') }})
            @else
                {{ $timetableData['month'] ?? \Carbon\Carbon::parse($date)->format('F Y') }}
            @endif
        </div>
        <div class="meta">Generated on: {{ $generatedAt }}</div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- DAILY VIEW                                     --}}
    {{-- ══════════════════════════════════════════════ --}}
    @if($view == 'daily')
        @if(isset($timetableData['schedules']) && count($timetableData['schedules']) > 0)
            <table class="daily-table">
                <thead>
                    <tr>
                        <th style="width:15%;">Time</th>
                        <th style="width:20%;">Class</th>
                        <th style="width:15%;">Subject</th>
                        <th style="width:18%;">Teacher</th>
                        <th style="width:15%;">Location</th>
                        <th style="width:9%;">Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timetableData['schedules'] as $schedule)
                        <tr>
                            <td style="white-space:nowrap;">
                                {{ date('h:i A', strtotime($schedule['start_time'])) }}
                                &ndash;
                                {{ date('h:i A', strtotime($schedule['end_time'])) }}
                            </td>
                            <td><strong>{{ $schedule['class_name'] }}</strong></td>
                            <td>{{ $schedule['subject'] }}</td>
                            <td>{{ $schedule['teacher_name'] ?? $schedule['teacher'] ?? 'N/A' }}</td>
                            <td>{{ $schedule['location'] ?? '-' }}</td>
                            <td>
                                <span class="{{ $schedule['type'] == 'online' ? 'badge-online' : 'badge-offline' }}">
                                    {{ ucfirst($schedule['type']) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">No classes scheduled for this day.</div>
        @endif

    {{-- ══════════════════════════════════════════════ --}}
    {{-- WEEKLY VIEW                                    --}}
    {{-- ══════════════════════════════════════════════ --}}
    @elseif($view == 'weekly')
        @php
            $timeSlots = [];
            foreach ($timetableData['timetable'] as $day => $schedules) {
                foreach ($schedules as $schedule) {
                    $start = (string) $schedule['start_time'];
                    $end   = (string) $schedule['end_time'];
                    if (!isset($timeSlots[$start])) {
                        $timeSlots[$start] = ['start' => $start, 'end' => $end];
                    }
                }
            }
            ksort($timeSlots);
            $dayHeaders = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $dayKeys    = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        @endphp

        @if(count($timeSlots) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width:80px;">Time</th>
                        @foreach($dayHeaders as $dh)
                            <th>{{ $dh }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeSlots as $timeSlot)
                        <tr>
                            <td class="time-cell">
                                {{ date('h:i A', strtotime($timeSlot['start'])) }}<br>
                                &darr;<br>
                                {{ date('h:i A', strtotime($timeSlot['end'])) }}
                            </td>
                            @foreach($dayKeys as $day)
                                <td>
                                    @php
                                        $daySchedules = collect($timetableData['timetable'][$day] ?? [])
                                            ->filter(fn($s) => (string) $s['start_time'] === $timeSlot['start']);
                                    @endphp
                                    @foreach($daySchedules as $schedule)
                                        <div class="class-block" style="border-left-color: {{ $schedule['color'] }};">
                                            <div class="class-name">{{ $schedule['class_name'] }}</div>
                                            <div class="class-detail">{{ $schedule['subject'] }}</div>
                                            @if(($schedule['teacher_name'] ?? 'N/A') != 'N/A')
                                                <div class="class-detail">{{ $schedule['teacher_name'] }}</div>
                                            @endif
                                            @if(!empty($schedule['location']))
                                                <div class="class-detail">{{ $schedule['location'] }}</div>
                                            @endif
                                            <span class="{{ $schedule['type'] == 'online' ? 'badge-online' : 'badge-offline' }}">
                                                {{ ucfirst($schedule['type']) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">No classes scheduled for this week.</div>
        @endif

    {{-- ══════════════════════════════════════════════ --}}
    {{-- MONTHLY VIEW                                   --}}
    {{-- ══════════════════════════════════════════════ --}}
    @else
        @php
            $startDate  = \Carbon\Carbon::parse($timetableData['start_date']);
            $endDate    = \Carbon\Carbon::parse($timetableData['end_date']);
            $current    = $startDate->copy()->startOfWeek();
            $weeks      = [];
            while ($current <= $endDate->copy()->endOfWeek()) {
                $week = [];
                for ($i = 0; $i < 7; $i++) {
                    $week[] = $current->copy();
                    $current->addDay();
                }
                $weeks[] = $week;
            }
        @endphp

        <table>
            <thead>
                <tr>
                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dh)
                        <th>{{ $dh }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($weeks as $week)
                    <tr>
                        @foreach($week as $dateObj)
                            @php
                                $dateKey        = $dateObj->format('Y-m-d');
                                $schedules      = $timetableData['schedules'][$dateKey] ?? [];
                                $isCurrentMonth = $dateObj->month == $startDate->month;
                            @endphp
                            <td class="month-cell {{ !$isCurrentMonth ? 'other-month' : '' }}">
                                <div class="day-num">{{ $dateObj->format('j') }}</div>
                                @if($isCurrentMonth && count($schedules) > 0)
                                    @foreach(collect($schedules)->take(3) as $schedule)
                                        <div class="month-event" style="border-left-color: {{ $schedule['color'] }};">
                                            {{ date('h:iA', strtotime($schedule['start_time'])) }}
                                            {{ $schedule['class_name'] }}
                                        </div>
                                    @endforeach
                                    @if(count($schedules) > 3)
                                        <div style="font-size:7px; color:#666; text-align:center;">
                                            +{{ count($schedules) - 3 }} more
                                        </div>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ── Footer ───────────────────────────────────── --}}
    <div class="footer">
        &copy; {{ date('Y') }} Arena Matriks Edu Group. All rights reserved.
    </div>

</body>
</html>
