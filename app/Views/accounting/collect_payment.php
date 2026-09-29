<!-- ===================== COLLECT PAYMENT MODAL ===================== -->
<div class="modal fade" id="collectPaymentModal" tabindex="-1" aria-labelledby="collectPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                <h5 class="modal-title text-white fw-bold" id="collectPaymentModalLabel">
                    <i class="bi bi-cash-coin me-2"></i> Collect Customer Payment
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= site_url('accounting/collectPayment') ?>" method="post" id="collectPaymentForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="modal-body" style="padding: 1.2rem;">
                    <div class="mb-2">
                        <label for="collect_customer_name" class="form-label fw-semibold">Customer</label>
                        <input type="text" class="form-control" id="collect_customer_name" name="customer_name" readonly required>
                    </div>

                    <div class="mb-2">
                        <label for="collect_customer_address" class="form-label fw-semibold">Address</label>
                        <input type="text" class="form-control" id="collect_customer_address" readonly>
                    </div>

                    <div class="mb-2">
                        <label for="collect_outstanding_balance" class="form-label fw-semibold">Outstanding Balance</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="text" class="form-control" id="collect_outstanding_balance" readonly>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="collect_payment_amount" class="form-label fw-semibold">Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="collect_payment_amount" name="payment_amount" placeholder="0.00" required>
                            <button type="button" class="btn btn-outline-success" id="btnCollectFullAmount">Full</button>
                        </div>
                        <div class="invalid-feedback">Enter a valid payment amount.</div>
                        <small class="text-muted" id="collect_remaining_hint">Remaining after payment: ₱0.00</small>
                    </div>

                    <div class="mb-2">
                        <label for="collect_payment_method" class="form-label fw-semibold">Payment Method</label>
                        <select class="form-select" id="collect_payment_method" name="payment_method">
                            <option value="Cash" selected>Cash</option>
                            <option value="GCash">GCash</option>
                            <option value="Card">Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label for="collect_notes" class="form-label fw-semibold">Notes (optional)</label>
                        <textarea class="form-control" id="collect_notes" name="notes" rows="2" placeholder="Reference no., collector notes, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                        <i class="bi bi-check2-circle me-1"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END COLLECT PAYMENT MODAL =================== -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('collectPaymentModal');
    const form = document.getElementById('collectPaymentForm');
    if (!modalEl || !form) {
        return;
    }

    const customerInput = document.getElementById('collect_customer_name');
    const addressInput = document.getElementById('collect_customer_address');
    const outstandingInput = document.getElementById('collect_outstanding_balance');
    const paymentInput = document.getElementById('collect_payment_amount');
    const remainingHint = document.getElementById('collect_remaining_hint');
    const fullBtn = document.getElementById('btnCollectFullAmount');

    let outstanding = 0;

    const formatMoney = (value) => Number(value || 0).toFixed(2);

    const refreshRemaining = () => {
        const payment = Number(paymentInput.value || 0);
        const remaining = Math.max(0, outstanding - payment);
        if (remainingHint) {
            remainingHint.textContent = `Remaining after payment: ₱${formatMoney(remaining)}`;
        }
    };

    modalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) {
            return;
        }

        const customer = (trigger.getAttribute('data-customer') || '').trim();
        const address = (trigger.getAttribute('data-address') || '').trim();
        outstanding = Number(trigger.getAttribute('data-balance') || 0);

        customerInput.value = customer;
        addressInput.value = address;
        outstandingInput.value = formatMoney(outstanding);
        paymentInput.value = '';
        paymentInput.max = formatMoney(outstanding);
        refreshRemaining();
    });

    if (fullBtn) {
        fullBtn.addEventListener('click', function () {
            paymentInput.value = formatMoney(outstanding);
            refreshRemaining();
        });
    }

    paymentInput.addEventListener('input', refreshRemaining);

    form.addEventListener('submit', function (event) {
        const payment = Number(paymentInput.value || 0);
        if (!form.checkValidity() || payment <= 0 || payment > outstanding) {
            event.preventDefault();
            event.stopPropagation();
            if (payment > outstanding) {
                paymentInput.setCustomValidity('Payment cannot exceed outstanding balance.');
            } else {
                paymentInput.setCustomValidity('');
            }
        } else {
            paymentInput.setCustomValidity('');
        }

        form.classList.add('was-validated');
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        form.reset();
        form.classList.remove('was-validated');
        paymentInput.setCustomValidity('');
        outstanding = 0;
        if (remainingHint) {
            remainingHint.textContent = 'Remaining after payment: ₱0.00';
        }
    });
});
</script>
