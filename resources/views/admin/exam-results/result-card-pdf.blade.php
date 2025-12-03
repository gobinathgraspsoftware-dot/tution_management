<!DOCTYPE html>
<html>
<head>
    <title>Result Card - {{ $result->student->user->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #333; }
        .header h2 { margin: 5px 0 0 0; color: #666; }
        hr { border: none; border-top: 2px solid #333; margin: 20px 0; }
        table { width: 100%; margin-bottom: 20px; }
        table th { text-align: left; width: 35%; padding: 5px 0; }
        table td { padding: 5px 0; }
        .results-box { background: #f5f5f5; padding: 20px; margin: 20px 0; text-align: center; }
        .results-box .item { display: inline-block; width: 23%; margin: 0 1%; }
        .results-box h3 { margin: 5px 0; color: #333; }
        .badge { display: inline-block; padding: 15px 30px; font-size: 20px; font-weight: bold; margin: 20px 0; }
        .badge.pass { background: #10b981; color: white; }
        .badge.fail { background: #ef4444; color: white; }
        .footer { margin-top: 50px; padding-top: 20px; border-top: 1px solid #ccc; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ARENA MATRIKS EDU GROUP</h1>
        <h2>EXAMINATION RESULT CARD</h2>
        <hr>
    </div>

    <table>
        <tr>
            <th>Student Name:</th>
            <td><strong>{{ $result->student->user->name }}</strong></td>
            <th>Exam Name:</th>
            <td>{{ $result->exam->name }}</td>
        </tr>
        <tr>
            <th>Student ID:</th>
            <td>{{ $result->student->student_id }}</td>
            <th>Subject:</th>
            <td>{{ $result->exam->subject->name }}</td>
        </tr>
        <tr>
            <th>Class:</th>
            <td>{{ $result->exam->class->name }}</td>
            <th>Exam Date:</th>
            <td>{{ \Carbon\Carbon::parse($result->exam->exam_date)->format('F j, Y') }}</td>
        </tr>
    </table>

    <div class="results-box">
        <div class="item">
            <p>Marks Obtained</p>
            <h3>{{ $result->marks_obtained }}/{{ $result->exam->max_marks }}</h3>
        </div>
        <div class="item">
            <p>Percentage</p>
            <h3>{{ number_format($result->percentage, 2) }}%</h3>
        </div>
        <div class="item">
            <p>Grade</p>
            <h3>{{ $result->grade }}</h3>
        </div>
        <div class="item">
            <p>Rank</p>
            <h3>{{ $result->rank }}</h3>
        </div>
    </div>

    <div style="text-align: center;">
        @if($result->marks_obtained >= $result->exam->passing_marks)
            <span class="badge pass">✓ PASSED</span>
        @else
            <span class="badge fail">✗ NEEDS IMPROVEMENT</span>
        @endif
    </div>

    @if($result->remarks)
        <div style="margin-top: 20px;">
            <strong>Remarks:</strong>
            <p>{{ $result->remarks }}</p>
        </div>
    @endif

    <div class="footer">
        <table>
            <tr>
                <td><strong>Published Date:</strong> {{ $result->published_at->format('F j, Y') }}</td>
                <td style="text-align: right;">
                    <strong>Signature:</strong> ___________________<br>
                    <small>Authorized Signatory</small>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
