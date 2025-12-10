{{-- 
    Installment Progress Component
    
    Usage:
    <x-installment-progress :invoice="$invoice" />
    <x-installment-progress :invoice="$invoice" :show-details="true" />
    <x-installment-progress :installments="$installments" :total="$totalAmount" />
    
    Props:
    - invoice: Invoice model with installments relationship
    - installments: Collection of installments (alternative to invoice)
    - total: Total amount (required if using installments collection)
    - show-details: boolean - Whether to show installment breakdown (default: false)
    - compact: boolean - Whether to show compact version (default: false)
    - size: string - 'sm', 'md', 'lg' (default: 'md')
--}}

@props([
    'invoice' => null,
    'installments' => null,
    'total' => null,
    'showDetails' => false,
    'compact' => false,
    'size' => 'md'
])

@php
    // Get installments data
    if ($invoice) {
        $installments = $invoice->installments ?? collect();
        $total = $invoice->total_amount ?? 0;
    }
    
    $installments = $installments ?? collect();
    $total = $total ?? 0;
    
    // Calculate progress
    $totalInstallments = $installments->count();
    $paidInstallments = $installments->where('status', 'paid')->count();
    $partialInstallments = $installments->where('status', 'partial')->count();
    $overdueInstallments = $installments->where('status', 'overdue')->count();
    $pendingInstallments = $installments->whereIn('status', ['pending', 'scheduled'])->count();
    
    // Calculate amounts
    $totalPaid = $installments->sum('paid_amount');
    $totalDue = $installments->sum('amount');
    $remainingAmount = $totalDue - $totalPaid;
    
    // Calculate percentages
    $paidPercentage = $totalDue > 0 ? round(($totalPaid / $totalDue) * 100, 1) : 0;
    $progressPercentage = $totalInstallments > 0 ? round(($paidInstallments / $totalInstallments) * 100, 1) : 0;
    
    // Get next due installment
    $nextDue = $installments->whereIn('status', ['pending', 'scheduled', 'partial', 'overdue'])
                           ->sortBy('due_date')
                           ->first();
    
    // Determine overall status
    $overallStatus = 'pending';
    if ($paidInstallments === $totalInstallments && $totalInstallments > 0) {
        $overallStatus = 'completed';
    } elseif ($overdueInstallments > 0) {
        $overallStatus = 'overdue';
    } elseif ($paidInstallments > 0 || $partialInstallments > 0) {
        $overallStatus = 'in_progress';
    }
    
    // Size classes
    $sizeClasses = [
        'sm' => ['bar' => 'progress-sm', 'text' => 'small', 'spacing' => 'py-1'],
        'md' => ['bar' => '', 'text' => '', 'spacing' => 'py-2'],
        'lg' => ['bar' => 'progress-lg', 'text' => 'fs-6', 'spacing' => 'py-3'],
    ];
    $currentSize = $sizeClasses[$size] ?? $sizeClasses['md'];
    
    // Status colors
    $statusColors = [
        'completed' => 'success',
        'in_progress' => 'primary',
        'overdue' => 'danger',
        'pending' => 'secondary',
    ];
    $statusColor = $statusColors[$overallStatus] ?? 'secondary';
@endphp

