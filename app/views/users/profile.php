<?php
$pageTitle = 'My Profile';
$breadcrumb = 'My Profile';

$content = function () use ($user) {
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h5 class="fw-bold mb-0">My Profile</h5>
            </div>

            <div class="card-body">
                <form method="POST" action="/profile">
                    <?= csrf_field() ?>

                    <h6 class="fw-semibold mb-3">Account Information</h6>

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="<?= e($user['name']) ?>"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Email Address</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="<?= e($user['email']) ?>"
                            required
                        >
                    </div>

                    <hr>

                    <h6 class="fw-semibold mb-3">Change Password</h6>
                    <p class="text-muted small">
                        Leave these fields blank if you do not want to change your password.
                    </p>

                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" autocomplete="new-password">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirm" class="form-control" autocomplete="new-password">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="/" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
};

require APP_ROOT . '/app/views/layouts/app.php';
