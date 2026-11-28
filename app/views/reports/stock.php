<?php
$pageTitle = 'Stock Report';
$breadcrumb = 'Stock Report';
$content = function () use ($summary, $lowStock, $stockValuation, $recentMovements) {
?>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Total Products</div><h5 class="fw-bold mb-0"><?= number_format((int)($summary['total_products'] ?? 0)) ?></h5></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Stock Value</div><h5 class="fw-bold mb-0">$<?= number_format((float)($summary['stock_value'] ?? 0), 2) ?></h5></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Low Stock Items</div><h5 class="fw-bold mb-0 text-danger"><?= number_format((int)($summary['low_stock_count'] ?? 0)) ?></h5></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Movements Today</div><h5 class="fw-bold mb-0"><?= number_format((int)($summary['movements_today'] ?? 0)) ?></h5></div></div></div>
</div>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h6 class="fw-semibold mb-0">Low Stock Alerts</h6></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th class="small">Product</th><th class="small text-end">Stock</th><th class="small text-end">Min</th></tr></thead>
                    <tbody>
                        <?php foreach ($lowStock as $p): ?>
                        <tr><td class="small"><?= e($p['name']) ?></td><td class="small text-end text-danger fw-medium"><?= $p['stock_quantity'] ?></td><td class="small text-end"><?= $p['min_stock_level'] ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($lowStock)): ?><tr><td colspan="3" class="text-center text-muted py-3">All stock levels healthy</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h6 class="fw-semibold mb-0">Recent Stock Movements</h6></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th class="small">Product</th><th class="small text-end">Qty</th><th class="small">Type</th><th class="small">Date</th></tr></thead>
                    <tbody>
                        <?php foreach ($recentMovements as $m): ?>
                        <tr>
                            <td class="small"><?= e($m['product_name'] ?? 'N/A') ?></td>
                            <td class="small text-end"><?= ($m['type'] === 'sale' || $m['type'] === 'adjustment' ? '-' : '+') ?><?= $m['quantity'] ?></td>
                            <td class="small"><span class="badge bg-<?= $m['type'] === 'purchase' ? 'info' : ($m['type'] === 'sale' ? 'success' : 'warning text-dark') ?>"><?= ucfirst($m['type']) ?></span></td>
                            <td class="small"><?= format_date($m['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
