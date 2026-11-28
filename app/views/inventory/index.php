<?php
$pageTitle = 'Inventory';
$breadcrumb = 'Inventory';
$content = function () use ($products, $lowStock, $outOfStock, $page, $search, $productFilter) {
?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-boxes"></i></div>
                <div><div class="text-muted small">Total Products</div><h5 class="fw-bold mb-0"><?= number_format(count($products['data'])) ?></h5></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-exclamation-triangle"></i></div>
                <div><div class="text-muted small">Low Stock</div><h5 class="fw-bold mb-0"><?= $lowStock ?></h5></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-x-circle"></i></div>
                <div><div class="text-muted small">Out of Stock</div><h5 class="fw-bold mb-0"><?= $outOfStock ?></h5></div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" action="/inventory" class="d-flex gap-2">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search inventory..." value="<?= e($search) ?>">
        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="/inventory/stock-movements" class="btn btn-outline-secondary btn-sm"><i class="bi bi-clock-history me-1"></i> Stock Movements</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th class="small fw-semibold">Product</th><th class="small fw-semibold">SKU</th><th class="small fw-semibold text-end">Current Stock</th><th class="small fw-semibold text-end">Min Stock</th><th class="small fw-semibold">Status</th><th class="small fw-semibold text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($products['data'] as $p): ?>
                <tr>
                    <td class="small fw-medium"><?= e($p['name']) ?></td>
                    <td class="small font-monospace"><?= e($p['sku']) ?></td>
                    <td class="small text-end"><?= number_format((int)$p['stock_quantity']) ?></td>
                    <td class="small text-end"><?= $p['minimum_stock'] ?></td>
                    <td>
                        <?php if ((int)$p['stock_quantity'] === 0): ?>
                            <span class="badge bg-danger">Out of Stock</span>
                        <?php elseif ((int)$p['stock_quantity'] <= (int)$p['minimum_stock']): ?>
                            <span class="badge bg-warning text-dark">Low Stock</span>
                        <?php else: ?>
                            <span class="badge bg-success">OK</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <?php if (is_manager()): ?>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#adjustModal<?= $p['id'] ?>"><i class="bi bi-sliders"></i> Adjust</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products['data'])): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No products found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($products['total_pages'] > 1): ?>
    <div class="card-footer bg-white d-flex justify-content-center">
        <?= pagination($products['total_pages'], $products['current_page'], $products['total'], function($p) { return '/inventory?page=' . $p; }) ?>
    </div>
    <?php endif; ?>
</div>

<?php foreach ($products['data'] as $p): ?>
<div class="modal fade" id="adjustModal<?= $p['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="/inventory/<?= $p['id'] ?>/adjust">
                <?= csrf_field() ?>
                <div class="modal-header"><h6 class="modal-title">Adjust Stock: <?= e($p['name']) ?></h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Current Stock</label><input type="text" class="form-control" value="<?= $p['stock_quantity'] ?>" disabled></div>
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type</label>
                        <select name="type" class="form-select">
                            <option value="in">Add Stock (+)</option>
                            <option value="out">Remove Stock (-)</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Quantity</label><input type="number" name="quantity" class="form-control" min="1" required></div>
                    <div class="mb-3"><label class="form-label">Reason</label><textarea name="reason" class="form-control" rows="2" placeholder="e.g. Damaged goods, donation..."></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Adjust</button></div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
