@extends('layouts.app')

@section('title', 'Point of Sale')

@section('styles')
<style>
    .pos-container {
        display: flex;
        height: calc(100vh - 120px);
        gap: 1rem;
    }
    .products-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.75rem;
        overflow-y: auto;
        padding: 0.5rem;
    }
    .product-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #fff;
    }
    .product-card:hover {
        border-color: #FDA530;
        box-shadow: 0 4px 12px rgba(253, 165, 48, 0.15);
        transform: translateY(-2px);
    }
    .product-card.out-of-stock {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .product-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        margin-bottom: 0.5rem;
    }
    .product-placeholder {
        width: 80px;
        height: 80px;
        background: #f3f4f6;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
    }
    .product-name {
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .product-price {
        font-size: 0.9rem;
        font-weight: 600;
        color: #FDA530;
    }
    .product-stock {
        font-size: 0.75rem;
        color: #6b7280;
    }
    .cart-section {
        width: 380px;
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .cart-header {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 10px 10px 0 0;
    }
    .cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 0.5rem;
    }
    .cart-item {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        border-bottom: 1px solid #f3f4f6;
        gap: 0.75rem;
    }
    .cart-item-info {
        flex: 1;
        min-width: 0;
    }
    .cart-item-name {
        font-weight: 500;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cart-item-price {
        font-size: 0.85rem;
        color: #6b7280;
    }
    .cart-item-total {
        font-weight: 600;
        color: #FDA530;
    }
    .qty-control {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .qty-btn {
        width: 28px;
        height: 28px;
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;
    }
    .qty-btn:hover {
        background: #f3f4f6;
    }
    .qty-input {
        width: 40px;
        text-align: center;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 0.25rem;
    }
    .cart-footer {
        padding: 1rem;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 0 0 10px 10px;
    }
    .cart-totals {
        margin-bottom: 1rem;
    }
    .cart-total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    .cart-total-row.grand-total {
        font-size: 1.25rem;
        font-weight: 700;
        color: #FDA530;
        border-top: 2px solid #e5e7eb;
        padding-top: 0.5rem;
        margin-top: 0.5rem;
    }
    .payment-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    .payment-tab {
        flex: 1;
        padding: 0.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s;
    }
    .payment-tab.active {
        border-color: #FDA530;
        background: #FFF8ED;
    }
    .payment-tab i {
        font-size: 1.5rem;
        display: block;
        margin-bottom: 0.25rem;
    }
    .quick-cash-btns {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.5rem;
        margin-top: 0.5rem;
    }
    .quick-cash-btn {
        padding: 0.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: #fff;
        cursor: pointer;
        font-weight: 500;
    }
    .quick-cash-btn:hover {
        background: #FFF8ED;
        border-color: #FDA530;
    }
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .stat-card {
        background: #fff;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .stat-label {
        font-size: 0.8rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }
    .stat-value {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
    }
    /* Calculator */
    .calculator-widget {
        position: fixed;
        bottom: 20px;
        right: 420px;
        width: 240px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        z-index: 1000;
        display: none;
    }
    .calculator-widget.show {
        display: block;
    }
    .calc-header {
        padding: 0.75rem;
        background: #FDA530;
        color: #fff;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: move;
    }
    .calc-display {
        padding: 0.75rem;
        font-size: 1.5rem;
        text-align: right;
        background: #f3f4f6;
        border: none;
        width: 100%;
    }
    .calc-buttons {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 4px;
        padding: 8px;
    }
    .calc-btn {
        padding: 0.75rem;
        border: 1px solid #e2e8f0;
        background: #fff;
        cursor: pointer;
        font-size: 1.1rem;
        border-radius: 6px;
    }
    .calc-btn:hover {
        background: #f3f4f6;
    }
    .calc-btn.operator {
        background: #FFF8ED;
        color: #FDA530;
    }
    .calc-btn.equals {
        background: #FDA530;
        color: #fff;
    }
    .calc-toggle {
        position: fixed;
        bottom: 20px;
        right: 420px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #FDA530;
        color: #fff;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(253, 165, 48, 0.4);
        z-index: 999;
    }
    .cart-empty {
        text-align: center;
        padding: 2rem;
        color: #9ca3af;
    }
    .remove-item {
        color: #ef4444;
        cursor: pointer;
        padding: 0.25rem;
    }
    .remove-item:hover {
        color: #dc2626;
    }
    /* Drawer closed overlay */
    .drawer-closed-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .drawer-closed-content {
        background: #fff;
        padding: 2rem;
        border-radius: 12px;
        text-align: center;
        max-width: 400px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Stats Bar -->
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-label">Today's Sales</div>
            <div class="stat-value">RM {{ number_format($todayStats['total_sales'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Transactions</div>
            <div class="stat-value">{{ $todayStats['total_transactions'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Cash Sales</div>
            <div class="stat-value">RM {{ number_format($todayStats['cash_sales'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">QR Sales</div>
            <div class="stat-value">RM {{ number_format($todayStats['qr_sales'], 2) }}</div>
        </div>
    </div>

    <div class="pos-container">
        <!-- Products Section -->
        <div class="products-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2 flex-grow-1 me-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search products..." style="max-width: 250px;">
                    <select id="categoryFilter" class="form-select" style="max-width: 180px;">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.pos.transactions') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-history me-1"></i> History
                    </a>
                    @can('view-daily-cash-report')
                    <a href="{{ route('admin.pos.daily-reports.today') }}" class="btn btn-outline-primary">
                        <i class="fas fa-chart-line me-1"></i> Daily Report
                    </a>
                    @endcan
                </div>
            </div>

            <div class="products-grid" id="productsGrid">
                @foreach($items as $item)
                    <div class="product-card {{ $item->current_stock <= 0 ? 'out-of-stock' : '' }}"
                         data-id="{{ $item->id }}"
                         data-name="{{ $item->name }}"
                         data-price="{{ $item->selling_price }}"
                         data-stock="{{ $item->current_stock }}"
                         data-image="{{ $item->image ? asset('storage/' . $item->image) : '' }}"
                         onclick="addToCart(this)">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="product-image">
                        @else
                            <div class="product-placeholder">
                                <i class="fas fa-box fa-2x text-muted"></i>
                            </div>
                        @endif
                        <div class="product-name" title="{{ $item->name }}">{{ $item->name }}</div>
                        <div class="product-price">RM {{ number_format($item->selling_price, 2) }}</div>
                        <div class="product-stock">
                            @if($item->current_stock <= 0)
                                <span class="text-danger">Out of Stock</span>
                            @elseif($item->current_stock <= $item->reorder_level)
                                <span class="text-warning">{{ $item->current_stock }} left</span>
                            @else
                                <span>{{ $item->current_stock }} in stock</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Cart Section -->
        <div class="cart-section">
            <div class="cart-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Current Sale</h5>
                    <button class="btn btn-sm btn-outline-danger" onclick="clearCart()" id="clearCartBtn" style="display: none;">
                        <i class="fas fa-trash"></i> Clear
                    </button>
                </div>
            </div>

            <div class="cart-items" id="cartItems">
                <div class="cart-empty" id="cartEmpty">
                    <i class="fas fa-shopping-basket fa-3x mb-3"></i>
                    <p>Cart is empty</p>
                    <small class="text-muted">Click on products to add them</small>
                </div>
            </div>

            <div class="cart-footer">
                <div class="cart-totals">
                    <div class="cart-total-row">
                        <span>Subtotal</span>
                        <span id="subtotalAmount">RM 0.00</span>
                    </div>
                    <div class="cart-total-row">
                        <div class="d-flex align-items-center gap-2">
                            <span>Discount</span>
                            <input type="number" id="discountInput" class="form-control form-control-sm" style="width: 80px;" value="0" min="0" step="0.01" onchange="updateTotals()">
                        </div>
                        <span id="discountAmount">- RM 0.00</span>
                    </div>
                    <div class="cart-total-row grand-total">
                        <span>Total</span>
                        <span id="totalAmount">RM 0.00</span>
                    </div>
                </div>

                <!-- Payment Method Tabs -->
                <div class="payment-tabs">
                    <div class="payment-tab active" data-method="cash" onclick="selectPaymentMethod('cash')">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Cash</span>
                    </div>
                    <div class="payment-tab" data-method="qr" onclick="selectPaymentMethod('qr')">
                        <i class="fas fa-qrcode"></i>
                        <span>QR Pay</span>
                    </div>
                </div>

                <!-- Cash Payment -->
                <div id="cashPayment">
                    <div class="mb-2">
                        <label class="form-label small">Amount Received</label>
                        <input type="number" id="amountReceived" class="form-control" placeholder="0.00" step="0.01" min="0" onchange="calculateChange()">
                    </div>
                    <div class="quick-cash-btns">
                        <button class="quick-cash-btn" onclick="setQuickCash(10)">RM10</button>
                        <button class="quick-cash-btn" onclick="setQuickCash(20)">RM20</button>
                        <button class="quick-cash-btn" onclick="setQuickCash(50)">RM50</button>
                        <button class="quick-cash-btn" onclick="setQuickCash(100)">RM100</button>
                    </div>
                    <div class="d-flex justify-content-between mt-2 mb-3">
                        <span>Change:</span>
                        <span class="fw-bold" id="changeAmount">RM 0.00</span>
                    </div>
                </div>

                <!-- QR Payment -->
                <div id="qrPayment" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label small">Reference Number</label>
                        <input type="text" id="referenceNumber" class="form-control" placeholder="Enter reference number">
                    </div>
                    <div class="text-center mb-3">
                        <img src="{{ asset('images/qr-payment.png') }}" alt="QR Code" class="img-fluid" style="max-width: 150px; display: none;" id="qrImage">
                        <p class="small text-muted mt-2">Scan QR code or enter reference manually</p>
                    </div>
                </div>

                <div class="mb-2">
                    <input type="text" id="transactionNotes" class="form-control form-control-sm" placeholder="Notes (optional)">
                </div>

                <button class="btn btn-lg w-100" style="background-color: #FDA530; color: #fff;" onclick="processPayment()" id="payBtn" disabled>
                    <i class="fas fa-check-circle me-2"></i>Complete Sale
                </button>
            </div>
        </div>
    </div>

    <!-- Calculator Toggle Button -->
    <button class="calc-toggle" onclick="toggleCalculator()" title="Calculator">
        <i class="fas fa-calculator"></i>
    </button>

    <!-- Calculator Widget -->
    <div class="calculator-widget" id="calculator">
        <div class="calc-header">
            <span><i class="fas fa-calculator me-2"></i>Calculator</span>
            <button class="btn btn-sm text-white" onclick="toggleCalculator()"><i class="fas fa-times"></i></button>
        </div>
        <input type="text" class="calc-display" id="calcDisplay" readonly value="0">
        <div class="calc-buttons">
            <button class="calc-btn" onclick="calcInput('7')">7</button>
            <button class="calc-btn" onclick="calcInput('8')">8</button>
            <button class="calc-btn" onclick="calcInput('9')">9</button>
            <button class="calc-btn operator" onclick="calcInput('/')">÷</button>
            <button class="calc-btn" onclick="calcInput('4')">4</button>
            <button class="calc-btn" onclick="calcInput('5')">5</button>
            <button class="calc-btn" onclick="calcInput('6')">6</button>
            <button class="calc-btn operator" onclick="calcInput('*')">×</button>
            <button class="calc-btn" onclick="calcInput('1')">1</button>
            <button class="calc-btn" onclick="calcInput('2')">2</button>
            <button class="calc-btn" onclick="calcInput('3')">3</button>
            <button class="calc-btn operator" onclick="calcInput('-')">-</button>
            <button class="calc-btn" onclick="calcInput('0')">0</button>
            <button class="calc-btn" onclick="calcInput('.')">.</button>
            <button class="calc-btn" onclick="calcClear()">C</button>
            <button class="calc-btn operator" onclick="calcInput('+')">+</button>
            <button class="calc-btn equals" style="grid-column: span 4;" onclick="calcEquals()">=</button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                </div>
                <h4 class="mb-3">Transaction Complete!</h4>
                <p class="text-muted mb-2">Transaction #: <strong id="successTransactionNo"></strong></p>
                <p class="text-muted mb-4">Total: <strong id="successTotal"></strong></p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="#" id="viewReceiptLink" class="btn btn-outline-primary">
                        <i class="fas fa-receipt me-1"></i> View Receipt
                    </a>
                    <button class="btn btn-primary" onclick="printReceipt()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                    <button class="btn btn-success" onclick="newTransaction()">
                        <i class="fas fa-plus me-1"></i> New Sale
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$drawerOpen)
<!-- Drawer Closed Overlay -->
<div class="drawer-closed-overlay" id="drawerOverlay">
    <div class="drawer-closed-content">
        <i class="fas fa-cash-register fa-4x text-warning mb-3"></i>
        <h4>Cash Drawer Not Open</h4>
        <p class="text-muted mb-4">Please open the cash drawer to start making sales.</p>
        <a href="{{ route('admin.pos.daily-reports.open-drawer') }}" class="btn btn-primary btn-lg">
            <i class="fas fa-unlock me-2"></i> Open Drawer
        </a>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
let cart = [];
let paymentMethod = 'cash';
let lastTransaction = null;

// Add to cart
function addToCart(element) {
    if (element.classList.contains('out-of-stock')) {
        return;
    }

    const id = parseInt(element.dataset.id);
    const name = element.dataset.name;
    const price = parseFloat(element.dataset.price);
    const stock = parseInt(element.dataset.stock);

    const existingItem = cart.find(item => item.id === id);

    if (existingItem) {
        if (existingItem.quantity >= stock) {
            toastr.warning('Cannot add more. Stock limit reached.');
            return;
        }
        existingItem.quantity++;
    } else {
        cart.push({
            id: id,
            name: name,
            price: price,
            stock: stock,
            quantity: 1
        });
    }

    renderCart();
    updateTotals();
}

// Render cart
function renderCart() {
    const cartItems = document.getElementById('cartItems');
    const cartEmpty = document.getElementById('cartEmpty');
    const clearBtn = document.getElementById('clearCartBtn');

    if (cart.length === 0) {
        cartEmpty.style.display = 'block';
        clearBtn.style.display = 'none';
        cartItems.innerHTML = cartEmpty.outerHTML;
        return;
    }

    cartEmpty.style.display = 'none';
    clearBtn.style.display = 'block';

    let html = '';
    cart.forEach((item, index) => {
        html += `
            <div class="cart-item">
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">RM ${item.price.toFixed(2)} each</div>
                </div>
                <div class="qty-control">
                    <button class="qty-btn" onclick="updateQuantity(${index}, -1)">-</button>
                    <input type="number" class="qty-input" value="${item.quantity}" min="1" max="${item.stock}" onchange="setQuantity(${index}, this.value)">
                    <button class="qty-btn" onclick="updateQuantity(${index}, 1)">+</button>
                </div>
                <div class="cart-item-total">RM ${(item.price * item.quantity).toFixed(2)}</div>
                <span class="remove-item" onclick="removeItem(${index})"><i class="fas fa-times"></i></span>
            </div>
        `;
    });

    cartItems.innerHTML = html;
}

// Update quantity
function updateQuantity(index, delta) {
    const item = cart[index];
    const newQty = item.quantity + delta;

    if (newQty <= 0) {
        removeItem(index);
        return;
    }

    if (newQty > item.stock) {
        toastr.warning('Cannot exceed available stock.');
        return;
    }

    item.quantity = newQty;
    renderCart();
    updateTotals();
}

// Set quantity directly
function setQuantity(index, value) {
    const item = cart[index];
    let qty = parseInt(value) || 1;

    if (qty <= 0) {
        removeItem(index);
        return;
    }

    if (qty > item.stock) {
        qty = item.stock;
        toastr.warning('Quantity adjusted to available stock.');
    }

    item.quantity = qty;
    renderCart();
    updateTotals();
}

// Remove item
function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
    updateTotals();
}

// Clear cart
function clearCart() {
    if (confirm('Clear all items from cart?')) {
        cart = [];
        renderCart();
        updateTotals();
    }
}

// Update totals
function updateTotals() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const total = Math.max(0, subtotal - discount);

    document.getElementById('subtotalAmount').textContent = 'RM ' + subtotal.toFixed(2);
    document.getElementById('discountAmount').textContent = '- RM ' + discount.toFixed(2);
    document.getElementById('totalAmount').textContent = 'RM ' + total.toFixed(2);

    document.getElementById('payBtn').disabled = cart.length === 0;

    calculateChange();
}

// Select payment method
function selectPaymentMethod(method) {
    paymentMethod = method;

    document.querySelectorAll('.payment-tab').forEach(tab => {
        tab.classList.toggle('active', tab.dataset.method === method);
    });

    document.getElementById('cashPayment').style.display = method === 'cash' ? 'block' : 'none';
    document.getElementById('qrPayment').style.display = method === 'qr' ? 'block' : 'none';
}

// Set quick cash
function setQuickCash(amount) {
    document.getElementById('amountReceived').value = amount;
    calculateChange();
}

// Calculate change
function calculateChange() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const total = Math.max(0, subtotal - discount);
    const received = parseFloat(document.getElementById('amountReceived').value) || 0;
    const change = received - total;

    document.getElementById('changeAmount').textContent = 'RM ' + (change >= 0 ? change.toFixed(2) : '0.00');
    document.getElementById('changeAmount').classList.toggle('text-danger', change < 0);
}

// Process payment
function processPayment() {
    if (cart.length === 0) {
        toastr.error('Cart is empty');
        return;
    }

    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const total = Math.max(0, subtotal - discount);

    // Validate payment
    if (paymentMethod === 'cash') {
        const received = parseFloat(document.getElementById('amountReceived').value) || 0;
        if (received < total) {
            toastr.error('Amount received is less than total');
            return;
        }
    } else {
        const reference = document.getElementById('referenceNumber').value.trim();
        if (!reference) {
            toastr.error('Please enter reference number');
            return;
        }
    }

    // Prepare data
    const data = {
        items: cart.map(item => ({
            inventory_id: item.id,
            quantity: item.quantity,
            unit_price: item.price
        })),
        payment_method: paymentMethod,
        discount: discount,
        notes: document.getElementById('transactionNotes').value,
        _token: '{{ csrf_token() }}'
    };

    if (paymentMethod === 'cash') {
        data.amount_received = parseFloat(document.getElementById('amountReceived').value);
        data.change_amount = data.amount_received - total;
    } else {
        data.reference_number = document.getElementById('referenceNumber').value;
        data.amount_received = total;
        data.change_amount = 0;
    }

    // Send request
    document.getElementById('payBtn').disabled = true;
    document.getElementById('payBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

    $.ajax({
        url: '{{ route("admin.pos.process-sale") }}',
        method: 'POST',
        data: data,
        success: function(response) {
            if (response.success) {
                lastTransaction = response.transaction;

                document.getElementById('successTransactionNo').textContent = response.transaction.transaction_number;
                document.getElementById('successTotal').textContent = 'RM ' + parseFloat(response.transaction.total_amount).toFixed(2);
                document.getElementById('viewReceiptLink').href = response.receipt_url;

                new bootstrap.Modal(document.getElementById('successModal')).show();

                // Reset form
                cart = [];
                renderCart();
                updateTotals();
                document.getElementById('amountReceived').value = '';
                document.getElementById('referenceNumber').value = '';
                document.getElementById('transactionNotes').value = '';
                document.getElementById('discountInput').value = 0;

                // Refresh stats
                refreshStats();
            }
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.message || 'An error occurred';
            toastr.error(msg);
        },
        complete: function() {
            document.getElementById('payBtn').disabled = false;
            document.getElementById('payBtn').innerHTML = '<i class="fas fa-check-circle me-2"></i>Complete Sale';
        }
    });
}

// Refresh stats
function refreshStats() {
    $.get('{{ route("admin.pos.today-summary") }}', function(response) {
        if (response.success) {
            // Update stats display
            const stats = response.stats;
            document.querySelectorAll('.stat-card')[0].querySelector('.stat-value').textContent = 'RM ' + parseFloat(stats.total_sales).toFixed(2);
            document.querySelectorAll('.stat-card')[1].querySelector('.stat-value').textContent = stats.total_transactions;
            document.querySelectorAll('.stat-card')[2].querySelector('.stat-value').textContent = 'RM ' + parseFloat(stats.cash_sales).toFixed(2);
            document.querySelectorAll('.stat-card')[3].querySelector('.stat-value').textContent = 'RM ' + parseFloat(stats.qr_sales).toFixed(2);
        }
    });
}

// Print receipt
function printReceipt() {
    if (lastTransaction) {
        window.open('{{ url("admin/pos") }}/' + lastTransaction.id + '/print', '_blank');
    }
}

// New transaction
function newTransaction() {
    bootstrap.Modal.getInstance(document.getElementById('successModal')).hide();
    lastTransaction = null;
}

// Search products
document.getElementById('searchInput').addEventListener('input', debounce(function() {
    filterProducts();
}, 300));

document.getElementById('categoryFilter').addEventListener('change', function() {
    filterProducts();
});

function filterProducts() {
    const search = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;

    $.get('{{ route("admin.pos.search-items") }}', { search: search, category_id: category }, function(response) {
        if (response.success) {
            renderProducts(response.items);
        }
    });
}

function renderProducts(items) {
    const grid = document.getElementById('productsGrid');
    let html = '';

    items.forEach(item => {
        const outOfStock = item.stock <= 0;
        html += `
            <div class="product-card ${outOfStock ? 'out-of-stock' : ''}"
                 data-id="${item.id}"
                 data-name="${item.name}"
                 data-price="${item.price}"
                 data-stock="${item.stock}"
                 onclick="addToCart(this)">
                ${item.image ?
                    `<img src="${item.image}" alt="${item.name}" class="product-image">` :
                    `<div class="product-placeholder"><i class="fas fa-box fa-2x text-muted"></i></div>`
                }
                <div class="product-name" title="${item.name}">${item.name}</div>
                <div class="product-price">RM ${parseFloat(item.price).toFixed(2)}</div>
                <div class="product-stock">
                    ${outOfStock ? '<span class="text-danger">Out of Stock</span>' : item.stock + ' in stock'}
                </div>
            </div>
        `;
    });

    grid.innerHTML = html || '<div class="text-center text-muted p-5">No products found</div>';
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Calculator functions
let calcValue = '0';

function toggleCalculator() {
    document.getElementById('calculator').classList.toggle('show');
    document.querySelector('.calc-toggle').style.display =
        document.getElementById('calculator').classList.contains('show') ? 'none' : 'block';
}

function calcInput(val) {
    if (calcValue === '0' && val !== '.') {
        calcValue = val;
    } else {
        calcValue += val;
    }
    document.getElementById('calcDisplay').value = calcValue;
}

function calcClear() {
    calcValue = '0';
    document.getElementById('calcDisplay').value = '0';
}

function calcEquals() {
    try {
        calcValue = eval(calcValue).toString();
        document.getElementById('calcDisplay').value = calcValue;
    } catch(e) {
        document.getElementById('calcDisplay').value = 'Error';
        calcValue = '0';
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    renderCart();
    updateTotals();
});
</script>
@endsection
