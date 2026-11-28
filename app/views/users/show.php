<?php
$pageTitle = 'User: ' . e($user['first_name'] . ' ' . $user['last_name']);
$breadcrumb = 'User Details';
$content = function () use ($user) {
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;font-size:28px;font-weight:600;"><?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?></div>
                <h5 class="fw-bold mb-1"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                <p class="text-muted mb-2"><?= e($user['email']) ?></p>
                <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'manager' ? 'warning text-dark' : 'secondary') ?> fs-6"><?= ucfirst($user['role']) ?></span>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center"><span>Status</span><span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($user['status']) ?></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center"><span>Joined</span><span class="text-muted small"><?= format_date($user['created_at']) ?></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center"><span>Last Login</span><span class="text-muted small"><?= $user['last_login'] ? format_date($user['last_login']) : 'Never' ?></span></li>
            </ul>
            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="/users" class="btn btn-outline-secondary btn-sm">Back</a>
                <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-primary btn-sm">Edit</a>
            </div>
        </div>
    </div>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
