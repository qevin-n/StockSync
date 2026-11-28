<?php
$pageTitle = 'Add User';
$breadcrumb = 'Add User';
$content = function () use ($roles) {
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h5 class="fw-bold mb-0">Add User</h5></div>
            <div class="card-body">
                <form method="POST" action="/users/create">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control <?= has_error('first_name') ? 'is-invalid' : '' ?>" value="<?= old('first_name') ?>" required>
                            <?= show_error('first_name') ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control <?= has_error('last_name') ? 'is-invalid' : '' ?>" value="<?= old('last_name') ?>" required>
                            <?= show_error('last_name') ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" value="<?= old('email') ?>" required>
                            <?= show_error('email') ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Role</label>
                            <select name="role_id" class="form-select <?= has_error('role_id') ? 'is-invalid' : '' ?>" required>
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>"<?= old('role_id') == $r['id'] ? ' selected' : '' ?>><?= e($r['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= show_error('role_id') ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>" required>
                            <?= show_error('password') ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirm" class="form-control <?= has_error('password_confirm') ? 'is-invalid' : '' ?>" required>
                            <?= show_error('password_confirm') ?>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Create User</button>
                            <a href="/users" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
