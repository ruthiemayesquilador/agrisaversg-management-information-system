<!-- ===================== NEW STOCK MODAL ===================== -->
<div class="modal fade" id="newStockModal" tabindex="-1" aria-labelledby="newStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                <h5 class="modal-title text-white fw-bold" id="newStockModalLabel">
                    <i class="bi bi-plus-circle me-2"></i> New Stock
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('stocks/store') ?>" method="post" id="newStockForm" class="needs-validation" novalidate enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 1.5rem;">
                    <div class="row g-3">
                        <!-- Product Image Display -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Product Image</label>
                            <div style="text-align: center; background: #f8f9fa; border: 2px dashed #dee2e6; padding: 20px; border-radius: 8px; min-height: 220px; display: flex; align-items: center; justify-content: center;">
                                <div id="ns_image_container" style="display: none; width: 100%;">
                                    <img id="ns_product_image" src="" alt="Product Image" style="max-width: 100%; max-height: 200px; object-fit: contain;">
                                </div>
                                <div id="ns_image_placeholder" style="color: #999; font-size: 13px; text-align: center;">Select a product to display its image</div>
                            </div>
                        </div>
                        
                        <!-- Product Selection with Search -->
                        <div class="col-12">
                            <label for="ns_product_id" class="form-label fw-semibold">Part Name <span class="text-danger">*</span></label>
                            <select class="form-select" id="ns_product_id" name="product_id" required style="width: 100%;">
                                <option value="">Select a product...</option>
                            </select>
                            <div class="invalid-feedback">Please select a product.</div>
                        </div>

                        <!-- Auto-filled Product Fields (Read-Only) -->
                        <div class="col-md-4">
                            <label for="ns_sku" class="form-label fw-semibold">SKU</label>
                            <input type="text" class="form-control" id="ns_sku" name="sku" placeholder="Auto-filled" readonly style="background-color: #f0f0f0;">
                        </div>
                        <div class="col-md-4">
                            <label for="ns_no" class="form-label fw-semibold">No.</label>
                            <input type="text" class="form-control" id="ns_no" name="no" placeholder="Auto-filled" readonly style="background-color: #f0f0f0;">
                        </div>
                        <div class="col-md-4">
                            <label for="ns_part_no" class="form-label fw-semibold">Part No.</label>
                            <input type="text" class="form-control" id="ns_part_no" name="part_no" placeholder="Auto-filled" readonly style="background-color: #f0f0f0;">
                        </div>

                        <div class="col-md-6">
                            <label for="ns_part_name" class="form-label fw-semibold">Product Name</label>
                            <input type="text" class="form-control" id="ns_part_name" name="part_name" placeholder="Auto-filled" readonly style="background-color: #f0f0f0;">
                        </div>
                        <div class="col-md-6">
                            <label for="ns_price" class="form-label fw-semibold">Price (₱)</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="ns_price" name="price" placeholder="Auto-filled" readonly style="background-color: #f0f0f0;">
                            </div>
                        </div>

                        <!-- Stock-specific Fields (Editable) -->
                        <div class="col-md-6">
                            <label for="ns_items_in" class="form-label fw-semibold">Item's In</label>
                            <input type="number" min="0" class="form-control" id="ns_items_in" name="items_in" placeholder="0">
                        </div>
                        <div class="col-md-6">
                            <label for="ns_items_out" class="form-label fw-semibold">Item's Out</label>
                            <input type="number" min="0" class="form-control" id="ns_items_out" name="items_out" placeholder="0">
                        </div>

                        <div class="col-12">
                            <label for="ns_stocks" class="form-label fw-semibold">Total Stock (Calculated)</label>
                            <input type="number" min="0" class="form-control" id="ns_stocks" name="stocks" placeholder="0" readonly style="background-color: #f0f0f0; font-weight: bold;">
                        </div>

                        <div class="col-md-6">
                            <label for="ns_remarks_in" class="form-label fw-semibold">Remarks (In)</label>
                            <textarea class="form-control" id="ns_remarks_in" name="remarks_in" rows="2" placeholder="e.g. Shipment New Arrival, Packing list reference"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="ns_remarks_out" class="form-label fw-semibold">Remarks (Out)</label>
                            <textarea class="form-control" id="ns_remarks_out" name="remarks_out" rows="2" placeholder="e.g. Delivered to Pili branch"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <div class="me-auto text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Fields marked <span class="text-danger">*</span> are required.
                    </div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                        <i class="bi bi-check-circle me-1"></i> Save Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END NEW STOCK MODAL =================== -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for product selection
    const productSelect = $('#ns_product_id');
    
    productSelect.select2({
        placeholder: 'Search for a product by name, SKU, or part number...',
        allowClear: true,
        ajax: {
            url: '<?= site_url("products/getProductsJson") ?>',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    search: params.term || ''
                };
            },
            processResults: function(data) {
                return {
                    results: data.results || []
                };
            },
            cache: true
        },
        templateResult: function(data) {
            if (!data.id) return data.text;
            const partName = data.text || 'Unknown Product';
            const sku = (data.sku && data.sku !== '—') ? data.sku : 'N/A';
            const partNo = (data.part_no && data.part_no !== '—') ? data.part_no : 'N/A';
            const price = parseFloat(data.price || 0).toFixed(2);
            return $('<div><strong style="font-size: 14px;">' + partName + '</strong><br><small style="color: #888; font-size: 12px;">SKU: ' + sku + ' | Part No: ' + partNo + ' | ₱' + price + '</small></div>');
        },
        templateSelection: function(data) {
            if (!data.id) return data.text;
            return data.text || 'Unknown Product';
        },
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Auto-fill product fields when a product is selected
    productSelect.on('select2:select', function(e) {
        const selectedProduct = e.params.data;
        
        console.log('Selected Product:', selectedProduct); // Debug: log product data
        
        // Fill the text fields
        document.getElementById('ns_sku').value = selectedProduct.sku || '';
        document.getElementById('ns_no').value = selectedProduct.sku || ''; // Item No. is typically same as SKU
        document.getElementById('ns_part_no').value = selectedProduct.part_no || '';
        document.getElementById('ns_part_name').value = selectedProduct.text || '';
        document.getElementById('ns_price').value = (parseFloat(selectedProduct.price) || 0).toFixed(2);
        
        // Display product image
        const productImage = document.getElementById('ns_product_image');
        const imageContainer = document.getElementById('ns_image_container');
        const imagePlaceholder = document.getElementById('ns_image_placeholder');
        
        // Get image URL from API
        let imageUrl = selectedProduct.image_url;
        console.log('Image URL from API:', imageUrl); // Debug: log API URL
        
        if (imageUrl) {
            productImage.src = imageUrl;
            
            // Show container with image
            imageContainer.style.display = 'block';
            imagePlaceholder.style.display = 'none';
            
            productImage.onerror = function() {
                console.log('Image failed to load from:', imageUrl); // Debug
                // Show placeholder if image fails
                imageContainer.style.display = 'none';
                imagePlaceholder.style.display = 'block';
                imagePlaceholder.textContent = 'Image not found';
            };
            
            productImage.onload = function() {
                console.log('Image loaded successfully'); // Debug
                imageContainer.style.display = 'block';
                imagePlaceholder.style.display = 'none';
            };
        } else {
            imageContainer.style.display = 'none';
            imagePlaceholder.style.display = 'block';
            imagePlaceholder.textContent = 'No image available';
        }
        
        // Trigger calculation
        syncNewStockTotals();
    });

    // Clear form when modal is hidden
    document.getElementById('newStockModal').addEventListener('hidden.bs.modal', function () {
        const form = document.getElementById('newStockForm');
        form.reset();
        form.classList.remove('was-validated');
        productSelect.val(null).trigger('change');
    });

    // Calculate total stock from items in and items out
    function syncNewStockTotals() {
        const itemsIn = document.getElementById('ns_items_in');
        const itemsOut = document.getElementById('ns_items_out');
        const stocks = document.getElementById('ns_stocks');

        if (!itemsIn || !itemsOut || !stocks) {
            return;
        }

        const compute = function () {
            const i = parseInt(itemsIn.value, 10) || 0;
            const o = parseInt(itemsOut.value, 10) || 0;
            stocks.value = Math.max(0, i - o);
        };

        ['input', 'change'].forEach(function (evt) {
            itemsIn.addEventListener(evt, compute);
            itemsOut.addEventListener(evt, compute);
        });

        compute();
    }

    // Initial sync
    syncNewStockTotals();

    // Form validation
    document.getElementById('newStockForm').addEventListener('submit', function (e) {
        if (!this.checkValidity()) { 
            e.preventDefault(); 
            e.stopPropagation(); 
        }
        this.classList.add('was-validated');
    });
});
</script>