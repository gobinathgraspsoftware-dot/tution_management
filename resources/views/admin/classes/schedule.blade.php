@extends('layouts.app')

@section('title', 'Manage Class Schedule')
@section('page-title', 'Manage Class Schedule')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">{{ $class->name }}</h4>
            <small class="text-muted">
                {{ $class->subject->name }} | {{ $class->teacher->user->name ?? 'No Teacher' }}
            </small>
        </div>
        <div>
            <a href="{{ route('admin.classes.show', $class) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Class
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Add New Schedule -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Add New Schedule</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.classes.schedule.store', $class) }}" method="POST" id="scheduleForm">
                        @csrf

                        <!-- Day of Week -->
                        <div class="mb-3">
                            <label for="day_of_week" class="form-label">Day of Week <span class="text-danger">*</span></label>
                            <select class="form-select @error('day_of_week') is-invalid @enderror"
                                    id="day_of_week" name="day_of_week" required>
                                <option value="">Select Day</option>
                                <option value="monday" {{ old('day_of_week') == 'monday' ? 'selected' : '' }}>Monday</option>
                                <option value="tuesday" {{ old('day_of_week') == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                                <option value="wednesday" {{ old('day_of_week') == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                                <option value="thursday" {{ old('day_of_week') == 'thursday' ? 'selected' : '' }}>Thursday</option>
                                <option value="friday" {{ old('day_of_week') == 'friday' ? 'selected' : '' }}>Friday</option>
                                <option value="saturday" {{ old('day_of_week') == 'saturday' ? 'selected' : '' }}>Saturday</option>
                                <option value="sunday" {{ old('day_of_week') == 'sunday' ? 'selected' : '' }}>Sunday</option>
                            </select>
                            @error('day_of_week')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Start Time -->
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                   id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- End Time -->
                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('end_time') is-invalid @enderror"
                                   id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Effective From -->
                        <div class="mb-3">
                            <label for="effective_from" class="form-label">Effective From</label>
                            <input type="date" class="form-control @error('effective_from') is-invalid @enderror"
                                   id="effective_from" name="effective_from" value="{{ old('effective_from') }}">
                            <small class="text-muted">Leave blank for immediate effect</small>
                            @error('effective_from')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Effective Until -->
                        <div class="mb-3">
                            <label for="effective_until" class="form-label">Effective Until</label>
                            <input type="date" class="form-control @error('effective_until') is-invalid @enderror"
                                   id="effective_until" name="effective_until" value="{{ old('effective_until') }}">
                            <small class="text-muted">Leave blank for indefinite</small>
                            @error('effective_until')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Is Active -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active Schedule
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Add Schedule
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Existing Schedules -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Current Schedules</h5>
                </div>
                <div class="card-body">
                    @if($class->schedules->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>Time</th>
                                        <th>Duration</th>
                                        <th>Effective Period</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($class->schedules as $schedule)
                                        <tr>
                                            <td><strong>{{ ucfirst($schedule->day_of_week) }}</strong></td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}<br>
                                                <small class="text-muted">to {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->diffInMinutes($schedule->end_time) }} min
                                            </td>
                                            <td>
                                                <small>
                                                    @if($schedule->effective_from)
                                                        From: {{ \Carbon\Carbon::parse($schedule->effective_from)->format('d M Y') }}<br>
                                                    @endif
                                                    @if($schedule->effective_until)
                                                        Until: {{ \Carbon\Carbon::parse($schedule->effective_until)->format('d M Y') }}
                                                    @else
                                                        Indefinite
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $schedule->is_active ? 'success' : 'secondary' }}">
                                                    {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-info"
                                                            onclick="editSchedule({{ $schedule->id }}, '{{ $schedule->day_of_week }}', '{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}', '{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}', '{{ $schedule->effective_from }}', '{{ $schedule->effective_until }}', {{ $schedule->is_active ? 'true' : 'false' }})"
                                                            title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('admin.classes.schedule.toggle-status', [$class, $schedule]) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-warning" title="Toggle Status">
                                                            <i class="fas fa-power-off"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-danger"
                                                            onclick="confirmDelete({{ $schedule->id }})"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <form id="delete-form-{{ $schedule->id }}"
                                                          action="{{ route('admin.classes.schedule.destroy', [$class, $schedule]) }}"
                                                          method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No schedules added yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editScheduleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_schedule_id" name="schedule_id">

                    <div class="mb-3">
                        <label for="edit_day_of_week" class="form-label">Day of Week</label>
                        <select class="form-select" id="edit_day_of_week" name="day_of_week" required>
                            <option value="monday">Monday</option>
                            <option value="tuesday">Tuesday</option>
                            <option value="wednesday">Wednesday</option>
                            <option value="thursday">Thursday</option>
                            <option value="friday">Friday</option>
                            <option value="saturday">Saturday</option>
                            <option value="sunday">Sunday</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_start_time" class="form-label">Start Time</label>
                        <input type="time" class="form-control" id="edit_start_time" name="start_time" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_end_time" class="form-label">End Time</label>
                        <input type="time" class="form-control" id="edit_end_time" name="end_time" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_effective_from" class="form-label">Effective From</label>
                        <input type="date" class="form-control" id="edit_effective_from" name="effective_from">
                    </div>

                    <div class="mb-3">
                        <label for="edit_effective_until" class="form-label">Effective Until</label>
                        <input type="date" class="form-control" id="edit_effective_until" name="effective_until">
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">Active Schedule</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editSchedule(id, day, startTime, endTime, effectiveFrom, effectiveUntil, isActive) {
    $('#edit_schedule_id').val(id);
    $('#edit_day_of_week').val(day);
    $('#edit_start_time').val(startTime);
    $('#edit_end_time').val(endTime);
    $('#edit_effective_from').val(effectiveFrom || '');
    $('#edit_effective_until').val(effectiveUntil || '');
    $('#edit_is_active').prop('checked', isActive);

    // Set form action
    $('#editScheduleForm').attr('action', "{{ route('admin.classes.schedule.update', [$class, '__SCHEDULE__']) }}".replace('__SCHEDULE__', id));

    // Show modal
    $('#editScheduleModal').modal('show');
}

function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this schedule?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush
