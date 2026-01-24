@extends('layouts.app')

@section('title', 'Edit Inventory Item')

@section('page-title', 'Edit Inventory Item')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.show', $inventory) }}">{{ $inventory->name }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Edit: {{ $inventory->name }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.inventory.update', $inventory) }}" method="POST" enctype="multipart/form-data" id="inventoryForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-8">
                                <h6 class="text-muted mb-3">Basic Information</h6>

                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $inventory->name) }}" required>
                                        @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">SKU</label>
                                        <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $inventory->sku) }}">
                                        @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Category <span class="text-danger">*</span></label>
                                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $inventory->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Unit</label>
                                        <select name="unit" class="form-select @error('unit') is-invalid @enderror">
                                            <option value="pcs" {{ old('unit', $inventory->unit) == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                            <option value="box" {{ old('unit', $inventory->unit) == 'box' ? 'selected' : '' }}>Box</option>
                                            <option value="pack" {{ old('unit', $inventory->unit) == 'pack' ? 'selected' : '' }}>Pack</option>
                                            <option value="bottle" {{ old('unit', $inventory->unit) == 'bottle' ? 'selected' : '' }}>Bottle</option>
                                            <option value="can" {{ old('unit', $inventory->unit) == 'can' ? 'selected' : '' }}>Can</option>
                                            <option value="kg" {{ old('unit', $inventory->unit) == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                            <option value="g" {{ old('unit', $inventory->unit) == 'g' ? 'selected' : '' }}>Gram (g)</option>
                                            <option value="l" {{ old('unit', $inventory->unit) == 'l' ? 'selected' : '' }}>Liter (l)</option>
                                            <option value="ml" {{ old('unit', $inventory->unit) == 'ml' ? 'selected' : '' }}>Milliliter (ml)</option>
                                        </select>
                                        @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                                            <option value="active" {{ old('status', $inventory->status) == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old('status', $inventory->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            <option value="out_of_stock" {{ old('status', $inventory->status) == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                        </select>
                                        @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Enter item description...">{{ old('description', $inventory->description) }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <hr class="my-4">

                                <h6 class="text-muted mb-3">Pricing</h6>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Cost Price (RM)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">RM</span>
                                            <input type="number" name="cost_price" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price', $inventory->cost_price) }}" step="0.01" min="0">
                                        </div>
                                        @error('cost_price')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Selling Price (RM) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">RM</span>
                                            <input type="number" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price', $inventory->selling_price) }}" step="0.01" min="0" required>
                                        </div>
                                        @error('selling_price')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <hr class="my-4">

                                <h6 class="text-muted mb-3">Stock Settings</h6>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Current Stock</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="{{ $inventory->current_stock }} {{ $inventory->unit }}" disabled>
                                            <a href="{{ route('admin.inventory.adjust-stock', $inventory) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-balance-scale me-1"></i> Adjust
                                            </a>
                                        </div>
                                        <small class="text-muted">Use "Adjust Stock" to modify quantity</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Reorder Level</label>
                                        <input type="number" name="reorder_level" class="form-control @error('reorder_level') is-invalid @enderror" value="{{ old('reorder_level', $inventory->reorder_level) }}" min="0">
                                        @error('reorder_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Alert when stock falls below this level</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Image Upload -->
                            <div class="col-md-4">
                                <h6 class="text-muted mb-3">Product Image</h6>

                                <div class="card border">
                                    <div class="card-body text-center">
                                        <div id="imagePreview" class="mb-3">
                                            @if($inventory->image)
                                            <img src="{{ Storage::url($inventory->image) }}" class="img-fluid rounded" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                            @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width: 200px; height: 200px;">
                                                <i class="fas fa-image fa-4x text-muted"></i>
                                            </div>
                                            @endif
                                        </div>
                                        <input type="file" name="image" id="imageInput" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                        @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted d-block mt-2">Leave empty to keep current image</small>
                                    </div>
                                </div>

                                <!-- Stock Status Card -->
                                <div class="card border mt-3">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="fas fa-chart-bar me-1"></i> Stock Status</h6>
                                        @php
                                            $stockPercentage = $inventory->reorder_level > 0
                                                ? min(100, ($inventory->current_stock / ($inventory->reorder_level * 2)) * 100)
                                                : ($inventory->current_stock > 0 ? 100 : 0);
                                            $stockColor = $inventory->current_stock == 0 ? 'danger'
                                                : ($inventory->current_stock <= $inventory->reorder_level ? 'warning' : 'success');
                                        @endphp
                                        <div class="progress mb-2" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $stockColor }}" style="width: {{ $stockPercentage }}%">
                                                {{ $inventory->current_stock }} {{ $inventory->unit }}
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            @if($inventory->current_stock == 0)
                                            <span class="text-danger"><i class="fas fa-exclamation-circle"></i> Out of stock!</span>
                                            @elseif($inventory->current_stock <= $inventory->reorder_level)
                                            <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Low stock - reorder needed</span>
                                            @else
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Stock level healthy</span>
                                            @endif
                                        </small>
                                    </div>
                                </div>

                                <!-- Item Info Card -->
                                <div class="card border mt-3">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="fas fa-info-circle me-1"></i> Item Info</h6>
                                        <ul class="list-unstyled small text-muted mb-0">
                                            <li><strong>Created:</strong> {{ $inventory->created_at->format('d M Y, h:i A') }}</li>
                                            <li><strong>Updated:</strong> {{ $inventory->updated_at->format('d M Y, h:i A') }}</li>
                                            <li><strong>Stock Value:</strong> RM {{ number_format($inventory->current_stock * $inventory->cost_price, 2) }}</li>
                                            <li><strong>Retail Value:</strong> RM {{ number_format($inventory->current_stock * $inventory->selling_price, 2) }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.inventory.show', $inventory) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').innerHTML = `
                <img src="${e.target.result}" class="img-fluid rounded" style="max-width: 200px; max-height: 200px; object-fit: cover;">
            `;
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
