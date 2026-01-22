@extends('layouts.app')

@section('title', 'Material Collections - ' . $physicalMaterial->name)
@section('page-title', 'Material Collections')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">
            <i class="fas fa-hands me-2"></i> {{ $physicalMaterial->name }}
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('staff.physical-materials.index') }}">Physical Materials</a></li>
                <li class="breadcrumb-item active">Collections</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('staff.physical-materials.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Materials
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <!-- Material Info -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Material Details
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Name:</th>
                        <td>{{ $physicalMaterial->name }}</td>
                    </tr>
                    <tr>
                        <th>Subject:</th>
                        <td>{{ $physicalMaterial->subject->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Grade Level:</th>
                        <td>{{ $physicalMaterial->grade_level ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Month/Year:</th>
                        <td>{{ $physicalMaterial->month }} {{ $physicalMaterial->year }}</td>
                    </tr>
                    <tr>
                        <th>Available:</th>
                        <td>
                            <span class="badge bg-{{ $physicalMaterial->quantity_available > 10 ? 'success' : ($physicalMaterial->quantity_available > 0 ? 'warning' : 'danger') }}">
                                {{ $physicalMaterial->quantity_available }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($physicalMaterial->status == 'available')
                                <span class="badge bg-success">Available</span>
                            @elseif($physicalMaterial->status == 'out_of_stock')
                                <span class="badge bg-danger">Out of Stock</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($physicalMaterial->status) }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Record Collection Form -->
        @if($physicalMaterial->status == 'available' && $physicalMaterial->quantity_available > 0)
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="fas fa-plus-circle me-2"></i> Record Collection
            </div>
            <div class="card-body">
                <form action="{{ route('staff.physical-materials.record-collection', $physicalMaterial) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student <span class="text-danger">*</span></label>
                        <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                            <option value="">Select Student...</option>
                            @foreach($pendingStudents as $student)
                                <option value="{{ $student->id }}">
                                    {{ $student->user->name }} ({{ $student->student_id }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($pendingStudents->isEmpty())
                            <small class="text-muted">All students have collected this material.</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="collected_by_name" class="form-label">Collected By <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="collected_by_name" 
                               id="collected_by_name" 
                               class="form-control @error('collected_by_name') is-invalid @enderror"
                               value="{{ old('collected_by_name', auth()->user()->name) }}"
                               placeholder="Person collecting the material"
                               required>
                        @error('collected_by_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" 
                                  id="notes" 
                                  class="form-control @error('notes') is-invalid @enderror"
                                  rows="2"
                                  placeholder="Optional notes...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-success w-100" {{ $pendingStudents->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-check me-2"></i> Record Collection
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            This material is currently out of stock.
        </div>
        @endif
    </div>

    <!-- Collections List -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i> Collection History</span>
                <span class="badge bg-primary">{{ $collections->total() }} collections</span>
            </div>
            <div class="card-body">
                @if($collections->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Collected By</th>
                                <th>Staff</th>
                                <th>Date</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($collections as $collection)
                            <tr>
                                <td>
                                    <strong>{{ $collection->student->user->name ?? 'N/A' }}</strong>
                                    <br><small class="text-muted">{{ $collection->student->student_id ?? '' }}</small>
                                </td>
                                <td>{{ $collection->collected_by_name }}</td>
                                <td>{{ $collection->staff->user->name ?? 'N/A' }}</td>
                                <td>
                                    {{ $collection->collected_at->format('d M Y') }}
                                    <br><small class="text-muted">{{ $collection->collected_at->format('h:i A') }}</small>
                                </td>
                                <td>{{ $collection->notes ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center mt-4">
                    {{ $collections->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No collections recorded yet.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for student dropdown if available
    if (typeof $.fn.select2 !== 'undefined') {
        $('#student_id').select2({
            placeholder: 'Search student...',
            allowClear: true,
            width: '100%'
        });
    }
});
</script>
@endpush