<div class="installment-progress-component {{ $compact ? 'compact' : '' }}" 
     {{ $attributes->merge(['class' => $currentSize['spacing']]) }}>
    
    @if($totalInstallments > 0)
        {{-- Compact Version --}}
        @if($compact)
            <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1 {{ $currentSize['bar'] }}" style="height: 8px;">
                    <div class="progress-bar bg-{{ $statusColor }}" 
                         role="progressbar" 
                         style="width: {{ $paidPercentage }}%"
                         aria-valuenow="{{ $paidPercentage }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
                <span class="badge bg-{{ $statusColor }} {{ $currentSize['text'] }}">
                    {{ $paidInstallments }}/{{ $totalInstallments }}
                </span>
            </div>
        @else
            {{-- Full Version --}}
            <div class="installment-progress-wrapper">
                {{-- Header Stats --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="{{ $currentSize['text'] }} fw-semibold">Installment Progress</span>
                        @if($overallStatus === 'completed')
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Completed</span>
                        @elseif($overallStatus === 'overdue')
                            <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Overdue</span>
                        @elseif($overallStatus === 'in_progress')
                            <span class="badge bg-primary"><i class="fas fa-clock me-1"></i>In Progress</span>
                        @else
                            <span class="badge bg-secondary"><i class="fas fa-hourglass-start me-1"></i>Pending</span>
                        @endif
                    </div>
                    <span class="{{ $currentSize['text'] }} text-muted">
                        {{ $paidInstallments }} of {{ $totalInstallments }} paid
                    </span>
                </div>
                
                {{-- Progress Bar --}}
                <div class="progress mb-2 {{ $currentSize['bar'] }}" style="height: 12px;">
                    {{-- Paid portion --}}
                    <div class="progress-bar bg-success" 
                         role="progressbar" 
                         style="width: {{ ($paidInstallments / $totalInstallments) * 100 }}%"
                         title="Paid: {{ $paidInstallments }}">
                    </div>
                    {{-- Partial portion --}}
                    @if($partialInstallments > 0)
                        <div class="progress-bar bg-warning" 
                             role="progressbar" 
                             style="width: {{ ($partialInstallments / $totalInstallments) * 100 }}%"
                             title="Partial: {{ $partialInstallments }}">
                        </div>
                    @endif
                    {{-- Overdue portion --}}
                    @if($overdueInstallments > 0)
                        <div class="progress-bar bg-danger" 
                             role="progressbar" 
                             style="width: {{ ($overdueInstallments / $totalInstallments) * 100 }}%"
                             title="Overdue: {{ $overdueInstallments }}">
                        </div>
                    @endif
                </div>
                
                {{-- Amount Summary --}}
                <div class="d-flex justify-content-between {{ $currentSize['text'] }}">
                    <span class="text-success">
                        <i class="fas fa-check-circle me-1"></i>
                        Paid: RM {{ number_format($totalPaid, 2) }}
                    </span>
                    <span class="text-{{ $remainingAmount > 0 ? 'danger' : 'muted' }}">
                        <i class="fas fa-{{ $remainingAmount > 0 ? 'exclamation-circle' : 'check-circle' }} me-1"></i>
                        Remaining: RM {{ number_format($remainingAmount, 2) }}
                    </span>
                </div>
                
                {{-- Next Due Info --}}
                @if($nextDue && $overallStatus !== 'completed')
                    <div class="mt-2 p-2 bg-light rounded {{ $currentSize['text'] }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-calendar-alt text-primary me-1"></i>
                                Next: Installment #{{ $nextDue->installment_number }}
                            </span>
                            <span class="fw-semibold">
                                RM {{ number_format($nextDue->amount - $nextDue->paid_amount, 2) }}
                            </span>
                        </div>
                        <small class="text-muted">
                            Due: {{ \Carbon\Carbon::parse($nextDue->due_date)->format('d M Y') }}
                            @if($nextDue->status === 'overdue')
                                <span class="text-danger ms-2">
                                    ({{ \Carbon\Carbon::parse($nextDue->due_date)->diffForHumans() }})
                                </span>
                            @endif
                        </small>
                    </div>
                @endif
            </div>
        @endif
        
        {{-- Detailed Breakdown --}}
        @if($showDetails && !$compact)
            <div class="installment-details mt-3">
                <div class="accordion" id="installmentAccordion{{ $invoice?->id ?? rand() }}">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 {{ $currentSize['text'] }}" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#installmentList{{ $invoice?->id ?? rand() }}">
                                <i class="fas fa-list me-2"></i>View All Installments
                            </button>
                        </h2>
                        <div id="installmentList{{ $invoice?->id ?? rand() }}" 
                             class="accordion-collapse collapse">
                            <div class="accordion-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0 {{ $currentSize['text'] }}">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Due Date</th>
                                                <th class="text-end">Amount</th>
                                                <th class="text-end">Paid</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($installments->sortBy('installment_number') as $installment)
                                                @php
                                                    $instStatusClass = match($installment->status) {
                                                        'paid' => 'success',
                                                        'partial' => 'warning',
                                                        'overdue' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <tr class="{{ $installment->status === 'overdue' ? 'table-danger' : '' }}">
                                                    <td>{{ $installment->installment_number }}</td>
                                                    <td>
                                                        {{ \Carbon\Carbon::parse($installment->due_date)->format('d/m/Y') }}
                                                    </td>
                                                    <td class="text-end">
                                                        RM {{ number_format($installment->amount, 2) }}
                                                    </td>
                                                    <td class="text-end">
                                                        RM {{ number_format($installment->paid_amount, 2) }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $instStatusClass }}">
                                                            {{ ucfirst($installment->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr class="fw-bold">
                                                <td colspan="2">Total</td>
                                                <td class="text-end">RM {{ number_format($totalDue, 2) }}</td>
                                                <td class="text-end">RM {{ number_format($totalPaid, 2) }}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        {{-- No Installments --}}
        <div class="text-center text-muted py-2 {{ $currentSize['text'] }}">
            <i class="fas fa-info-circle me-1"></i>
            No installment plan
        </div>
    @endif
</div>

<style>
    .installment-progress-component .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .installment-progress-component .progress-bar {
        transition: width 0.6s ease;
    }
    
    .installment-progress-component.compact .progress {
        height: 6px;
    }
    
    .installment-progress-component .accordion-button {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    
    .installment-progress-component .accordion-button:not(.collapsed) {
        background: #e9ecef;
        box-shadow: none;
    }
    
    .installment-progress-component .accordion-button::after {
        width: 1rem;
        height: 1rem;
        background-size: 1rem;
    }
    
    .progress-sm {
        height: 6px !important;
    }
    
    .progress-lg {
        height: 20px !important;
    }
</style>
