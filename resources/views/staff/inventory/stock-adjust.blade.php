@extends('layouts.staff')

@section('title', 'Adjust Stock')

@section('page-title', 'Adjust Stock')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('staff.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active">Adjust Stock</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-balance-scale me-2"></i>Stock Adjustment
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Item Info -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    @if($inventory->image)
                                    <img src="{{ Storage::url($inventory->image) }}" alt="{{ $inventory->name }}" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                    @else
                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-image fa-2x text-white"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="col">
                                    <h5 class="mb-1">{{ $inventory->name }}</h5>
                                    <p class="mb-1 text-muted">
                                        <code>{{ $inventory->sku }}</code> · {{ $inventory->category->name ?? 'Uncategorized' }}
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <span class="me-3">
                                            <strong>Current Stock:</strong>
                                            <span class="badge bg-{{ $inventory->current_stock <= $inventory->reorder_level ? 'warning text-dark' : 'success' }} fs-5 ms-1">
                                                {{ $inventory->current_stock }} {{ $inventory->unit }}
                                            </span>
                                        </span>
                                        <span>
                                            <strong>Reorder Level:</strong> {{ $inventory->reorder_level }} {{ $inventory->unit }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Adjustment Form -->
                    <form action="{{ route('staff.inventory.process-adjustment', $inventory) }}" method="POST" id="adjustmentForm">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold">Adjustment Type <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="typeAdd" value="add" {{ old('type', 'add') == 'add' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-success w-100 py-3" for="typeAdd">
                                        <i class="fas fa-plus fa-2x mb-2 d-block"></i>
                                        <strong>Add Stock</strong>
                                        <small class="d-block text-muted">Restock / Receive items</small>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="typeRemove" value="remove" {{ old('type') == 'remove' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger w-100 py-3" for="typeRemove">
                                        <i class="fas fa-minus fa-2x mb-2 d-block"></i>
                                        <strong>Remove Stock</strong>
                                        <small class="d-block text-muted">Damage / Loss / Expired</small>
                                    </label>
                                </div>
                            </div>
                            @error('type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <button type="button" class="btn btn-outline-secondary" onclick="decrementQty()">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" name="quantity" id="quantityInput" class="form-control text-center @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}" min="1" required>
                                <span class="input-group-text">{{ $inventory->unit }}</span>
                                <button type="button" class="btn btn-outline-secondary" onclick="incrementQty()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            @error('quantity')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div id="resultPreview" class="alert alert-info mt-2 mb-0"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Reference (Optional)</label>
                            <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror" value="{{ old('reference') }}" placeholder="e.g., PO Number, Delivery Note">
                            @error('reference')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Notes / Reason</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Describe the reason for this adjustment...">{{ old('notes') }}</textarea>
                            @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('staff.inventory.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-check me-1"></i> Confirm Adjustment
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Activity -->
            @if($recentLogs->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Stock After</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentLogs as $log)
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
                                    <td class="text-center">
                                        @if(in_array($log->type, ['add']))
                                        <span class="text-success">+{{ $log->quantity }}</span>
                                        @elseif(in_array($log->type, ['remove', 'sale']))
                                        <span class="text-danger">-{{ $log->quantity }}</span>
                                        @else
                                        {{ $log->quantity }}
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $log->new_stock }}</td>
                                    <td><small>{{ $log->createdBy->name ?? 'System' }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const currentStock = {{ $inventory->current_stock }};
const quantityInput = document.getElementById('quantityInput');
const resultPreview = document.getElementById('resultPreview');

function incrementQty() {
    quantityInput.value = parseInt(quantityInput.value || 0) + 1;
    updatePreview();
}

function decrementQty() {
    const current = parseInt(quantityInput.value || 0);
    if (current > 1) {
        quantityInput.value = current - 1;
        updatePreview();
    }
}

function updatePreview() {
    const type = document.querySelector('input[name="type"]:checked')?.value || 'add';
    const quantity = parseInt(quantityInput.value) || 0;
    let newStock = currentStock;
    let message = '';

    if (type === 'add') {
        newStock = currentStock + quantity;
        message = `<i class="fas fa-arrow-up text-success me-2"></i>Stock will increase from <strong>${currentStock}</strong> to <strong>${newStock}</strong>`;
    } else if (type === 'remove') {
        newStock = Math.max(0, currentStock - quantity);
        if (quantity > currentStock) {
            message = `<i class="fas fa-exclamation-triangle text-warning me-2"></i>Cannot remove more than available. Stock will be set to <strong>0</strong>`;
        } else {
            message = `<i class="fas fa-arrow-down text-danger me-2"></i>Stock will decrease from <strong>${currentStock}</strong> to <strong>${newStock}</strong>`;
        }
    }

    resultPreview.innerHTML = message;
}

// Event listeners
quantityInput.addEventListener('input', updatePreview);
document.querySelectorAll('input[name="type"]').forEach(input => {
    input.addEventListener('change', updatePreview);
});

// Initial update
updatePreview();
</script>
@endpush
