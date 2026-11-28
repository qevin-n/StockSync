<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Category;
use App\Middleware\AuthMiddleware;

class ReportController {
    public function index(): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];

        $start = $_GET['start_date'] ?? date('Y-m-01');
        $end = $_GET['end_date'] ?? today();

        $saleModel = new Sale();
        $purchaseModel = new Purchase();
        $productModel = new Product();

        $totalSales = $saleModel->getTotalSales($businessId, $start, $end);
        $totalPurchases = $purchaseModel->getTotalPurchases($businessId, $start, $end);
        $profit = $totalSales - $totalPurchases;

        $salesByDate = $saleModel->getSalesByDate($businessId, $start, $end);
        $topProducts = $productModel->getTopSelling($businessId, 10, $start, $end);
        $lowStock = $productModel->getLowStock($businessId);
        $salesByCategory = $productModel->getSalesByCategory($businessId, $start, $end);

        $chartLabels = [];
        $chartValues = [];
        $period = new \DatePeriod(new \DateTime($start), new \DateInterval('P1D'), (new \DateTime($end))->modify('+1 day'));
        foreach ($period as $date) {
            $d = $date->format('Y-m-d');
            $chartLabels[] = $date->format('M d');
            $chartValues[$d] = 0;
        }
        foreach ($salesByDate as $row) {
            $chartValues[$row['date']] = (float) $row['total'];
        }

        $categoryData = [];
        foreach ($salesByCategory as $row) {
            $categoryData['labels'][] = $row['category_name'] ?? 'Uncategorized';
            $categoryData['values'][] = (float) $row['total'];
        }

        require APP_ROOT . '/app/views/reports/index.php';
    }

    public function sales(): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];
        $start = $_GET['start_date'] ?? date('Y-m-01');
        $end = $_GET['end_date'] ?? today();
        $page = (int) ($_GET['page'] ?? 1);

        $saleModel = new Sale();
        $sales = $saleModel->getAllWithRelations($businessId, $page, 50, '', '', $start, $end);
        $summary = $saleModel->getSalesByDate($businessId, $start, $end);

        require APP_ROOT . '/app/views/reports/sales.php';
    }

    public function purchases(): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];
        $start = $_GET['start_date'] ?? date('Y-m-01');
        $end = $_GET['end_date'] ?? today();
        $page = (int) ($_GET['page'] ?? 1);

        $purchaseModel = new Purchase();
        $purchases = $purchaseModel->getAllWithRelations($businessId, $page, 50, '', '', $start, $end);

        require APP_ROOT . '/app/views/reports/purchases.php';
    }

    public function stock(): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];

        $productModel = new Product();
        $inventoryStats = $productModel->getInventoryStats($businessId);
        $lowStock = $productModel->getLowStock($businessId);
        $outOfStock = $productModel->getOutOfStock($businessId);
        $valuations = $productModel->getStockValuation($businessId);

        require APP_ROOT . '/app/views/reports/stock.php';
    }
}
