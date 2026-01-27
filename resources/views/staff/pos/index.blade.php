@extends('layouts.app')

@section('title', 'POS Terminal')
@section('page-title', 'POS Terminal')

@push('styles')
<style>
    .pos-container {
        height: calc(100vh - 200px);
        min-height: 500px;
    }
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 10px;
        max-height: 60vh;
        overflow-y: auto;
        padding: 10px;
    }
    .product-card {
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
        text-align: center;
        padding: 10px;
    }
    .product-card:hover {
        border-color: #0d6efd;
        transform: translateY(-2px);
    }
    .product-card.out-of-stock {
        opacity: 0.5;
        pointer-events: none;
    }
    .product-card img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
    }
    .product-card .product-name {
        font-size: 0.85rem;
        font-weight: 500;
        margin-top: 5px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .product-card .product-price {
        font-size: 0.9rem;
        font-weight: bold;
        color: #198754;
    }
    .product-card .product-stock {
        font-size: 0.75rem;
        color: #6c757d;
    }
    .cart-items {
        max-height: 40vh;
        overflow-y: auto;
    }
    .cart-item {
        border-bottom: 1px solid #e9ecef;
        padding: 10px 0;
    }
    .cart-item:last-child {
        border-bottom: none;
    }
    .cart-total {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .category-tabs {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 10px;
    }
    .category-tabs .btn {
        margin-right: 5px;
    }
    .drawer-closed-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        z-index: 100;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
    }
    .drawer-closed-overlay i {
        font-size: 4rem;
        color: #dc3545;
        margin-bottom: 1rem;
    }
    .pos-section {
        position: relative;
    }
    .qty-btn {
        width: 28px;
        height: 28px;
        padding: 0;
        line-height: 1;
    }
    .numpad-btn {
        width: 60px;
        height: 50px;
        font-size: 1.2rem;
        font-weight: bold;
    }
    @media (max-width: 991px) {
        .pos-container {
            height: auto;
        }
        .product-grid {
            max-height: 40vh;
        }
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-cash-register me-2"></i> POS Terminal</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">POS Terminal</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @if(Route::has('staff.pos.my-transactions'))
        <a href="{{ route('staff.pos.my-transactions') }}" class="btn btn-outline-primary">
            <i class="fas fa-history me-1"></i> My Transactions
        </a>
        @endif
    </div>
</div>

{{-- FIX: Show drawer status alert instead of redirecting --}}
@if(!$drawerOpen)
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
        <div>
            <h5 class="alert-heading mb-1">Cash Drawer is Not Open</h5>
            <p class="mb-0">The cash drawer has not been opened for today. Please contact an administrator to open the drawer before processing any sales.</p>
        </div>
    </div>
</div>
@else
{{-- Show drawer status when open --}}
<div class="alert alert-success mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-check-circle me-2"></i>
            <strong>Drawer Open</strong> - Opening Cash: RM {{ number_format($cashReport->opening_cash ?? 0, 2) }}
        </div>
        <span class="badge bg-success">{{ now()->format('d M Y') }}</span>
    </div>
</div>
@endif

<!-- Today's Stats -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3 mb-md-0">
        <div class="card border-0 bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="mb-1">My Sales Today</h6>
                        <h3 class="mb-0" id="statTotalSales">RM {{ number_format($todayStats['total_sales'] ?? 0, 2) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3 mb-md-0">
        <div class="card border-0 bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="mb-1">Transactions</h6>
                        <h3 class="mb-0" id="statTransactions">{{ $todayStats['total_transactions'] ?? 0 }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-receipt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="mb-1">Cash Sales</h6>
                        <h3 class="mb-0" id="statCashSales">RM {{ number_format($todayStats['cash_sales'] ?? 0, 2) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="mb-1">QR Sales</h6>
                        <h3 class="mb-0" id="statQrSales">RM {{ number_format($todayStats['qr_sales'] ?? 0, 2) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-qrcode fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- POS Main Section -->
<div class="row pos-container">
    <!-- Products Section -->
    <div class="col-lg-7 mb-4 mb-lg-0">
        <div class="card h-100 pos-section">
            {{-- FIX: Overlay when drawer is closed --}}
            @if(!$drawerOpen)
            <div class="drawer-closed-overlay">
                <i class="fas fa-lock"></i>
                <h4 class="text-danger">POS Disabled</h4>
                <p class="text-muted">Cash drawer must be opened to process sales</p>
                <p class="text-muted small">Please contact your administrator</p>
            </div>
            @endif
            
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-boxes me-2"></i> Products</h5>
                    <div class="input-group" style="max-width: 250px;">
                        <input type="text" id="searchItems" class="form-control" placeholder="Search items..." {{ !$drawerOpen ? 'disabled' : '' }}>
                        <button class="btn btn-outline-secondary" type="button" id="btnSearch" {{ !$drawerOpen ? 'disabled' : '' }}>
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <!-- Category Tabs -->
                <div class="category-tabs">
                    <button class="btn btn-primary btn-sm category-btn active" data-category="all" {{ !$drawerOpen ? 'disabled' : '' }}>All</button>
                    @foreach($categories as $category)
                    <button class="btn btn-outline-primary btn-sm category-btn" data-category="{{ $category->id }}" {{ !$drawerOpen ? 'disabled' : '' }}>
                        {{ $category->name }}
                    </button>
                    @endforeach
                </div>
            </div>
            <div class="card-body">
                <div class="product-grid" id="productGrid">
                    @forelse($items as $item)
                    <div class="card product-card {{ $item->current_stock <= 0 ? 'out-of-stock' : '' }}" 
                         data-id="{{ $item->id }}"
                         data-name="{{ $item->name }}"
                         data-price="{{ $item->selling_price }}"
                         data-stock="{{ $item->current_stock }}"
                         data-category="{{ $item->category_id }}">
                        @if($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="mx-auto">
                        @else
                        <div class="bg-light d-flex align-items-center justify-content-center mx-auto" style="width:80px;height:80px;border-radius:8px;">
                            <i class="fas fa-image fa-2x text-muted"></i>
                        </div>
                        @endif
                        <div class="product-name" title="{{ $item->name }}">{{ $item->name }}</div>
                        <div class="product-price">RM {{ number_format($item->selling_price, 2) }}</div>
                        <div class="product-stock">
                            @if($item->current_stock <= 0)
                            <span class="text-danger">Out of Stock</span>
                            @elseif($item->current_stock <= 5)
                            <span class="text-warning">{{ $item->current_stock }} left</span>
                            @else
                            <span>Stock: {{ $item->current_stock }}</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No products available</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Section -->
    <div class="col-lg-5">
        <div class="card h-100 pos-section">
            {{-- FIX: Overlay when drawer is closed --}}
            @if(!$drawerOpen)
            <div class="drawer-closed-overlay">
                <i class="fas fa-shopping-cart"></i>
                <h4 class="text-danger">Cart Disabled</h4>
                <p class="text-muted">Open the drawer to start selling</p>
            </div>
            @endif
            
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Cart</h5>
                <button class="btn btn-outline-danger btn-sm" id="btnClearCart" {{ !$drawerOpen ? 'disabled' : '' }}>
                    <i class="fas fa-trash me-1"></i> Clear
                </button>
            </div>
            <div class="card-body d-flex flex-column">
                <!-- Cart Items -->
                <div class="cart-items flex-grow-1" id="cartItems">
                    <div class="text-center text-muted py-5" id="emptyCart">
                        <i class="fas fa-shopping-basket fa-3x mb-3"></i>
                        <p>Cart is empty</p>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="cartSubtotal">RM 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="cart-total">Total:</span>
                        <span class="cart-total text-primary" id="cartTotal">RM 0.00</span>
                    </div>

                    <!-- Payment Buttons -->
                    <div class="d-grid gap-2">
                        <button class="btn btn-success btn-lg" id="btnPayCash" disabled>
                            <i class="fas fa-money-bill-wave me-2"></i> Pay with Cash
                        </button>
                        <button class="btn btn-primary btn-lg" id="btnPayQR" disabled>
                            <i class="fas fa-qrcode me-2"></i> Pay with QR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-cash-register me-2"></i> Complete Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <h2 class="text-primary mb-0" id="modalTotal">RM 0.00</h2>
                    <small class="text-muted">Total Amount</small>
                </div>

                <input type="hidden" id="paymentMethod" value="cash">

                <!-- Cash Payment Section -->
                <div id="cashPaymentSection">
                    <div class="mb-3">
                        <label class="form-label">Amount Received</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">RM</span>
                            <input type="number" class="form-control text-end" id="amountReceived" step="0.01" min="0">
                        </div>
                    </div>
                    
                    <!-- Quick Amount Buttons -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-outline-secondary quick-amount" data-amount="exact">Exact</button>
                        <button type="button" class="btn btn-outline-secondary quick-amount" data-amount="5">RM 5</button>
                        <button type="button" class="btn btn-outline-secondary quick-amount" data-amount="10">RM 10</button>
                        <button type="button" class="btn btn-outline-secondary quick-amount" data-amount="20">RM 20</button>
                        <button type="button" class="btn btn-outline-secondary quick-amount" data-amount="50">RM 50</button>
                        <button type="button" class="btn btn-outline-secondary quick-amount" data-amount="100">RM 100</button>
                    </div>

                    <div class="alert alert-info" id="changeSection" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Change:</span>
                            <strong class="fs-4" id="changeAmount">RM 0.00</strong>
                        </div>
                    </div>
                </div>

                <!-- QR Payment Section -->
                <div id="qrPaymentSection" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Reference Number (Optional)</label>
                        <input type="text" class="form-control" id="referenceNumber" placeholder="Enter reference number">
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Confirm that payment has been received via QR before completing.
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-3">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea class="form-control" id="transactionNotes" rows="2" placeholder="Add any notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-lg" id="btnCompletePayment">
                    <i class="fas fa-check me-2"></i> Complete Sale
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i> Transaction Complete!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-receipt fa-4x text-success mb-3"></i>
                <h4 id="receiptTransactionNumber"></h4>
                <p class="text-muted">Transaction completed successfully</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <a href="#" class="btn btn-primary" id="btnViewReceipt" target="_blank">
                    <i class="fas fa-receipt me-1"></i> View Receipt
                </a>
                <a href="#" class="btn btn-success" id="btnPrintReceipt" target="_blank">
                    <i class="fas fa-print me-1"></i> Print Receipt
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Check if drawer is open - disable functionality if not
    const drawerOpen = {{ $drawerOpen ? 'true' : 'false' }};
    
    let cart = [];
    let currentTotal = 0;

    // Product click - add to cart (only if drawer is open)
    $(document).on('click', '.product-card:not(.out-of-stock)', function() {
        if (!drawerOpen) {
            Swal.fire({
                icon: 'warning',
                title: 'Drawer Not Open',
                text: 'Please contact admin to open the cash drawer first.'
            });
            return;
        }
        
        const id = $(this).data('id');
        const name = $(this).data('name');
        const price = parseFloat($(this).data('price'));
        const stock = parseInt($(this).data('stock'));

        addToCart(id, name, price, stock);
    });

    // Add to cart function
    function addToCart(id, name, price, maxStock) {
        const existingItem = cart.find(item => item.id === id);
        
        if (existingItem) {
            if (existingItem.quantity >= maxStock) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock Limit',
                    text: 'Cannot add more. Stock limit reached.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                return;
            }
            existingItem.quantity++;
        } else {
            cart.push({
                id: id,
                name: name,
                price: price,
                quantity: 1,
                maxStock: maxStock
            });
        }

        updateCartDisplay();
    }

    // Update cart display
    function updateCartDisplay() {
        const cartContainer = $('#cartItems');
        
        if (cart.length === 0) {
            cartContainer.html(`
                <div class="text-center text-muted py-5" id="emptyCart">
                    <i class="fas fa-shopping-basket fa-3x mb-3"></i>
                    <p>Cart is empty</p>
                </div>
            `);
            currentTotal = 0;
        } else {
            let html = '';
            currentTotal = 0;

            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                currentTotal += itemTotal;

                html += `
                    <div class="cart-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <strong>${item.name}</strong>
                                <br><small class="text-muted">RM ${item.price.toFixed(2)} each</small>
                            </div>
                            <div class="text-end">
                                <strong>RM ${itemTotal.toFixed(2)}</strong>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary qty-btn btn-decrease" data-index="${index}">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="btn btn-outline-secondary" style="pointer-events:none;min-width:40px;">
                                    ${item.quantity}
                                </span>
                                <button class="btn btn-outline-secondary qty-btn btn-increase" data-index="${index}" 
                                        ${item.quantity >= item.maxStock ? 'disabled' : ''}>
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <button class="btn btn-outline-danger btn-sm btn-remove" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });

            cartContainer.html(html);
        }

        $('#cartSubtotal').text('RM ' + currentTotal.toFixed(2));
        $('#cartTotal').text('RM ' + currentTotal.toFixed(2));
        $('#modalTotal').text('RM ' + currentTotal.toFixed(2));

        // Enable/disable payment buttons
        const hasItems = cart.length > 0 && drawerOpen;
        $('#btnPayCash').prop('disabled', !hasItems);
        $('#btnPayQR').prop('disabled', !hasItems);
    }

    // Quantity controls
    $(document).on('click', '.btn-decrease', function() {
        const index = $(this).data('index');
        if (cart[index].quantity > 1) {
            cart[index].quantity--;
        } else {
            cart.splice(index, 1);
        }
        updateCartDisplay();
    });

    $(document).on('click', '.btn-increase', function() {
        const index = $(this).data('index');
        if (cart[index].quantity < cart[index].maxStock) {
            cart[index].quantity++;
        }
        updateCartDisplay();
    });

    $(document).on('click', '.btn-remove', function() {
        const index = $(this).data('index');
        cart.splice(index, 1);
        updateCartDisplay();
    });

    // Clear cart
    $('#btnClearCart').click(function() {
        if (cart.length === 0) return;
        
        Swal.fire({
            title: 'Clear Cart?',
            text: 'This will remove all items from the cart.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, clear it'
        }).then((result) => {
            if (result.isConfirmed) {
                cart = [];
                updateCartDisplay();
            }
        });
    });

    // Category filter
    $('.category-btn').click(function() {
        if (!drawerOpen) return;
        
        $('.category-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('active btn-primary');

        const category = $(this).data('category');
        
        if (category === 'all') {
            $('.product-card').show();
        } else {
            $('.product-card').hide();
            $(`.product-card[data-category="${category}"]`).show();
        }
    });

    // Search items
    $('#searchItems').on('keyup', function() {
        if (!drawerOpen) return;
        
        const search = $(this).val().toLowerCase();
        $('.product-card').each(function() {
            const name = $(this).data('name').toLowerCase();
            $(this).toggle(name.includes(search));
        });
    });

    // Pay with Cash
    $('#btnPayCash').click(function() {
        $('#paymentMethod').val('cash');
        $('#cashPaymentSection').show();
        $('#qrPaymentSection').hide();
        $('#amountReceived').val('');
        $('#changeSection').hide();
        new bootstrap.Modal($('#paymentModal')).show();
    });

    // Pay with QR
    $('#btnPayQR').click(function() {
        $('#paymentMethod').val('qr');
        $('#cashPaymentSection').hide();
        $('#qrPaymentSection').show();
        $('#referenceNumber').val('');
        new bootstrap.Modal($('#paymentModal')).show();
    });

    // Quick amount buttons
    $('.quick-amount').click(function() {
        const amount = $(this).data('amount');
        if (amount === 'exact') {
            $('#amountReceived').val(currentTotal.toFixed(2));
        } else {
            $('#amountReceived').val(amount);
        }
        calculateChange();
    });

    // Calculate change
    $('#amountReceived').on('input', calculateChange);

    function calculateChange() {
        const received = parseFloat($('#amountReceived').val()) || 0;
        const change = received - currentTotal;

        if (received >= currentTotal && received > 0) {
            $('#changeSection').show();
            $('#changeAmount').text('RM ' + change.toFixed(2));
            $('#btnCompletePayment').prop('disabled', false);
        } else {
            $('#changeSection').hide();
            $('#btnCompletePayment').prop('disabled', $('#paymentMethod').val() === 'cash');
        }
    }

    // Complete payment
    $('#btnCompletePayment').click(function() {
        const paymentMethod = $('#paymentMethod').val();
        const amountReceived = parseFloat($('#amountReceived').val()) || 0;

        if (paymentMethod === 'cash' && amountReceived < currentTotal) {
            Swal.fire({
                icon: 'error',
                title: 'Insufficient Amount',
                text: 'Amount received is less than the total.'
            });
            return;
        }

        // Prepare data
        const data = {
            _token: '{{ csrf_token() }}',
            items: cart.map(item => ({
                inventory_id: item.id,
                quantity: item.quantity,
                unit_price: item.price
            })),
            payment_method: paymentMethod,
            amount_received: amountReceived,
            change_amount: Math.max(0, amountReceived - currentTotal),
            reference_number: $('#referenceNumber').val(),
            notes: $('#transactionNotes').val()
        };

        // Disable button and show loading
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Processing...');

        $.ajax({
            url: '{{ route("staff.pos.sale") }}',
            method: 'POST',
            data: data,
            success: function(response) {
                bootstrap.Modal.getInstance($('#paymentModal')).hide();
                
                // Show receipt modal
                $('#receiptTransactionNumber').text(response.transaction.transaction_number);
                $('#btnViewReceipt').attr('href', response.receipt_url);
                $('#btnPrintReceipt').attr('href', response.receipt_url + '?print=1');
                
                new bootstrap.Modal($('#receiptModal')).show();
                
                // Clear cart
                cart = [];
                updateCartDisplay();
                
                // Reload page to refresh stats and stock
                setTimeout(() => {
                    location.reload();
                }, 2500);
            },
            error: function(xhr) {
                let message = 'An error occurred while processing the transaction.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-check me-2"></i> Complete Sale');
            }
        });
    });

    // Initial display
    updateCartDisplay();
});
</script>
@endpush
