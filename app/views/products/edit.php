<?php
$pageTitle = 'Edit Product';
$breadcrumb = 'Edit Product';
$content = function () use ($product, $categories, $suppliers) {
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="/products/<?= $product['id'] ?>/edit" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control" value="<?= e($product['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" value="<?= e($product['sku']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"<?= $product['category_id'] == $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" class="form-select">
                                <option value="">Select supplier</option>
                                <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"<?= ($product['supplier_id'] ?? null) == $s['id'] ? ' selected' : '' ?>><?= e($s['company_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Barcode</label>
                            <input type="text" name="barcode" class="form-control" value="<?= e($product['barcode'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active"<?= $product['status'] === 'active' ? ' selected' : '' ?>>Active</option>
                                <option value="inactive"<?= $product['status'] === 'inactive' ? ' selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Purchase Price</label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control" value="<?= $product['purchase_price'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Selling Price</label>
                            <input type="number" step="0.01" name="selling_price" class="form-control" value="<?= $product['selling_price'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Minimum Stock</label>
                            <input type="number" name="minimum_stock" class="form-control" value="<?= $product['minimum_stock'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Product Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <?php if ($product['image']): ?>
                            <div class="mt-2"><img src="/uploads/<?= e($product['image']) ?>" class="rounded" width="80" height="80" style="object-fit:cover"></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= e($product['description'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Product</button>
                            <a href="/products/<?= $product['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
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
