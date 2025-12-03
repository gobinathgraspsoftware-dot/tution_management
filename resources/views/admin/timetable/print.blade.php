<!DOCTYPE html>
<html>
<head>
    <title>Timetable - Arena Matriks Edu Group</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .class-card { page-break-inside: avoid; }
        }
        .class-card { border-left: 4px solid; padding: 10px; margin-bottom: 10px; background: #f8f9fa; }
        .header-logo { max-height: 60px; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="text-center mb-4">
            <h2>Arena Matriks Edu Group</h2>
            <h4>Class Timetable</h4>
            <p class="text-muted">
                @if($view == 'daily')
                    {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                @elseif($view == 'weekly')
                    Week {{ $timetableData['week_number'] ?? '' }}
                    ({{ \Carbon\Carbon::parse($timetableData['start_date'] ?? $date)->format('M j') }} -
                    {{ \Carbon\Carbon::parse($timetableData['end_date'] ?? $date)->format('M j, Y') }})
                @else
                    {{ $timetableData['month'] ?? \Carbon\Carbon::parse($date)->format('F Y') }}
                @endif
            </p>
            <p class="small">Generated on: {{ now()->format('F j, Y h:i A') }}</p>
        </div>

        @if($view == 'daily')
            @include('admin.timetable._daily', ['timetableData' => $timetableData])
        @elseif($view == 'weekly')
            @include('admin.timetable._weekly', ['timetableData' => $timetableData])
        @else
            @include('admin.timetable._monthly', ['timetableData' => $timetableData])
        @endif

        <div class="mt-4 text-center text-muted small">
            <p>© {{ date('Y') }} Arena Matriks Edu Group. All rights reserved.</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
