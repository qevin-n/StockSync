<?php
$pageTitle = 'Products';
$breadcrumb = 'Products';
$content = function () use ($products, $categories, $page, $search, $category, $status) {
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-2">
        <form method="GET" action="/products" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search products..." value="<?= e($search) ?>">
            <select name="category" class="form-select form-select-sm">
                <option value="">All Categories</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"<?= $category == $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="active"<?= $status === 'active' ? ' selected' : '' ?>>Active</option>
                <option value="inactive"<?= $status === 'inactive' ? ' selected' : '' ?>>Inactive</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
        </form>
    </div>
    <?php if (is_manager()): ?>
    <a href="/products/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> Add Product</a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="small fw-semibold">Product</th>
                    <th class="small fw-semibold">SKU</th>
                    <th class="small fw-semibold">Category</th>
                    <th class="small fw-semibold text-end">Stock</th>
                    <th class="small fw-semibold text-end">Price</th>
                    <th class="small fw-semibold">Status</th>
                    <th class="small fw-semibold text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products['data'] as $p): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($p['image']): ?>
                                <img src="/uploads/<?= e($p['image']) ?>" class="rounded" width="40" height="40" style="object-fit:cover">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:40px;height:40px"><i class="bi bi-image text-muted small"></i></div>
                            <?php endif; ?>
                            <div>
                                <div class="small fw-medium"><?= e($p['name']) ?></div>
                                <div class="text-muted small"><?= e($p['barcode'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="small font-monospace"><?= e($p['sku']) ?></td>
                    <td class="small"><?= e($p['category_name'] ?? '-') ?></td>
                    <td class="small text-end">
                        <span class="badge bg-<?= (int)$p['stock_quantity'] === 0 ? 'danger' : ((int)$p['stock_quantity'] <= (int)($p['minimum_stock'] ?? 0) ? 'warning text-dark' : 'success') ?>">
                            <?= number_format((int)$p['stock_quantity']) ?>
                        </span>
                    </td>
                    <td class="small text-end">$<?= number_format((float)$p['selling_price'], 2) ?></td>
                    <td><span class="badge bg-<?= $p['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td class="text-end">
                        <a href="/products/<?= $p['id'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                        <?php if (is_manager()): ?>
                        <a href="/products/<?= $p['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <?php endif; ?>
                        <?php if (is_admin()): ?>
                        <form method="POST" action="/products/<?= $p['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this product?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products['data'])): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No products found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($products['total_pages'] > 1): ?>
    <div class="card-footer bg-white d-flex justify-content-center">
        <?= pagination($products['total_pages'], $products['current_page'], $products['total'], function($p) { return '/products?page=' . $p; }) ?>
    </div>
    <?php endif; ?>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
