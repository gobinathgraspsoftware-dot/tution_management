<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule - Print</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
        
        .print-header {
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .schedule-table {
            font-size: 0.9rem;
        }
        
        .schedule-table th {
            background-color: #f8f9fa !important;
        }
        
        .class-block {
            padding: 5px;
            margin-bottom: 5px;
            border-left: 3px solid #0d6efd;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <!-- Print Header -->
        <div class="print-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-0">My Class Schedule</h2>
                    <p class="text-muted mb-0">{{ auth()->user()->name }} - {{ auth()->user()->student->student_id }}</p>
                </div>
                <div class="col-md-4 text-end">
                    <p class="mb-0"><strong>Printed:</strong> {{ now()->format('d M Y, h:i A') }}</p>
                    <p class="mb-0"><strong>View:</strong> {{ ucfirst($view) }}</p>
                    @if($view !== 'monthly')
                        <p class="mb-0"><strong>Date:</strong> {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Print Button (hide on print) -->
        <div class="mb-3 no-print text-end">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i> Close
            </button>
        </div>

        <!-- Schedule Content -->
        @if($view === 'daily')
            @if(isset($timetableData['classes']) && count($timetableData['classes']) > 0)
                <h5 class="mb-3">{{ \Carbon\Carbon::parse($timetableData['date'])->format('l, d F Y') }}</h5>
                
                <table class="table table-bordered schedule-table">
                    <thead>
                        <tr>
                            <th width="20%">Time</th>
                            <th width="30%">Class</th>
                            <th width="25%">Subject</th>
                            <th width="25%">Teacher</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timetableData['classes'] as $class)
                            <tr>
                                <td>
                                    {{ \Carbon\Carbon::parse($class['start_time'])->format('h:i A') }} - 
                                    {{ \Carbon\Carbon::parse($class['end_time'])->format('h:i A') }}
                                </td>
                                <td><strong>{{ $class['class_name'] }}</strong></td>
                                <td>{{ $class['subject'] ?? 'N/A' }}</td>
                                <td>{{ $class['teacher'] ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-center text-muted py-5">No classes scheduled for this day</p>
            @endif
            
        @elseif($view === 'weekly')
            @if(isset($timetableData['days']) && count($timetableData['days']) > 0)
                <h5 class="mb-3">
                    Week: {{ \Carbon\Carbon::parse($timetableData['week_start'])->format('d M') }} - 
                    {{ \Carbon\Carbon::parse($timetableData['week_end'])->format('d M Y') }}
                </h5>
                
                <table class="table table-bordered schedule-table">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Classes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                            <tr>
                                <td width="20%"><strong>{{ $day }}</strong></td>
                                <td>
                                    @if(isset($timetableData['days'][$day]) && count($timetableData['days'][$day]) > 0)
                                        @foreach($timetableData['days'][$day] as $class)
                                            <div class="class-block">
                                                <div>
                                                    <strong>{{ $class['class_name'] }}</strong> - {{ $class['subject'] ?? 'N/A' }}
                                                </div>
                                                <div class="small text-muted">
                                                    {{ \Carbon\Carbon::parse($class['start_time'])->format('h:i A') }} - 
                                                    {{ \Carbon\Carbon::parse($class['end_time'])->format('h:i A') }}
                                                    | Teacher: {{ $class['teacher'] ?? 'N/A' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No classes</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-center text-muted py-5">No classes scheduled for this week</p>
            @endif
            
        @else
            <h5 class="mb-3">{{ \Carbon\Carbon::parse($timetableData['month_start'])->format('F Y') }}</h5>
            
            @if(isset($timetableData['days']) && count(array_filter($timetableData['days'])) > 0)
                <table class="table table-bordered schedule-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Classes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timetableData['days'] as $dateKey => $classes)
                            @if(count($classes) > 0)
                                <tr>
                                    <td width="20%">
                                        <strong>{{ \Carbon\Carbon::parse($dateKey)->format('D, d M Y') }}</strong>
                                    </td>
                                    <td>
                                        @foreach($classes as $class)
                                            <div class="class-block">
                                                <div>
                                                    <strong>{{ $class['class_name'] }}</strong> - {{ $class['subject'] ?? 'N/A' }}
                                                </div>
                                                <div class="small text-muted">
                                                    {{ \Carbon\Carbon::parse($class['start_time'])->format('h:i A') }} - 
                                                    {{ \Carbon\Carbon::parse($class['end_time'])->format('h:i A') }}
                                                    | Teacher: {{ $class['teacher'] ?? 'N/A' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-center text-muted py-5">No classes scheduled for this month</p>
            @endif
        @endif

        <!-- Footer -->
        <div class="mt-4 pt-3 border-top text-center text-muted small">
            <p class="mb-0">Arena Matriks Edu Group - Tuition Management System</p>
            <p class="mb-0">Generated on {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
