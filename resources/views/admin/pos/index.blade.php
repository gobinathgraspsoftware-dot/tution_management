@extends('layouts.app')

@section('title', 'POS Terminal')

@section('styles')
<style>
    /* Drawer Not Open Overlay */
    .drawer-not-open-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.85);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .drawer-not-open-box {
        background: #fff;
        padding: 50px 60px;
        border-radius: 20px;
        text-align: center;
        max-width: 450px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .drawer-not-open-box .icon-wrapper {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
    }

    .drawer-not-open-box .icon-wrapper i {
        font-size: 50px;
        color: #fff;
    }

    .drawer-not-open-box h3 {
        margin-bottom: 15px;
        color: #333;
        font-weight: 700;
    }

    .drawer-not-open-box p {
        color: #666;
        margin-bottom: 30px;
        font-size: 16px;
    }

    /* POS Container */
    .pos-container {
        display: flex;
        height: calc(100vh - 180px);
        gap: 20px;
    }

    /* Products Section */
    .products-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .products-header {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        background: #f8f9fa;
    }

    .products-grid {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 15px;
        align-content: start;
    }

    .product-card {
        background: #fff;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .product-card:hover {
        border-color: #0d6efd;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }

    .product-card.out-of-stock {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .product-card .product-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        color: #fff;
        font-size: 28px;
    }

    .product-card .product-name {
        font-size: 13px;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
        line-height: 1.3;
        height: 34px;
        overflow: hidden;
    }

    .product-card .product-price {
        font-size: 15px;
        font-weight: 700;
        color: #28a745;
    }

    .product-card .product-stock {
        font-size: 11px;
        color: #6c757d;
        margin-top: 5px;
    }

    /* Cart Section */
    .cart-section {
        width: 400px;
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .cart-header {
        padding: 18px 20px;
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        color: #fff;
    }

    .cart-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 18px;
    }

    .cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        min-height: 150px;
        max-height: 220px;
    }

    .cart-item {
        display: flex;
        align-items: center;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 10px;
        gap: 10px;
    }

    .cart-item-info { flex: 1; }
    .cart-item-name { font-weight: 600; font-size: 13px; color: #333; }
    .cart-item-price { font-size: 12px; color: #6c757d; }
    .cart-item-qty { display: flex; align-items: center; gap: 8px; }
    .cart-item-qty button { width: 30px; height: 30px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 16px; }
    .cart-item-qty .btn-minus { background: #fee2e2; color: #dc3545; }
    .cart-item-qty .btn-plus { background: #dcfce7; color: #28a745; }
    .cart-item-qty span { font-weight: 700; min-width: 25px; text-align: center; }
    .cart-item-total { font-weight: 700; color: #333; min-width: 80px; text-align: right; }
    .cart-item-remove { color: #dc3545; cursor: pointer; padding: 5px; font-size: 16px; }
    .cart-empty { text-align: center; padding: 40px 20px; color: #6c757d; }
    .cart-empty i { font-size: 50px; margin-bottom: 15px; opacity: 0.4; }

    /* Cart Summary */
    .cart-summary { padding: 15px 20px; background: #f8f9fa; border-top: 1px solid #eee; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
    .summary-row.total { font-size: 20px; font-weight: 700; color: #333; padding-top: 12px; border-top: 2px solid #dee2e6; margin-top: 10px; }

    /* Payment Section */
    .payment-section { padding: 20px; border-top: 1px solid #eee; }
    .payment-methods { display: flex; gap: 10px; margin-bottom: 15px; }
    .payment-method { flex: 1; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; text-align: center; cursor: pointer; transition: all 0.2s; }
    .payment-method:hover { border-color: #0d6efd; }
    .payment-method.active { border-color: #0d6efd; background: #e7f1ff; }
    .payment-method i { font-size: 24px; display: block; margin-bottom: 5px; }
    .payment-method span { font-size: 13px; font-weight: 600; }

    .quick-cash { display: flex; gap: 8px; margin-bottom: 12px; }
    .quick-cash button { flex: 1; padding: 10px; border: 1px solid #dee2e6; background: #fff; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
    .quick-cash button:hover { background: #0d6efd; color: #fff; border-color: #0d6efd; }

    .change-display { background: #dcfce7; padding: 12px; border-radius: 10px; text-align: center; margin-bottom: 12px; }
    .change-display.negative { background: #fee2e2; }
    .change-display label { font-size: 12px; color: #666; display: block; margin-bottom: 3px; }
    .change-display .amount { font-size: 26px; font-weight: 700; color: #28a745; }
    .change-display.negative .amount { color: #dc3545; }

    .btn-complete-sale { width: 100%; padding: 15px; font-size: 17px; font-weight: 700; border-radius: 10px; }

    /* Calculator */
    .calculator-toggle { position: fixed; bottom: 25px; right: 25px; width: 55px; height: 55px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; font-size: 22px; cursor: pointer; box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4); z-index: 100; }
    .calculator-widget { position: fixed; bottom: 90px; right: 25px; background: #fff; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 20px; display: none; z-index: 100; width: 240px; }
    .calculator-widget.show { display: block; }
    .calculator-widget input { width: 100%; padding: 12px; font-size: 20px; text-align: right; border: 2px solid #e9ecef; border-radius: 8px; margin-bottom: 12px; }
    .calculator-widget .calc-buttons { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .calculator-widget .calc-buttons button { padding: 14px; border: none; background: #f8f9fa; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; }
    .calculator-widget .calc-buttons button.operator { background: #ffc107; color: #333; }
    .calculator-widget .calc-buttons button.equals { background: #28a745; color: #fff; grid-column: span 4; }
    .calculator-widget .calc-buttons button.clear { background: #dc3545; color: #fff; }

    /* Stats Bar */
    .stats-bar { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
    .stat-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
    .stat-card .stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 12px; }
    .stat-card .stat-value { font-size: 22px; font-weight: 700; color: #333; }
    .stat-card .stat-label { font-size: 13px; color: #6c757d; margin-top: 3px; }

    /* Category Filter */
    .category-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .category-tab { padding: 8px 18px; border: 1px solid #dee2e6; background: #fff; border-radius: 25px; font-size: 13px; font-weight: 500; cursor: pointer; }
    .category-tab:hover, .category-tab.active { background: #0d6efd; color: #fff; border-color: #0d6efd; }

    /* Success Modal */
    .success-modal .modal-content { border-radius: 20px; border: none; }
    .success-modal .modal-body { padding: 50px; text-align: center; }
    .success-modal .success-icon { width: 90px; height: 90px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; }
    .success-modal .success-icon i { font-size: 45px; color: #28a745; }
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">POS Terminal</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">POS Terminal</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if($drawerOpen)
        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-icon" style="background: #dcfce7; color: #28a745;"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-value">RM {{ number_format($todayStats['total_sales'] ?? 0, 2) }}</div>
                <div class="stat-label">Today's Sales</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #dbeafe; color: #2196f3;"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-value">{{ $todayStats['total_transactions'] ?? 0 }}</div>
                <div class="stat-label">Transactions</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-value">RM {{ number_format($todayStats['cash_sales'] ?? 0, 2) }}</div>
                <div class="stat-label">Cash Sales</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #f3e8ff; color: #9333ea;"><i class="fas fa-qrcode"></i></div>
                <div class="stat-value">RM {{ number_format($todayStats['qr_sales'] ?? 0, 2) }}</div>
                <div class="stat-label">QR Sales</div>
            </div>
        </div>

        <!-- POS Container -->
        <div class="pos-container">
            <!-- Products Section -->
            <div class="products-section">
                <div class="products-header">
                    <div class="row align-items-center g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="searchProduct" placeholder="Search products...">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="category-tabs">
                                <button class="category-tab active" data-category="">All Items</button>
                                @foreach($categories as $category)
                                <button class="category-tab" data-category="{{ $category->id }}">{{ $category->name }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="products-grid" id="productsGrid">
                    @forelse($products as $product)
                    <div class="product-card {{ $product->stock_quantity <= 0 ? 'out-of-stock' : '' }}"
                         data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->selling_price }}"
                         data-stock="{{ $product->stock_quantity }}" data-category="{{ $product->category_id }}" onclick="addToCart(this)">
                        <div class="product-icon"><i class="fas fa-box"></i></div>
                        <div class="product-name">{{ $product->name }}</div>
                        <div class="product-price">RM {{ number_format($product->selling_price, 2) }}</div>
                        <div class="product-stock">Stock: {{ $product->stock_quantity }}</div>
                    </div>
                    @empty
                    <div class="text-center py-5" style="grid-column: span 5;">
                        <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted">No products available</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Cart Section -->
            <div class="cart-section">
                <div class="cart-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-shopping-cart me-2"></i>Current Sale</h5>
                        <button class="btn btn-sm btn-outline-light" onclick="clearCart()"><i class="fas fa-trash me-1"></i> Clear</button>
                    </div>
                </div>

                <div class="cart-items" id="cartItems">
                    <div class="cart-empty">
                        <i class="fas fa-shopping-basket d-block"></i>
                        <p class="mb-1">Cart is empty</p>
                        <small>Click on products to add them</small>
                    </div>
                </div>

                <div class="cart-summary">
                    <div class="summary-row"><span>Subtotal</span><span id="subtotal">RM 0.00</span></div>
                    <div class="summary-row"><span>Discount</span><span id="discount">RM 0.00</span></div>
                    <div class="summary-row total"><span>Total</span><span id="grandTotal">RM 0.00</span></div>
                </div>

                <div class="payment-section">
                    <div class="payment-methods">
                        <div class="payment-method active" data-method="cash" onclick="selectPaymentMethod('cash')">
                            <i class="fas fa-money-bill-wave text-success"></i><span>Cash</span>
                        </div>
                        <div class="payment-method" data-method="qr" onclick="selectPaymentMethod('qr')">
                            <i class="fas fa-qrcode text-primary"></i><span>QR Pay</span>
                        </div>
                    </div>

                    <div id="cashPayment">
                        <div class="mb-2">
                            <input type="number" class="form-control form-control-lg" id="amountReceived" placeholder="Amount Received" step="0.01" oninput="calculateChange()">
                        </div>
                        <div class="quick-cash">
                            <button type="button" onclick="setQuickCash(10)">RM10</button>
                            <button type="button" onclick="setQuickCash(20)">RM20</button>
                            <button type="button" onclick="setQuickCash(50)">RM50</button>
                            <button type="button" onclick="setQuickCash(100)">RM100</button>
                        </div>
                        <div class="change-display" id="changeDisplay">
                            <label>Change</label>
                            <div class="amount" id="changeAmount">RM 0.00</div>
                        </div>
                    </div>

                    <div id="qrPayment" style="display: none;">
                        <div class="mb-3">
                            <input type="text" class="form-control form-control-lg" id="qrReference" placeholder="QR Reference Number">
                        </div>
                    </div>

                    <div class="mb-3">
                        <input type="text" class="form-control" id="saleNotes" placeholder="Notes (optional)">
                    </div>

                    <button class="btn btn-success btn-complete-sale" onclick="completeSale()" id="btnCompleteSale">
                        <i class="fas fa-check-circle me-2"></i> Complete Sale
                    </button>
                </div>
            </div>
        </div>

        <!-- Calculator Toggle -->
        <button class="calculator-toggle" onclick="toggleCalculator()" title="Calculator"><i class="fas fa-calculator"></i></button>
        <div class="calculator-widget" id="calculatorWidget">
            <input type="text" id="calcDisplay" value="0" readonly>
            <div class="calc-buttons">
                <button onclick="calcInput('7')">7</button><button onclick="calcInput('8')">8</button><button onclick="calcInput('9')">9</button><button class="operator" onclick="calcInput('/')">÷</button>
                <button onclick="calcInput('4')">4</button><button onclick="calcInput('5')">5</button><button onclick="calcInput('6')">6</button><button class="operator" onclick="calcInput('*')">×</button>
                <button onclick="calcInput('1')">1</button><button onclick="calcInput('2')">2</button><button onclick="calcInput('3')">3</button><button class="operator" onclick="calcInput('-')">-</button>
                <button onclick="calcInput('0')">0</button><button onclick="calcInput('.')">.</button><button class="clear" onclick="calcClear()">C</button><button class="operator" onclick="calcInput('+')">+</button>
                <button class="equals" onclick="calcEquals()">=</button>
            </div>
        </div>
        @endif
    </div>
</section>

<!-- Success Modal -->
<div class="modal fade success-modal" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="success-icon"><i class="fas fa-check"></i></div>
                <h4 class="mb-2">Sale Completed!</h4>
                <p class="text-muted mb-3">Transaction #<span id="transactionNumber"></span></p>
                <h3 class="text-success mb-4" id="saleAmount">RM 0.00</h3>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="#" class="btn btn-outline-primary" id="receiptLink" target="_blank"><i class="fas fa-receipt me-1"></i> View Receipt</a>
                    <button class="btn btn-success" onclick="newSale()"><i class="fas fa-plus me-1"></i> New Sale</button>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$drawerOpen)
<!-- Cash Drawer Not Open Overlay -->
<div class="drawer-not-open-overlay">
    <div class="drawer-not-open-box">
        <div class="icon-wrapper"><i class="fas fa-cash-register"></i></div>
        <h3>Cash Drawer Not Open</h3>
        <p>Please open the cash drawer to start making sales.</p>
        <a href="{{ route('admin.daily-cash-reports.open-drawer') }}" class="btn btn-warning btn-lg">
            <i class="fas fa-unlock me-2"></i> Open Drawer
        </a>
    </div>
</div>
@endif
@endsection

@section('scripts')
@if($drawerOpen)
<script>
let cart = [];
let paymentMethod = 'cash';

function addToCart(el) {
    const id = el.dataset.id, name = el.dataset.name, price = parseFloat(el.dataset.price), stock = parseInt(el.dataset.stock);
    if (stock <= 0) { Swal.fire('Out of Stock', 'This item is out of stock.', 'warning'); return; }
    const existing = cart.find(item => item.id === id);
    if (existing) {
        if (existing.qty >= stock) { Swal.fire('Stock Limit', 'Stock limit reached.', 'warning'); return; }
        existing.qty++;
    } else {
        cart.push({ id, name, price, qty: 1, stock });
    }
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartEl = document.getElementById('cartItems');
    if (cart.length === 0) {
        cartEl.innerHTML = '<div class="cart-empty"><i class="fas fa-shopping-basket d-block"></i><p class="mb-1">Cart is empty</p><small>Click on products to add them</small></div>';
    } else {
        cartEl.innerHTML = cart.map((item, i) => `
            <div class="cart-item">
                <div class="cart-item-info"><div class="cart-item-name">${item.name}</div><div class="cart-item-price">RM ${item.price.toFixed(2)}</div></div>
                <div class="cart-item-qty"><button class="btn-minus" onclick="updateQty(${i},-1)">-</button><span>${item.qty}</span><button class="btn-plus" onclick="updateQty(${i},1)">+</button></div>
                <div class="cart-item-total">RM ${(item.price * item.qty).toFixed(2)}</div>
                <div class="cart-item-remove" onclick="removeItem(${i})"><i class="fas fa-times"></i></div>
            </div>
        `).join('');
    }
    updateTotals();
}

function updateQty(i, change) {
    const item = cart[i], newQty = item.qty + change;
    if (newQty <= 0) { removeItem(i); return; }
    if (newQty > item.stock) { Swal.fire('Stock Limit', 'Stock limit reached.', 'warning'); return; }
    item.qty = newQty;
    updateCartDisplay();
}

function removeItem(i) { cart.splice(i, 1); updateCartDisplay(); }

function clearCart() {
    if (cart.length === 0) return;
    Swal.fire({ title: 'Clear Cart?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, clear!' })
        .then(r => { if (r.isConfirmed) { cart = []; updateCartDisplay(); } });
}

function updateTotals() {
    const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
    document.getElementById('subtotal').textContent = 'RM ' + subtotal.toFixed(2);
    document.getElementById('discount').textContent = 'RM 0.00';
    document.getElementById('grandTotal').textContent = 'RM ' + subtotal.toFixed(2);
    calculateChange();
}

function selectPaymentMethod(method) {
    paymentMethod = method;
    document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
    document.querySelector(`.payment-method[data-method="${method}"]`).classList.add('active');
    document.getElementById('cashPayment').style.display = method === 'cash' ? 'block' : 'none';
    document.getElementById('qrPayment').style.display = method === 'qr' ? 'block' : 'none';
}

function setQuickCash(amt) { document.getElementById('amountReceived').value = amt; calculateChange(); }

function calculateChange() {
    const total = cart.reduce((s, i) => s + i.price * i.qty, 0);
    const received = parseFloat(document.getElementById('amountReceived').value) || 0;
    const change = received - total;
    const el = document.getElementById('changeDisplay'), amtEl = document.getElementById('changeAmount');
    el.classList.toggle('negative', change < 0);
    amtEl.textContent = (change < 0 ? '-' : '') + 'RM ' + Math.abs(change).toFixed(2);
}

function completeSale() {
    if (cart.length === 0) { Swal.fire('Empty Cart', 'Add items first.', 'warning'); return; }
    const total = cart.reduce((s, i) => s + i.price * i.qty, 0);
    if (paymentMethod === 'cash') {
        const received = parseFloat(document.getElementById('amountReceived').value) || 0;
        if (received < total) { Swal.fire('Insufficient', 'Amount received is less than total.', 'warning'); return; }
    }
    if (paymentMethod === 'qr' && !document.getElementById('qrReference').value) {
        Swal.fire('Reference Required', 'Enter QR reference.', 'warning'); return;
    }

    const btn = document.getElementById('btnCompleteSale');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';

    $.ajax({
        url: '{{ route("admin.pos.sale") }}', method: 'POST',
        data: {
            items: cart.map(i => ({ inventory_id: i.id, quantity: i.qty, unit_price: i.price })),
            payment_method: paymentMethod,
            amount_received: paymentMethod === 'cash' ? parseFloat(document.getElementById('amountReceived').value) : total,
            qr_reference: paymentMethod === 'qr' ? document.getElementById('qrReference').value : null,
            notes: document.getElementById('saleNotes').value,
            _token: '{{ csrf_token() }}'
        },
        success: function(r) {
            if (r.success) {
                document.getElementById('transactionNumber').textContent = r.transaction.transaction_number;
                document.getElementById('saleAmount').textContent = 'RM ' + parseFloat(r.transaction.total_amount).toFixed(2);
                document.getElementById('receiptLink').href = r.receipt_url;
                new bootstrap.Modal(document.getElementById('successModal')).show();
            } else { Swal.fire('Error', r.message || 'Failed.', 'error'); }
        },
        error: function(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Error occurred.', 'error'); },
        complete: function() { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check-circle me-2"></i> Complete Sale'; }
    });
}

function newSale() {
    bootstrap.Modal.getInstance(document.getElementById('successModal')).hide();
    cart = []; updateCartDisplay();
    document.getElementById('amountReceived').value = '';
    document.getElementById('qrReference').value = '';
    document.getElementById('saleNotes').value = '';
    calculateChange();
    location.reload();
}

document.getElementById('searchProduct')?.addEventListener('input', function() {
    const s = this.value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(c => c.style.display = c.dataset.name.toLowerCase().includes(s) ? '' : 'none');
});

document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const cat = this.dataset.category;
        document.querySelectorAll('.product-card').forEach(c => c.style.display = (!cat || c.dataset.category === cat) ? '' : 'none');
    });
});

let calcExpr = '';
function toggleCalculator() { document.getElementById('calculatorWidget').classList.toggle('show'); }
function calcInput(v) { calcExpr = calcExpr === '0' && v !== '.' ? v : calcExpr + v; document.getElementById('calcDisplay').value = calcExpr; }
function calcClear() { calcExpr = ''; document.getElementById('calcDisplay').value = '0'; }
function calcEquals() { try { const r = eval(calcExpr); document.getElementById('calcDisplay').value = r; calcExpr = r.toString(); } catch(e) { document.getElementById('calcDisplay').value = 'Error'; calcExpr = ''; } }

document.addEventListener('DOMContentLoaded', function() { updateCartDisplay(); });
</script>
@endif
@endsection
