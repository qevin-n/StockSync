<?php
$pageTitle = 'Customers';
$breadcrumb = 'Customers';
$content = function () use ($customers, $page, $search) {
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form method="GET" action="/customers" class="d-flex gap-2">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search customers..." value="<?= e($search) ?>">
        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="/customers/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> Add Customer</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th class="small fw-semibold">Name</th><th class="small fw-semibold">Email</th><th class="small fw-semibold">Phone</th><th class="small fw-semibold">Joined</th><th class="small fw-semibold text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($customers['data'] as $c): ?>
                <tr>
                    <td class="small fw-medium"><?= e($c['name']) ?></td>
                    <td class="small"><?= e($c['email'] ?: '-') ?></td>
                    <td class="small"><?= e($c['phone'] ?: '-') ?></td>
                    <td class="small"><?= format_date($c['created_at']) ?></td>
                    <td class="text-end">
                        <a href="/customers/<?= $c['id'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                        <a href="/customers/<?= $c['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteItem('/customers/<?= $c['id'] ?>/delete')"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($customers['data'])): ?><tr><td colspan="5" class="text-center text-muted py-4">No customers found</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($customers['total_pages'] > 1): ?>
    <div class="card-footer bg-white d-flex justify-content-center">
        <?= pagination($customers['total_pages'], $customers['current_page'], $customers['total'], function($p) { return '/customers?page=' . $p; }) ?>
    </div>
    <?php endif; ?>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
