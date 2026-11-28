<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Middleware\AuthMiddleware;

class InventoryController {
    public function index(): void {
        AuthMiddleware::requireStaff();
        $businessId = (int) $_SESSION['business_id'];
        $page = (int) ($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';

        $productModel = new Product();
        $products = $productModel->getAllWithRelations($businessId, $page, 20, $search, $category, $status);
        $categories = (new Category())->findByBusiness($businessId);
        $inventoryStats = $productModel->getInventoryStats($businessId);

        $lowStock = $inventoryStats['low_stock'] ?? 0;
        $outOfStock = $inventoryStats['out_of_stock'] ?? 0;
        $productFilter = $search;

        require APP_ROOT . '/app/views/inventory/index.php';
    }

    public function stockMovements(): void {
        AuthMiddleware::requireStaff();
        $businessId = (int) $_SESSION['business_id'];
        $page = (int) ($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';

        $movements = (new StockMovement())->getAllWithRelations($businessId, $page, 20, $search, $type);
        require APP_ROOT . '/app/views/inventory/stock-movements.php';
    }

    public function adjust(int $id): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $productModel = new Product();
        $product = $productModel->findByIdAndBusiness($id, $businessId);
        if (!$product) {
            flash_message('error', 'Product not found.');
            redirect('/inventory');
        }

        if (!validate_csrf()) {
            flash_message('error', 'Invalid request.');
            redirect('/inventory');
        }

        $newQty = (int) ($_POST['new_quantity'] ?? $product['stock_quantity']);
        if ($newQty < 0) {
            flash_message('error', 'Stock cannot be negative.');
            redirect('/inventory');
        }

        $prevQty = (int) $product['stock_quantity'];
        $diff = $newQty - $prevQty;

        $productModel->setStock($id, $newQty);

        (new StockMovement())->log($businessId, $id, $userId, 'adjustment', $diff, $prevQty, $newQty, 'product', $id, trim($_POST['reason'] ?? 'Manual adjustment'));
        (new ActivityLog())->log($businessId, $userId, 'adjust', 'inventory', $id, 'Adjusted stock for "' . $product['name'] . '" from ' . $prevQty . ' to ' . $newQty);

        if ($newQty <= $product['minimum_stock'] && $newQty > 0) {
            (new Notification())->createNotification($businessId, $userId, 'low_stock', 'Low Stock Alert', 'Product "' . $product['name'] . '" is running low. Current stock: ' . $newQty);
        } elseif ($newQty === 0) {
            (new Notification())->createNotification($businessId, $userId, 'out_of_stock', 'Out of Stock', 'Product "' . $product['name'] . '" is out of stock.');
        }

        flash_message('success', 'Stock adjusted successfully.');
        redirect('/inventory');
    }
}
