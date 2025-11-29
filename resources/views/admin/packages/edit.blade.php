@extends('layouts.app')

@section('title', 'Edit Package')
@section('page-title', 'Edit Package')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-edit me-2"></i> Edit Package</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.packages.index') }}">Packages</a></li>
                <li class="breadcrumb-item active">Edit {{ $package->name }}</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.packages.show', $package) }}" class="btn btn-outline-info me-2">
            <i class="fas fa-eye me-1"></i> View
        </a>
        <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>

<form action="{{ route('admin.packages.update', $package) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Left Column - Basic Info -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-box me-2"></i> Package Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Package Name -->
                        <div class="col-md-8 mb-3">
                            <label class="form-label" for="name">Package Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $package->name) }}"
                                   placeholder="e.g., SPM Science Package" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Package Code -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="code">Package Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                   id="code" name="code" value="{{ old('code', $package->code) }}"
                                   placeholder="e.g., SPM-SCI" required maxlength="20">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="col-12 mb-3">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="Describe what's included in this package...">{{ old('description', $package->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Package Type -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="type">Package Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="offline" {{ old('type', $package->type) == 'offline' ? 'selected' : '' }}>Offline (Physical)</option>
                                <option value="online" {{ old('type', $package->type) == 'online' ? 'selected' : '' }}>Online</option>
                                <option value="hybrid" {{ old('type', $package->type) == 'hybrid' ? 'selected' : '' }}>Hybrid (Both)</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Duration -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="duration_months">Duration <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('duration_months') is-invalid @enderror"
                                       id="duration_months" name="duration_months"
                                       value="{{ old('duration_months', $package->duration_months) }}" min="1" max="24" required>
                                <span class="input-group-text">month(s)</span>
                            </div>
                            @error('duration_months')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Max Students -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="max_students">Max Students</label>
                            <input type="number" class="form-control @error('max_students') is-invalid @enderror"
                                   id="max_students" name="max_students"
                                   value="{{ old('max_students', $package->max_students) }}" min="1" max="100"
                                   placeholder="e.g., 30">
                            @error('max_students')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subjects Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i> Subjects & Sessions</h5>
                </div>
                <div class="card-body">
                    @if($subjects->count() > 0)
                        <p class="text-muted mb-3">Select subjects included in this package and set sessions per month.</p>
                        <div class="table-responsive">
                            <table class="table table-hover" id="subjectsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">Select</th>
                                        <th>Subject</th>
                                        <th>Code</th>
                                        <th width="180">Sessions/Month</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $currentSubjects = old('subjects', $package->subjects->pluck('id')->toArray());
                                    @endphp
                                    @foreach($subjects as $subject)
                                        @php
                                            $isSelected = in_array($subject->id, $currentSubjects);
                                            $sessions = old('sessions_' . $subject->id, $selectedSubjects[$subject->id] ?? 4);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input subject-checkbox" type="checkbox"
                                                           name="subjects[]" value="{{ $subject->id }}"
                                                           id="subject_{{ $subject->id }}"
                                                           data-subject-id="{{ $subject->id }}"
                                                           {{ $isSelected ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td>
                                                <label for="subject_{{ $subject->id }}" class="mb-0" style="cursor: pointer;">
                                                    {{ $subject->name }}
                                                </label>
                                            </td>
                                            <td><span class="badge bg-secondary">{{ $subject->code }}</span></td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm sessions-input"
                                                       name="sessions_{{ $subject->id }}"
                                                       id="sessions_{{ $subject->id }}"
                                                       value="{{ $sessions }}"
                                                       min="1" max="30"
                                                       {{ $isSelected ? '' : 'disabled' }}>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No active subjects found.
                            <a href="{{ route('admin.subjects.create') }}">Create a subject first</a>.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Features Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i> Package Features</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFeature()">
                        <i class="fas fa-plus me-1"></i> Add Feature
                    </button>
                </div>
                <div class="card-body">
                    <div id="featuresContainer">
                        @php
                            $features = old('features', $package->features ?? []);
                        @endphp
                        @if(count($features) > 0)
                            @foreach($features as $feature)
                                <div class="input-group mb-2 feature-row">
                                    <span class="input-group-text"><i class="fas fa-check text-success"></i></span>
                                    <input type="text" class="form-control" name="features[]"
                                           value="{{ $feature }}" placeholder="e.g., Free study materials">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <div class="input-group mb-2 feature-row">
                                <span class="input-group-text"><i class="fas fa-check text-success"></i></span>
                                <input type="text" class="form-control" name="features[]"
                                       placeholder="e.g., Free study materials">
                                <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                    <small class="text-muted">Add features that will be displayed on the package description.</small>
                </div>
            </div>
        </div>

        <!-- Right Column - Pricing & Status -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> Pricing</h5>
                </div>
                <div class="card-body">
                    <!-- Base Price -->
                    <div class="mb-3">
                        <label class="form-label" for="price">Base Price (RM) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" class="form-control @error('price') is-invalid @enderror"
                                   id="price" name="price" value="{{ old('price', $package->price) }}"
                                   min="0" max="99999.99" step="0.01" required
                                   placeholder="0.00">
                        </div>
                        @error('price')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Online Fee -->
                    <div class="mb-3" id="onlineFeeContainer"
                         style="display: {{ in_array($package->type, ['online', 'hybrid']) ? 'block' : 'none' }};">
                        <label class="form-label" for="online_fee">Online Payment Fee (RM)</label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" class="form-control @error('online_fee') is-invalid @enderror"
                                   id="online_fee" name="online_fee"
                                   value="{{ old('online_fee', $package->online_fee ?? $defaultOnlineFee) }}"
                                   min="0" max="999.99" step="0.01"
                                   placeholder="130.00">
                        </div>
                        <small class="text-muted">Default: RM {{ number_format($defaultOnlineFee, 2) }}</small>
                        @error('online_fee')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Price Summary -->
                    <div class="border rounded p-3 bg-light">
                        <h6 class="mb-2">Price Summary</h6>
                        <div class="d-flex justify-content-between">
                            <span>Base Price:</span>
                            <span id="summaryBasePrice">RM {{ number_format($package->price, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between" id="summaryOnlineFeeRow"
                             style="display: {{ in_array($package->type, ['online', 'hybrid']) ? 'flex' : 'none' }};">
                            <span>Online Fee:</span>
                            <span id="summaryOnlineFee">RM {{ number_format($package->online_fee ?? 0, 2) }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong class="text-primary" id="summaryTotal">RM {{ number_format($package->total_price, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i> Settings</h5>
                </div>
                <div class="card-body">
                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', $package->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $package->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Includes Materials -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="includes_materials"
                               name="includes_materials" value="1"
                               {{ old('includes_materials', $package->includes_materials) ? 'checked' : '' }}>
                        <label class="form-check-label" for="includes_materials">
                            Includes study materials
                        </label>
                    </div>
                </div>
            </div>

            <!-- Usage Info -->
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Usage:</strong> {{ $package->enrollments()->count() }} enrollment(s)
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-1"></i> Update Package
                </button>
                <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
// Handle package type change
document.getElementById('type').addEventListener('change', function() {
    const onlineFeeContainer = document.getElementById('onlineFeeContainer');
    const summaryOnlineFeeRow = document.getElementById('summaryOnlineFeeRow');

    if (this.value === 'online' || this.value === 'hybrid') {
        onlineFeeContainer.style.display = 'block';
        summaryOnlineFeeRow.style.display = 'flex';
    } else {
        onlineFeeContainer.style.display = 'none';
        summaryOnlineFeeRow.style.display = 'none';
    }
    updatePriceSummary();
});

// Subject checkbox handlers
document.querySelectorAll('.subject-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        const subjectId = this.dataset.subjectId;
        const sessionsInput = document.getElementById('sessions_' + subjectId);
        sessionsInput.disabled = !this.checked;
        if (!this.checked) {
            sessionsInput.value = 4;
        }
    });
});

// Update price summary
function updatePriceSummary() {
    const price = parseFloat(document.getElementById('price').value) || 0;
    const type = document.getElementById('type').value;
    let onlineFee = 0;

    if (type === 'online' || type === 'hybrid') {
        onlineFee = parseFloat(document.getElementById('online_fee').value) || 0;
    }

    const total = price + onlineFee;

    document.getElementById('summaryBasePrice').textContent = 'RM ' + price.toFixed(2);
    document.getElementById('summaryOnlineFee').textContent = 'RM ' + onlineFee.toFixed(2);
    document.getElementById('summaryTotal').textContent = 'RM ' + total.toFixed(2);
}

document.getElementById('price').addEventListener('input', updatePriceSummary);
document.getElementById('online_fee').addEventListener('input', updatePriceSummary);

// Features management
function addFeature() {
    const container = document.getElementById('featuresContainer');
    const div = document.createElement('div');
    div.className = 'input-group mb-2 feature-row';
    div.innerHTML = `
        <span class="input-group-text"><i class="fas fa-check text-success"></i></span>
        <input type="text" class="form-control" name="features[]" placeholder="e.g., Free study materials">
        <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeFeature(btn) {
    const rows = document.querySelectorAll('.feature-row');
    if (rows.length > 1) {
        btn.closest('.feature-row').remove();
    } else {
        btn.closest('.feature-row').querySelector('input').value = '';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', updatePriceSummary);
</script>
@endsection
