<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<style>
    .pos-wrapper {
        display: flex;
        gap: 20px;
        height: calc(100vh - 140px);
        min-height: 600px;
    }

    /* ── LEFT PANEL ── */
    .pos-left {
        flex: 1 1 0;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .pos-search-bar {
        display: flex;
        gap: 10px;
        margin-bottom: 12px;
        align-items: center;
    }

    .pos-search-input {
        flex: 1;
        padding: 10px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 15px;
        outline: none;
        transition: border-color .2s;
    }

    .pos-search-input:focus { border-color: #FF8C42; }

    .pos-search-field {
        position: relative;
        flex: 1;
        min-width: 0;
    }

    .pos-search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #8a8a8a;
        pointer-events: none;
        font-size: 14px;
    }

    .pos-search-field .pos-search-input {
        padding-left: 38px;
        width: 100%;
    }

    .return-items-scroll {
        max-height: 180px;
        overflow-y: auto;
    }

    .return-item-row {
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 10px;
        background: #fff;
    }

    .return-item-check-col {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 22px;
        margin-top: 1px;
    }

    .return-item-check {
        margin: 0;
    }

    .return-item-info {
        flex: 1 1 auto;
        min-width: 0;
    }

    .return-item-title {
        font-size: 0.92rem;
        line-height: 1.2;
        word-break: break-word;
        color: #212529;
    }

    .return-item-subtext {
        font-size: 0.78rem;
        color: #6c757d;
        margin-top: 2px;
    }

    .return-item-qty-wrap {
        flex: 0 0 96px;
    }

    .return-item-qty {
        text-align: center;
    }

    .pos-cat-filter {
        padding: 9px 14px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 14px;
        background: #fff;
        outline: none;
        cursor: pointer;
        transition: border-color .2s;
    }

    .pos-cat-filter:focus { border-color: #FF8C42; }

    .btn-scan {
        padding: 9px 14px;
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        transition: all .18s;
        flex-shrink: 0;
    }
    .btn-scan:hover { background: linear-gradient(135deg, #138496 0%, #0f6674 100%); transform: translateY(-1px); }
    .btn-scan.scanning { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }

    /* ── Scanner Modal ── */
    #scannerModal .modal-content { border-radius: 16px; overflow: hidden; }
    #scannerModal .modal-header { background: linear-gradient(135deg,#17a2b8,#138496); color:#fff; border:none; }
    #scannerModal .btn-close { filter: invert(1); }
    #scanner-container { position: relative; width: 100%; background: #000; border-radius: 10px; overflow: hidden; min-height: 280px; }
    #scanner-container video { width: 100%; display: block; }
    .scan-overlay {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        pointer-events: none;
    }
    .scan-frame {
        width: 220px; height: 140px;
        border: 3px solid #17a2b8;
        border-radius: 12px;
        box-shadow: 0 0 0 2000px rgba(0,0,0,.45);
        position: relative;
    }
    .scan-frame::before {
        content: '';
        position: absolute; left: 0; right: 0;
        height: 2px; background: #17a2b8;
        box-shadow: 0 0 8px 2px rgba(23,162,184,.8);
        animation: scanLine 2s ease-in-out infinite;
        top: 0;
    }
    @keyframes scanLine { 0%,100%{ top:0; } 50%{ top: calc(100% - 2px); } }
    .scan-frame .corner { position:absolute; width:18px; height:18px; border-color:#fff; border-style:solid; }
    .scan-frame .corner.tl { top:-2px; left:-2px; border-width:3px 0 0 3px; }
    .scan-frame .corner.tr { top:-2px; right:-2px; border-width:3px 3px 0 0; }
    .scan-frame .corner.bl { bottom:-2px; left:-2px; border-width:0 0 3px 3px; }
    .scan-frame .corner.br { bottom:-2px; right:-2px; border-width:0 3px 3px 0; }
    #scan-status { font-size: 13px; margin-top: 10px; text-align: center; color: #555; min-height: 24px; }
    #scan-last { font-size: 12px; margin-top: 4px; text-align: center; color: #888; min-height: 20px; }
    .scan-success-flash { animation: flashGreen .4s ease; }
    @keyframes flashGreen { 0%,100%{ background:#000; } 50%{ background:#1e7e34; } }

    .pos-products-grid {
        flex: 1;
        overflow-y: auto;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
        align-content: start;
        align-items: start;
        gap: 12px;
        padding-right: 4px;
    }

    .pos-product-card {
        background: #fff;
        border: 2px solid #f0f0f0;
        border-radius: 12px;
        padding: 14px 12px 10px;
        align-self: start;
        cursor: pointer;
        transition: all .18s;
        display: flex;
        flex-direction: column;
        gap: 4px;
        position: relative;
        user-select: none;
        min-height: 120px;
    }

    .pos-product-card:hover {
        border-color: #FF8C42;
        box-shadow: 0 4px 14px rgba(255,140,66,.18);
        transform: translateY(-2px);
    }

    .pos-product-card.out-of-stock {
        opacity: .45;
        cursor: not-allowed;
        pointer-events: none;
    }

    .pos-card-name {
        font-size: 13px;
        font-weight: 600;
        color: #1a1a1a;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.6em;
    }

    .pos-card-sku {
        font-size: 11px;
        color: #999;
    }

    .pos-card-price {
        font-size: 15px;
        font-weight: 700;
        color: #FF8C42;
        margin-top: 4px;
    }

    .pos-card-stock {
        font-size: 11px;
        color: #6c757d;
    }

    .pos-card-add-icon {
        position: absolute;
        top: 8px;
        right: 8px;
        background: #FF8C42;
        color: #fff;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        opacity: 0;
        transition: opacity .15s;
    }

    .pos-product-card:hover .pos-card-add-icon { opacity: 1; }

    .pos-empty { text-align: center; color: #aaa; padding: 40px 0; grid-column: 1/-1; }

    /* ── RIGHT PANEL (CART) ── */
    .pos-right {
        width: 340px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,.08);
        overflow: hidden;
        min-height: 0;
        height: 100%;
    }

    .pos-cart-header {
        background: linear-gradient(135deg, #FF8C42 0%, #FF7A2F 100%);
        color: #fff;
        padding: 12px 16px;
        font-size: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .pos-cart-body {
        flex: 1 1 auto;
        overflow-y: auto;
        padding: 6px 10px;
        min-height: 0;
    }

    .pos-cart-empty {
        text-align: center;
        color: #ccc;
        padding: 40px 0;
        font-size: 14px;
    }

    .cart-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 4px 0;
        border-bottom: 1px solid #f5f5f5;
    }

    .cart-item-info { flex: 1; min-width: 0; }

    .cart-item-name {
        font-size: 11px;
        font-weight: 600;
        color: #1a1a1a;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cart-item-price { font-size: 10px; color: #888; }

    .cart-item-controls {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .qty-btn {
        width: 22px;
        height: 22px;
        border: 1px solid #ddd;
        background: #f8f9fa;
        border-radius: 5px;
        font-size: 13px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        transition: background .1s;
    }

    .qty-btn:hover { background: #FF8C42; color: #fff; border-color: #FF8C42; }

    .qty-display {
        width: 24px;
        text-align: center;
        font-size: 12px;
        font-weight: 700;
    }

    .cart-item-sub {
        font-size: 11px;
        font-weight: 700;
        color: #FF8C42;
        min-width: 54px;
        text-align: right;
    }

    .cart-remove {
        background: none;
        border: none;
        color: #dc3545;
        font-size: 13px;
        cursor: pointer;
        padding: 1px 3px;
        transition: opacity .15s;
    }

    .cart-remove:hover { opacity: .7; }

    /* ── TOTALS / PAYMENT ── */
    .pos-cart-footer {
        padding: 10px 12px;
        border-top: 2px solid #f5f5f5;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .pos-total-row {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: #666;
    }

    .pos-grand-total {
        display: flex;
        justify-content: space-between;
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
        border-top: 2px solid #f0f0f0;
        padding-top: 6px;
        margin-top: 2px;
    }

    .pos-payment-section {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .pos-input-label {
        font-size: 11px;
        font-weight: 600;
        color: #888;
        margin-bottom: 1px;
    }

    .pos-input {
        width: 100%;
        padding: 6px 10px;
        border: 1.5px solid #e0e0e0;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        transition: border-color .2s;
    }

    .pos-input:focus { border-color: #FF8C42; }

    .pos-payment-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        align-items: end;
    }

    .pos-status-col {
        min-height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .pos-change-display {
        background: #f0fff4;
        border: 1.5px solid #28a745;
        border-radius: 8px;
        padding: 6px 10px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        font-weight: 700;
        color: #28a745;
        font-size: 13px;
        min-height: 37px;
    }

    .pos-balance-display {
        background: #fff4f4;
        border: 1.5px solid #dc3545;
        border-radius: 8px;
        padding: 6px 10px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        font-weight: 700;
        color: #dc3545;
        font-size: 13px;
        min-height: 37px;
    }

    .btn-checkout {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-checkout:hover {
        background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(40,167,69,.3);
    }

    .btn-checkout:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* ── Receipt Preview Modal ── */
    #receiptPreviewModal .modal-content { border-radius: 14px; overflow: hidden; }
    #receiptPreviewModal .modal-header {
        background: linear-gradient(135deg, #FF8C42 0%, #FF7A2F 100%);
        color: #fff;
        border: none;
    }
    #receiptPreviewModal .btn-close { filter: invert(1); }
    .receipt-preview-wrap {
        background: #fff;
        border: none;
        border-radius: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 360px;
        max-height: 70vh;
        overflow: auto;
    }
    .receipt-preview-frame {
        width: 100%;
        max-width: 360px;
        min-height: 360px;
        border: none;
        border-radius: 0;
        background: #efefef;
    }

    .btn-clear-cart {
        background: none;
        border: 1.5px solid #dc3545;
        color: #dc3545;
        border-radius: 10px;
        padding: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s;
        width: 100%;
        margin-top: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-clear-cart:hover { background: #dc3545; color: #fff; }

    .method-btn {
        flex: 1;
        padding: 6px 4px;
        border: 1.5px solid #e0e0e0;
        border-radius: 7px;
        background: #fff;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s;
        color: #555;
    }

    .method-btn.active {
        border-color: #FF8C42;
        background: #fff5ee;
        color: #FF8C42;
    }

    @media (min-width: 901px) {
        .pos-cart-body {
            min-height: 290px;
        }
    }

    @media (max-width: 900px) {
        .pos-wrapper { flex-direction: column; height: auto; }
        .pos-right { width: 100%; }
        .pos-products-grid { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); }
    }

    @media (max-height: 820px) {
        .pos-wrapper {
            min-height: 0;
            height: calc(100vh - 120px);
        }

        .pos-right {
            min-height: 0;
        }

        .pos-cart-body {
            min-height: 0;
        }

        .pos-cart-footer {
            max-height: none;
        }
    }

    /* Prevent accidental full-page POS prints. */
    @media print {
        body * {
            visibility: hidden !important;
        }

        #receiptPreviewModal.show,
        #receiptPreviewModal.show * {
            visibility: visible !important;
        }

        #receiptPreviewModal {
            position: static !important;
            display: block !important;
            padding: 0 !important;
            margin: 0 !important;
            background: #fff !important;
        }

        #receiptPreviewModal .modal-dialog {
            margin: 0 !important;
            max-width: none !important;
            width: auto !important;
            transform: none !important;
        }

        #receiptPreviewModal .modal-content {
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: #fff !important;
        }

        #receiptPreviewModal .modal-header,
        #receiptPreviewModal .modal-footer {
            display: none !important;
        }

        #receiptPreviewModal .modal-body {
            padding: 0 !important;
        }
    }
</style>

<?php
$branchText = trim((string) (($branch ?? session()->get('branch') ?? '')));
$categoriesInput = isset($categories) ? $categories : [];
$categories = is_array($categoriesInput) ? $categoriesInput : [];
$productsInput = isset($products) ? $products : [];
$products = is_array($productsInput) ? $productsInput : [];
?>

<div class="container-fluid px-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 style="font-size:26px; font-weight:700; color:#1a1a1a;">Point of Sale</h2>
        <span class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?= esc($branchText) ?></span>
    </div>

    <div class="pos-wrapper">

        <!-- ──────────── LEFT: Products ──────────── -->
        <div class="pos-left">
            <div class="pos-search-bar">
                <div class="pos-search-field">
                    <i class="bi bi-search pos-search-icon"></i>
                    <input type="text" id="posSearch" class="pos-search-input" placeholder="Search by name, SKU…" autocomplete="off">
                </div>
                <button class="btn-scan" id="btnOpenScanner" title="Scan barcode with camera">
                    <i class="bi bi-upc-scan"></i> Scan
                </button>
                <button class="btn btn-outline-warning" id="btnOpenReturnModal" type="button" title="Process returns or void sale">
                    <i class="bi bi-arrow-counterclockwise"></i> Returns
                </button>
                <select id="posCatFilter" class="pos-cat-filter">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <?php if (!is_array($cat)) { continue; } ?>
                        <option value="<?= (int) ($cat['category_id'] ?? 0) ?>"><?= esc((string) ($cat['category_name'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="pos-products-grid" id="posProductsGrid">
                <?php foreach ($products as $p): ?>
                <?php if (!is_array($p)) { continue; } ?>
                 <?php
                    $description = trim((string) ($p['description'] ?? ''));
                    $displayName = trim(trim((string) ($p['part_name'] ?? '')) . ' ' . $description);
                 ?>
                 <div class="pos-product-card <?= (int) ($p['current_stock'] ?? 0) <= 0 ? 'out-of-stock' : '' ?>"
                     data-id="<?= (int) ($p['product_id'] ?? 0) ?>"
                     data-name="<?= esc($displayName !== '' ? $displayName : (string) ($p['part_name'] ?? '')) ?>"
                     data-sku="<?= esc((string) ($p['sku'] ?? '')) ?>"
                     data-part-no="<?= esc((string) ($p['part_no'] ?? '')) ?>"
                     data-upc="<?= esc((string) ($p['resolved_upc'] ?? '')) ?>"
                     data-price="<?= (float) ($p['price'] ?? 0) ?>"
                     data-stock="<?= (int) ($p['current_stock'] ?? 0) ?>"
                     data-cat="<?= (int) ($p['category_id'] ?? 0) ?>">
                    <div class="pos-card-add-icon"><i class="bi bi-plus"></i></div>
                    <div class="pos-card-name"><?= esc($displayName !== '' ? $displayName : (string) ($p['part_name'] ?? '')) ?></div>
                    <?php if (!empty($p['sku'])): ?>
                        <div class="pos-card-sku"><?= esc((string) $p['sku']) ?></div>
                    <?php endif; ?>
                    <div class="pos-card-price">₱<?= number_format((float) ($p['price'] ?? 0), 2) ?></div>
                    <div class="pos-card-stock">Stock: <?= (int) ($p['current_stock'] ?? 0) ?></div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <div class="pos-empty"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No products available.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ──────────── RIGHT: Cart ──────────── -->
        <div class="pos-right">
            <div class="pos-cart-header">
                <span><i class="bi bi-cart3 me-2"></i>Cart</span>
                <span id="cartCount" class="badge bg-white text-dark fw-bold">0 items</span>
            </div>

            <div class="pos-cart-body" id="cartBody">
                <div class="pos-cart-empty" id="cartEmpty">
                    <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                    Cart is empty.<br>
                    <small class="text-muted">Click a product to add.</small>
                </div>
            </div>

            <div class="pos-cart-footer">
                <!-- Customer -->
                <div>
                    <div class="pos-input-label">Customer Name*</div>
                    <input type="text" id="customerName" class="pos-input" placeholder="Walk-in Customer">
                </div>

                <div>
                    <div class="pos-input-label">Customer Address*</div>
                    <input type="text" id="customerAddress" class="pos-input" placeholder="Address">
                </div>

                <!-- Totals -->
                <div class="pos-total-row" id="itemsCountRow" style="display:none;">
                    <span>Items</span><span id="footerItemCount">0</span>
                </div>
                <div class="pos-total-row" id="subtotalRow" style="display:none;">
                    <span>Subtotal</span><span id="subTotal">₱0.00</span>
                </div>
                <div class="pos-total-row" id="discountRow" style="display:none;">
                    <span>Discount</span><span id="discountTotal">₱0.00</span>
                </div>
                <div class="pos-grand-total">
                    <span>Total</span>
                    <span id="grandTotal">₱0.00</span>
                </div>

                <div class="pos-payment-row">
                    <div>
                        <div class="pos-input-label">How many % discount</div>
                        <input type="number" id="discountPercent" class="pos-input" value="0" min="0" max="100" step="0.01" autocomplete="off">
                    </div>
                    <div>
                        <div class="pos-input-label">Discount Amount</div>
                        <input type="number" id="discountAmount" class="pos-input" value="0.00" min="0" step="0.01" readonly tabindex="-1" autocomplete="off">
                    </div>
                </div>

                <!-- Payment Method -->
                <div>
                    <div class="pos-input-label">Payment Method</div>
                    <div class="d-flex gap-2">
                        <button class="method-btn active" data-method="Cash">💵 Cash</button>
                        <button class="method-btn" data-method="GCash">📱 GCash</button>
                        <button class="method-btn" data-method="Card">💳 Card</button>
                        <button class="method-btn" data-method="Credit">📋 Credit</button>
                    </div>
                </div>

                <div class="pos-payment-row">
                    <!-- Amount paid -->
                    <div id="cashSection">
                        <div class="pos-input-label" id="paymentAmountLabel">Cash Tendered</div>
                        <input type="number" id="cashTendered" class="pos-input" placeholder="0.00" min="0" step="0.01">
                    </div>

                    <!-- Change / Balance status -->
                    <div class="pos-status-col">
                        <div class="pos-input-label" id="balanceLabel" style="display:none;">Balance Due</div>
                        <div class="pos-balance-display" id="balanceRow" style="display:none;">
                            <span id="balanceAmount">₱0.00</span>
                        </div>

                        <div class="pos-input-label" id="changeLabel" style="display:none;">Change</div>
                        <div class="pos-change-display" id="changeRow" style="display:none;">
                            <span id="changeAmount">₱0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Checkout -->
                <button class="btn-checkout" id="btnCheckout" disabled>
                    <i class="bi bi-check-circle"></i> Checkout
                </button>
                <button class="btn-clear-cart" id="btnClearCart">
                    <i class="bi bi-trash me-1"></i> Clear Cart
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
(() => {
    // ── State ──────────────────────────────────────────────────────────
    let cart = {};          // { product_id: { id, name, sku, price, stock, qty } }
    let paymentMethod = 'Cash';
    let printersLoaded = false;
    const printProfiles = {
        pos80_auto: { label: 'POS 80mm (Auto Length)', widthMm: 80, pageHeightMm: null, lineWidth: 42 },
        pos80_fixed: { label: 'POS 80x297mm (Fixed)', widthMm: 80, pageHeightMm: 297, lineWidth: 42 },
        windows_safe: { label: 'Windows Safe (A4 dialog)', widthMm: 80, pageHeightMm: null, lineWidth: 40 },
    };

    function getActivePrintProfile() {
        const el = document.getElementById('receiptProfileSelect');
        const key = (el && el.value && printProfiles[el.value]) ? el.value : 'pos80_auto';
        return printProfiles[key];
    }

    // ── DOM refs ────────────────────────────────────────────────────────
    const grid         = document.getElementById('posProductsGrid');
    const cartBody     = document.getElementById('cartBody');
    const cartEmpty    = document.getElementById('cartEmpty');
    const cartCount    = document.getElementById('cartCount');
    const grandTotal   = document.getElementById('grandTotal');
    const cashSection  = document.getElementById('cashSection');
    const cashTendered = document.getElementById('cashTendered');
    const changeRow    = document.getElementById('changeRow');
    const changeAmount = document.getElementById('changeAmount');
    const changeLabel  = document.getElementById('changeLabel');
    const balanceRow   = document.getElementById('balanceRow');
    const balanceAmount= document.getElementById('balanceAmount');
    const balanceLabel = document.getElementById('balanceLabel');
    const paymentAmountLabel = document.getElementById('paymentAmountLabel');
    const btnCheckout  = document.getElementById('btnCheckout');
    const itemsCountRow= document.getElementById('itemsCountRow');
    const footerItemCount = document.getElementById('footerItemCount');
    const subTotalEl   = document.getElementById('subTotal');
    const discountTotalEl = document.getElementById('discountTotal');
    const subtotalRow  = document.getElementById('subtotalRow');
    const discountRow  = document.getElementById('discountRow');
    const discountPercentInput = document.getElementById('discountPercent');
    const discountAmountInput = document.getElementById('discountAmount');

    // ── Product search / filter ─────────────────────────────────────────
    let searchTimer;
    document.getElementById('posSearch').addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => filterProducts(), 200);
    });
    document.getElementById('posCatFilter').addEventListener('change', filterProducts);

    function filterProducts() {
        const q   = document.getElementById('posSearch').value.toLowerCase().trim();
        const cat = document.getElementById('posCatFilter').value;
        let any = false;
        document.querySelectorAll('.pos-product-card').forEach(card => {
            const nameMatch = card.dataset.name.toLowerCase().includes(q);
            const skuMatch  = (card.dataset.sku || '').toLowerCase().includes(q);
            const partNoMatch = (card.dataset.partNo || '').toLowerCase().includes(q);
            const upcMatch = (card.dataset.upc || '').toLowerCase().includes(q);
            const catMatch  = !cat || card.dataset.cat === cat;
            const show = (nameMatch || skuMatch || partNoMatch || upcMatch) && catMatch;
            card.style.display = show ? '' : 'none';
            if (show) any = true;
        });

        let empty = grid.querySelector('.pos-search-empty');
        if (!any && !grid.querySelector('.pos-empty')) {
            if (!empty) {
                empty = document.createElement('div');
                empty.className = 'pos-empty pos-search-empty';
                empty.innerHTML = '<i class="bi bi-search fs-1 d-block mb-2"></i>No products found.';
                grid.appendChild(empty);
            }
        } else if (empty) {
            empty.remove();
        }
    }

    // ── Add to cart ─────────────────────────────────────────────────────
    grid.addEventListener('click', function (e) {
        const card = e.target.closest('.pos-product-card');
        if (!card || card.classList.contains('out-of-stock')) return;

        const id    = card.dataset.id;
        const stock = parseInt(card.dataset.stock);

        if (cart[id]) {
            if (cart[id].qty >= stock) {
                showToast('Maximum stock reached for this item.');
                return;
            }
            cart[id].qty++;
        } else {
            cart[id] = {
                id:    id,
                name:  card.dataset.name,
                sku:   card.dataset.sku,
                price: parseFloat(card.dataset.price),
                stock: stock,
                qty:   1,
            };
        }
        renderCart();
    });

    // ── Render cart ─────────────────────────────────────────────────────
    function renderCart() {
        const ids = Object.keys(cart);

        // Cleanup removed rows
        document.querySelectorAll('.cart-item').forEach(r => r.remove());

        if (ids.length === 0) {
            cartEmpty.style.display = '';
            cartCount.textContent   = '0 items';
            grandTotal.textContent  = '₱0.00';
            itemsCountRow.style.display = 'none';
            subtotalRow.style.display = 'none';
            discountRow.style.display = 'none';
            footerItemCount.textContent = '0';
            btnCheckout.disabled = true;
            recalcChange();
            return;
        }

        cartEmpty.style.display = 'none';
        let totalQty = 0;

        ids.forEach(id => {
            const item = cart[id];
            totalQty += item.qty;

            const row = document.createElement('div');
            row.className = 'cart-item';
            row.dataset.id = id;
            const displayName = item.sku ? `${escHtml(item.sku)} - ${escHtml(item.name)}` : escHtml(item.name);
            const fullTitle = item.sku ? `${item.sku} - ${item.name}` : item.name;
            row.innerHTML = `
                <div class="cart-item-info">
                    <div class="cart-item-name" title="${escHtml(fullTitle)}">${displayName}</div>
                    <div class="cart-item-price">₱${item.price.toFixed(2)} / pc</div>
                </div>
                <div class="cart-item-controls">
                    <button class="qty-btn" data-action="dec" data-id="${id}">−</button>
                    <span class="qty-display">${item.qty}</span>
                    <button class="qty-btn" data-action="inc" data-id="${id}">+</button>
                </div>
                <div class="cart-item-sub">₱${(item.price * item.qty).toFixed(2)}</div>
                <button class="cart-remove" data-action="remove" data-id="${id}"><i class="bi bi-x"></i></button>
            `;
            cartBody.appendChild(row);
        });

        const total = cartTotal();
        cartCount.textContent       = `${totalQty} item${totalQty !== 1 ? 's' : ''}`;
        grandTotal.textContent      = `₱${total.toFixed(2)}`;
        if (subTotalEl) {
            subTotalEl.textContent = `₱${cartSubtotal().toFixed(2)}`;
        }
        if (discountTotalEl) {
            discountTotalEl.textContent = `₱${getDiscountAmount().toFixed(2)}`;
        }
        subtotalRow.style.display = '';
        discountRow.style.display = '';
        itemsCountRow.style.display = '';
        footerItemCount.textContent = totalQty;
        btnCheckout.disabled        = false;
        recalcChange();
    }

    // ── Cart controls (qty +/- / remove) ───────────────────────────────
    cartBody.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const id     = btn.dataset.id;
        const action = btn.dataset.action;

        if (action === 'inc') {
            if (cart[id].qty < cart[id].stock) cart[id].qty++;
            else showToast('Maximum stock reached.');
        } else if (action === 'dec') {
            cart[id].qty--;
            if (cart[id].qty <= 0) delete cart[id];
        } else if (action === 'remove') {
            delete cart[id];
        }
        renderCart();
    });

    // ── Payment method toggle ───────────────────────────────────────────
    document.querySelectorAll('.method-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.method-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            paymentMethod = this.dataset.method;
            if (paymentAmountLabel) {
                paymentAmountLabel.textContent = paymentMethod === 'Cash' ? 'Cash Tendered' : 'Amount Paid';
            }
            cashSection.style.display = '';
            recalcChange();
        });
    });

    cashTendered.addEventListener('input', recalcChange);
    if (discountPercentInput) {
        discountPercentInput.addEventListener('input', recalcChange);
        discountPercentInput.addEventListener('change', recalcChange);
    }

    // Force deterministic defaults so browser-restored field state cannot inject a discount.
    if (discountPercentInput) {
        discountPercentInput.value = '0';
    }
    if (discountAmountInput) {
        discountAmountInput.value = '0.00';
    }

    function recalcChange() {
        const subtotal = cartSubtotal();
        const discount = getDiscountAmount();
        const total = Math.max(0, subtotal - discount);
        const tendered = Math.max(0, parseFloat(cashTendered.value) || 0);
        const balanceDue = Math.max(0, total - tendered);

        if (subTotalEl) {
            subTotalEl.textContent = `₱${subtotal.toFixed(2)}`;
        }
        if (discountTotalEl) {
            discountTotalEl.textContent = `₱${discount.toFixed(2)}`;
        }
        if (grandTotal) {
            grandTotal.textContent = `₱${total.toFixed(2)}`;
        }

        if (balanceDue > 0) {
            balanceRow.style.display = '';
            if (balanceLabel) balanceLabel.style.display = '';
            balanceAmount.textContent = '₱' + balanceDue.toFixed(2);
            changeRow.style.display = 'none';
            if (changeLabel) changeLabel.style.display = 'none';
        } else {
            balanceRow.style.display = 'none';
            if (balanceLabel) balanceLabel.style.display = 'none';

            changeRow.style.display = '';
            if (changeLabel) changeLabel.style.display = '';
            changeAmount.textContent = '₱' + (tendered - total).toFixed(2);
        }
    }

    function cartSubtotal() {
        return Object.values(cart).reduce((sum, i) => sum + i.price * i.qty, 0);
    }

    function getDiscountAmount() {
        const subtotal = cartSubtotal();
        const rawPercent = parseFloat((discountPercentInput && discountPercentInput.value) || '0');
        const percent = Math.max(0, Math.min(100, Number.isFinite(rawPercent) ? rawPercent : 0));

        if (discountPercentInput) {
            discountPercentInput.value = percent.toString();
        }

        const computed = subtotal * (percent / 100);
        const discount = Math.min(computed, subtotal);

        if (discountAmountInput) {
            discountAmountInput.value = discount.toFixed(2);
        }

        return discount;
    }

    function cartTotal() {
        const subtotal = cartSubtotal();
        const discount = getDiscountAmount();
        return Math.max(0, subtotal - discount);
    }

    // ── Clear cart ──────────────────────────────────────────────────────
    document.getElementById('btnClearCart').addEventListener('click', function () {
        if (Object.keys(cart).length === 0) return;
        if (!confirm('Clear the entire cart?')) return;
        cart = {};
        cashTendered.value = '';
        if (discountPercentInput) discountPercentInput.value = '0';
        if (discountAmountInput) discountAmountInput.value = '0.00';
        document.getElementById('customerAddress').value = '';
        renderCart();
    });

    // ── Checkout ────────────────────────────────────────────────────────
    btnCheckout.addEventListener('click', async function () {
        const ids = Object.keys(cart);
        if (ids.length === 0) return;

        const total    = cartTotal();
        const tendered = parseFloat(cashTendered.value) || 0;
        const customerNameInput = document.getElementById('customerName');
        const customerAddressInput = document.getElementById('customerAddress');
        const customerName = (customerNameInput && customerNameInput.value || '').trim();
        const customerAddress = (customerAddressInput && customerAddressInput.value || '').trim();

        if (customerName === '' || customerAddress === '') {
            showToast('Customer name and address are required for all transactions.', 'warning');
            if (customerNameInput && customerName === '') {
                customerNameInput.focus();
            } else if (customerAddressInput && customerAddress === '') {
                customerAddressInput.focus();
            }
            return;
        }

        btnCheckout.disabled = true;
        btnCheckout.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing…';

        const items = ids.map(id => ({
            product_id: cart[id].id,
            name:  cart[id].name,
            sku:   cart[id].sku,
            price: cart[id].price,
            qty:   cart[id].qty,
        }));

        const formData = new FormData();
        formData.append('items', JSON.stringify(items));
        formData.append('payment_amount', String(Math.max(0, tendered)));
        formData.append('payment_method', paymentMethod);
        formData.append('customer_name', customerName);
        formData.append('customer_address', customerAddress);
        formData.append('voucher_code', '');
        formData.append('discount_amount', String(getDiscountAmount()));
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        try {
            const res  = await fetch('<?= site_url('pos/checkout') ?>', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                printReceipt(data);
                cart = {};
                cashTendered.value = '';
                if (discountPercentInput) discountPercentInput.value = '0';
                if (discountAmountInput) discountAmountInput.value = '0.00';
                document.getElementById('customerName').value = '';
                document.getElementById('customerAddress').value = '';
                renderCart();
                // Refresh stock counts on product cards
                data.items.forEach(item => {
                    const card = document.querySelector(`.pos-product-card[data-id="${item.product_id}"]`);
                    if (card) {
                        const newStock = parseInt(card.dataset.stock) - parseInt(item.qty);
                        card.dataset.stock = Math.max(0, newStock);
                        card.querySelector('.pos-card-stock').textContent = 'Stock: ' + Math.max(0, newStock);
                        if (newStock <= 0) card.classList.add('out-of-stock');
                    }
                });
            } else {
                showToast(data.message || 'Checkout failed.', 'danger');
            }
        } catch (err) {
            showToast('Network error. Please try again.', 'danger');
        }

        btnCheckout.disabled = false;
        btnCheckout.innerHTML = '<i class="bi bi-check-circle"></i> Checkout';
    });

    // ── Print Receipt ───────────────────────────────────────────────────
    function printReceipt(data) {
        const formatMoney = value => '₱' + Number(value || 0).toFixed(2);
        const logoUrl = '<?= 'data:image/png;base64,' . base64_encode(file_get_contents(FCPATH . 'images/agri-savers-logo.png')) ?>';
        const itemCount = Array.isArray(data.items)
            ? data.items.reduce((sum, item) => sum + ((parseInt(item.qty, 10) || 0)), 0)
            : 0;
        const computedGross = Array.isArray(data.items)
            ? data.items.reduce((sum, item) => sum + ((parseFloat(item.price || 0) * (parseInt(item.qty, 10) || 0))), 0)
            : 0;
        const grossTotal = parseFloat(data.gross_total ?? computedGross) || 0;
        const discountAmount = Math.max(0, parseFloat(data.discount_amount ?? 0) || 0);
        const grandTot = Math.max(0, parseFloat(data.net_total ?? (grossTotal - discountAmount)) || 0);
        const deposit = parseFloat(data.payment || 0);
        const tendered = parseFloat((data.tendered ?? data.payment) || 0);
        const change = Math.max(0, parseFloat(data.change || 0));
        const balanceDue = Math.max(0, grandTot - deposit);
        const taxAmount = grandTot > 0 ? (grandTot * 12 / 112) : 0;
        const cashier = escHtml(data.cashier || '');
        const customerName = escHtml(data.customer_name || 'Walk-in Customer');
        const customerAddress = escHtml(data.customer_address || '');
        const branchName = escHtml(data.branch || '');
        const branchAddress = escHtml(data.branch_address || data.branch || '');
        const receiptNo = escHtml(data.receipt_no || '');
        const orderNo = escHtml(String(data.sale_id || data.receipt_no || ''));
        const dateLine = escHtml(`${data.date || ''} ${data.time || ''}`.trim());
        const barcodeValue = String(data.receipt_no || data.sale_id || '').trim() || '0000000000';

        function buildPseudoBarcodeSvg(value) {
            const cleaned = String(value || '').toUpperCase().replace(/[^A-Z0-9\-]/g, '') || '0000000000';
            let x = 8;
            let rects = '';
            const addBar = (width) => {
                rects += `<rect x="${x}" y="0" width="${width}" height="38" fill="#000"></rect>`;
                x += width;
            };
            const addGap = (width) => {
                x += width;
            };

            [2, 1, 2, 1, 2].forEach((w, i) => (i % 2 === 0 ? addBar(w) : addGap(w)));

            cleaned.split('').forEach(ch => {
                const code = ch.charCodeAt(0);
                for (let bit = 0; bit < 7; bit++) {
                    const wide = ((code >> bit) & 1) === 1;
                    addBar(wide ? 2 : 1);
                    addGap(1);
                }
                addGap(1);
            });

            [2, 1, 2, 1, 2].forEach((w, i) => (i % 2 === 0 ? addBar(w) : addGap(w)));
            x += 8;

            return `<svg xmlns="http://www.w3.org/2000/svg" width="${x}" height="38" viewBox="0 0 ${x} 38" preserveAspectRatio="none">${rects}</svg>`;
        }

        const barcodeSvg = buildPseudoBarcodeSvg(barcodeValue);
        const barcodeDataUrl = `data:image/svg+xml;utf8,${encodeURIComponent(barcodeSvg)}`;

        let rows = '';
        data.items.forEach(item => {
            const qty = parseInt(item.qty, 10) || 0;
            const unitPrice = parseFloat(item.price || 0);
            const sub = unitPrice * qty;
            rows += `
                <div style="display:grid;grid-template-columns:28px 42px 1fr 52px 60px;gap:4px;align-items:start;font-size:9px;line-height:1.15;margin:1px 0;">
                    <div>${qty}</div>
                    <div>${escHtml(item.sku || '')}</div>
                    <div>${escHtml(item.name || '')}</div>
                    <div style="text-align:right;">${formatMoney(unitPrice)}</div>
                    <div style="text-align:right;">${formatMoney(sub)}</div>
                </div>`;
        });

        // Store receipt data globally for direct print
        window.currentReceiptHtml = `
    <div id="receipt-wrap" style="padding:1.2mm 2mm 1mm;font-family:'Courier New',monospace;font-size:12px;font-weight:600;line-height:1.28;color:#000;box-sizing:border-box;">
<div style="text-align:center;margin:0 0 1.5mm;"><img src="${logoUrl}" alt="Agri Savers G" style="max-width:38mm;max-height:12mm;height:auto;width:auto;object-fit:contain;"></div>
    <div style="text-align:center;font-size:13px;font-weight:800;line-height:1.05;margin:0;">AGRI SAVERS G AGRICULTURAL<br>PRODUCT STORE</div>
    <div style="text-align:center;font-size:12px;font-weight:800;margin:0;">${branchAddress}</div>
    <div style="text-align:center;font-size:12px;margin:0 0 1.2mm;">NON-VAT REG TIN: 612-328-260-00000</div>
<hr style="border:none;border-top:1px solid #333;margin:3px 0;">
    <div style="font-size:13px;font-weight:800;margin:0 0 1px;">Bill To:</div>
    <div style="font-size:12px;padding-left:14px;line-height:1.2;">${customerName}</div>
    <div style="font-size:12px;padding-left:14px;line-height:1.2;">${customerAddress}</div>
    <div style="display:flex;justify-content:space-between;gap:8px;font-size:12px;font-weight:800;margin:1px 0;"><span><strong>Cashier:</strong> ${cashier}</span><span><strong>Transaction #</strong> ${receiptNo}</span></div>
    <div style="font-size:12px;font-weight:800;margin:1px 0;"><strong>Order #</strong> ${orderNo}</div>
    <div style="font-size:12px;font-weight:800;margin:1px 0 2px;"><strong>Date:</strong> ${dateLine}</div>
<hr style="border:none;border-top:1px solid #333;margin:3px 0;">
    <div style="font-size:13px;font-weight:800;margin:1px 0 3px;">Order</div>
    <div style="display:grid;grid-template-columns:28px 40px 1fr 52px 60px;gap:4px;font-size:12px;font-weight:800;margin:0 0 2px;">
    <div>Qty</div><div>SKU</div><div>Products Name</div><div style="text-align:right;">Price</div><div style="text-align:right;">Total</div>
</div>
    <div>${rows || '<div style="font-size:12px;">No items</div>'}</div>
    <div style="font-size:12px;font-weight:800;margin:2px 0 1px;"><strong>Disc:</strong> ${formatMoney(discountAmount)}</div>
    <div style="font-size:12px;margin:1px 0 2px;">${itemCount} Item${itemCount !== 1 ? 's' : ''} Sold</div>
<hr style="border:none;border-top:1px solid #333;margin:3px 0;">
    <div style="display:flex;justify-content:space-between;gap:8px;font-size:12px;font-weight:800;margin:1px 0;"><span>Order Deposit:</span><span>${formatMoney(deposit)}</span></div>
    <div style="display:flex;justify-content:space-between;gap:8px;font-size:14px;font-weight:700;margin:1px 0 2px;"><span>Receipt Total:</span><span>${formatMoney(grandTot)}</span></div>
<hr style="border:none;border-top:1px solid #333;margin:3px 0;">
    <div style="text-align:center;font-size:17px;font-weight:700;margin:2px 0 1px;">Tenders</div>
    <div style="display:grid;grid-template-columns:1fr 70px;gap:8px;font-size:12px;font-weight:800;margin:0 0 2px;"><div>Payment Method</div><div style="text-align:right;">Amount</div></div>
    <div style="display:grid;grid-template-columns:1fr 70px;gap:8px;font-size:12px;font-weight:600;margin:0 0 2px;"><div>${escHtml(data.payment_method || 'Cash')}</div><div style="text-align:right;">${formatMoney(tendered)}</div></div>
    <div style="display:flex;justify-content:space-between;gap:8px;font-size:12px;font-weight:600;margin:1px 0 2px;"><span>Change:</span><span>${formatMoney(change)}</span></div>
<hr style="border:none;border-top:1px solid #333;margin:3px 0;">
    <div style="text-align:center;font-size:17px;font-weight:700;margin:2px 0 1px;">Order Summary</div>
    <div style="text-align:center;font-size:12px;font-weight:700;margin:0 0 2px;">${itemCount} items Ordered</div>
    <div style="display:flex;justify-content:space-between;gap:8px;font-size:12px;margin:1px 0;"><span>Order Subtotal:</span><span>${formatMoney(grossTotal)}</span></div>
    <div style="display:flex;justify-content:space-between;gap:8px;font-size:12px;margin:1px 0;"><span>Order Discount:</span><span>${formatMoney(discountAmount)}</span></div>
    <div style="display:flex;justify-content:space-between;gap:8px;font-size:12px;margin:1px 0;"><span>Order Tax:</span><span>${formatMoney(taxAmount)}</span></div>
    <div style="display:flex;justify-content:space-between;gap:8px;font-size:12px;margin:1px 0;"><span>Order Deposit:</span><span>${formatMoney(deposit)}</span></div>
    <div style="display:flex;justify-content:space-between;gap:8px;font-size:12px;margin:1px 0;"><span>Change:</span><span>${formatMoney(change)}</span></div>
    <div style="display:flex;justify-content:space-between;gap:8px;font-size:12px;margin:1px 0 2px;"><span>Order Balance Due:</span><span>${formatMoney(balanceDue)}</span></div>
<div style="display:flex;justify-content:center;align-items:center;margin:4px 0 2px;"><img src="${barcodeDataUrl}" alt="Barcode" style="width:60mm;height:10mm;image-rendering:pixelated;"></div>
    <div style="text-align:center;font-size:12px;font-weight:400;letter-spacing:1px;margin:0 0 3px;">${escHtml(barcodeValue)}</div>
    <div style="text-align:center;font-size:12px;font-weight:700;line-height:1.18;margin:4px 0 2px;">AGRICULTURAL PRODUCT STORE<br>AGRI SAVERS G</div>
    <div style="text-align:center;font-size:10px;line-height:1.12;margin:0 0 3px;">POLANGUI BRANCH | NABUA BRANCH | SORSOGON BRANCH | MASBATE BRANCH | PILI BRANCH | CANAMAN BRANCH | VINZON BRANCH | LIBMANAN BRANCH | CANDELARIA BRANCH | ORMOC BRANCH | LEYTE BRANCH | BOHOL BRANCH</div>
    <div style="text-align:center;font-size:13px;font-weight:800;line-height:1.15;margin:3px 0 1px;">***THIS SERVES AS A SALES INVOICE***</div>
    <div style="text-align:center;font-size:12px;font-weight:800;line-height:1.12;margin:0 0 1px;">BRING THIS RECEIPT INCASE OF EXCHANGE OF<br>MERCHANDISE WITHIN 7 DAY(S)</div>
    <div style="text-align:center;font-size:13px;font-weight:800;line-height:1.1;margin:1px 0;">NO RECEIPT NO EXCHANGE!</div>
    <div style="text-align:center;font-size:13px;font-weight:800;line-height:1.1;margin:1px 0 0;">THANK YOU AND COME AGAIN!</div>
</div>`;

        const receiptLines = [];
        receiptLines.push('[LOGO]');
        receiptLines.push('AGRI SAVERS G AGRICULTURAL PRODUCT STORE');
        receiptLines.push(String(data.branch_address || data.branch || ''));
        receiptLines.push('NON-VAT REG TIN: 612-328-260-00000');
        receiptLines.push('------------------------------------------');
        receiptLines.push('Bill To:');
        receiptLines.push('  ' + String(data.customer_name || 'Walk-in Customer'));
        if (customerAddress) receiptLines.push('  ' + customerAddress);
        receiptLines.push('Cashier: ' + String(data.cashier || '').slice(0, 10) + '   TR: ' + String(data.receipt_no || '').slice(-4));
        receiptLines.push('Order #: ' + String(data.sale_id || data.receipt_no || ''));
        if (branchName) {
            receiptLines.push('Branch: ' + branchName);
        }
        receiptLines.push('Date: ' + String(data.date || '') + ' ' + String(data.time || ''));
        receiptLines.push('------------------------------------------');
        receiptLines.push('Order');
        receiptLines.push('Qty SKU  Product Name  Price    Total');
        data.items.forEach(item => {
            const qty = parseInt(item.qty, 10) || 0;
            const sku = String(item.sku || '').slice(0, 4);
                    const desc = String(item.name || '').slice(0, 12);
            const unit = formatMoney(parseFloat(item.price || 0)).replace('₱', 'P');
            const sub = formatMoney((qty * parseFloat(item.price || 0))).replace('₱', 'P');
            receiptLines.push(`${String(qty).padEnd(3)} ${sku.padEnd(5)} ${desc.padEnd(12)} ${unit.padStart(7)} ${sub.padStart(8)}`);
        });
        receiptLines.push('Disc: ' + formatMoney(discountAmount));
        receiptLines.push(String(itemCount) + ' Item' + (itemCount !== 1 ? 's' : '') + ' Sold');
        receiptLines.push('------------------------------------------');
        receiptLines.push('Order Deposit:'.padEnd(22) + formatMoney(deposit).replace('₱', 'P'));
        receiptLines.push('Receipt Total:'.padEnd(22) + formatMoney(grandTot).replace('₱', 'P'));
        receiptLines.push('TENDERS');
        receiptLines.push('Pmt Method'.padEnd(20) + 'Amount');
        receiptLines.push(String(data.payment_method || 'Cash').padEnd(20) + formatMoney(tendered).replace('₱', 'P'));
        receiptLines.push('Change:'.padEnd(22) + formatMoney(change).replace('₱', 'P'));
        receiptLines.push('ORDER SUMMARY');
        receiptLines.push(String(itemCount) + ' items');
        receiptLines.push('Subtotal:'.padEnd(22) + formatMoney(grossTotal).replace('₱', 'P'));
        receiptLines.push('Discount:'.padEnd(22) + formatMoney(discountAmount).replace('₱', 'P'));
        receiptLines.push('Tax:'.padEnd(22) + formatMoney(taxAmount).replace('₱', 'P'));
        receiptLines.push('Deposit:'.padEnd(22) + formatMoney(deposit).replace('₱', 'P'));
        receiptLines.push('Change:'.padEnd(22) + formatMoney(change).replace('₱', 'P'));
        receiptLines.push('Balance:'.padEnd(22) + formatMoney(balanceDue).replace('₱', 'P'));
        receiptLines.push('');
        receiptLines.push('|||| ||| |||| || ||||| ||| ||||');
        receiptLines.push(String(barcodeValue));
        receiptLines.push('');
        receiptLines.push('AGRI SAVERS G - AGRICULTURAL STORE');
        receiptLines.push('Polangui | Nabua | Sorsogon | Masbate');
        receiptLines.push('Pili | Canaman | Vinzon | Libmanan');
        receiptLines.push('Candelaria | Ormoc | Leyte | Bohol');
        receiptLines.push('***THIS SERVES AS A SALES INVOICE***');
        receiptLines.push('BRING THIS RECEIPT INCASE OF EXCHANGE');
        receiptLines.push('WITHIN 7 DAY(S)');
        receiptLines.push('NO RECEIPT NO EXCHANGE!');
        receiptLines.push('THANK YOU AND COME AGAIN!');
        window.currentReceiptText = receiptLines.join('\n');

        // Show preview first, then print on user confirmation.
        openReceiptPreview();
    }

    async function loadPrinters() {
        if (printersLoaded) return;
        const sel = document.getElementById('receiptPrinterSelect');
        const hint = document.getElementById('serverPrinterHint');
        if (!sel) return;

        try {
            const res = await fetch('<?= site_url('pos/printers') ?>');
            const data = await res.json();
            if (!data.success) {
                if (hint) {
                    hint.textContent = 'Server printers unavailable. Use "Print On This PC".';
                }
                return;
            }

            sel.innerHTML = '<option value="">Default printer</option>';
            (data.printers || []).forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.name;
                opt.textContent = p.default ? `${p.name} (Default)` : p.name;
                sel.appendChild(opt);
            });

            if (data.default_printer) {
                sel.value = data.default_printer;
            }

            if (hint) {
                const host = data.source_host ? ` (${data.source_host})` : '';
                hint.textContent = `Server printers${host}`;
            }

            if (!data.printers || data.printers.length === 0) {
                showToast('No printers found on server. Use "Print On This PC" for local printers.', 'warning');
            }

            printersLoaded = true;
        } catch (e) {
            console.error('Failed to load printers', e);
            if (hint) {
                hint.textContent = 'Failed to load server printers. Use "Print On This PC".';
            }
        }
    }

    async function openReceiptPreview() {
        const frame = document.getElementById('receiptPreviewFrame');
        const modalEl = document.getElementById('receiptPreviewModal');
        if (!frame || !modalEl) {
            showToast('Receipt preview is unavailable.', 'danger');
            return;
        }

        await loadPrinters();
        const profile = getActivePrintProfile();
        const previewHtml = buildReceiptDocument(profile, true);
        frame.srcdoc = previewHtml;

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function buildReceiptDocument(profile, forPreview, overrideHeightMm = null) {
        const pageHeightMm = overrideHeightMm || profile.pageHeightMm || null;
        const pageSize = pageHeightMm ? `${profile.widthMm}mm ${pageHeightMm}mm` : `${profile.widthMm}mm auto`;
        const bg = forPreview ? '#efefef' : '#fff';
        const bodyLayout = forPreview
            ? 'display:flex;justify-content:center;align-items:flex-start;padding:8px 0;'
            : 'display:block;padding:0;margin:0;';
        const rootBoxCss = forPreview
            ? 'html,body{width:100% !important;min-width:0 !important;height:auto !important;}'
            : `html,body{width:${profile.widthMm}mm;min-width:${profile.widthMm}mm;height:auto !important;min-height:0 !important;overflow:hidden !important;}`;
        const bodyOverflowCss = forPreview ? 'overflow:auto !important;' : 'overflow:hidden !important;';
        const wrapMarginCss = forPreview ? 'margin:0 auto !important;' : 'margin:0 !important;';

        return `<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
@page{size:${pageSize};margin:0;}
*{margin:0;padding:0;box-sizing:border-box;}
html,body{background:${bg};}
${rootBoxCss}
    body{${bodyLayout}font-family:'Courier New',monospace;font-size:12px;font-weight:600;line-height:1.2;color:#000;${bodyOverflowCss}}
#receipt-wrap{width:${profile.widthMm}mm;max-width:${profile.widthMm}mm;box-sizing:border-box;background:#fff;font-weight:600;${wrapMarginCss}}
@media print {
    html, body { margin:0 !important; padding:0 !important; background:#fff !important; }
    html, body { width:${profile.widthMm}mm !important; min-width:${profile.widthMm}mm !important; height:auto !important; min-height:0 !important; overflow:hidden !important; }
    body { display:block !important; }
    #receipt-wrap { margin:0 !important; height:auto !important; min-height:0 !important; overflow:visible !important; }
}
.line-item{padding:1px 0;}
.line-item-name{font-size:12px;font-weight:600;line-height:1.15;word-break:break-word;}
.line-item-meta{display:flex;justify-content:space-between;gap:6px;font-size:12px;font-weight:600;color:#111;}
</style></head><body>${window.currentReceiptHtml || ''}</body></html>`;
    }

    function directPrintReceipt() {
        const printBtn = document.getElementById('btnConfirmPrintReceipt');
        const profile = getActivePrintProfile();
        if (printBtn) {
            printBtn.disabled = true;
            printBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Printing...';
        }

        fetch('<?php echo base_url('pos/directPrint'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                html: window.currentReceiptHtml,
                text: window.currentReceiptText,
                printer_name: (document.getElementById('receiptPrinterSelect') || {}).value || '',
                line_width: profile.lineWidth,
                profile: (document.getElementById('receiptProfileSelect') || {}).value || 'pos80_auto'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (printBtn) {
                printBtn.disabled = false;
                printBtn.innerHTML = '<i class="bi bi-printer me-1"></i> Print Now';
            }
            if (data.success) {
                const modalEl = document.getElementById('receiptPreviewModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.hide();
                }
                showToast('Receipt printed successfully!', 'success');
            } else {
                showToast('Print failed: ' + (data.message || 'Unknown error') + ' Use "Print On This PC" if this device printer is not on server.', 'danger');
            }
        })
        .catch(error => {
            if (printBtn) {
                printBtn.disabled = false;
                printBtn.innerHTML = '<i class="bi bi-printer me-1"></i> Print Now';
            }
            console.error('Print error:', error);
            showToast('Print error: ' + error.message, 'danger');
        });
    }

    function printOnThisPc() {
        if (!window.currentReceiptHtml) {
            showToast('No receipt content available to print.', 'danger');
            return;
        }

        const selectedProfile = getActivePrintProfile();
        // Local browser printing should always be compact to avoid wasting thermal paper.
        const profile = { ...selectedProfile, pageHeightMm: null };
        const html = buildReceiptDocument(profile, false);
        const tempFrame = document.createElement('iframe');
        tempFrame.style.position = 'fixed';
        tempFrame.style.right = '0';
        tempFrame.style.bottom = '0';
        tempFrame.style.width = '1px';
        tempFrame.style.height = '1px';
        tempFrame.style.border = '0';
        tempFrame.style.opacity = '0';
        document.body.appendChild(tempFrame);

        const cleanup = () => {
            if (tempFrame && tempFrame.parentNode) {
                tempFrame.parentNode.removeChild(tempFrame);
            }
        };

        tempFrame.onload = () => {
            try {
                const w = tempFrame.contentWindow;
                if (!w) {
                    cleanup();
                    showToast('Local print failed: print window unavailable.', 'danger');
                    return;
                }

                // Chromium and printer dialogs can keep tall roll-paper presets; force a short measured page.
                if (!profile.pageHeightMm && !tempFrame.dataset.measured) {
                    const doc = w.document;
                    const wrap = doc.getElementById('receipt-wrap');
                    const pxHeight = Math.max(0, Math.ceil((wrap && wrap.getBoundingClientRect)
                        ? wrap.getBoundingClientRect().height
                        : (wrap ? wrap.scrollHeight : 0)));

                    if (pxHeight > 0) {
                        const measuredMm = Math.min(900, Math.max(55, Math.ceil((pxHeight * 25.4) / 96) + 1));
                        tempFrame.dataset.measured = '1';
                        tempFrame.srcdoc = buildReceiptDocument(profile, false, measuredMm);
                        return;
                    }
                }

                w.onafterprint = cleanup;
                w.focus();
                w.print();
                setTimeout(cleanup, 5000);
            } catch (err) {
                cleanup();
                showToast('Local print failed: ' + err.message, 'danger');
            }
        };

        try {
            tempFrame.srcdoc = html;
        } catch (err) {
            cleanup();
            showToast('Local print failed: ' + err.message, 'danger');
        }
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('#btnConfirmPrintReceipt')) {
            directPrintReceipt();
        }
        if (e.target.closest('#btnPrintThisPc')) {
            printOnThisPc();
        }
    });

    document.addEventListener('change', function (e) {
        if (!e.target.closest('#receiptProfileSelect')) return;
        const modalEl = document.getElementById('receiptPreviewModal');
        if (!modalEl || !modalEl.classList.contains('show')) return;
        if (!window.currentReceiptHtml) return;
        openReceiptPreview();
    });

    // ── Returns / Void ────────────────────────────────────────────────
    const btnOpenReturnModal = document.getElementById('btnOpenReturnModal');
    const returnModalEl = document.getElementById('returnModal');
    const returnReceiptInput = document.getElementById('returnReceiptNo');
    const returnItemsChecklist = document.getElementById('returnItemsChecklist');
    const returnRefundAmountInput = document.getElementById('returnRefundAmount');
    const returnReasonInput = document.getElementById('returnReason');
    const returnSaleInfo = document.getElementById('returnSaleInfo');
    const returnComputation = document.getElementById('returnComputation');
    const returnComputationText = document.getElementById('returnComputationText');
    const returnComputationMeta = document.getElementById('returnComputationMeta');

    let loadedReturnSale = null;
    let loadedReturnItems = [];

    if (btnOpenReturnModal && returnModalEl) {
        btnOpenReturnModal.addEventListener('click', () => {
            const modal = bootstrap.Modal.getOrCreateInstance(returnModalEl);
            modal.show();
        });
    }

    document.addEventListener('click', async function (e) {
        if (e.target.closest('#btnLoadReturnSale')) {
            await loadReturnSaleItems();
        }
        if (e.target.closest('#btnProcessReturn')) {
            await submitItemReturn();
        }
        if (e.target.closest('#btnVoidSale')) {
            await submitSaleVoid();
        }
    });

    async function loadReturnSaleItems() {
        const receiptNo = (returnReceiptInput && returnReceiptInput.value || '').trim();
        if (!receiptNo) {
            showToast('Enter receipt number first.', 'warning');
            return;
        }

        if (returnItemsChecklist) {
            returnItemsChecklist.innerHTML = '<div class="text-muted small">Loading items...</div>';
        }
        try {
            const res = await fetch(`<?= site_url('pos/sale-items') ?>?receipt_no=${encodeURIComponent(receiptNo)}`);
            const data = await res.json();

            if (!data.success) {
                if (returnItemsChecklist) {
                    returnItemsChecklist.innerHTML = '<div class="text-muted small">No items available.</div>';
                }
                returnSaleInfo.textContent = data.message || 'Sale not found.';
                showToast(data.message || 'Unable to load sale items.', 'danger');
                return;
            }

            loadedReturnSale = data.sale || null;
            loadedReturnItems = Array.isArray(data.items) ? data.items : [];
            if (returnItemsChecklist) {
                if (loadedReturnItems.length === 0) {
                    returnItemsChecklist.innerHTML = '<div class="text-muted small">No remaining returnable items.</div>';
                } else {
                    returnItemsChecklist.innerHTML = loadedReturnItems.map((item) => {
                        const saleItemId = Number(item.sale_item_id || 0);
                        const remainingQty = Math.max(0, Number(item.remaining_qty || 0));
                        const unitPrice = Number(item.unit_price || 0);
                        const label = `${item.part_name || item.sku || 'Item'} | Remaining: ${remainingQty} | ₱${unitPrice.toFixed(2)}`;
                        return `
                            <div class="return-item-row">
                                <div class="return-item-check-col">
                                    <input
                                        class="form-check-input return-item-check"
                                        type="checkbox"
                                        id="returnItemCheck${saleItemId}"
                                        data-sale-item-id="${saleItemId}"
                                        data-remaining="${remainingQty}"
                                        data-unit-price="${unitPrice}"
                                    >
                                </div>
                                <label class="return-item-info" for="returnItemCheck${saleItemId}">
                                    <div class="return-item-title">${escHtml(item.part_name || item.sku || 'Item')}</div>
                                    <div class="return-item-subtext">Remaining: ${remainingQty} | ₱${unitPrice.toFixed(2)}</div>
                                </label>
                                <div class="return-item-qty-wrap">
                                    <input
                                        type="number"
                                        class="form-control form-control-sm return-item-qty"
                                        min="1"
                                        max="${remainingQty}"
                                        value="1"
                                        data-sale-item-id="${saleItemId}"
                                        data-remaining="${remainingQty}"
                                        disabled
                                    >
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            }
            clearReturnComputation();

            const customerLabel = (loadedReturnSale && loadedReturnSale.customer_name) ? ` | Customer: ${loadedReturnSale.customer_name}` : '';
            returnSaleInfo.textContent = `Loaded: ${loadedReturnSale.csi}${customerLabel}`;
            updateRefundPreview();
            showToast('Sale items loaded.', 'success');
        } catch (err) {
            if (returnItemsChecklist) {
                returnItemsChecklist.innerHTML = '<div class="text-muted small">No items available.</div>';
            }
            returnSaleInfo.textContent = 'Failed to load sale items.';
            showToast('Network error while loading sale items.', 'danger');
        }
    }

    function updateRefundPreview() {
        if (!returnRefundAmountInput || !returnItemsChecklist) {
            return;
        }

        let returnAmount = 0;
        const checks = Array.from(returnItemsChecklist.querySelectorAll('.return-item-check:checked'));
        checks.forEach((check) => {
            const saleItemId = String(check.dataset.saleItemId || '');
            const unitPrice = Number(check.dataset.unitPrice || 0);
            const remaining = Math.max(0, Number(check.dataset.remaining || 0));
            const qtyInput = returnItemsChecklist.querySelector(`.return-item-qty[data-sale-item-id="${saleItemId}"]`);
            const qty = qtyInput ? Math.max(0, parseInt(qtyInput.value || '0', 10) || 0) : 0;
            const safeQty = Math.min(qty, remaining);
            returnAmount += unitPrice * safeQty;
        });

        const saleReceivable = Number(loadedReturnSale && loadedReturnSale.account_receivable ? loadedReturnSale.account_receivable : 0);
        const salePaid = Number(loadedReturnSale && loadedReturnSale.cash_sales ? loadedReturnSale.cash_sales : 0);
        const refundableAfterBalance = Math.max(0, returnAmount - Math.max(0, saleReceivable));
        const refundAmount = Math.min(Math.max(0, salePaid), refundableAfterBalance);
        returnRefundAmountInput.value = refundAmount.toFixed(2);
    }

    if (returnItemsChecklist) {
        returnItemsChecklist.addEventListener('change', function (e) {
            const check = e.target.closest('.return-item-check');
            if (check) {
                const saleItemId = String(check.dataset.saleItemId || '');
                const qtyInput = returnItemsChecklist.querySelector(`.return-item-qty[data-sale-item-id="${saleItemId}"]`);
                if (qtyInput) {
                    qtyInput.disabled = !check.checked;
                }
            }
            updateRefundPreview();
        });

        returnItemsChecklist.addEventListener('input', function (e) {
            const qtyInput = e.target.closest('.return-item-qty');
            if (!qtyInput) {
                return;
            }

            const remaining = Math.max(0, parseInt(qtyInput.dataset.remaining || '0', 10) || 0);
            let value = Math.max(1, parseInt(qtyInput.value || '1', 10) || 1);
            value = Math.min(value, remaining > 0 ? remaining : 1);
            qtyInput.value = String(value);
            updateRefundPreview();
        });
    }

    async function submitItemReturn() {
        const receiptNo = (returnReceiptInput && returnReceiptInput.value || '').trim();
        const reason = (returnReasonInput && returnReasonInput.value || '').trim();

        const selectedItems = [];
        if (returnItemsChecklist) {
            const checks = Array.from(returnItemsChecklist.querySelectorAll('.return-item-check:checked'));
            checks.forEach((check) => {
                const saleItemId = parseInt(check.dataset.saleItemId || '0', 10) || 0;
                const remaining = Math.max(0, parseInt(check.dataset.remaining || '0', 10) || 0);
                const qtyInput = returnItemsChecklist.querySelector(`.return-item-qty[data-sale-item-id="${saleItemId}"]`);
                const qty = qtyInput ? Math.max(0, parseInt(qtyInput.value || '0', 10) || 0) : 0;

                if (saleItemId > 0 && qty > 0 && qty <= remaining) {
                    selectedItems.push({ sale_item_id: saleItemId, qty: qty });
                }
            });
        }

        if (!receiptNo || selectedItems.length === 0) {
            showToast('Receipt and at least one checked item are required.', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('receipt_no', receiptNo);
        formData.append('items', JSON.stringify(selectedItems));
        formData.append('reason', reason);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        try {
            const res = await fetch('<?= site_url('pos/process-return') ?>', { method: 'POST', body: formData });
            const data = await res.json();

            if (!data.success) {
                showToast(data.message || 'Return failed.', 'danger');
                return;
            }

            showReturnComputation(data);

            if (Array.isArray(data.restocked) && data.restocked.length > 0) {
                data.restocked.forEach((row) => bumpCardStock(row.product_id, row.qty || 0));
            } else {
                bumpCardStock(data.product_id, data.qty || 0);
            }
            await loadReturnSaleItems();
            if (returnReasonInput) returnReasonInput.value = '';
            updateRefundPreview();
            showToast(data.message || 'Return processed.', 'success');
        } catch (err) {
            showToast('Network error while processing return.', 'danger');
        }
    }

    async function submitSaleVoid() {
        const receiptNo = (returnReceiptInput && returnReceiptInput.value || '').trim();
        const reason = (returnReasonInput && returnReasonInput.value || '').trim();
        if (!receiptNo) {
            showToast('Enter receipt number first.', 'warning');
            return;
        }
        if (!confirm('Void this sale? Remaining items will be returned to stock.')) {
            return;
        }

        const formData = new FormData();
        formData.append('receipt_no', receiptNo);
        formData.append('reason', reason);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        try {
            const res = await fetch('<?= site_url('pos/void-sale') ?>', { method: 'POST', body: formData });
            const data = await res.json();

            if (!data.success) {
                showToast(data.message || 'Void failed.', 'danger');
                return;
            }

            (data.restocked || []).forEach(row => bumpCardStock(row.product_id, row.qty || 0));
            if (returnItemsChecklist) {
                returnItemsChecklist.innerHTML = '<div class="text-muted small">Load a receipt to see returnable items.</div>';
            }
            returnSaleInfo.textContent = '';
            loadedReturnSale = null;
            loadedReturnItems = [];
            clearReturnComputation();
            showToast(data.message || 'Sale voided.', 'success');
        } catch (err) {
            showToast('Network error while voiding sale.', 'danger');
        }
    }

    function showReturnComputation(data) {
        if (!returnComputation || !returnComputationText || !returnComputationMeta) {
            return;
        }

        const returnAmount = Number(data.return_amount || 0);
        const refundAmount = Number(data.refund_amount || 0);
        const balanceReduction = Number(data.balance_reduction || 0);

        returnComputationText.textContent = `Refund to customer: ${formatPeso(refundAmount)}`;
        returnComputationMeta.textContent = `Returned amount: ${formatPeso(returnAmount)} | Balance reduced: ${formatPeso(balanceReduction)}`;
        returnComputation.classList.remove('d-none');
    }

    function clearReturnComputation() {
        if (!returnComputation || !returnComputationText || !returnComputationMeta) {
            return;
        }

        returnComputation.classList.add('d-none');
        returnComputationText.textContent = '';
        returnComputationMeta.textContent = '';
    }

    function formatPeso(value) {
        return `₱${Number(value || 0).toFixed(2)}`;
    }

    function bumpCardStock(productId, qty) {
        const id = String(productId || '').trim();
        if (!id) return;

        const card = document.querySelector(`.pos-product-card[data-id="${id}"]`);
        if (!card) return;

        const current = parseInt(card.dataset.stock || '0', 10) || 0;
        const updated = Math.max(0, current + (parseInt(qty, 10) || 0));
        card.dataset.stock = String(updated);
        const stockEl = card.querySelector('.pos-card-stock');
        if (stockEl) {
            stockEl.textContent = 'Stock: ' + updated;
        }
        if (updated > 0) {
            card.classList.remove('out-of-stock');
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────
    function escHtml(str) {
        return String(str || '')
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;');
    }

    let toastEl;
    function showToast(msg, type = 'warning') {
        if (toastEl) toastEl.remove();
        toastEl = document.createElement('div');
        toastEl.className = `alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`;
        toastEl.style.cssText = 'z-index:9999;min-width:260px;font-size:14px;';
        toastEl.textContent = msg;
        document.body.appendChild(toastEl);
        setTimeout(() => toastEl && toastEl.remove(), 3500);
    }
})();
});
</script>

<!-- ── Receipt Preview Modal ── -->
<div class="modal fade" id="receiptPreviewModal" tabindex="-1" aria-labelledby="receiptPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="receiptPreviewModalLabel"><i class="bi bi-receipt me-2"></i>Receipt Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="receipt-preview-wrap">
                    <iframe id="receiptPreviewFrame" class="receipt-preview-frame" title="Receipt Preview"></iframe>
                </div>
            </div>
            <div class="modal-footer">
                <div class="me-auto d-flex align-items-center gap-2" style="min-width: 280px;">
                    <label for="receiptPrinterSelect" class="small text-muted mb-0">Printer</label>
                    <select id="receiptPrinterSelect" class="form-select form-select-sm">
                        <option value="">Default printer</option>
                    </select>
                </div>
                <div class="d-flex align-items-center gap-2" style="min-width: 250px;">
                    <label for="receiptProfileSelect" class="small text-muted mb-0">Profile</label>
                    <select id="receiptProfileSelect" class="form-select form-select-sm">
                        <option value="pos80_auto" selected>POS 80mm (Auto Length)</option>
                        <option value="pos80_fixed">POS 80x297mm (Fixed)</option>
                        <option value="windows_safe">Windows Safe (A4 dialog)</option>
                    </select>
                </div>
                <small id="serverPrinterHint" class="text-muted me-auto"></small>
                <button type="button" class="btn btn-outline-primary" id="btnPrintThisPc">
                    <i class="bi bi-display me-1"></i> Print On This PC
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning text-white" id="btnConfirmPrintReceipt">
                    <i class="bi bi-printer me-1"></i> Print Now
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── Return / Void Modal ── -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="returnModalLabel"><i class="bi bi-arrow-counterclockwise me-2"></i>Return / Void Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small mb-1" for="returnReceiptNo">Receipt Number</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="returnReceiptNo" placeholder="RCP-YYYYMMDD-0001">
                        <button class="btn btn-outline-secondary" id="btnLoadReturnSale" type="button">Load</button>
                    </div>
                    <small class="text-muted" id="returnSaleInfo"></small>
                </div>

                <div class="mb-2">
                    <label class="form-label small mb-1">Items to Return</label>
                    <div id="returnItemsChecklist" class="return-items-scroll border rounded p-2">
                        <div class="text-muted small">Load a receipt to see returnable items.</div>
                    </div>
                    <small class="text-muted">Check one or more items and adjust qty per checked row.</small>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-12">
                        <label class="form-label small mb-1" for="returnRefundAmount">Refund Amount</label>
                        <input type="text" class="form-control" id="returnRefundAmount" value="0.00" readonly>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label small mb-1" for="returnReason">Reason</label>
                    <textarea id="returnReason" class="form-control" rows="2" placeholder="Reason for return or void"></textarea>
                </div>

                <div id="returnComputation" class="alert alert-info small d-none mb-0" role="status">
                    <div id="returnComputationText" class="fw-semibold"></div>
                    <div id="returnComputationMeta" class="text-muted"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger me-auto" id="btnVoidSale">Void Entire Sale</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="btnProcessReturn">Process Return</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Barcode Scanner Modal ── -->
<div class="modal fade" id="scannerModal" tabindex="-1" aria-labelledby="scannerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title" id="scannerModalLabel"><i class="bi bi-upc-scan me-2"></i>Scan Barcode</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pb-3">
                <div class="d-flex gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-info text-white" id="btnScanModeCamera">
                        <i class="bi bi-camera me-1"></i>Camera
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnScanModeUsb">
                        <i class="bi bi-keyboard me-1"></i>USB Scanner
                    </button>
                </div>
        <div id="scanner-container">
          <div id="html5-qrcode-reader"></div>
          <div class="scan-overlay">
            <div class="scan-frame">
              <div class="corner tl"></div>
              <div class="corner tr"></div>
              <div class="corner bl"></div>
              <div class="corner br"></div>
            </div>
          </div>
        </div>
        <div id="scan-status">Point camera at a barcode…</div>
        <div id="scan-last"></div>
                <div class="mt-3 d-flex gap-2" id="camera-controls">
          <select id="scanCameraSelect" class="form-select form-select-sm" style="font-size:12px;"></select>
          <button class="btn btn-sm btn-outline-secondary" id="btnFlipCamera" style="white-space:nowrap;">
            <i class="bi bi-arrow-repeat"></i> Flip
          </button>
        </div>
                <div id="usb-scan-panel" class="mt-3" style="display:none;">
                    <label for="usbScanInput" class="form-label small mb-1">Scan using USB puncher (keyboard scanner), then press Enter:</label>
                    <input type="text" id="usbScanInput" class="form-control" autocomplete="off" placeholder="Waiting for barcode input...">
                </div>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
    // ── Scanner globals ─────────────────────────────────────────────────
    let scanner = null;
    let lastScanned = '';
    let lastScannedTime = 0;
    let cameras = [];
    let currentCameraIdx = 0;
    let scanMode = 'camera';

    const beepCtx = (() => { try { return new (window.AudioContext || window.webkitAudioContext)(); } catch(e){ return null; } })();
    function beep(freq = 880, dur = 120, vol = 0.3) {
        if (!beepCtx) return;
        const osc  = beepCtx.createOscillator();
        const gain = beepCtx.createGain();
        osc.connect(gain); gain.connect(beepCtx.destination);
        osc.type = 'square'; osc.frequency.value = freq;
        gain.gain.setValueAtTime(vol, beepCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, beepCtx.currentTime + dur / 1000);
        osc.start(); osc.stop(beepCtx.currentTime + dur / 1000);
    }

    const scanModal    = document.getElementById('scannerModal');
    const scanStatus   = document.getElementById('scan-status');
    const scanLast     = document.getElementById('scan-last');
    const camSelect    = document.getElementById('scanCameraSelect');
    const scanContainer = document.getElementById('scanner-container');
    const cameraControls = document.getElementById('camera-controls');
    const usbPanel = document.getElementById('usb-scan-panel');
    const usbInput = document.getElementById('usbScanInput');
    const btnModeCamera = document.getElementById('btnScanModeCamera');
    const btnModeUsb = document.getElementById('btnScanModeUsb');

    // Open scanner
    document.getElementById('btnOpenScanner').addEventListener('click', function () {
        const modal = new bootstrap.Modal(scanModal);
        modal.show();
    });

    scanModal.addEventListener('shown.bs.modal', () => {
        setScanMode(scanMode);
    });
    scanModal.addEventListener('hide.bs.modal',  stopScanner);

    btnModeCamera.addEventListener('click', () => setScanMode('camera'));
    btnModeUsb.addEventListener('click', () => setScanMode('usb'));

    usbInput.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const code = this.value.trim();
        if (!code) return;
        this.value = '';
        onScanSuccess(code);
    });

    function setScanMode(mode) {
        scanMode = mode === 'usb' ? 'usb' : 'camera';

        const isCamera = scanMode === 'camera';
        scanContainer.style.display = isCamera ? '' : 'none';
        cameraControls.style.display = isCamera ? 'flex' : 'none';
        usbPanel.style.display = isCamera ? 'none' : '';

        btnModeCamera.className = `btn btn-sm ${isCamera ? 'btn-info text-white' : 'btn-outline-secondary'}`;
        btnModeUsb.className = `btn btn-sm ${isCamera ? 'btn-outline-secondary' : 'btn-info text-white'}`;

        if (isCamera) {
            scanStatus.textContent = 'Starting camera…';
            scanLast.textContent = '';
            startScanner();
        } else {
            stopScanner();
            scanStatus.textContent = 'Ready for USB scanner input.';
            scanLast.textContent = '';
            setTimeout(() => usbInput.focus(), 80);
        }
    }

    async function startScanner() {
        if (scanMode !== 'camera') return;
        scanStatus.textContent = 'Starting camera…';
        scanLast.textContent   = '';

        try {
            cameras = await Html5Qrcode.getCameras();
        } catch(e) {
            scanStatus.textContent = 'Camera access denied. Please allow camera permission.';
            return;
        }

        if (!cameras || cameras.length === 0) {
            scanStatus.textContent = 'No camera found on this device.';
            return;
        }

        // Populate camera selector
        camSelect.innerHTML = '';
        cameras.forEach((cam, idx) => {
            const opt = document.createElement('option');
            opt.value = idx;
            opt.textContent = cam.label || `Camera ${idx + 1}`;
            camSelect.appendChild(opt);
        });
        // Prefer back camera
        const backIdx = cameras.findIndex(c => /back|rear|environment/i.test(c.label));
        currentCameraIdx = backIdx >= 0 ? backIdx : cameras.length - 1;
        camSelect.value = currentCameraIdx;

        launchScanner(cameras[currentCameraIdx].id);
    }

    function launchScanner(cameraId) {
        if (scanner) {
            scanner.stop().catch(()=>{}).then(() => { scanner.clear(); startWithCamera(cameraId); });
        } else {
            startWithCamera(cameraId);
        }
    }

    function startWithCamera(cameraId) {
        scanner = new Html5Qrcode('html5-qrcode-reader', { verbose: false });
        scanner.start(
            cameraId,
            { fps: 15, qrbox: { width: 220, height: 120 }, aspectRatio: 1.6 },
            onScanSuccess,
            () => {}
        ).then(() => {
            scanStatus.textContent = 'Point camera at a barcode…';
        }).catch(err => {
            scanStatus.textContent = 'Could not start camera: ' + err;
        });
    }

    function stopScanner() {
        if (scanner) {
            scanner.stop().catch(()=>{}).finally(() => { scanner.clear(); scanner = null; });
        }
    }

    // Camera selector change
    camSelect.addEventListener('change', function () {
        currentCameraIdx = parseInt(this.value);
        if (cameras[currentCameraIdx]) launchScanner(cameras[currentCameraIdx].id);
    });

    // Flip button (cycle cameras)
    document.getElementById('btnFlipCamera').addEventListener('click', function () {
        if (cameras.length < 2) return;
        currentCameraIdx = (currentCameraIdx + 1) % cameras.length;
        camSelect.value = currentCameraIdx;
        launchScanner(cameras[currentCameraIdx].id);
    });

    // ── Handle a successful scan ────────────────────────────────────────
    async function onScanSuccess(decodedText) {
        const now = Date.now();
        // Debounce: ignore same code within 1.5 s
        if (decodedText === lastScanned && now - lastScannedTime < 1500) return;
        lastScanned     = decodedText;
        lastScannedTime = now;

        const code = decodedText.trim();
        let card  = findProductCard(code);

        if (!card) {
            card = await findCardFromServerLookup(code);
        }

        if (card && !card.classList.contains('out-of-stock')) {
            handleMatchedCard(card, code);
        } else if (card && card.classList.contains('out-of-stock')) {
            beep(220, 300);
            scanStatus.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Out of stock: ${escHtmlScan(card.dataset.name)}</span>`;
            scanLast.textContent = `Barcode: ${code}`;
        }
    }

    function handleMatchedCard(card, code) {
        beep(880, 120);
        scanContainer.classList.add('scan-success-flash');
        setTimeout(() => scanContainer.classList.remove('scan-success-flash'), 400);

        // Add to cart (same as clicking the card)
        card.click();

        scanStatus.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Added: ${escHtmlScan(card.dataset.name)}</span>`;
        scanLast.textContent = `Barcode: ${code}`;
    }

    async function findCardFromServerLookup(code) {
        try {
            const res = await fetch(`<?= site_url('pos/scan-lookup') ?>?code=${encodeURIComponent(code)}`);
            const data = await res.json();

            if (data?.status === 'found' || data?.status === 'out_of_stock') {
                const productId = String(data?.product?.product_id || '');
                const card = productId
                    ? document.querySelector(`.pos-product-card[data-id="${CSS.escape(productId)}"]`)
                    : null;

                if (card) {
                    if (data.status === 'out_of_stock') {
                        card.classList.add('out-of-stock');
                        if (card.querySelector('.pos-card-stock')) {
                            card.querySelector('.pos-card-stock').textContent = 'Stock: 0';
                        }
                    }
                    return card;
                }

                if (data.status === 'out_of_stock') {
                    beep(220, 300);
                    scanStatus.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Out of stock: ${escHtmlScan(data?.product?.part_name || code)}</span>`;
                    scanLast.textContent = `Barcode: ${code}`;
                    return null;
                }
            }

            if (data?.status === 'other_branch') {
                beep(220, 300);
                scanStatus.innerHTML = `<span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Found in branch: ${escHtmlScan(data?.product?.branch || 'Unknown')}</span>`;
                scanLast.textContent = `Barcode: ${code}`;
                return null;
            }

            if (data?.status === 'stock_only') {
                beep(220, 300);
                const stockQty = parseInt(data?.stock?.stocks ?? 0, 10);
                scanStatus.innerHTML = `<span class="text-warning"><i class="bi bi-exclamation-circle me-1"></i>Stock exists (SKU: ${escHtmlScan(data?.stock?.sku || '-')}, Qty: ${Number.isNaN(stockQty) ? 0 : stockQty}) but no matching product in POS.</span>`;
                scanLast.textContent = `Barcode: ${code}`;
                return null;
            }
        } catch (e) {
            // Fall back to local matching message below.
        }

        beep(220, 300);
        scanStatus.innerHTML = `<span class="text-warning"><i class="bi bi-exclamation-circle me-1"></i>No product found for: ${escHtmlScan(code)}</span>`;
        scanLast.textContent = '';
        return null;
    }

    function findProductCard(code) {
        const candidates = barcodeCandidates(code);
        if (!candidates.length) return null;

        const cards = document.querySelectorAll('.pos-product-card');
        for (const card of cards) {
            const values = [
                card.dataset.upc || '',
                card.dataset.sku || '',
                card.dataset.partNo || '',
                card.dataset.id || '',
            ];

            const normalizedValues = new Set(values.flatMap(v => barcodeCandidates(v)));
            for (const c of candidates) {
                if (normalizedValues.has(c)) {
                    return card;
                }
            }
        }

        return null;
    }

    function barcodeCandidates(code) {
        const raw = String(code || '').trim();
        if (!raw) return [];

        const digits = raw.replace(/\D/g, '');
        const set = new Set([raw]);

        if (digits) {
            set.add(digits);
            set.add(digits.replace(/^0+/, ''));
            if (digits.length === 13 && digits.startsWith('0')) {
                set.add(digits.slice(1));
            }
            if (digits.length > 12) {
                set.add(digits.slice(-12));
            }
        }

        return Array.from(set).filter(Boolean);
    }

    function escHtmlScan(str) {
        return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
})();
</script>

<?php $this->endSection(); ?>
