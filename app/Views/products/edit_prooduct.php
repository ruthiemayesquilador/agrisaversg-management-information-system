<!-- ===================== EDIT PRODUCT MODAL ===================== -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg,  #f57c00 100%);">
                <h5 class="modal-title text-white fw-bold" id="editProductModalLabel">
                    <i class="fas fa-edit me-2"></i> Edit Product
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= base_url('products/update') ?>" method="post" enctype="multipart/form-data" id="editProductForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" id="edit-product-id" name="product_id">

                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 1.5rem;">

                    <!-- Product Image Upload -->
                    <div class="mb-4 text-center">
                        <label class="form-label fw-semibold d-block mb-2">Product Image</label>
                        <div id="editImagePreviewWrapper" style="width:130px; height:130px; margin:0 auto 10px; border:2px dashed #f57c00; border-radius:10px; overflow:hidden; background:#fff8f3; display:flex; align-items:center; justify-content:center; cursor:pointer;" onclick="document.getElementById('edit_product_image').click()">
                            <img id="editImagePreview" src="" alt="" style="width:100%; height:100%; object-fit:cover; display:none;">
                            <span id="editImagePlaceholder" class="text-muted" style="font-size:12px; text-align:center; padding:8px;">
                                <i class="fas fa-camera fa-2x mb-1 d-block" style="color:#ff9800;"></i>Click to change
                            </span>
                        </div>
                        <input type="file" id="edit_product_image" name="product_image" accept="image/*" class="d-none">
                    </div>

                    <div class="row g-3">
                        <!-- Product Name -->
                        <div class="col-12">
                            <label for="edit_product_name" class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" id="edit_product_name" name="product_name" class="form-control" required>
                            <div class="invalid-feedback">Product name is required.</div>
                        </div>

                        <!-- Category -->
                        <div class="col-md-3">
                            <label for="edit_category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select id="edit_category_id" name="category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= esc((string) $cat['category_id']) ?>"><?= esc((string) $cat['category_name']) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="1">Body, Frame &amp; Bumpers</option>
                                    <option value="2">Engine Parts</option>
                                    <option value="3">Brake System</option>
                                    <option value="4">Tires &amp; Accessories</option>
                                    <option value="5">Electronics</option>
                                    <option value="6">Fuel System</option>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>

                        <!-- SKU -->
                        <div class="col-md-3">
                            <label for="edit_sku" class="form-label fw-semibold">SKU</label>
                            <input type="text" id="edit_sku" name="sku" class="form-control">
                        </div>

                        <!-- Part Number -->
                        <div class="col-md-3">
                            <label for="edit_part_no" class="form-label fw-semibold">Part Number</label>
                            <input type="text" id="edit_part_no" name="part_no" class="form-control">
                        </div>

                        <!-- Price -->
                        <div class="col-md-6">
                            <label for="edit_price" class="form-label fw-semibold">Price (₱) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0" id="edit_price" name="price" class="form-control" required>
                            </div>
                            <div class="invalid-feedback">Price is required.</div>
                        </div>

                        <!-- Beginning Inventory (Read-Only) -->
                        <div class="col-md-6">
                            <label for="edit_beg_inv" class="form-label fw-semibold">Beginning Inventory (Beg Inv) - pcs</label>
                            <input type="number" id="edit_beg_inv" name="beg_inv" class="form-control" readonly style="background-color: #f0f0f0;">
                            <small class="form-text text-muted d-block mt-1">Initial stock count in pieces (cannot be changed after product creation)</small>
                        </div>

                        <!-- Current Stocks (Computed) -->
                        <div class="col-md-6">
                            <label for="edit_current_stock" class="form-label fw-semibold">Current Stocks (System-Calculated)</label>
                            <input type="number" id="edit_current_stock" name="current_stock_display" class="form-control" readonly style="background-color: #e8f5e9;">
                            <small class="form-text text-muted d-block mt-1">Auto-calculated from Beginning Inventory ± Stock movements</small>
                        </div>

                        <!-- Supplier -->
                        <div class="col-md-6">
                            <label for="edit_supplier_id" class="form-label fw-semibold">Supplier</label>
                            <select id="edit_supplier_id" name="supplier_id" class="form-select">
                                <option value="">-- Select Supplier --</option>
                                <?php if (!empty($suppliers)): ?>
                                    <?php foreach ($suppliers as $sup): ?>
                                        <option value="<?= esc((string) $sup['supplier_id']) ?>"><?= esc((string) $sup['supplier_name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label fw-semibold">Status</label>
                            <select id="edit_status" name="status" class="form-select">
                                <option value="">-- Select Status --</option>
                                <option value="Available">Available</option>
                                <option value="Low Stock">Low Stock</option>
                                <option value="Out of Stock">Out of Stock</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <div class="me-auto text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Fields marked <span class="text-danger">*</span> are required.
                    </div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg,  #ec7600 100%);">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END EDIT PRODUCT MODAL =================== -->

<script>
// Populate Edit Modal on open
document.getElementById('editProductModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    if (!btn) return;
    const d = btn.dataset;

    const textOrEmpty = (value) => (value ?? '').toString().trim();
    const setSelectValue = (selectId, value) => {
        const selectEl = document.getElementById(selectId);
        const target = textOrEmpty(value);
        let matched = false;
        [...selectEl.options].forEach((o) => {
            const isMatch = o.value === target;
            o.selected = isMatch;
            if (isMatch) matched = true;
        });
        if (!matched) {
            selectEl.value = '';
        }
    };

    const skuValue = textOrEmpty(d.sku);
    const partNoValue = textOrEmpty(d.partno);
    document.getElementById('edit-product-id').value   = d.id       || '';
    document.getElementById('edit_product_name').value = d.name     || '';
    document.getElementById('edit_sku').value          = skuValue;
    document.getElementById('edit_part_no').value      = partNoValue;

    // Category
    setSelectValue('edit_category_id', d.catid);

    // Supplier
    setSelectValue('edit_supplier_id', d.supplierid);

    // Price: strip ₱ and commas
    const rawPrice = (d.price || '').replace(/[₱,]/g, '');
    document.getElementById('edit_price').value = rawPrice;

    // Beginning Inventory (read-only) and Current Stocks (computed)
    const begInvValue = parseInt(d.begInv || d['beg-inv'] || 0);
    const currentStockValue = parseInt(d.currentStock || d['current-stock'] || 0);
    document.getElementById('edit_beg_inv').value = begInvValue;
    document.getElementById('edit_current_stock').value = currentStockValue;

    // Status
    setSelectValue('edit_status', d.status || 'Available');

    // Image preview
    const img = document.getElementById('editImagePreview');
    const placeholder = document.getElementById('editImagePlaceholder');
    if (d.img) {
        img.src = d.img;
        img.style.display = 'block';
        placeholder.style.display = 'none';
    } else {
        img.src = '';
        img.style.display = 'none';
        placeholder.style.display = 'block';
    }
});

// Live image preview on file change
document.getElementById('edit_product_image').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById('editImagePreview');
        img.src = e.target.result;
        img.style.display = 'block';
        document.getElementById('editImagePlaceholder').style.display = 'none';
    };
    reader.readAsDataURL(file);
});

// Bootstrap validation
document.getElementById('editProductForm').addEventListener('submit', function (e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});

// Reset on close
document.getElementById('editProductModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('editProductForm');
    form.reset();
    form.classList.remove('was-validated');
    document.getElementById('editImagePreview').style.display = 'none';
    document.getElementById('editImagePlaceholder').style.display = 'block';
});
</script>
