@extends('layouts.app')

@section('title', 'Point of Sale')

@section('styles')
<style>
    .product-card {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        height: 140px;
    }
    .product-card:hover {
        border-color: #0d6efd;
        transform: translateY(-2px);
    }
    .product-card.out-of-stock {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .product-card .product-image {
        height: 60px;
        object-fit: contain;
    }
    .cart-item {
        border-bottom: 1px solid #eee;
        padding: 10px 0;
    }
    .cart-item:last-child {
        border-bottom: none;
    }
    .qty-btn {
        width: 28px;
        height: 28px;
        padding: 0;
        font-size: 14px;
    }
    .payment-method-btn {
        border: 2px solid #dee2e6;
        padding: 15px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .payment-method-btn:hover,
    .payment-method-btn.active {
        border-color: #0d6efd;
        background: #e7f1ff;
    }
    .calculator-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1050;
        width: 280px;
        display: none;
    }
    .calculator-widget.show {
        display: block;
    }
    .calc-btn {
        width: 100%;
        height: 45px;
        font-size: 18px;
        font-weight: bold;
    }
    .stock-badge {
        position: absolute;
        top: 5px;
        right: 5px;
        font-size: 10px;
    }
    .category-filter .btn {
        border-radius: 20px;
    }
    .cart-sidebar {
        background: #f8f9fa;
        min-height: calc(100vh - 200px);
    }
    @media print {
        .no-print { display: none !important; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    @if(!$drawerOpen)
        <!-- Drawer Not Open Alert -->
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-cash-register fa-4x text-warning mb-4"></i>
                        <h4>Cash Drawer Not Open</h4>
                        <p class="text-muted mb-4">
                            The cash drawer needs to be opened before you can process sales.
                            Please contact your administrator to open the drawer.
                        </p>
                        <a href="{{ route('staff.dashboard') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <div>
                <h4 class="mb-0">Point of Sale</h4>
                <small class="text-muted">{{ now()->format('l, d M Y') }}</small>
            </div>
            <div>
                <a href="{{ route('staff.pos.my-transactions') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-history me-1"></i> My Transactions
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm ms-1" id="toggleCalculator">
                    <i class="fas fa-calculator"></i>
                </button>
            </div>
        </div>

        <!-- Today's Stats -->
        <div class="row g-2 mb-3 no-print">
            <div class="col-md-3">
                <div class="card border-0 bg-primary text-white h-100">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="opacity-75">My Sales Today</small>
                                <h5 class="mb-0">RM <span id="myTodaySales">{{ number_format($myStats['total_sales'] ?? 0, 2) }}</span></h5>
                            </div>
                            <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-success text-white h-100">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="opacity-75">My Transactions</small>
                                <h5 class="mb-0"><span id="myTodayTransactions">{{ $myStats['transaction_count'] ?? 0 }}</span></h5>
                            </div>
                            <i class="fas fa-receipt fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-info text-white h-100">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="opacity-75">Cash Sales</small>
                                <h5 class="mb-0">RM {{ number_format($myStats['cash_sales'] ?? 0, 2) }}</h5>
                            </div>
                            <i class="fas fa-money-bill fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-warning text-dark h-100">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="opacity-75">QR Sales</small>
                                <h5 class="mb-0">RM {{ number_format($myStats['qr_sales'] ?? 0, 2) }}</h5>
                            </div>
                            <i class="fas fa-qrcode fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row no-print">
            <!-- Products Section -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="productSearch" placeholder="Search products...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="category-filter d-flex flex-wrap gap-1">
                                    <button class="btn btn-sm btn-primary category-btn active" data-category="all">All</button>
                                    @foreach($categories as $category)
                                        <button class="btn btn-sm btn-outline-secondary category-btn"
                                                data-category="{{ $category->id }}">
                                            {{ $category->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="max-height: 60vh; overflow-y: auto;">
                        <div class="row g-2" id="productsGrid">
                            @foreach($items as $item)
                                <div class="col-6 col-md-4 col-lg-3 product-item"
                                     data-category="{{ $item->category_id }}"
                                     data-name="{{ strtolower($item->name) }}">
                                    <div class="card product-card h-100 text-center p-2 {{ $item->current_stock <= 0 ? 'out-of-stock' : '' }}"
                                         data-id="{{ $item->id }}"
                                         data-name="{{ $item->name }}"
                                         data-price="{{ $item->selling_price }}"
                                         data-stock="{{ $item->current_stock }}">
                                        @if($item->current_stock <= 5 && $item->current_stock > 0)
                                            <span class="stock-badge badge bg-warning">Low</span>
                                        @elseif($item->current_stock <= 0)
                                            <span class="stock-badge badge bg-danger">Out</span>
                                        @endif
                                        @if($item->image)
                                            <img src="{{ asset('storage/' . $item->image) }}" class="product-image mx-auto" alt="{{ $item->name }}">
                                        @else
                                            <div class="product-image d-flex align-items-center justify-content-center">
                                                <i class="fas fa-box fa-2x text-muted"></i>
                                            </div>
                                        @endif
                                        <div class="mt-1">
                                            <small class="d-block text-truncate" title="{{ $item->name }}">{{ $item->name }}</small>
                                            <strong class="text-primary">RM {{ number_format($item->selling_price, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Section -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm cart-sidebar">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Cart</h5>
                            <button class="btn btn-sm btn-outline-danger" id="clearCart" disabled>
                                <i class="fas fa-trash"></i> Clear
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3" style="max-height: 300px; overflow-y: auto;">
                        <div id="cartItems">
                            <div class="text-center text-muted py-4" id="emptyCart">
                                <i class="fas fa-shopping-basket fa-3x mb-2"></i>
                                <p class="mb-0">Cart is empty</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Footer -->
                    <div class="card-footer bg-white">
                        <!-- Totals -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Subtotal:</span>
                                <span id="subtotal">RM 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Discount:</span>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <input type="number" class="form-control form-control-sm" id="discountAmount"
                                           value="0" min="0" step="0.01">
                                    <span class="input-group-text">RM</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between border-top pt-2">
                                <strong>Total:</strong>
                                <strong class="text-primary fs-4" id="grandTotal">RM 0.00</strong>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Payment Method</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="payment-method-btn active text-center" data-method="cash">
                                        <i class="fas fa-money-bill-wave fa-2x text-success mb-1"></i>
                                        <div>Cash</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="payment-method-btn text-center" data-method="qr">
                                        <i class="fas fa-qrcode fa-2x text-primary mb-1"></i>
                                        <div>QR</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cash Payment Fields -->
                        <div id="cashFields">
                            <div class="mb-2">
                                <label class="form-label small">Amount Received</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" class="form-control" id="amountReceived"
                                           step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @foreach([10, 20, 50, 100] as $amount)
                                    <button type="button" class="btn btn-sm btn-outline-secondary quick-cash"
                                            data-amount="{{ $amount }}">RM{{ $amount }}</button>
                                @endforeach
                                <button type="button" class="btn btn-sm btn-outline-primary" id="exactAmount">Exact</button>
                            </div>
                            <div class="alert alert-info py-2 mb-0">
                                <div class="d-flex justify-content-between">
                                    <span>Change:</span>
                                    <strong id="changeAmount">RM 0.00</strong>
                                </div>
                            </div>
                        </div>

                        <!-- QR Payment Fields -->
                        <div id="qrFields" style="display: none;">
                            <div class="mb-2">
                                <label class="form-label small">Reference Number</label>
                                <input type="text" class="form-control" id="qrReference" placeholder="Enter reference...">
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3 mt-3">
                            <input type="text" class="form-control form-control-sm" id="transactionNotes"
                                   placeholder="Add notes (optional)">
                        </div>

                        <!-- Process Button -->
                        <button class="btn btn-success btn-lg w-100" id="processPayment" disabled>
                            <i class="fas fa-check-circle me-2"></i>Process Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calculator Widget -->
        <div class="card calculator-widget shadow-lg" id="calculatorWidget">
            <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center"
                 style="cursor: move;" id="calcHeader">
                <span><i class="fas fa-calculator me-1"></i> Calculator</span>
                <button type="button" class="btn-close btn-close-white btn-sm" id="closeCalculator"></button>
            </div>
            <div class="card-body p-2">
                <input type="text" class="form-control form-control-lg mb-2 text-end" id="calcDisplay" readonly>
                <div class="row g-1">
                    <div class="col-3"><button class="btn btn-outline-secondary calc-btn" data-val="7">7</button></div>
                    <div class="col-3"><button class="btn btn-outline-secondary calc-btn" data-val="8">8</button></div>
                    <div class="col-3"><button class="btn btn-outline-secondary calc-btn" data-val="9">9</button></div>
                    <div class="col-3"><button class="btn btn-warning calc-btn" data-val="/">÷</button></div>
                    <div class="col-3"><button class="btn btn-outline-secondary calc-btn" data-val="4">4</button></div>
                    <div class="col-3"><button class="btn btn-outline-secondary calc-btn" data-val="5">5</button></div>
                    <div class="col-3"><button class="btn btn-outline-secondary calc-btn" data-val="6">6</button></div>
                    <div class="col-3"><button class="btn btn-warning calc-btn" data-val="*">×</button></div>
                    <div class="col-3"><button class="btn btn-outline-secondary calc-btn" data-val="1">1</button></div>
                    <div class="col-3"><button class="btn btn-outline-secondary calc-btn" data-val="2">2</button></div>
                    <div class="col-3"><button class="btn btn-outline-secondary calc-btn" data-val="3">3</button></div>
                    <div class="col-3"><button class="btn btn-warning calc-btn" data-val="-">−</button></div>
                    <div class="col-3"><button class="btn btn-outline-secondary calc-btn" data-val="0">0</button></div>
                    <div class="col-3"><button class="btn btn-outline-secondary calc-btn" data-val=".">.</button></div>
                    <div class="col-3"><button class="btn btn-danger calc-btn" id="calcClear">C</button></div>
                    <div class="col-3"><button class="btn btn-warning calc-btn" data-val="+">+</button></div>
                    <div class="col-12"><button class="btn btn-success calc-btn" id="calcEquals">=</button></div>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle fa-5x text-success"></i>
                        </div>
                        <h4 class="mb-3">Sale Completed!</h4>
                        <p class="text-muted mb-1">Transaction: <strong id="successTransactionNo"></strong></p>
                        <p class="text-muted mb-3">Total: <strong id="successTotal"></strong></p>
                        <p id="successChange" class="mb-4"></p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="#" id="viewReceiptBtn" class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-receipt me-1"></i> View Receipt
                            </a>
                            <button type="button" class="btn btn-primary" id="newSaleBtn">
                                <i class="fas fa-plus me-1"></i> New Sale
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let cart = [];
    let selectedPaymentMethod = 'cash';

    // Add to cart
    $(document).on('click', '.product-card:not(.out-of-stock)', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const price = parseFloat($(this).data('price'));
        const stock = parseInt($(this).data('stock'));

        const existingItem = cart.find(item => item.id === id);

        if (existingItem) {
            if (existingItem.quantity < stock) {
                existingItem.quantity++;
            } else {
                alert('Maximum stock reached');
                return;
            }
        } else {
            cart.push({ id, name, price, quantity: 1, maxStock: stock });
        }

        updateCartDisplay();
    });

    // Update cart display
    function updateCartDisplay() {
        const $cartItems = $('#cartItems');
        const $emptyCart = $('#emptyCart');

        if (cart.length === 0) {
            $cartItems.html($emptyCart.clone().show());
            $('#clearCart, #processPayment').prop('disabled', true);
            $('#subtotal').text('RM 0.00');
            $('#grandTotal').text('RM 0.00');
            return;
        }

        $emptyCart.hide();
        let html = '';
        let subtotal = 0;

        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;

            html += `
                <div class="cart-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <strong class="d-block">${item.name}</strong>
                            <small class="text-muted">RM ${item.price.toFixed(2)} × ${item.quantity}</small>
                        </div>
                        <strong>RM ${itemTotal.toFixed(2)}</strong>
                    </div>
                    <div class="d-flex align-items-center mt-1">
                        <button class="btn btn-sm btn-outline-secondary qty-btn decrease-qty" data-index="${index}">-</button>
                        <span class="mx-2">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary qty-btn increase-qty" data-index="${index}"
                                ${item.quantity >= item.maxStock ? 'disabled' : ''}>+</button>
                        <button class="btn btn-sm btn-link text-danger ms-auto remove-item" data-index="${index}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        $cartItems.html(html);

        const discount = parseFloat($('#discountAmount').val()) || 0;
        const grandTotal = Math.max(0, subtotal - discount);

        $('#subtotal').text('RM ' + subtotal.toFixed(2));
        $('#grandTotal').text('RM ' + grandTotal.toFixed(2));
        $('#clearCart').prop('disabled', false);

        updatePaymentValidation();
    }

    // Quantity controls
    $(document).on('click', '.decrease-qty', function() {
        const index = $(this).data('index');
        if (cart[index].quantity > 1) {
            cart[index].quantity--;
        } else {
            cart.splice(index, 1);
        }
        updateCartDisplay();
    });

    $(document).on('click', '.increase-qty', function() {
        const index = $(this).data('index');
        if (cart[index].quantity < cart[index].maxStock) {
            cart[index].quantity++;
        }
        updateCartDisplay();
    });

    $(document).on('click', '.remove-item', function() {
        const index = $(this).data('index');
        cart.splice(index, 1);
        updateCartDisplay();
    });

    // Clear cart
    $('#clearCart').click(function() {
        if (confirm('Clear all items from cart?')) {
            cart = [];
            updateCartDisplay();
        }
    });

    // Discount change
    $('#discountAmount').on('input', function() {
        updateCartDisplay();
    });

    // Payment method selection
    $('.payment-method-btn').click(function() {
        $('.payment-method-btn').removeClass('active');
        $(this).addClass('active');
        selectedPaymentMethod = $(this).data('method');

        if (selectedPaymentMethod === 'cash') {
            $('#cashFields').show();
            $('#qrFields').hide();
        } else {
            $('#cashFields').hide();
            $('#qrFields').show();
        }

        updatePaymentValidation();
    });

    // Quick cash buttons
    $('.quick-cash').click(function() {
        $('#amountReceived').val($(this).data('amount')).trigger('input');
    });

    $('#exactAmount').click(function() {
        const total = parseFloat($('#grandTotal').text().replace('RM ', ''));
        $('#amountReceived').val(total.toFixed(2)).trigger('input');
    });

    // Calculate change
    $('#amountReceived').on('input', function() {
        const total = parseFloat($('#grandTotal').text().replace('RM ', ''));
        const received = parseFloat($(this).val()) || 0;
        const change = received - total;
        $('#changeAmount').text('RM ' + Math.max(0, change).toFixed(2));
        updatePaymentValidation();
    });

    // Validate payment
    function updatePaymentValidation() {
        const total = parseFloat($('#grandTotal').text().replace('RM ', ''));
        let valid = cart.length > 0 && total > 0;

        if (selectedPaymentMethod === 'cash') {
            const received = parseFloat($('#amountReceived').val()) || 0;
            valid = valid && received >= total;
        }

        $('#processPayment').prop('disabled', !valid);
    }

    // Process payment
    $('#processPayment').click(function() {
        const $btn = $(this);
        const total = parseFloat($('#grandTotal').text().replace('RM ', ''));

        if (!confirm('Process this sale for RM ' + total.toFixed(2) + '?')) return;

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');

        const data = {
            _token: '{{ csrf_token() }}',
            items: cart.map(item => ({
                inventory_id: item.id,
                quantity: item.quantity,
                unit_price: item.price
            })),
            payment_method: selectedPaymentMethod,
            discount_amount: parseFloat($('#discountAmount').val()) || 0,
            notes: $('#transactionNotes').val()
        };

        if (selectedPaymentMethod === 'cash') {
            data.amount_received = parseFloat($('#amountReceived').val()) || 0;
        } else {
            data.qr_reference = $('#qrReference').val();
        }

        $.ajax({
            url: '{{ route("staff.pos.process-sale") }}',
            method: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    $('#successTransactionNo').text(response.transaction_number);
                    $('#successTotal').text('RM ' + response.total.toFixed(2));

                    if (response.change > 0) {
                        $('#successChange').html('Change: <strong class="text-success">RM ' + response.change.toFixed(2) + '</strong>');
                    } else {
                        $('#successChange').empty();
                    }

                    $('#viewReceiptBtn').attr('href', response.receipt_url);
                    $('#successModal').modal('show');

                    // Update stats
                    $('#myTodaySales').text((parseFloat($('#myTodaySales').text()) + response.total).toFixed(2));
                    $('#myTodayTransactions').text(parseInt($('#myTodayTransactions').text()) + 1);
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Error processing sale');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Process Sale');
            }
        });
    });

    // New sale
    $('#newSaleBtn').click(function() {
        cart = [];
        updateCartDisplay();
        $('#discountAmount').val(0);
        $('#amountReceived').val('');
        $('#qrReference').val('');
        $('#transactionNotes').val('');
        $('#changeAmount').text('RM 0.00');
        $('#successModal').modal('hide');
    });

    // Product search
    $('#productSearch').on('input', function() {
        const query = $(this).val().toLowerCase();
        $('.product-item').each(function() {
            const name = $(this).data('name');
            $(this).toggle(name.includes(query));
        });
    });

    // Category filter
    $('.category-btn').click(function() {
        $('.category-btn').removeClass('active btn-primary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('active btn-primary');

        const category = $(this).data('category');
        if (category === 'all') {
            $('.product-item').show();
        } else {
            $('.product-item').each(function() {
                $(this).toggle($(this).data('category') == category);
            });
        }
    });

    // Calculator
    $('#toggleCalculator').click(function() {
        $('#calculatorWidget').toggleClass('show');
    });

    $('#closeCalculator').click(function() {
        $('#calculatorWidget').removeClass('show');
    });

    let calcValue = '';
    $('.calc-btn[data-val]').click(function() {
        calcValue += $(this).data('val');
        $('#calcDisplay').val(calcValue);
    });

    $('#calcClear').click(function() {
        calcValue = '';
        $('#calcDisplay').val('');
    });

    $('#calcEquals').click(function() {
        try {
            calcValue = eval(calcValue).toString();
            $('#calcDisplay').val(parseFloat(calcValue).toFixed(2));
        } catch (e) {
            $('#calcDisplay').val('Error');
            calcValue = '';
        }
    });
});
</script>
@endpush
