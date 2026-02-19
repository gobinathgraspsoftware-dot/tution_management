<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Result Card - {{ $result->student->user->name ?? 'Student' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #fda530;
            padding-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            color: #4c4c4c;
        }
        .header h4 {
            margin: 5px 0 0;
            color: #888;
            font-weight: normal;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px 10px;
            vertical-align: top;
        }
        .info-table .label {
            font-weight: 600;
            color: #666;
            width: 35%;
        }
        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .result-table th, .result-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .result-table th {
            background-color: #f5f5f5;
            font-weight: 600;
        }
        .grade-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
        }
        .grade-pass { background-color: #28a745; }
        .grade-fail { background-color: #dc3545; }
        .remarks { margin: 15px 0; }
        .signatures {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .signature-line {
            text-align: center;
            width: 30%;
            display: inline-block;
        }
        .signature-line .line {
            border-top: 1px solid #333;
            margin-bottom: 5px;
            padding-top: 5px;
        }
        @media print {
            body { margin: 20px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Arena Matriks Edu Group</h2>
        <h4>Exam Result Card</h4>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <table>
                    <tr><td class="label">Student Name:</td><td>{{ $result->student->user->name ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Student ID:</td><td>{{ $result->student->student_id ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Parent:</td><td>{{ $result->student->parent->user->name ?? 'N/A' }}</td></tr>
                </table>
            </td>
            <td>
                <table>
                    <tr><td class="label">Exam:</td><td>{{ $result->exam->name }}</td></tr>
                    <tr><td class="label">Class:</td><td>{{ $result->exam->class->name ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Subject:</td><td>{{ $result->exam->subject->name ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Date:</td><td>{{ $result->exam->exam_date ? $result->exam->exam_date->format('d M Y') : 'N/A' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="result-table">
        <thead>
            <tr>
                <th>Maximum Marks</th>
                <th>Passing Marks</th>
                <th>Marks Obtained</th>
                <th>Percentage</th>
                <th>Grade</th>
                <th>Rank</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($result->exam->max_marks, 0) }}</td>
                <td>{{ number_format($result->exam->passing_marks, 0) }}</td>
                <td style="font-weight: bold; font-size: 18px;">{{ number_format($result->marks_obtained, 0) }}</td>
                <td>{{ number_format($result->percentage, 1) }}%</td>
                <td><strong>{{ $result->grade }}</strong></td>
                <td>{{ $result->rank ? '#' . $result->rank : 'N/A' }}</td>
                <td>
                    @if($result->marks_obtained >= $result->exam->passing_marks)
                        <span class="grade-badge grade-pass">PASS</span>
                    @else
                        <span class="grade-badge grade-fail">FAIL</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    @if($result->remarks)
        <div class="remarks">
            <strong>Remarks:</strong> {{ $result->remarks }}
        </div>
    @endif

    <div style="margin-top: 80px;">
        <table style="width: 100%;">
            <tr>
                <td style="text-align: center; width: 33%;">
                    <div style="border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 5px;">
                        <small>Teacher's Signature</small>
                    </div>
                </td>
                <td style="text-align: center; width: 33%;">
                    <div style="border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 5px;">
                        <small>Date</small>
                    </div>
                </td>
                <td style="text-align: center; width: 33%;">
                    <div style="border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 5px;">
                        <small>Principal's Signature</small>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
