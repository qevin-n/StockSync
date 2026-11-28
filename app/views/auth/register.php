<?php
$pageTitle = 'Register';
$content = function () {
    $old = get_old_input();
?>
<form method="POST" action="/register" class="needs-validation" novalidate>
    <?= csrf_field() ?>
    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="name" name="name" value="<?= e($old['name'] ?? '') ?>" required placeholder="Your full name">
    </div>
    <div class="mb-3">
        <label for="business_name" class="form-label">Business Name</label>
        <input type="text" class="form-control" id="business_name" name="business_name" value="<?= e($old['business_name'] ?? '') ?>" required placeholder="Your business name">
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control" id="email" name="email" value="<?= e($old['email'] ?? '') ?>" required placeholder="you@example.com">
    </div>
    <div class="mb-3">
        <label for="phone" class="form-label">Phone</label>
        <input type="text" class="form-control" id="phone" name="phone" value="<?= e($old['phone'] ?? '') ?>" placeholder="+1 (555) 000-0000">
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" required minlength="6" placeholder="At least 6 characters">
            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', this)" aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
        </div>
    </div>
    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <div class="input-group">
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Re-enter password">
            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password', this)" aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
        </div>
    </div>
    <button type="submit" class="btn btn-primary w-100">Create Account</button>
    <div class="text-center mt-3">
        <small class="text-muted">Already have an account? <a href="/login" class="text-decoration-none">Sign in</a></small>
    </div>
</form>
<?php
};
require APP_ROOT . '/app/views/layouts/auth.php';
