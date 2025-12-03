@extends('layouts.app')

@section('title', 'Add Physical Material')
@section('page-title', 'Add Physical Material')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-plus me-2"></i> Add New Physical Material</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.physical-materials.index') }}">Physical Materials</a></li>
            <li class="breadcrumb-item active">Add New</li>
        </ol>
    </nav>
</div>

<form action="{{ route('admin.physical-materials.store') }}" method="POST">
    @csrf

    <div class="row">
        <!-- Material Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-box me-2"></i> Material Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Material Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g., BM Module 1 & 2" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="4" placeholder="Describe the physical material...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Grade Level</label>
                            <input type="text" name="grade_level" class="form-control @error('grade_level') is-invalid @enderror"
                                   value="{{ old('grade_level') }}" placeholder="e.g., Form 1, Form 2">
                            @error('grade_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Month</label>
                            <select name="month" class="form-select @error('month') is-invalid @enderror">
                                <option value="">Select Month</option>
                                @foreach($months as $month)
                                    <option value="{{ $month }}" {{ old('month') == $month ? 'selected' : '' }}>
                                        {{ $month }}
                                    </option>
                                @endforeach
                            </select>
                            @error('month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control @error('year') is-invalid @enderror"
                                   value="{{ old('year', date('Y')) }}" min="2020" max="2030">
                            @error('year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3">Quantity Management</h6>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Total Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_total" id="quantityTotal"
                                   class="form-control @error('quantity_total') is-invalid @enderror"
                                   value="{{ old('quantity_total', 0) }}" min="0" required>
                            @error('quantity_total')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Available Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_available" id="quantityAvailable"
                                   class="form-control @error('quantity_available') is-invalid @enderror"
                                   value="{{ old('quantity_available', 0) }}" min="0" required>
                            @error('quantity_available')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Minimum Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="minimum_quantity" class="form-control @error('minimum_quantity') is-invalid @enderror"
                                   value="{{ old('minimum_quantity', 10) }}" min="0" required>
                            <small class="form-text text-muted">Alert when stock reaches this level</small>
                            @error('minimum_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings & Actions -->
        <div class="col-md-4">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-cog me-2"></i> Settings
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="low_stock" {{ old('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out_of_stock" {{ old('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Status will auto-update based on available quantity.
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-1"></i> Create Material
                    </button>
                    <a href="{{ route('admin.physical-materials.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <i class="fas fa-question-circle me-2"></i> Help
                </div>
                <div class="card-body">
                    <p class="small mb-2"><strong>Physical Materials:</strong></p>
                    <ul class="small mb-0">
                        <li>Used for monthly modules (e.g., BM Module 1 & 2)</li>
                        <li>Tracks physical material collection by students</li>
                        <li>Monitors stock levels and availability</li>
                        <li>Sends notifications to parents when ready</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Auto-fill available quantity when total quantity changes
document.getElementById('quantityTotal').addEventListener('input', function() {
    const availableInput = document.getElementById('quantityAvailable');
    if (availableInput.value == 0 || confirm('Update available quantity to match total quantity?')) {
        availableInput.value = this.value;
    }
});
</script>
@endpush
