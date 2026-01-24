@extends('layouts.app')

@section('title', 'Add Inventory Item')

@section('page-title', 'Add Inventory Item')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active">Add Item</li>
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
                        <i class="fas fa-plus-circle me-2"></i>Add New Inventory Item
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.inventory.store') }}" method="POST" enctype="multipart/form-data" id="inventoryForm">
                        @csrf

                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-8">
                                <h6 class="text-muted mb-3">Basic Information</h6>

                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                        @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">SKU</label>
                                        <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}" placeholder="Auto-generated if empty">
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
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                            <option value="pcs" {{ old('unit', 'pcs') == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                            <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>Box</option>
                                            <option value="pack" {{ old('unit') == 'pack' ? 'selected' : '' }}>Pack</option>
                                            <option value="bottle" {{ old('unit') == 'bottle' ? 'selected' : '' }}>Bottle</option>
                                            <option value="can" {{ old('unit') == 'can' ? 'selected' : '' }}>Can</option>
                                            <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                            <option value="g" {{ old('unit') == 'g' ? 'selected' : '' }}>Gram (g)</option>
                                            <option value="l" {{ old('unit') == 'l' ? 'selected' : '' }}>Liter (l)</option>
                                            <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>Milliliter (ml)</option>
                                        </select>
                                        @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Enter item description...">{{ old('description') }}</textarea>
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
                                            <input type="number" name="cost_price" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price', '0.00') }}" step="0.01" min="0">
                                        </div>
                                        @error('cost_price')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Your purchase price per unit</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Selling Price (RM) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">RM</span>
                                            <input type="number" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price') }}" step="0.01" min="0" required>
                                        </div>
                                        @error('selling_price')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Price you sell to customers</small>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <h6 class="text-muted mb-3">Stock Information</h6>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Initial Stock <span class="text-danger">*</span></label>
                                        <input type="number" name="current_stock" class="form-control @error('current_stock') is-invalid @enderror" value="{{ old('current_stock', 0) }}" min="0" required>
                                        @error('current_stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Starting quantity in inventory</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Reorder Level</label>
                                        <input type="number" name="reorder_level" class="form-control @error('reorder_level') is-invalid @enderror" value="{{ old('reorder_level', 10) }}" min="0">
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
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width: 200px; height: 200px;">
                                                <i class="fas fa-image fa-4x text-muted"></i>
                                            </div>
                                        </div>
                                        <input type="file" name="image" id="imageInput" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                        @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted d-block mt-2">Max size: 2MB. Formats: JPEG, PNG, GIF, WebP</small>
                                    </div>
                                </div>

                                <div class="card border mt-3">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="fas fa-info-circle me-1"></i> Quick Tips</h6>
                                        <ul class="small text-muted mb-0">
                                            <li>SKU will be auto-generated if left empty</li>
                                            <li>Set reorder level to get low stock alerts</li>
                                            <li>Cost price helps track profit margins</li>
                                            <li>Use clear product images for easy identification</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Cancel
                            </a>
                            <div>
                                <button type="submit" name="save_and_new" value="1" class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-1"></i> Save & Add Another
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Item
                                </button>
                            </div>
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

// Calculate profit margin on price change
document.querySelectorAll('input[name="cost_price"], input[name="selling_price"]').forEach(function(input) {
    input.addEventListener('input', function() {
        const costPrice = parseFloat(document.querySelector('input[name="cost_price"]').value) || 0;
        const sellingPrice = parseFloat(document.querySelector('input[name="selling_price"]').value) || 0;

        if (costPrice > 0 && sellingPrice > 0) {
            const margin = ((sellingPrice - costPrice) / costPrice * 100).toFixed(2);
            console.log('Profit Margin: ' + margin + '%');
        }
    });
});
</script>
@endpush
