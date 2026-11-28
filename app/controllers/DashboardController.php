<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Business;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Notification;
use App\Models\StockMovement;
use App\Models\ActivityLog;
use App\Middleware\AuthMiddleware;

class DashboardController {
    public function index(): void {
        AuthMiddleware::requireAuth();
        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        // Load the business currency for dashboard formatting.
        $business = (new Business())->findById($businessId);
        $currency = $business['currency'] ?? '$';

        $productModel = new Product();
        $saleModel = new Sale();
        $purchaseModel = new Purchase();
        $notificationModel = new Notification();
        $logModel = new ActivityLog();

        $stats = [
            'total_products' => $productModel->count(['business_id' => $businessId]),
            'inventory_value' => $productModel->getTotalValue($businessId),
            'low_stock_count' => count($productModel->getLowStock($businessId)),
            'today_sales' => $saleModel->getTodaySales($businessId),
            'monthly_sales' => $saleModel->getMonthlySales($businessId),
            'pending_purchases' => $purchaseModel->getPendingCount($businessId),
            'out_of_stock' => count($productModel->getOutOfStock($businessId)),
        ];

        // Compare with yesterday for percentage change
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $yesterdayData = $saleModel->getSalesByDate(
            $businessId,
            $yesterday,
            $yesterday
        );
        $yesterdaySales = (float) ($yesterdayData[0]['total'] ?? 0);

        $salesChange = 0;
        if ($yesterdaySales > 0) {
            $salesChange = (($stats['today_sales'] - $yesterdaySales) / $yesterdaySales) * 100;
        } elseif ($stats['today_sales'] > 0) {
            $salesChange = 100;
        }

        $lowStockItems = $productModel->getLowStock($businessId);
        $inventoryStats = $productModel->getInventoryStats($businessId);
        $topProducts = $productModel->getTopSelling($businessId, 5);
        $salesByCategory = $productModel->getSalesByCategory($businessId);
        $notifications = $notificationModel->getUnread($businessId, $userId, 10);
        $unreadCount = $notificationModel->getUnreadCount($businessId, $userId);
        $activityLogs = $logModel->getRecent($businessId, 15);

        // Sales chart data (last 30 days)
        $endDate = today();
        $startDate = date('Y-m-d', strtotime('-29 days'));
        $salesChartData = $saleModel->getSalesByDate($businessId, $startDate, $endDate);

        $chartLabels = [];
        $chartValues = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $chartLabels[] = date('M d', strtotime($d));
            $chartValues[$d] = 0;
        }
        foreach ($salesChartData as $row) {
            $chartValues[$row['date']] = (float) $row['total'];
        }

        $chartData = array_values($chartValues);

        require APP_ROOT . '/app/views/dashboard/index.php';
    }
}
