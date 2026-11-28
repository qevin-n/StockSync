<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>StockSync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/main.css" rel="stylesheet">
</head>
<body class="auth-body">
<div class="auth-wrapper">
    <div class="auth-card<?= ($pageTitle ?? '') === 'Register' ? ' auth-card-wide' : '' ?>">
        <div class="text-center mb-4">
            <i class="bi bi-box-seam-fill text-primary" style="font-size: 3rem;"></i>
            <h3 class="fw-bold mt-3">StockSync</h3>
            <p class="text-muted small">Know your stock. Run your business smarter.</p>
        </div>
        <?php foreach (get_flash_messages() as $msg): ?>
            <div class="alert alert-<?= $msg['type'] ?> alert-dismissible fade show" role="alert">
                <?= $msg['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
        <?php
        if (isset($content) && is_callable($content)) {
            $content();
        } elseif (isset($content)) {
            echo $content;
        }
        ?>
    </div>
    <p class="text-center text-muted small mt-4">&copy; <?= date('Y') ?> StockSync. All rights reserved.</p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);

    if (!input || !button) return;

    const showingPassword = input.type === 'password';
    input.type = showingPassword ? 'text' : 'password';

    const icon = button.querySelector('i');

    if (icon) {
        icon.classList.toggle('bi-eye', !showingPassword);
        icon.classList.toggle('bi-eye-slash', showingPassword);
    }

    const label = showingPassword ? 'Hide password' : 'Show password';
    button.setAttribute('aria-label', label);
    button.setAttribute('title', label);
}
</script>

</body>
</html>
