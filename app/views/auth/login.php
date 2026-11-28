<?php
$pageTitle = 'Login';
$content = function () {
    $old = get_old_input();
?>
<form method="POST" action="/login" class="needs-validation" novalidate>
    <?= csrf_field() ?>
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control" id="email" name="email" value="<?= e($old['email'] ?? '') ?>" required autofocus placeholder="Enter your email">
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', this)"><i class="bi bi-eye"></i></button>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
            <label class="form-check-label small" for="remember">Remember me</label>
        </div>
        <a href="/forgot-password" class="small text-decoration-none">Forgot password?</a>
    </div>
    <button type="submit" class="btn btn-primary w-100">Sign In</button>
    <div class="text-center mt-3">
        <small class="text-muted">Don't have an account? <a href="/register" class="text-decoration-none">Sign up</a></small>
    </div>
</form>
<?php
};
require APP_ROOT . '/app/views/layouts/auth.php';
