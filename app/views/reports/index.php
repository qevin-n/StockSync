<?php
$pageTitle = 'Reports';
$breadcrumb = 'Reports';
$content = function () {
?>
<div class="row g-4">
    <div class="col-md-4">
        <a href="/reports/sales" class="card border-0 shadow-sm text-decoration-none text-dark h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-graph-up-arrow"></i></div>
                <div><h6 class="fw-bold mb-1">Sales Report</h6><p class="text-muted small mb-0">View sales trends, revenue breakdowns, and top products.</p></div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/reports/purchases" class="card border-0 shadow-sm text-decoration-none text-dark h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-bag"></i></div>
                <div><h6 class="fw-bold mb-1">Purchase Report</h6><p class="text-muted small mb-0">Track purchase orders, supplier performance, and costs.</p></div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/reports/stock" class="card border-0 shadow-sm text-decoration-none text-dark h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-boxes"></i></div>
                <div><h6 class="fw-bold mb-1">Stock Report</h6><p class="text-muted small mb-0">Monitor stock levels, valuations, and movement history.</p></div>
            </div>
        </a>
    </div>
</div>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
