{{--
    Arrears Badge Component

    Usage:
    <x-arrears-badge :amount="$arrearsAmount" />
    <x-arrears-badge :amount="$amount" :days="$daysOverdue" />
    <x-arrears-badge :invoice="$invoice" />
    <x-arrears-badge :student="$student" />

    Props:
    - amount: decimal - The arrears amount (optional)
    - days: integer - Number of days overdue (optional)
    - invoice: Invoice model (alternative to amount/days)
    - student: Student model - calculates total arrears (alternative to invoice)
    - show-amount: boolean - Whether to show the amount (default: true)
    - show-days: boolean - Whether to show days overdue (default: true)
    - size: string - 'sm', 'md', 'lg' (default: 'md')
    - style: string - 'badge', 'pill', 'card' (default: 'badge')
    - icon: boolean - Whether to show icon (default: true)
--}}

@props([
    'amount' => null,
    'days' => null,
    'invoice' => null,
    'student' => null,
    'showAmount' => true,
    'showDays' => true,
    'size' => 'md',
    'style' => 'badge',
    'icon' => true,
    'status', 'days' => 0
])

@php
    // Calculate arrears data
    if ($student) {
        // Get total arrears for student
        $unpaidInvoices = $student->invoices()
                                  ->whereIn('status', ['pending', 'overdue', 'partial'])
                                  ->get();
        $amount = $unpaidInvoices->sum('balance');
        $oldestOverdue = $unpaidInvoices->where('status', 'overdue')
                                        ->sortBy('due_date')
                                        ->first();
        $days = $oldestOverdue ? \Carbon\Carbon::parse($oldestOverdue->due_date)->diffInDays(now()) : 0;
    } elseif ($invoice) {
        $amount = $invoice->balance ?? 0;
        $days = ($invoice->due_date && $invoice->due_date < now())
                ? \Carbon\Carbon::parse($invoice->due_date)->diffInDays(now())
                : 0;
    }

    $amount = $amount ?? 0;
    $days = $days ?? 0;

    // Determine severity level
    $severity = 'none';
    $severityColor = 'secondary';
    $severityIcon = 'fa-check-circle';
    $severityLabel = 'No Arrears';

    if ($amount > 0) {
        if ($days > 90) {
            $severity = 'critical';
            $severityColor = 'danger';
            $severityIcon = 'fa-exclamation-circle';
            $severityLabel = 'Critical';
        } elseif ($days > 60) {
            $severity = 'high';
            $severityColor = 'danger';
            $severityIcon = 'fa-exclamation-triangle';
            $severityLabel = 'High';
        } elseif ($days > 30) {
            $severity = 'medium';
            $severityColor = 'warning';
            $severityIcon = 'fa-clock';
            $severityLabel = 'Medium';
        } elseif ($days > 0) {
            $severity = 'low';
            $severityColor = 'warning';
            $severityIcon = 'fa-hourglass-half';
            $severityLabel = 'Overdue';
        } else {
            $severity = 'pending';
            $severityColor = 'info';
            $severityIcon = 'fa-info-circle';
            $severityLabel = 'Pending';
        }
    }

    // Size classes
    $sizeClasses = [
        'sm' => ['badge' => 'badge-sm', 'text' => 'small', 'padding' => 'px-2 py-1'],
        'md' => ['badge' => '', 'text' => '', 'padding' => 'px-3 py-2'],
        'lg' => ['badge' => 'badge-lg', 'text' => 'fs-6', 'padding' => 'px-4 py-2'],
    ];
    $currentSize = $sizeClasses[$size] ?? $sizeClasses['md'];

    $badgeClass = 'bg-secondary';
    $badgeText = 'Unknown';

    // Determine badge based on status and days overdue
    if ($status === 'paid') {
        $badgeClass = 'bg-success';
        $badgeText = 'Paid';
    } elseif ($status === 'cancelled') {
        $badgeClass = 'bg-dark';
        $badgeText = 'Cancelled';
    } elseif ($days > 90) {
        $badgeClass = 'bg-danger';
        $badgeText = 'Critical - ' . $days . ' days';
    } elseif ($days > 60) {
        $badgeClass = 'bg-danger';
        $badgeText = 'High Risk - ' . $days . ' days';
    } elseif ($days > 30) {
        $badgeClass = 'bg-warning text-dark';
        $badgeText = 'Medium Risk - ' . $days . ' days';
    } elseif ($days > 0) {
        $badgeClass = 'bg-info';
        $badgeText = 'Overdue - ' . $days . ' days';
    } elseif ($status === 'partial') {
        $badgeClass = 'bg-warning text-dark';
        $badgeText = 'Partial Payment';
    } elseif ($status === 'pending') {
        $badgeClass = 'bg-primary';
        $badgeText = 'Pending';
    } elseif ($status === 'overdue') {
        $badgeClass = 'bg-danger';
        $badgeText = 'Overdue';
    }
@endphp

