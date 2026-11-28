<?php
$pageTitle = e($customer['name']);
$breadcrumb = 'Customer Details';
$content = function () use ($customer) {
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h5 class="fw-bold mb-0"><?= e($customer['name']) ?></h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th class="text-muted small" style="width:120px;">Email</th><td class="small"><?= e($customer['email'] ?: '-') ?></td></tr>
                    <tr><th class="text-muted small">Phone</th><td class="small"><?= e($customer['phone'] ?: '-') ?></td></tr>
                    <tr><th class="text-muted small">Address</th><td class="small"><?= nl2br(e($customer['address'] ?: '-')) ?></td></tr>
                    <tr><th class="text-muted small">Notes</th><td class="small text-muted"><?= nl2br(e($customer['notes'] ?: '-')) ?></td></tr>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="/customers" class="btn btn-outline-secondary btn-sm">Back</a>
                <a href="/customers/<?= $customer['id'] ?>/edit" class="btn btn-primary btn-sm">Edit</a>
            </div>
        </div>
    </div>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
