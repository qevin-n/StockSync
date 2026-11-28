<?php
$pageTitle = 'Categories';
$breadcrumb = 'Categories';
$content = function () use ($categories) {
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Manage Categories</h6>
    <?php if (is_manager()): ?>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="bi bi-plus-lg me-1"></i> Add Category</button>
    <?php endif; ?>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th class="small fw-semibold">Name</th><th class="small fw-semibold">Description</th><th class="small fw-semibold text-end">Products</th><th class="small fw-semibold text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td class="fw-medium small"><?= e($c['name']) ?></td>
                    <td class="small text-muted"><?= e($c['description'] ?: '-') ?></td>
                    <td class="small text-end"><?= number_format((int)($c['product_count'] ?? 0)) ?></td>
                    <td class="text-end">
                        <?php if (is_manager()): ?>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?= $c['id'] ?>"><i class="bi bi-pencil"></i></button>
                        <?php endif; ?>
                        <?php if (is_admin()): ?>
                        <form method="POST" action="/categories/<?= $c['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this category?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">No categories found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="/categories/create">
                <?= csrf_field() ?>
                <div class="modal-header"><h6 class="modal-title">Add Category</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals -->
<?php foreach ($categories as $c): ?>
<div class="modal fade" id="editCategoryModal<?= $c['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="/categories/<?= $c['id'] ?>/update">
                <?= csrf_field() ?>
                <div class="modal-header"><h6 class="modal-title">Edit Category</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?= e($c['name']) ?>" required></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?= e($c['description'] ?? '') ?></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
