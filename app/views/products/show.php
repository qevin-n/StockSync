<?php
$pageTitle = e($product['name']);
$breadcrumb = 'Product Details';
$content = function () use ($product, $category, $supplier, $stockMovements, $totalSales, $totalPurchased, $currency) {
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <?php if ($product['image']): ?>
                    <img src="/uploads/<?= e($product['image']) ?>" class="img-fluid rounded mb-3" style="max-height:200px;object-fit:cover">
                <?php else: ?>
                    <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height:200px"><i class="bi bi-image text-muted" style="font-size:4rem"></i></div>
                <?php endif; ?>
                <h5 class="fw-bold"><?= e($product['name']) ?></h5>
                <span class="badge bg-<?= $product['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($product['status']) ?></span>
            </div>
        </div>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white border-0"><h6 class="fw-semibold mb-0">Product Details</h6></div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">SKU</span><span class="font-monospace small"><?= e($product['sku']) ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Barcode</span><span class="font-monospace small"><?= e($product['barcode'] ?: '-') ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Category</span><span><?= e($category['name'] ?? '-') ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Supplier</span><span><?= e($supplier['company_name'] ?? '-') ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Purchase Price</span><span class="fw-medium"><?= $currency ?><?= number_format((float)$product['purchase_price'], 2) ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Selling Price</span><span class="fw-medium"><?= $currency ?><?= number_format((float)$product['selling_price'], 2) ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Min Stock</span><span><?= $product['minimum_stock'] ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Total Sold</span><span><?= number_format($totalSales) ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Total Purchased</span><span><?= number_format($totalPurchased) ?></span></li>
            </ul>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0">Stock Movements</h6>
                <a href="/inventory?product=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary">View Inventory</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th class="small">Date</th><th class="small">Type</th><th class="small text-end">Qty</th><th class="small">Reference</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stockMovements as $sm): ?>
                        <tr>
                            <td class="small"><?= format_date($sm['created_at']) ?></td>
                            <td class="small"><span class="badge bg-<?= $sm['movement_type'] === 'in' ? 'success' : ($sm['movement_type'] === 'out' ? 'danger' : 'warning') ?>"><?= ucfirst($sm['movement_type']) ?></span></td>
                            <td class="small text-end"><?= number_format((int)$sm['quantity']) ?></td>
                            <td class="small"><?= e($sm['reference'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($stockMovements)): ?>
                        <tr><td colspan="4" class="text-center text-muted small py-3">No stock movements</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($product['description']): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white border-0"><h6 class="fw-semibold mb-0">Description</h6></div>
            <div class="card-body"><p class="mb-0 text-muted small"><?= nl2br(e($product['description'])) ?></p></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
