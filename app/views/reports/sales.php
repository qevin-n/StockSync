<?php
$pageTitle = 'Sales Report';
$breadcrumb = 'Sales Report';
$content = function () use ($summary, $dailySales, $topProducts, $salesByCategory, $chartLabels, $chartData, $currency, $startDate, $endDate) {
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form method="GET" action="/reports/sales" class="d-flex gap-2">
        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= e($startDate) ?>">
        <input type="date" name="end_date" class="form-control form-control-sm" value="<?= e($endDate) ?>">
        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Total Sales</div><h5 class="fw-bold mb-0"><?= $currency ?><?= number_format((float)($summary['total_sales'] ?? 0), 2) ?></h5></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Total Orders</div><h5 class="fw-bold mb-0"><?= number_format((int)($summary['total_orders'] ?? 0)) ?></h5></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Avg Order</div><h5 class="fw-bold mb-0"><?= $currency ?><?= number_format((float)($summary['avg_order'] ?? 0), 2) ?></h5></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Total Items</div><h5 class="fw-bold mb-0"><?= number_format((int)($summary['total_items'] ?? 0)) ?></h5></div></div></div>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0"><h6 class="fw-semibold mb-0">Daily Sales</h6></div>
    <div class="card-body"><canvas id="salesReportChart" height="80"></canvas></div>
</div>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h6 class="fw-semibold mb-0">Top Products</h6></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th class="small">Product</th><th class="small text-end">Qty</th><th class="small text-end">Revenue</th></tr></thead>
                    <tbody>
                        <?php foreach ($topProducts as $p): ?>
                        <tr><td class="small"><?= e($p['name']) ?></td><td class="small text-end"><?= number_format((int)($p['total_sold'] ?? 0)) ?></td><td class="small text-end"><?= $currency ?><?= number_format((float)($p['total_revenue'] ?? 0), 2) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0"><h6 class="fw-semibold mb-0">Sales by Category</h6></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th class="small">Category</th><th class="small text-end">Sales</th></tr></thead>
                    <tbody>
                        <?php foreach ($salesByCategory as $c): ?>
                        <tr><td class="small"><?= e($c['category_name'] ?? 'Uncategorized') ?></td><td class="small text-end"><?= $currency ?><?= number_format((float)($c['total_sales'] ?? 0), 2) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('salesReportChart'), {
        type: 'bar',
        data: { labels: <?= json_encode($chartLabels) ?>, datasets: [{ label: 'Sales', data: <?= json_encode($chartData) ?>, backgroundColor: '#2563EB', borderRadius: 4 }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.03)' } }, x: { grid: { display: false } } } }
    });
});
</script>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
