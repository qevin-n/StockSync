<?php
$pageTitle = 'New Sale';
$breadcrumb = 'New Sale';
$content = function () use ($customers, $products) {
?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h6 class="fw-semibold mb-0">Add Items</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Select Product</label>
                    <select id="productSelect" class="form-select">
                        <option value="">Choose a product...</option>
                        <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" data-name="<?= e($p['name']) ?>" data-price="<?= $p['selling_price'] ?>" data-stock="<?= $p['stock_quantity'] ?>"><?= e($p['name']) ?> (<?= e($p['sku']) ?>) - $<?= $p['selling_price'] ?> - Stock: <?= $p['stock_quantity'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" id="qtyInput" class="form-control" min="1" value="1">
                </div>
                <button type="button" class="btn btn-outline-primary" onclick="addItem()"><i class="bi bi-plus-lg me-1"></i> Add to Sale</button>
            </div>
        </div>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white border-0"><h6 class="fw-semibold mb-0">Sale Items</h6></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="itemsTable">
                    <thead class="table-light">
                        <tr><th class="small">Product</th><th class="small text-end">Price</th><th class="small text-end">Qty</th><th class="small text-end">Subtotal</th><th class="small"></th></tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr><th colspan="3" class="text-end">Total:</th><th class="text-end" id="saleTotal">$0.00</th><th></th></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h6 class="fw-semibold mb-0">Sale Details</h6></div>
            <div class="card-body">
                <form method="POST" action="/sales/create" id="saleForm">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <select name="customer_id" class="form-select">
                            <option value="">Walk-in Customer</option>
                            <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sale Date</label>
                        <input type="date" name="sale_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <div id="itemsHidden"></div>
                    <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>Complete Sale</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
let items = [];
function addItem() {
    const select = document.getElementById('productSelect');
    const qty = parseInt(document.getElementById('qtyInput').value) || 1;
    if (!select.value) return alert('Please select a product');
    const opt = select.options[select.selectedIndex];
    const stock = parseInt(opt.dataset.stock);
    const existing = items.find(i => i.id == select.value);
    const currentQty = existing ? existing.qty + qty : qty;
    if (currentQty > stock) return alert('Insufficient stock! Available: ' + stock);
    if (existing) { existing.qty = currentQty; } else {
        items.push({id: select.value, name: opt.dataset.name, price: parseFloat(opt.dataset.price), qty: qty});
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
        const sub = item.price * item.qty; total += sub;
        html += `<tr><td class="small">${item.name}</td><td class="small text-end">$${item.price.toFixed(2)}</td><td class="small text-end">${item.qty}</td><td class="small text-end fw-medium">$${sub.toFixed(2)}</td><td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${idx})"><i class="bi bi-trash"></i></button></td></tr>`;
    });
    tbody.innerHTML = html;
    document.getElementById('saleTotal').textContent = '$' + total.toFixed(2);
    hidden.innerHTML = items.map((item, i) => `<input type="hidden" name="items[${i}][product_id]" value="${item.id}"><input type="hidden" name="items[${i}][quantity]" value="${item.qty}"><input type="hidden" name="items[${i}][unit_price]" value="${item.price}">`).join('');
    document.getElementById('submitBtn').disabled = items.length === 0;
}
</script>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
