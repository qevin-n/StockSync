<?php
$pageTitle = 'Dashboard';
$breadcrumb = 'Dashboard';
$content = function () use ($stats, $lowStockItems, $inventoryStats, $topProducts, $salesByCategory, $notifications, $unreadCount, $activityLogs, $chartLabels, $chartData, $salesChange, $currency) {
?>
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Total Products</p>
                        <h4 class="fw-bold mb-0"><?= number_format($stats['total_products']) ?></h4>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-box"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Today's Sales</p>
                        <h4 class="fw-bold mb-0"><?= $currency ?><?= number_format($stats['today_sales'], 2) ?></h4>
                        <small class="<?= $salesChange >= 0 ? 'text-success' : 'text-danger' ?>"><?= $salesChange >= 0 ? '+' : '' ?><?= number_format($salesChange, 1) ?>% vs yesterday</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-cart-check"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Inventory Value</p>
                        <h4 class="fw-bold mb-0"><?= $currency ?><?= number_format($stats['inventory_value'], 2) ?></h4>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-cash-stack"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Low Stock</p>
                        <h4 class="fw-bold mb-0"><?= $stats['low_stock_count'] ?></h4>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Sales Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0">Sales Overview (Last 30 Days)</h6>
                <a href="/reports" class="btn btn-sm btn-outline-primary">View Reports</a>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <!-- Low Stock -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="fw-semibold mb-0">Low Stock Alerts</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lowStockItems)): ?>
                    <div class="p-3 text-center text-muted small">No low stock items</div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($lowStockItems, 0, 6) as $item): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small fw-medium"><?= e($item['name']) ?></div>
                            <div class="text-muted small">SKU: <?= e($item['sku']) ?></div>
                        </div>
                        <span class="badge bg-<?= (int) $item['stock_quantity'] === 0 ? 'danger' : 'warning' ?>"><?= $item['stock_quantity'] ?> / <?= $item['minimum_stock'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <!-- Top Products -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="fw-semibold mb-0">Top Selling Products</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th class="small">Product</th><th class="small text-end">Qty Sold</th><th class="small text-end">Revenue</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topProducts as $p): ?>
                        <tr>
                            <td class="small"><?= e($p['name']) ?></td>
                            <td class="small text-end"><?= number_format((int) ($p['total_sold'] ?? 0)) ?></td>
                            <td class="small text-end"><?= $currency ?><?= number_format((float) ($p['total_revenue'] ?? 0), 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($topProducts)): ?>
                        <tr><td colspan="3" class="text-center text-muted small">No sales data yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Recent Activity -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0">Recent Activity</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($activityLogs as $log): ?>
                    <div class="list-group-item d-flex gap-3 align-items-start">
                        <div class="mt-1">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-clock-history"></i></span>
                        </div>
                        <div>
                            <div class="small"><?= e($log['description']) ?></div>
                            <div class="text-muted small"><?= time_ago($log['created_at']) ?> by <?= e($log['user_name'] ?? 'System') ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($activityLogs)): ?>
                        <div class="p-3 text-center text-muted small">No recent activity</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('salesChart');
    if (!canvas || typeof Chart === 'undefined') return;

    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(37, 99, 235, 0.20)');
    gradient.addColorStop(1, 'rgba(37, 99, 235, 0.00)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                label: 'Sales',
                data: <?= json_encode($chartData) ?>,
                borderColor: '#2563EB',
                backgroundColor: gradient,
                borderWidth: 2.5,
                fill: true,
                tension: 0.38,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#2563EB',
                pointBorderWidth: 2,
                pointHoverBackgroundColor: '#2563EB',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#cbd5e1',
                    bodyColor: '#ffffff',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return ' Sales: ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.16)',
                        drawTicks: false
                    },
                    ticks: {
                        padding: 12,
                        color: '#94a3b8',
                        precision: 0
                    }
                },
                x: {
                    border: { display: false },
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxTicksLimit: 8,
                        color: '#94a3b8',
                        padding: 10,
                        maxRotation: 0
                    }
                }
            }
        }
    });
});
</script>
<?php
};
require APP_ROOT . '/app/views/layouts/app.php';
