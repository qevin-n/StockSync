<?php
$pageTitle = 'Users';
$breadcrumb = 'Users';
$content = function () use ($users, $page, $search, $status) {
?>
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form method="GET" action="/users" class="d-flex gap-2">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name or email..." value="<?= e($search) ?>">
        <select name="status" class="form-select form-select-sm">
            <option value="">All</option>
            <option value="active"<?= $status === 'active' ? ' selected' : '' ?>>Active</option>
            <option value="inactive"<?= $status === 'inactive' ? ' selected' : '' ?>>Inactive</option>
        </select>
        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="/users/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> Add User</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th class="small fw-semibold">User</th><th class="small fw-semibold">Role</th><th class="small fw-semibold">Status</th><th class="small fw-semibold">Joined</th><th class="small fw-semibold text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users['data'] as $u): ?>
                <tr>
                    <td class="small">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:12px;font-weight:600;"><?= strtoupper(substr($u['first_name'], 0, 1) . substr($u['last_name'], 0, 1)) ?></div>
                            <div>
                                <div class="fw-medium"><?= e($u['first_name'] . ' ' . $u['last_name']) ?></div>
                                <div class="text-muted" style="font-size:11px;"><?= e($u['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="small"><span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'manager' ? 'warning text-dark' : 'secondary') ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td class="small"><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($u['status']) ?></span></td>
                    <td class="small"><?= format_date($u['created_at']) ?></td>
                    <td class="text-end">
                        <a href="/users/<?= $u['id'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                        <a href="/users/<?= $u['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <?php if ($_SESSION['user_id'] != $u['id']): ?>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteItem('/users/<?= $u['id'] ?>/delete')"><i class="bi bi-trash"></i></button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users['data'])): ?><tr><td colspan="5" class="text-center text-muted py-4">No users found</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($users['total_pages'] > 1): ?>
    <div class="card-footer bg-white d-flex justify-content-center">
        <?= pagination($users['total_pages'], $users['current_page'], $users['total'], function($p) { return '/users?page=' . $p; }) ?>
    </div>
    <?php endif; ?>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
