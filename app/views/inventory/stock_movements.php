<?php
$pageTitle = 'Stock Movements';
$breadcrumb = 'Stock Movements';
$content = function () use ($movements, $page, $search) {
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" action="/inventory/stock-movements" class="d-flex gap-2">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search reference or product..." value="<?= e($search) ?>">
        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="/inventory" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Inventory</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th class="small fw-semibold">Date</th><th class="small fw-semibold">Product</th><th class="small fw-semibold">Type</th><th class="small fw-semibold text-end">Qty</th><th class="small fw-semibold">Reference</th><th class="small fw-semibold">User</th></tr>
            </thead>
            <tbody>
                <?php foreach ($movements['data'] as $m): ?>
                <tr>
                    <td class="small"><?= format_date($m['created_at']) ?></td>
                    <td class="small fw-medium"><?= e($m['product_name'] ?? '-') ?></td>
                    <td class="small"><span class="badge bg-<?= $m['movement_type'] === 'in' ? 'success' : ($m['movement_type'] === 'out' ? 'danger' : 'warning') ?>"><?= ucfirst($m['movement_type']) ?></span></td>
                    <td class="small text-end"><?= number_format((int)$m['quantity']) ?></td>
                    <td class="small"><?= e($m['reference'] ?? '-') ?></td>
                    <td class="small"><?= e($m['user_name'] ?? 'System') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($movements['data'])): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No stock movements found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($movements['total_pages'] > 1): ?>
    <div class="card-footer bg-white d-flex justify-content-center">
        <?= pagination($movements['total_pages'], $movements['current_page'], $movements['total'], function($p) { return '/inventory/stock-movements?page=' . $p; }) ?>
    </div>
    <?php endif; ?>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
