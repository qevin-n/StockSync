<?php
$pageTitle = 'Settings';
$breadcrumb = 'Settings';
$content = function () use ($business) {
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h5 class="fw-bold mb-0">Business Settings</h5></div>
            <div class="card-body">
                <form method="POST" action="/settings">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Business Name</label>
                            <input type="text" name="name" class="form-control <?= has_error('name') ? 'is-invalid' : '' ?>" value="<?= old('name', $business['name']) ?>" required>
                            <?= show_error('name') ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= old('address', $business['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= old('phone', $business['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= old('email', $business['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax Number</label>
                            <input type="text" name="tax_number" class="form-control" value="<?= old('tax_number', $business['tax_number'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Currency</label>
                            <select name="currency" class="form-select">
                                <option value="$"<?= (old('currency', $business['currency'] ?? '$') === '$') ? ' selected' : '' ?>>$ USD</option>
                                <option value="EUR"<?= (old('currency', $business['currency'] ?? '') === 'EUR') ? ' selected' : '' ?>>EUR</option>
                                <option value="GBP"<?= (old('currency', $business['currency'] ?? '') === 'GBP') ? ' selected' : '' ?>>GBP</option>
                                <option value="CAD"<?= (old('currency', $business['currency'] ?? '') === 'CAD') ? ' selected' : '' ?>>CAD</option>
                                <option value="AUD"<?= (old('currency', $business['currency'] ?? '') === 'AUD') ? ' selected' : '' ?>>AUD</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
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
