<?php
$pageTitle = 'Purchase ' . e($purchase['reference_number']);
$breadcrumb = 'Purchase Details';
$content = function () use ($purchase, $items, $supplier, $currency) {
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0"><?= e($purchase['reference_number']) ?></h5>
                    <small class="text-muted"><?= format_date($purchase['purchase_date']) ?></small>
                </div>
                <span class="badge bg-<?= $purchase['status'] === 'received' ? 'success' : ($purchase['status'] === 'ordered' ? 'info' : ($purchase['status'] === 'partial' ? 'warning text-dark' : 'danger')) ?>"><?= ucfirst($purchase['status']) ?></span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="small fw-bold text-muted">SUPPLIER</h6>
                        <p class="mb-0"><?= e($supplier['company_name'] ?? '-') ?></p>
                        <small class="text-muted"><?= e($supplier['contact_person'] ?? '') ?></small><br>
                        <small class="text-muted"><?= e($supplier['phone'] ?? '') ?></small><br>
                        <small class="text-muted"><?= e($supplier['email'] ?? '') ?></small>
                    </div>
                </div>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr><th class="small">Product</th><th class="small text-end">Unit Cost</th><th class="small text-end">Qty</th><th class="small text-end">Received</th><th class="small text-end">Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $i): ?>
                        <tr>
                            <td class="small"><?= e($i['product_name']) ?></td>
                            <td class="small text-end"><?= $currency ?><?= number_format((float)$i['unit_cost'], 2) ?></td>
                            <td class="small text-end"><?= $i['quantity'] ?></td>
                            <td class="small text-end"><?= $i['received_quantity'] ?></td>
                            <td class="small text-end fw-medium"><?= $currency ?><?= number_format((float)$i['subtotal'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><th colspan="4" class="text-end">Total:</th><th class="text-end"><?= $currency ?><?= number_format((float)$purchase['total_amount'], 2) ?></th></tr>
                    </tfoot>
                </table>
                <?php if ($purchase['notes']): ?>
                <div class="mt-3"><h6 class="small fw-bold text-muted">NOTES</h6><p class="small text-muted mb-0"><?= nl2br(e($purchase['notes'])) ?></p></div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="/purchases" class="btn btn-outline-secondary btn-sm">Back to Purchases</a>
            </div>
        </div>
    </div>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
