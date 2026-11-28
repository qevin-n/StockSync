<?php
$pageTitle = 'Edit Customer';
$breadcrumb = 'Edit Customer';
$content = function () use ($customer) {
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h5 class="fw-bold mb-0">Edit Customer</h5></div>
            <div class="card-body">
                <form method="POST" action="/customers/<?= $customer['id'] ?>/update">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control <?= has_error('name') ? 'is-invalid' : '' ?>" value="<?= old('name', $customer['name']) ?>" required>
                            <?= show_error('name') ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= old('email', $customer['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= old('phone', $customer['phone'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= old('address', $customer['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"><?= old('notes', $customer['notes'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Customer</button>
                            <a href="/customers" class="btn btn-outline-secondary">Cancel</a>
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
