<?php
$pageTitle = 'New Purchase';
$breadcrumb = 'New Purchase';
$content = function () use ($suppliers, $products) {
?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h6 class="fw-semibold mb-0">Add Items</h6></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Select Product</label>
                        <select id="productSelect" class="form-select">
                            <option value="">Choose a product...</option>
                            <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>" data-name="<?= e($p['name']) ?>" data-cost="<?= $p['purchase_price'] ?: 0 ?>"><?= e($p['name']) ?> (<?= e($p['sku']) ?>) - Cost: $<?= $p['purchase_price'] ?: 0 ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Qty</label>
                        <input type="number" id="qtyInput" class="form-control" min="1" value="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Unit Cost</label>
                        <input type="number" step="0.01" id="costInput" class="form-control" value="0.00">
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary mt-3" onclick="addItem()"><i class="bi bi-plus-lg me-1"></i> Add to Purchase</button>
            </div>
        </div>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white border-0"><h6 class="fw-semibold mb-0">Purchase Items</h6></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="itemsTable">
                    <thead class="table-light">
                        <tr><th class="small">Product</th><th class="small text-end">Cost</th><th class="small text-end">Qty</th><th class="small text-end">Subtotal</th><th class="small"></th></tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr><th colspan="3" class="text-end">Total:</th><th class="text-end" id="purchaseTotal">$0.00</th><th></th></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h6 class="fw-semibold mb-0">Purchase Details</h6></div>
            <div class="card-body">
                <form method="POST" action="/purchases/create" id="purchaseForm">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">Select supplier</option>
                            <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= e($s['company_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="Auto-generated if empty">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <div id="itemsHidden"></div>
                    <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>Create Purchase</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
let items = [];
document.getElementById('productSelect').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('costInput').value = parseFloat(opt.dataset.cost || 0).toFixed(2);
});
function addItem() {
    const select = document.getElementById('productSelect');
    const qty = parseInt(document.getElementById('qtyInput').value) || 1;
    const cost = parseFloat(document.getElementById('costInput').value) || 0;
    if (!select.value) return alert('Please select a product');
    const opt = select.options[select.selectedIndex];
    const existing = items.find(i => i.id == select.value);
    if (existing) { existing.qty += qty; } else {
        items.push({id: select.value, name: opt.dataset.name, cost: cost, qty: qty});
    }
    renderItems();
    select.value = '';
}
function removeItem(idx) { items.splice(idx, 1); renderItems(); }
function renderItems() {
    const tbody = document.querySelector('#itemsTable tbody');
    const hidden = document.getElementById('itemsHidden');
    let total = 0; let html = '';
    items.forEach((item, idx) => {
        const sub = item.cost * item.qty; total += sub;
        html += `<tr><td class="small">${item.name}</td><td class="small text-end">$${item.cost.toFixed(2)}</td><td class="small text-end">${item.qty}</td><td class="small text-end fw-medium">$${sub.toFixed(2)}</td><td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${idx})"><i class="bi bi-trash"></i></button></td></tr>`;
    });
    tbody.innerHTML = html;
    document.getElementById('purchaseTotal').textContent = '$' + total.toFixed(2);
    hidden.innerHTML = items.map((item, i) => `<input type="hidden" name="items[${i}][product_id]" value="${item.id}"><input type="hidden" name="items[${i}][quantity]" value="${item.qty}"><input type="hidden" name="items[${i}][unit_cost]" value="${item.cost}">`).join('');
    document.getElementById('submitBtn').disabled = items.length === 0;
}
</script>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
