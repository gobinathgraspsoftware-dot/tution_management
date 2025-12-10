<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arrears Report - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }
        
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header Styles */
        .report-header {
            text-align: center;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: 600;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .report-date {
            font-size: 11px;
            color: #95a5a6;
        }
        
        /* Summary Section */
        .summary-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .summary-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        
        .summary-item {
            text-align: center;
            padding: 10px;
            background: #fff;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        
        .summary-label {
            font-size: 10px;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .summary-value.danger {
            color: #dc3545;
        }
        
        .summary-value.warning {
            color: #ffc107;
        }
        
        .summary-value.success {
            color: #28a745;
        }
        
        /* Age Analysis */
        .age-analysis {
            margin-bottom: 20px;
        }
        
        .age-analysis-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .age-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        
        .age-item {
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        
        .age-item.current {
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }
        
        .age-item.warning {
            background: #fff3cd;
            border: 1px solid #ffeeba;
        }
        
        .age-item.danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        
        .age-item.critical {
            background: #e74c3c;
            border: 1px solid #c0392b;
            color: #fff;
        }
        
        .age-label {
            font-size: 10px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .age-value {
            font-size: 14px;
            font-weight: bold;
        }
        
        .age-count {
            font-size: 10px;
            margin-top: 3px;
        }
        
        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        .data-table th {
            background: #2c3e50;
            color: #fff;
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
        }
        
        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        .data-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .data-table tbody tr:hover {
            background: #e9ecef;
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-badge.overdue {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-badge.critical {
            background: #e74c3c;
            color: #fff;
        }
        
        /* Amount Styles */
        .amount {
            font-weight: 600;
            text-align: right;
        }
        
        .amount.overdue {
            color: #dc3545;
        }
        
        /* Days Overdue */
        .days-overdue {
            font-weight: 600;
        }
        
        .days-overdue.low {
            color: #28a745;
        }
        
        .days-overdue.medium {
            color: #ffc107;
        }
        
        .days-overdue.high {
            color: #fd7e14;
        }
        
        .days-overdue.critical {
            color: #dc3545;
        }
        
        /* Footer */
        .report-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }
        
        .signature-section {
            margin-top: 40px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 50px;
        }
        
        .signature-box {
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 8px;
        }
        
        .signature-title {
            font-size: 11px;
            font-weight: 600;
        }
        
        .signature-date {
            font-size: 10px;
            color: #6c757d;
            margin-top: 3px;
        }
        
        /* Print Specific */
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .print-container {
                padding: 0;
                max-width: 100%;
            }
            
            .no-print {
                display: none !important;
            }
            
            .data-table th {
                background: #2c3e50 !important;
                color: #fff !important;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
        
        /* Print Button */
        .print-actions {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .btn-print {
            display: inline-block;
            padding: 10px 25px;
            background: #2c3e50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            margin-right: 10px;
        }
        
        .btn-print:hover {
            background: #34495e;
        }
        
        .btn-back {
            display: inline-block;
            padding: 10px 25px;
            background: #6c757d;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="print-container">
        {{-- Print Actions --}}
        <div class="print-actions no-print">
            <button class="btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
            <a href="{{ route('admin.arrears.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Arrears
            </a>
        </div>
        
        {{-- Report Header --}}
        <div class="report-header">
            <div class="company-name">{{ config('app.name', 'Arena Matriks Edu Group') }}</div>
            <div class="report-title">Arrears Report</div>
            <div class="report-date">
                Generated on: {{ now()->format('d F Y, h:i A') }}
                @if(request('date_from') || request('date_to'))
                    <br>
                    Period: {{ request('date_from', 'All Time') }} to {{ request('date_to', 'Present') }}
                @endif
            </div>
        </div>
        
        {{-- Summary Statistics --}}
        <div class="summary-section">
            <div class="summary-title">Summary Overview</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Total Arrears</div>
                    <div class="summary-value danger">RM {{ number_format($dashboardStats['total_arrears'] ?? 0, 2) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Overdue Invoices</div>
                    <div class="summary-value warning">{{ number_format($dashboardStats['overdue_count'] ?? 0) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Students with Arrears</div>
                    <div class="summary-value">{{ number_format($dashboardStats['students_with_arrears'] ?? 0) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Critical (90+ Days)</div>
                    <div class="summary-value danger">{{ number_format($dashboardStats['critical_count'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        
        {{-- Arrears by Age --}}
        @if(isset($dashboardStats['by_age']) && count($dashboardStats['by_age']) > 0)
        <div class="age-analysis">
            <div class="age-analysis-title">Arrears Aging Analysis</div>
            <div class="age-grid">
                @foreach($dashboardStats['by_age'] as $age => $data)
                    @php
                        $ageClass = 'current';
                        if (str_contains($age, '31-60')) $ageClass = 'warning';
                        elseif (str_contains($age, '61-90')) $ageClass = 'danger';
                        elseif (str_contains($age, '90+')) $ageClass = 'critical';
                    @endphp
                    <div class="age-item {{ $ageClass }}">
                        <div class="age-label">{{ $age }} Days</div>
                        <div class="age-value">RM {{ number_format($data['amount'] ?? 0, 2) }}</div>
                        <div class="age-count">{{ $data['count'] ?? 0 }} invoice(s)</div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        
        {{-- Arrears Detail Table --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 15%;">Invoice No.</th>
                    <th style="width: 20%;">Student</th>
                    <th style="width: 15%;">Package/Class</th>
                    <th style="width: 10%;">Due Date</th>
                    <th style="width: 10%;">Days Overdue</th>
                    <th style="width: 12%;">Amount Due</th>
                    <th style="width: 8%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['data'] ?? [] as $index => $item)
                    @php
                        $daysOverdue = $item->days_overdue ?? 0;
                        $daysClass = 'low';
                        if ($daysOverdue > 30) $daysClass = 'medium';
                        if ($daysOverdue > 60) $daysClass = 'high';
                        if ($daysOverdue > 90) $daysClass = 'critical';
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $item->invoice_number ?? 'N/A' }}</strong></td>
                        <td>
                            {{ $item->student->user->name ?? 'N/A' }}
                            @if($item->student->student_code ?? false)
                                <br><small style="color: #6c757d;">{{ $item->student->student_code }}</small>
                            @endif
                        </td>
                        <td>
                            {{ $item->enrollment->package->name ?? 'N/A' }}
                            @if($item->enrollment->classModel ?? false)
                                <br><small style="color: #6c757d;">{{ $item->enrollment->classModel->name }}</small>
                            @endif
                        </td>
                        <td>{{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('d/m/Y') : 'N/A' }}</td>
                        <td>
                            <span class="days-overdue {{ $daysClass }}">{{ $daysOverdue }} days</span>
                        </td>
                        <td class="amount overdue">RM {{ number_format($item->balance ?? 0, 2) }}</td>
                        <td>
                            @if($daysOverdue > 90)
                                <span class="status-badge critical">Critical</span>
                            @elseif($daysOverdue > 0)
                                <span class="status-badge overdue">Overdue</span>
                            @else
                                <span class="status-badge pending">Pending</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: #6c757d;">
                            No arrears records found for the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(isset($report['data']) && count($report['data']) > 0)
            <tfoot>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td colspan="6" style="text-align: right;">Total Arrears:</td>
                    <td class="amount overdue">RM {{ number_format($dashboardStats['total_arrears'] ?? 0, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
        
        {{-- Signature Section --}}
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-title">Prepared By</div>
                    <div class="signature-date">Date: _______________</div>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-title">Verified By</div>
                    <div class="signature-date">Date: _______________</div>
                </div>
            </div>
        </div>
        
        {{-- Report Footer --}}
        <div class="report-footer">
            <p>This is a system-generated report from {{ config('app.name') }}.</p>
            <p>Report generated by: {{ auth()->user()->name ?? 'System' }} | {{ now()->format('d/m/Y h:i A') }}</p>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads (optional - uncomment if needed)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
