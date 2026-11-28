<?php
$pageTitle = 'Edit Supplier';
$breadcrumb = 'Edit Supplier';
$content = function () use ($supplier) {
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h5 class="fw-bold mb-0">Edit Supplier</h5></div>
            <div class="card-body">
                <form method="POST" action="/suppliers/<?= $supplier['id'] ?>/update">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control <?= has_error('company_name') ? 'is-invalid' : '' ?>" value="<?= old('company_name', $supplier['company_name']) ?>" required>
                            <?= show_error('company_name') ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="<?= old('contact_person', $supplier['contact_person'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= old('email', $supplier['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= old('phone', $supplier['phone'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= old('address', $supplier['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" class="form-control" value="<?= old('website', $supplier['website'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active"<?= old('status', $supplier['status']) === 'active' ? ' selected' : '' ?>>Active</option>
                                <option value="inactive"<?= old('status', $supplier['status']) === 'inactive' ? ' selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"><?= old('notes', $supplier['notes'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Supplier</button>
                            <a href="/suppliers" class="btn btn-outline-secondary">Cancel</a>
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
