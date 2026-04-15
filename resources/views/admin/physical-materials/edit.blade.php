@extends('layouts.app')

@section('title', 'Edit Physical Material')
@section('page-title', 'Edit Physical Material')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-edit me-2"></i> Edit Physical Material</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.physical-materials.index') }}">Physical Materials</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('admin.physical-materials.update', $physicalMaterial) }}" method="POST">
    @csrf
    @method('PUT')

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
                               value="{{ old('name', $physicalMaterial->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="4">{{ old('description', $physicalMaterial->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Cascading Dropdowns: Grade Level → Subject + Class -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select name="grade_level_id" id="gradeLevelSelect" class="form-select @error('grade_level_id') is-invalid @enderror" required>
                                <option value="">Select Grade Level</option>
                                @foreach($gradeLevels as $gradeLevel)
                                    <option value="{{ $gradeLevel->id }}"
                                            {{ old('grade_level_id', $physicalMaterial->grade_level_id) == $gradeLevel->id ? 'selected' : '' }}>
                                        {{ $gradeLevel->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grade_level_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select name="subject_id" id="subjectSelect" class="form-select @error('subject_id') is-invalid @enderror" required>
                                <option value="">Select Grade Level First</option>
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Class</label>
                            <select name="class_id" id="classSelect" class="form-select @error('class_id') is-invalid @enderror">
                                <option value="">Select Grade Level First</option>
                            </select>
                            @error('class_id')
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
                                    <option value="{{ $month }}"
                                            {{ old('month', $physicalMaterial->month) == $month ? 'selected' : '' }}>
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
                                   value="{{ old('year', $physicalMaterial->year) }}" min="2020" max="2030">
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
                            <input type="number" name="quantity_total"
                                   class="form-control @error('quantity_total') is-invalid @enderror"
                                   value="{{ old('quantity_total', $physicalMaterial->quantity_total) }}" min="0" required>
                            @error('quantity_total')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Available Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_available"
                                   class="form-control @error('quantity_available') is-invalid @enderror"
                                   value="{{ old('quantity_available', $physicalMaterial->quantity_available) }}" min="0" required>
                            @error('quantity_available')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Minimum Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="minimum_quantity"
                                   class="form-control @error('minimum_quantity') is-invalid @enderror"
                                   value="{{ old('minimum_quantity', $physicalMaterial->minimum_quantity) }}" min="0" required>
                            <small class="form-text text-muted">Alert when stock reaches this level</small>
                            @error('minimum_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> {{ $physicalMaterial->collections()->count() }} collections have been recorded for this material.
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
                            <option value="available" {{ old('status', $physicalMaterial->status) == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="low_stock" {{ old('status', $physicalMaterial->status) == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out_of_stock" {{ old('status', $physicalMaterial->status) == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card mb-4">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-1"></i> Update Material
                    </button>
                    <a href="{{ route('admin.physical-materials.show', $physicalMaterial) }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-2"></i> Statistics
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Total Collections:</small>
                        <p class="mb-0"><strong>{{ $physicalMaterial->collections()->count() }}</strong></p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Collected This Month:</small>
                        <p class="mb-0"><strong>{{ $physicalMaterial->collections()->whereMonth('collected_at', date('m'))->count() }}</strong></p>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted">Last Updated:</small>
                        <p class="mb-0">{{ $physicalMaterial->updated_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const gradeLevelSelect = document.getElementById('gradeLevelSelect');
    const subjectSelect    = document.getElementById('subjectSelect');
    const classSelect      = document.getElementById('classSelect');

    // Current saved values (for pre-selecting on edit load)
    const currentSubjectId = '{{ old('subject_id', $physicalMaterial->subject_id) }}';
    const currentClassId   = '{{ old('class_id', $physicalMaterial->class_id) }}';

    /**
     * Grade Level change → Load Subjects + Classes
     */
    gradeLevelSelect.addEventListener('change', function() {
        const gradeLevelId = this.value;

        subjectSelect.innerHTML = '<option value="">Loading...</option>';
        classSelect.innerHTML   = '<option value="">Loading...</option>';

        if (!gradeLevelId) {
            subjectSelect.innerHTML = '<option value="">Select Grade Level First</option>';
            classSelect.innerHTML   = '<option value="">Select Grade Level First</option>';
            return;
        }

        // Load Subjects
        fetch(`{{ route('admin.physical-materials.get-subjects-by-grade-level') }}?grade_level_id=${gradeLevelId}`)
            .then(res => res.json())
            .then(data => {
                subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                data.forEach(subject => {
                    const opt = document.createElement('option');
                    opt.value = subject.id;
                    opt.textContent = subject.name + (subject.code ? ' (' + subject.code + ')' : '');
                    if (currentSubjectId == subject.id) opt.selected = true;
                    subjectSelect.appendChild(opt);
                });
                // After subjects loaded, trigger class load with selected subject
                if (currentSubjectId) {
                    subjectSelect.dispatchEvent(new Event('change'));
                } else {
                    loadClasses(gradeLevelId, '');
                }
            })
            .catch(() => {
                subjectSelect.innerHTML = '<option value="">Failed to load subjects</option>';
            });
    });

    /**
     * Subject change → Reload Classes
     */
    subjectSelect.addEventListener('change', function() {
        loadClasses(gradeLevelSelect.value, this.value);
    });

    /**
     * Load classes via AJAX
     */
    function loadClasses(gradeLevelId, subjectId) {
        classSelect.innerHTML = '<option value="">Loading...</option>';

        if (!gradeLevelId) {
            classSelect.innerHTML = '<option value="">Select Grade Level First</option>';
            return;
        }

        let url = `{{ route('admin.physical-materials.get-classes-by-filters') }}?grade_level_id=${gradeLevelId}`;
        if (subjectId) url += `&subject_id=${subjectId}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                classSelect.innerHTML = '<option value="">Select Class (Optional)</option>';
                data.forEach(cls => {
                    const opt = document.createElement('option');
                    opt.value = cls.id;
                    opt.textContent = cls.name + ' — ' + cls.subject_name + ' (' + cls.teacher_name + ')';
                    if (currentClassId == cls.id) opt.selected = true;
                    classSelect.appendChild(opt);
                });
            })
            .catch(() => {
                classSelect.innerHTML = '<option value="">Failed to load classes</option>';
            });
    }

    // Trigger cascade on page load to pre-populate Subject + Class dropdowns
    if (gradeLevelSelect.value) {
        gradeLevelSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
