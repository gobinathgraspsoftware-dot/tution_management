@extends('layouts.app')

@section('title', 'Material Collections')
@section('page-title', 'Material Collections')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-clipboard-check me-2"></i> Material Collections</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.physical-materials.index') }}">Physical Materials</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.physical-materials.show', $physicalMaterial) }}">{{ $physicalMaterial->name }}</a></li>
            <li class="breadcrumb-item active">Collections</li>
        </ol>
    </nav>
</div>

<div class="row">
    <!-- Record New Collection -->
    <div class="col-md-4">
        <div class="card mb-4 sticky-top" style="top: 20px;">
            <div class="card-header bg-success text-white">
                <i class="fas fa-plus me-2"></i> Record Collection
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.physical-materials.record-collection', $physicalMaterial) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Student <span class="text-danger">*</span></label>
                        <select name="student_id" id="studentSelect" class="form-select @error('student_id') is-invalid @enderror" required>
                            <option value="">Select Student</option>
                            @foreach($pendingStudents as $student)
                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->user->name }} ({{ $student->student_id }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($pendingStudents->count() == 0)
                        <small class="text-muted">All students have collected this material.</small>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Collected By Name <span class="text-danger">*</span></label>
                        <input type="text" name="collected_by_name" class="form-control @error('collected_by_name') is-invalid @enderror"
                               value="{{ old('collected_by_name') }}" placeholder="Parent/Guardian name" required>
                        @error('collected_by_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="3" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success w-100" {{ $pendingStudents->count() == 0 ? 'disabled' : '' }}>
                        <i class="fas fa-check me-1"></i> Record Collection
                    </button>
                </form>
            </div>
        </div>

        <!-- Material Info -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-box me-2"></i> Material Info
            </div>
            <div class="card-body">
                <h6 class="mb-2">{{ $physicalMaterial->name }}</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subject:</span>
                    <strong>{{ $physicalMaterial->subject->name ?? 'N/A' }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Available:</span>
                    <strong class="text-success">{{ $physicalMaterial->quantity_available }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Collected:</span>
                    <strong class="text-primary">{{ $collections->total() }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Collections List -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i> Collection Records</span>
                <span class="badge bg-primary">{{ $collections->total() }} Total</span>
            </div>
            <div class="card-body">
                @if($collections->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Collected By</th>
                                <th>Date & Time</th>
                                <th>Staff</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($collections as $collection)
                            <tr>
                                <td>{{ $loop->iteration + $collections->firstItem() - 1 }}</td>
                                <td>
                                    <strong>{{ $collection->student->user->name }}</strong>
                                    <br><small class="text-muted">{{ $collection->student->student_id }}</small>
                                </td>
                                <td>{{ $collection->collected_by_name }}</td>
                                <td>
                                    {{ $collection->collected_at->format('d M Y') }}
                                    <br><small class="text-muted">{{ $collection->collected_at->format('h:i A') }}</small>
                                </td>
                                <td>{{ $collection->staff->user->name ?? 'System' }}</td>
                                <td>
                                    @if($collection->notes)
                                    <button type="button" class="btn btn-sm btn-outline-info"
                                            data-bs-toggle="tooltip" title="{{ $collection->notes }}">
                                        <i class="fas fa-sticky-note"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $collections->firstItem() ?? 0 }} to {{ $collections->lastItem() ?? 0 }} of {{ $collections->total() }} entries
                    </div>
                    {{ $collections->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No collections recorded yet.</p>
                    <p class="small text-muted">Use the form on the left to record the first collection.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add search functionality for student select
    const studentSelect = document.getElementById('studentSelect');
    if (studentSelect && studentSelect.options.length > 20) {
        // Could integrate Select2 or similar for better UX with many students
        console.log('Consider adding Select2 for better student selection');
    }
});
</script>
@endpush
