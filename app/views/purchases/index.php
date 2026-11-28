<?php
$pageTitle = 'Purchases';
$breadcrumb = 'Purchases';
$content = function () use ($purchases, $page, $search, $status, $startDate, $endDate) {
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form method="GET" action="/purchases" class="d-flex gap-2 flex-wrap">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Reference # or supplier..." value="<?= e($search) ?>">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            <option value="ordered"<?= $status === 'ordered' ? ' selected' : '' ?>>Ordered</option>
            <option value="partial"<?= $status === 'partial' ? ' selected' : '' ?>>Partial</option>
            <option value="received"<?= $status === 'received' ? ' selected' : '' ?>>Received</option>
            <option value="cancelled"<?= $status === 'cancelled' ? ' selected' : '' ?>>Cancelled</option>
        </select>
        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= e($startDate) ?>">
        <input type="date" name="end_date" class="form-control form-control-sm" value="<?= e($endDate) ?>">
        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <?php if (is_manager()): ?>
    <a href="/purchases/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> New Purchase</a>
    <?php endif; ?>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th class="small fw-semibold">Reference</th><th class="small fw-semibold">Supplier</th><th class="small fw-semibold text-end">Total</th><th class="small fw-semibold">Status</th><th class="small fw-semibold">Date</th><th class="small fw-semibold text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($purchases['data'] as $p): ?>
                <tr>
                    <td class="small font-monospace fw-medium"><?= e($p['reference_number']) ?></td>
                    <td class="small"><?= e($p['supplier_name'] ?: '-') ?></td>
                    <td class="small text-end fw-medium">$<?= number_format((float)$p['total_amount'], 2) ?></td>
                    <td class="small"><span class="badge bg-<?= $p['status'] === 'received' ? 'success' : ($p['status'] === 'ordered' ? 'info' : ($p['status'] === 'partial' ? 'warning text-dark' : 'danger')) ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td class="small"><?= format_date($p['purchase_date']) ?></td>
                    <td class="text-end">
                        <a href="/purchases/<?= $p['id'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                        <?php if ($p['status'] !== 'received' && $p['status'] !== 'cancelled' && is_manager()): ?>
                        <form method="POST" action="/purchases/<?= $p['id'] ?>/receive" class="d-inline" onsubmit="return confirm('Mark this purchase as fully received?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-success" title="Receive"><i class="bi bi-check-lg"></i></button>
                        </form>
                        <?php endif; ?>
                        <?php if ($p['status'] !== 'cancelled' && is_manager()): ?>
                        <form method="POST" action="/purchases/<?= $p['id'] ?>/cancel" class="d-inline" onsubmit="return confirm('Cancel this purchase?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger" title="Cancel"><i class="bi bi-x-lg"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($purchases['data'])): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No purchases found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($purchases['total_pages'] > 1): ?>
    <div class="card-footer bg-white d-flex justify-content-center">
        <?= pagination($purchases['total_pages'], $purchases['current_page'], $purchases['total'], function($p) { return '/purchases?page=' . $p; }) ?>
    </div>
    <?php endif; ?>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
