{{--
    Status Badge Component

    Usage:
    @include('components.status-badge', ['status' => $user->status])
    @include('components.status-badge', ['status' => $student->approval_status, 'type' => 'approval'])
    @include('components.status-badge', ['status' => $teacher->employment_type, 'type' => 'employment'])
    @include('components.status-badge', ['status' => $payment->status, 'type' => 'payment'])
--}}

@props([
    'status' => 'unknown',
    'type' => 'default'
])

@php
    $badgeClass = 'bg-secondary';
    $icon = 'fas fa-circle';
    $label = ucfirst(str_replace('_', ' ', $status));

    switch($type) {
        case 'approval':
            switch($status) {
                case 'pending':
                    $badgeClass = 'bg-warning text-dark';
                    $icon = 'fas fa-clock';
                    break;
                case 'approved':
                    $badgeClass = 'bg-success';
                    $icon = 'fas fa-check-circle';
                    break;
                case 'rejected':
                    $badgeClass = 'bg-danger';
                    $icon = 'fas fa-times-circle';
                    break;
            }
            break;

        case 'employment':
            switch($status) {
                case 'full_time':
                    $badgeClass = 'bg-primary';
                    $icon = 'fas fa-briefcase';
                    $label = 'Full Time';
                    break;
                case 'part_time':
                    $badgeClass = 'bg-info';
                    $icon = 'fas fa-clock';
                    $label = 'Part Time';
                    break;
                case 'contract':
                    $badgeClass = 'bg-secondary';
                    $icon = 'fas fa-file-contract';
                    $label = 'Contract';
                    break;
            }
            break;

        case 'payment':
            switch($status) {
                case 'pending':
                    $badgeClass = 'bg-warning text-dark';
                    $icon = 'fas fa-clock';
                    break;
                case 'completed':
                case 'paid':
                    $badgeClass = 'bg-success';
                    $icon = 'fas fa-check-circle';
                    break;
                case 'failed':
                    $badgeClass = 'bg-danger';
                    $icon = 'fas fa-times-circle';
                    break;
                case 'partial':
                    $badgeClass = 'bg-info';
                    $icon = 'fas fa-adjust';
                    break;
                case 'refunded':
                    $badgeClass = 'bg-secondary';
                    $icon = 'fas fa-undo';
                    break;
                case 'overdue':
                    $badgeClass = 'bg-danger';
                    $icon = 'fas fa-exclamation-triangle';
                    break;
            }
            break;

        case 'registration':
            switch($status) {
                case 'online':
                    $badgeClass = 'bg-info';
                    $icon = 'fas fa-globe';
                    break;
                case 'offline':
                    $badgeClass = 'bg-secondary';
                    $icon = 'fas fa-building';
                    break;
            }
            break;

        case 'gender':
            switch($status) {
                case 'male':
                    $badgeClass = 'bg-primary';
                    $icon = 'fas fa-mars';
                    break;
                case 'female':
                    $badgeClass = 'bg-danger';
                    $icon = 'fas fa-venus';
                    break;
            }
            break;

        case 'relationship':
            switch($status) {
                case 'father':
                    $badgeClass = 'bg-primary';
                    $icon = 'fas fa-male';
                    break;
                case 'mother':
                    $badgeClass = 'bg-danger';
                    $icon = 'fas fa-female';
                    break;
                case 'guardian':
                    $badgeClass = 'bg-info';
                    $icon = 'fas fa-user-shield';
                    break;
            }
            break;

        case 'pay_type':
            switch($status) {
                case 'hourly':
                    $badgeClass = 'bg-info';
                    $icon = 'fas fa-clock';
                    break;
                case 'monthly':
                    $badgeClass = 'bg-primary';
                    $icon = 'fas fa-calendar-alt';
                    break;
                case 'per_class':
                    $badgeClass = 'bg-secondary';
                    $icon = 'fas fa-chalkboard';
                    $label = 'Per Class';
                    break;
            }
            break;

        default:
            switch($status) {
                case 'active':
                    $badgeClass = 'bg-success';
                    $icon = 'fas fa-check-circle';
                    break;
                case 'inactive':
                    $badgeClass = 'bg-danger';
                    $icon = 'fas fa-times-circle';
                    break;
                case 'on_leave':
                    $badgeClass = 'bg-warning text-dark';
                    $icon = 'fas fa-pause-circle';
                    $label = 'On Leave';
                    break;
                case 'suspended':
                    $badgeClass = 'bg-dark';
                    $icon = 'fas fa-ban';
                    break;
            }
            break;
    }
@endphp

<span class="badge {{ $badgeClass }}">
    <i class="{{ $icon }} me-1"></i>{{ $label }}
</span>
