<?php
$pageTitle = e($supplier['company_name']);
$breadcrumb = 'Supplier Details';
$content = function () use ($supplier) {
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h5 class="fw-bold mb-0"><?= e($supplier['company_name']) ?></h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th class="text-muted small" style="width:140px;">Contact Person</th><td class="small"><?= e($supplier['contact_person'] ?: '-') ?></td></tr>
                    <tr><th class="text-muted small">Email</th><td class="small"><?= e($supplier['email'] ?: '-') ?></td></tr>
                    <tr><th class="text-muted small">Phone</th><td class="small"><?= e($supplier['phone'] ?: '-') ?></td></tr>
                    <tr><th class="text-muted small">Address</th><td class="small"><?= nl2br(e($supplier['address'] ?: '-')) ?></td></tr>
                    <tr><th class="text-muted small">Website</th><td class="small"><?= $supplier['website'] ? '<a href="' . e($supplier['website']) . '" target="_blank">' . e($supplier['website']) . '</a>' : '-' ?></td></tr>
                    <tr><th class="text-muted small">Status</th><td class="small"><span class="badge bg-<?= $supplier['status'] === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($supplier['status']) ?></span></td></tr>
                    <tr><th class="text-muted small">Notes</th><td class="small text-muted"><?= nl2br(e($supplier['notes'] ?: '-')) ?></td></tr>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="/suppliers" class="btn btn-outline-secondary btn-sm">Back</a>
                <a href="/suppliers/<?= $supplier['id'] ?>/edit" class="btn btn-primary btn-sm">Edit</a>
            </div>
        </div>
    </div>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
