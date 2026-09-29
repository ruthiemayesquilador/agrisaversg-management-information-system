<!-- ===================== ADD PRODUCT MODAL ===================== -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #FF8C42 0%, #FF7A2F 100%);">
                <h5 class="modal-title text-white fw-bold" id="addProductModalLabel">
                    <i class="fas fa-plus-circle me-2"></i> Add New Product
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= base_url('products/store') ?>" method="post" enctype="multipart/form-data" id="addProductForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 1.5rem;">

                    <!-- Product Image Upload -->
                    <div class="mb-4 text-center">
                        <label class="form-label fw-semibold d-block mb-2">Product Image</label>
                        <div id="imagePreviewWrapper" style="width:130px; height:130px; margin:0 auto 10px; border:2px dashed #FF8C42; border-radius:10px; overflow:hidden; background:#fff8f3; display:flex; align-items:center; justify-content:center; cursor:pointer;" onclick="document.getElementById('product_image').click()">
                            <img id="imagePreview" src="" alt="" style="width:100%; height:100%; object-fit:cover; display:none;">
                            <span id="imagePlaceholder" class="text-muted" style="font-size:12px; text-align:center; padding:8px;">
                                <i class="fas fa-camera fa-2x mb-1 d-block" style="color:#FF8C42;"></i>Click to upload
                            </span>
                        </div>
                        <input type="file" id="product_image" name="product_image" accept="image/*" class="d-none">
                    </div>

                    <div class="row g-3">
                        <!-- Product Name -->
                        <div class="col-12">
                            <label for="product_name" class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" id="product_name" name="product_name" class="form-control" placeholder="e.g. ASSY. TRUCK ROLLER DC70" required>
                            <div class="invalid-feedback">Product name is required.</div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3" placeholder="Short notes about the product"></textarea>
                        </div>

                        <!-- Category -->
                        <div class="col-md-3">
                            <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select id="category_id" name="category_id" class="form-select" required>
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
                            <label for="sku" class="form-label fw-semibold">SKU</label>
                            <input type="text" id="sku" name="sku" class="form-control" placeholder="e.g. 5T078-23100">
                        </div>

                        <!-- Part Number -->
                        <div class="col-md-3">
                            <label for="part_no" class="form-label fw-semibold">Part Number</label>
                            <input type="text" id="part_no" name="part_no" class="form-control" placeholder="e.g. 5T078-23100-PN">
                        </div>

                        <!-- Price -->
                        <div class="col-md-6">
                            <label for="price" class="form-label fw-semibold">Price (₱) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0" id="price" name="price" class="form-control" placeholder="0.00" required>
                            </div>
                            <div class="invalid-feedback">Price is required.</div>
                        </div>

                        <!-- Beginning Inventory -->
                        <div class="col-md-6">
                            <label for="beg_inv" class="form-label fw-semibold">Beginning Inventory (Beg Inv) - pcs <span class="text-danger">*</span></label>
                            <input type="number" min="0" id="beg_inv" name="beg_inv" class="form-control" placeholder="0" required>
                            <div class="invalid-feedback">Beginning Inventory is required.</div>
                            <small class="form-text text-muted d-block mt-1">Initial stock count in pieces (pcs). System will automatically calculate total stocks from here.</small>
                        </div>

                        <!-- Supplier -->
                        <div class="col-md-6">
                            <label for="supplier_id" class="form-label fw-semibold">Supplier</label>
                            <select id="supplier_id" name="supplier_id" class="form-select">
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
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="" selected>-- Select Status --</option>
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
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #FF8C42 0%, #FF7A2F 100%);">
                        <i class="fas fa-plus-circle me-1"></i> Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END ADD PRODUCT MODAL =================== -->

<script>
// Image preview
document.getElementById('product_image').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById('imagePreview');
        const placeholder = document.getElementById('imagePlaceholder');
        img.src = e.target.result;
        img.style.display = 'block';
        placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
});

// Bootstrap validation
document.getElementById('addProductForm').addEventListener('submit', function (e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});

// Reset form & preview when modal is closed
document.getElementById('addProductModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('addProductForm');
    form.reset();
    form.classList.remove('was-validated');
    const img = document.getElementById('imagePreview');
    img.src = '';
    img.style.display = 'none';
    document.getElementById('imagePlaceholder').style.display = 'block';
});
</script>
