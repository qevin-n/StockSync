<?php
$pageTitle = 'Suppliers';
$breadcrumb = 'Suppliers';
$content = function () use ($suppliers, $page, $search, $status) {
?>
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form method="GET" action="/suppliers" class="d-flex gap-2">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search suppliers..." value="<?= e($search) ?>">
        <select name="status" class="form-select form-select-sm">
            <option value="">All</option>
            <option value="active"<?= $status === 'active' ? ' selected' : '' ?>>Active</option>
            <option value="inactive"<?= $status === 'inactive' ? ' selected' : '' ?>>Inactive</option>
        </select>
        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="/suppliers/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> Add Supplier</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th class="small fw-semibold">Company</th><th class="small fw-semibold">Contact</th><th class="small fw-semibold">Phone</th><th class="small fw-semibold">Status</th><th class="small fw-semibold text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers['data'] as $s): ?>
                <tr>
                    <td class="small fw-medium"><?= e($s['company_name']) ?></td>
                    <td class="small"><?= e($s['contact_person'] ?: '-') ?></td>
                    <td class="small"><?= e($s['phone'] ?: '-') ?></td>
                    <td class="small"><span class="badge bg-<?= $s['status'] === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($s['status']) ?></span></td>
                    <td class="text-end">
                        <a href="/suppliers/<?= $s['id'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                        <a href="/suppliers/<?= $s['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteItem('/suppliers/<?= $s['id'] ?>/delete')"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($suppliers['data'])): ?><tr><td colspan="5" class="text-center text-muted py-4">No suppliers found</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($suppliers['total_pages'] > 1): ?>
    <div class="card-footer bg-white d-flex justify-content-center">
        <?= pagination($suppliers['total_pages'], $suppliers['current_page'], $suppliers['total'], function($p) { return '/suppliers?page=' . $p; }) ?>
    </div>
    <?php endif; ?>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
