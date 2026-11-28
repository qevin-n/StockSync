<?php
$pageTitle = 'Sale ' . e($sale['invoice_number']);
$breadcrumb = 'Sale Details';
$content = function () use ($sale, $items, $customer, $currency) {
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0"><?= e($sale['invoice_number']) ?></h5>
                    <small class="text-muted"><?= format_date($sale['sale_date']) ?></small>
                </div>
                <div class="text-end">
                    <span class="badge bg-<?= $sale['status'] === 'completed' ? 'success' : ($sale['status'] === 'pending' ? 'warning text-dark' : 'danger') ?>"><?= ucfirst($sale['status']) ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="small fw-bold text-muted">CUSTOMER</h6>
                        <p class="mb-0"><?= e($customer['name'] ?? 'Walk-in Customer') ?></p>
                        <small class="text-muted"><?= e($customer['phone'] ?? '') ?></small><br>
                        <small class="text-muted"><?= e($customer['email'] ?? '') ?></small>
                    </div>
                </div>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr><th class="small">Product</th><th class="small text-end">Unit Price</th><th class="small text-end">Qty</th><th class="small text-end">Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $i): ?>
                        <tr>
                            <td class="small"><?= e($i['product_name']) ?></td>
                            <td class="small text-end"><?= $currency ?><?= number_format((float)$i['unit_price'], 2) ?></td>
                            <td class="small text-end"><?= $i['quantity'] ?></td>
                            <td class="small text-end fw-medium"><?= $currency ?><?= number_format((float)$i['subtotal'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><th colspan="3" class="text-end">Total:</th><th class="text-end"><?= $currency ?><?= number_format((float)$sale['total_amount'], 2) ?></th></tr>
                    </tfoot>
                </table>
                <?php if ($sale['notes']): ?>
                <div class="mt-3"><h6 class="small fw-bold text-muted">NOTES</h6><p class="small text-muted mb-0"><?= nl2br(e($sale['notes'])) ?></p></div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="/sales" class="btn btn-outline-secondary btn-sm">Back to Sales</a>
                <a href="/sales/<?= $sale['id'] ?>/invoice" class="btn btn-primary btn-sm" target="_blank"><i class="bi bi-printer me-1"></i> Print Invoice</a>
            </div>
        </div>
    </div>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