@if($severity !== 'none')
    @if($style === 'badge')
        {{-- Badge Style --}}
        <span {{ $attributes->merge(['class' => "badge bg-{$severityColor} {$currentSize['badge']} {$currentSize['text']}"]) }}
              title="{{ $days }} days overdue - RM {{ number_format($amount, 2) }}">
            @if($icon)
                <i class="fas {{ $severityIcon }} me-1"></i>
            @endif
            @if($showAmount && $showDays && $days > 0)
                RM {{ number_format($amount, 2) }} ({{ $days }}d)
            @elseif($showAmount)
                RM {{ number_format($amount, 2) }}
            @elseif($showDays && $days > 0)
                {{ $days }} days
            @else
                {{ $severityLabel }}
            @endif
        </span>

    @elseif($style === 'pill')
        {{-- Pill Style --}}
        <span {{ $attributes->merge(['class' => "badge rounded-pill bg-{$severityColor} {$currentSize['badge']} {$currentSize['text']}"]) }}
              title="{{ $days }} days overdue - RM {{ number_format($amount, 2) }}">
            @if($icon)
                <i class="fas {{ $severityIcon }} me-1"></i>
            @endif
            @if($showAmount)
                RM {{ number_format($amount, 2) }}
            @endif
            @if($showDays && $days > 0)
                <span class="ms-1">({{ $days }}d)</span>
            @endif
        </span>

    @elseif($style === 'card')
        {{-- Card Style --}}
        <div {{ $attributes->merge(['class' => "arrears-badge-card border-start border-4 border-{$severityColor} bg-{$severityColor} bg-opacity-10 rounded {$currentSize['padding']}"]) }}>
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    @if($icon)
                        <div class="arrears-icon me-2 text-{{ $severityColor }}">
                            <i class="fas {{ $severityIcon }} {{ $size === 'lg' ? 'fa-2x' : ($size === 'sm' ? '' : 'fa-lg') }}"></i>
                        </div>
                    @endif
                    <div>
                        <div class="fw-bold text-{{ $severityColor }} {{ $currentSize['text'] }}">
                            {{ $severityLabel }} Arrears
                        </div>
                        @if($showDays && $days > 0)
                            <small class="text-muted">{{ $days }} days overdue</small>
                        @endif
                    </div>
                </div>
                @if($showAmount)
                    <div class="text-end">
                        <div class="fw-bold text-{{ $severityColor }} {{ $currentSize['text'] }}">
                            RM {{ number_format($amount, 2) }}
                        </div>
                        @if($student && isset($unpaidInvoices))
                            <small class="text-muted">{{ $unpaidInvoices->count() }} invoice(s)</small>
                        @endif
                    </div>
                @endif
            </div>
        </div>

    @elseif($style === 'inline')
        {{-- Inline Style --}}
        <span {{ $attributes->merge(['class' => "text-{$severityColor} {$currentSize['text']}"]) }}
              title="{{ $days }} days overdue">
            @if($icon)
                <i class="fas {{ $severityIcon }} me-1"></i>
            @endif
            @if($showAmount)
                <strong>RM {{ number_format($amount, 2) }}</strong>
            @endif
            @if($showDays && $days > 0)
                <span class="text-muted">({{ $days }}d overdue)</span>
            @endif
        </span>

    @elseif($style === 'indicator')
        {{-- Simple Indicator Dot --}}
        <span {{ $attributes->merge(['class' => "arrears-indicator"]) }}
              title="{{ $severityLabel }}: RM {{ number_format($amount, 2) }} ({{ $days }} days overdue)">
            <span class="indicator-dot bg-{{ $severityColor }}"
                  style="display: inline-block; width: {{ $size === 'sm' ? '8px' : ($size === 'lg' ? '16px' : '12px') }};
                         height: {{ $size === 'sm' ? '8px' : ($size === 'lg' ? '16px' : '12px') }};
                         border-radius: 50%;
                         {{ $severity === 'critical' ? 'animation: pulse-danger 1.5s infinite;' : '' }}">
            </span>
        </span>
    @endif

@else
    {{-- No Arrears State --}}
    @if($style === 'badge')
        <span {{ $attributes->merge(['class' => "badge bg-success {$currentSize['badge']} {$currentSize['text']}"]) }}>
            @if($icon)
                <i class="fas fa-check-circle me-1"></i>
            @endif
            No Arrears
        </span>
    @elseif($style === 'pill')
        <span {{ $attributes->merge(['class' => "badge rounded-pill bg-success {$currentSize['badge']} {$currentSize['text']}"]) }}>
            @if($icon)
                <i class="fas fa-check-circle me-1"></i>
            @endif
            Clear
        </span>
    @elseif($style === 'card')
        <div {{ $attributes->merge(['class' => "arrears-badge-card border-start border-4 border-success bg-success bg-opacity-10 rounded {$currentSize['padding']}"]) }}>
            <div class="d-flex align-items-center">
                @if($icon)
                    <div class="arrears-icon me-2 text-success">
                        <i class="fas fa-check-circle {{ $size === 'lg' ? 'fa-2x' : ($size === 'sm' ? '' : 'fa-lg') }}"></i>
                    </div>
                @endif
                <div>
                    <div class="fw-bold text-success {{ $currentSize['text'] }}">No Arrears</div>
                    <small class="text-muted">All payments up to date</small>
                </div>
            </div>
        </div>
    @elseif($style === 'indicator')
        <span {{ $attributes->merge(['class' => "arrears-indicator"]) }} title="No Arrears">
            <span class="indicator-dot bg-success"
                  style="display: inline-block; width: {{ $size === 'sm' ? '8px' : ($size === 'lg' ? '16px' : '12px') }};
                         height: {{ $size === 'sm' ? '8px' : ($size === 'lg' ? '16px' : '12px') }};
                         border-radius: 50%;">
            </span>
        </span>
    @endif
@endif

<style>
    .badge-sm {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }

    .badge-lg {
        font-size: 0.9rem;
        padding: 0.5rem 0.8rem;
    }

    .arrears-badge-card {
        transition: all 0.2s ease;
    }

    .arrears-badge-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    @keyframes pulse-danger {
        0% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }
        70% {
            box-shadow: 0 0 0 6px rgba(220, 53, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }

    .arrears-indicator .indicator-dot {
        vertical-align: middle;
    }
</style>
