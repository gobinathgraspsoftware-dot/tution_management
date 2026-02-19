{{-- Exam Info Card (used in show page) --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Exam Information</h6>
        @php
            $statusColors = [
                'scheduled' => 'warning',
                'ongoing' => 'primary',
                'completed' => 'success',
                'cancelled' => 'danger',
            ];
            $statusIcons = [
                'scheduled' => 'calendar-check',
                'ongoing' => 'spinner fa-spin',
                'completed' => 'check-circle',
                'cancelled' => 'times-circle',
            ];
        @endphp
        <span class="badge bg-{{ $statusColors[$exam->status] ?? 'secondary' }} fs-6">
            <i class="fas fa-{{ $statusIcons[$exam->status] ?? 'circle' }} me-1"></i>
            {{ ucfirst($exam->status) }}
        </span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted fw-semibold" style="width: 40%;">Exam Name</td>
                        <td>{{ $exam->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Class</td>
                        <td>{{ $exam->class->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Subject</td>
                        <td>{{ $exam->subject->name ?? ($exam->class->subject->name ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Exam Date</td>
                        <td>
                            <i class="fas fa-calendar me-1 text-primary"></i>
                            {{ $exam->exam_date ? $exam->exam_date->format('d M Y (l)') : 'N/A' }}
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted fw-semibold" style="width: 40%;">Start Time</td>
                        <td>
                            <i class="fas fa-clock me-1 text-info"></i>
                            {{ $exam->start_time ? \Carbon\Carbon::parse($exam->start_time)->format('h:i A') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Duration</td>
                        <td>{{ $exam->duration_minutes ?? 0 }} minutes</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Max Marks</td>
                        <td><span class="badge bg-primary">{{ number_format($exam->max_marks, 0) }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Passing Marks</td>
                        <td><span class="badge bg-warning text-dark">{{ number_format($exam->passing_marks, 0) }}</span></td>
                    </tr>
                </table>
            </div>
        </div>
        @if($exam->description)
            <hr>
            <div>
                <strong class="text-muted">Description:</strong>
                <p class="mb-0 mt-1">{{ $exam->description }}</p>
            </div>
        @endif
    </div>
</div>
