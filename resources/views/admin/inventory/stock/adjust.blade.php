@extends('layouts.app')

@section('title', 'Adjust Stock')

@section('page-title', 'Adjust Stock: ' . $inventory->name)

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.show', $inventory) }}">{{ $inventory->name }}</a></li>
        <li class="breadcrumb-item active">Adjust Stock</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Adjustment Form -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-balance-scale me-2"></i>Stock Adjustment
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Current Stock Display -->
                    <div class="alert alert-info d-flex align-items-center mb-4">
                        <div class="flex-grow-1">
                            <strong>Current Stock:</strong>
                            <span class="fs-4 ms-2">{{ $inventory->current_stock }} {{ $inventory->unit }}</span>
                        </div>
                        @if($inventory->current_stock <= $inventory->reorder_level)
                        <span class="badge bg-warning text-dark">Low Stock</span>
                        @endif
                    </div>

                    <form action="{{ route('admin.inventory.process-adjustment', $inventory) }}" method="POST" id="adjustmentForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="type" id="typeAdd" value="add" {{ old('type', 'add') == 'add' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success" for="typeAdd">
                                    <i class="fas fa-plus me-1"></i> Add Stock
                                </label>

                                <input type="radio" class="btn-check" name="type" id="typeRemove" value="remove" {{ old('type') == 'remove' ? 'checked' : '' }}>
                                <label class="btn btn-outline-danger" for="typeRemove">
                                    <i class="fas fa-minus me-1"></i> Remove Stock
                                </label>

                                <input type="radio" class="btn-check" name="type" id="typeAdjust" value="adjust" {{ old('type') == 'adjust' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="typeAdjust">
                                    <i class="fas fa-edit me-1"></i> Set Stock
                                </label>
                            </div>
                            @error('type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="quantity" id="quantityInput" class="form-control form-control-lg @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}" min="1" required>
                                <span class="input-group-text">{{ $inventory->unit }}</span>
                            </div>
                            @error('quantity')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div id="resultPreview" class="mt-2 text-muted small"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reference / PO Number</label>
                            <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror" value="{{ old('reference') }}" placeholder="e.g., PO-2024-001">
                            @error('reference')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Notes / Reason</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Describe the reason for this adjustment...">{{ old('notes') }}</textarea>
                            @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.inventory.show', $inventory) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check me-1"></i> Apply Adjustment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent History & Info -->
        <div class="col-lg-6">
            <!-- Item Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        @if($inventory->image)
                        <img src="{{ Storage::url($inventory->image) }}" alt="{{ $inventory->name }}" class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-image fa-2x text-muted"></i>
                        </div>
                        @endif
                        <div>
                            <h5 class="mb-1">{{ $inventory->name }}</h5>
                            <p class="text-muted mb-0">
                                <code>{{ $inventory->sku }}</code> · {{ $inventory->category->name ?? 'Uncategorized' }}
                            </p>
                            <p class="mb-0 mt-1">
                                <small>Reorder Level: <strong>{{ $inventory->reorder_level }} {{ $inventory->unit }}</strong></small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Stock History -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                    <a href="{{ route('admin.inventory.stock-history', $inventory) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
                                <tr>
                                    <td><small>{{ $log->created_at->format('d M, h:i A') }}</small></td>
                                    <td>
                                        @if($log->type == 'add')
                                        <span class="badge bg-success">Add</span>
                                        @elseif($log->type == 'remove')
                                        <span class="badge bg-warning text-dark">Remove</span>
                                        @elseif($log->type == 'sale')
                                        <span class="badge bg-info">Sale</span>
                                        @else
                                        <span class="badge bg-secondary">Adjust</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(in_array($log->type, ['add']))
                                        <span class="text-success">+{{ $log->quantity }}</span>
                                        @elseif(in_array($log->type, ['remove', 'sale']))
                                        <span class="text-danger">-{{ $log->quantity }}</span>
                                        @else
                                        {{ $log->quantity }}
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $log->new_stock }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        No history yet
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const currentStock = {{ $inventory->current_stock }};
    const quantityInput = document.getElementById('quantityInput');
    const resultPreview = document.getElementById('resultPreview');
    const typeInputs = document.querySelectorAll('input[name="type"]');

    function updatePreview() {
        const type = document.querySelector('input[name="type"]:checked').value;
        const quantity = parseInt(quantityInput.value) || 0;
        let newStock = currentStock;
        let message = '';

        if (type === 'add') {
            newStock = currentStock + quantity;
            message = `<i class="fas fa-arrow-up text-success"></i> New stock will be: <strong>${newStock}</strong> (${currentStock} + ${quantity})`;
        } else if (type === 'remove') {
            newStock = Math.max(0, currentStock - quantity);
            if (quantity > currentStock) {
                message = `<i class="fas fa-exclamation-triangle text-warning"></i> Cannot remove more than current stock. New stock will be: <strong>0</strong>`;
            } else {
                message = `<i class="fas fa-arrow-down text-danger"></i> New stock will be: <strong>${newStock}</strong> (${currentStock} - ${quantity})`;
            }
        } else if (type === 'adjust') {
            newStock = quantity;
            const diff = quantity - currentStock;
            const direction = diff >= 0 ? '+' : '';
            message = `<i class="fas fa-edit text-primary"></i> Stock will be set to: <strong>${newStock}</strong> (${direction}${diff} change)`;
        }

        resultPreview.innerHTML = message;
    }

    quantityInput.addEventListener('input', updatePreview);
    typeInputs.forEach(input => input.addEventListener('change', updatePreview));

    // Initial update
    updatePreview();
});
</script>
@endpush
