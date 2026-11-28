<?php
$pageTitle = 'Sales';
$breadcrumb = 'Sales';
$content = function () use ($sales, $page, $search, $status, $startDate, $endDate) {
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form method="GET" action="/sales" class="d-flex gap-2 flex-wrap">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Invoice # or customer..." value="<?= e($search) ?>">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            <option value="completed"<?= $status === 'completed' ? ' selected' : '' ?>>Completed</option>
            <option value="pending"<?= $status === 'pending' ? ' selected' : '' ?>>Pending</option>
            <option value="cancelled"<?= $status === 'cancelled' ? ' selected' : '' ?>>Cancelled</option>
        </select>
        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= e($startDate) ?>">
        <input type="date" name="end_date" class="form-control form-control-sm" value="<?= e($endDate) ?>">
        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="/sales/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> New Sale</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th class="small fw-semibold">Invoice #</th><th class="small fw-semibold">Customer</th><th class="small fw-semibold text-end">Total</th><th class="small fw-semibold">Status</th><th class="small fw-semibold">Date</th><th class="small fw-semibold text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($sales['data'] as $s): ?>
                <tr>
                    <td class="small font-monospace fw-medium"><?= e($s['invoice_number']) ?></td>
                    <td class="small"><?= e($s['customer_name'] ?: 'Walk-in') ?></td>
                    <td class="small text-end fw-medium">$<?= number_format((float)$s['total_amount'], 2) ?></td>
                    <td class="small"><span class="badge bg-<?= $s['status'] === 'completed' ? 'success' : ($s['status'] === 'pending' ? 'warning text-dark' : 'danger') ?>"><?= ucfirst($s['status']) ?></span></td>
                    <td class="small"><?= format_date($s['sale_date']) ?></td>
                    <td class="text-end">
                        <a href="/sales/<?= $s['id'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                        <a href="/sales/<?= $s['id'] ?>/invoice" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-text"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($sales['data'])): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No sales found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($sales['total_pages'] > 1): ?>
    <div class="card-footer bg-white d-flex justify-content-center">
        <?= pagination($sales['total_pages'], $sales['current_page'], $sales['total'], function($p) { return '/sales?page=' . $p; }) ?>
    </div>
    <?php endif; ?>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
