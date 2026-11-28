<?php
namespace App\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\StockMovement;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Middleware\AuthMiddleware;

class SaleController {
    public function index(): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];
        $page = (int) ($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $sales = (new Sale())->getAllWithRelations(
            $businessId,
            $page,
            20,
            $search,
            $status,
            $startDate,
            $endDate
        );
        require APP_ROOT . '/app/views/sales/index.php';
    }

    public function create(): void {
        AuthMiddleware::requireStaff();

        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $customers = (new Customer())->findByBusiness($businessId);
        $products = (new Product())->findByBusiness($businessId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf()) {
                flash_message('error', 'Invalid request.');
                redirect('/sales/create');
            }

            $customerId = (int) ($_POST['customer_id'] ?? 0);
            $saleDate = $_POST['sale_date'] ?? today();
            $invoice = !empty($_POST['invoice_number'])
                ? trim($_POST['invoice_number'])
                : generate_invoice_number();
            $notes = trim($_POST['notes'] ?? '');

            if ($customerId > 0 && !(new Customer())->findByIdAndBusiness($customerId, $businessId)) {
                flash_message('error', 'The selected customer is invalid.');
                redirect('/sales/create');
            }

            $submittedItems = $_POST['items'] ?? [];

            if (!is_array($submittedItems) || empty($submittedItems)) {
                flash_message('error', 'Please add at least one product.');
                redirect('/sales/create');
            }

            $productModel = new Product();
            $items = [];
            $requiredStock = [];
            $subtotal = 0.0;

            foreach ($submittedItems as $submittedItem) {
                if (!is_array($submittedItem)) continue;

                $productId = (int) ($submittedItem['product_id'] ?? 0);
                $qty = (int) ($submittedItem['quantity'] ?? 0);
                $priceRaw = $submittedItem['unit_price'] ?? null;

                if ($productId <= 0 || $qty <= 0 || !is_numeric($priceRaw)) continue;

                $price = (float) $priceRaw;

                if ($price < 0) {
                    flash_message('error', 'Product prices cannot be negative.');
                    redirect('/sales/create');
                }

                $product = $productModel->findByIdAndBusiness($productId, $businessId);

                if (!$product) {
                    flash_message('error', 'A selected product was not found in this business.');
                    redirect('/sales/create');
                }

                $lineTotal = round($price * $qty, 2);
                $subtotal += $lineTotal;
                $requiredStock[$productId] = ($requiredStock[$productId] ?? 0) + $qty;

                $items[] = [
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $lineTotal,
                ];
            }

            if (empty($items)) {
                flash_message('error', 'Please add at least one valid product with a quantity and price.');
                redirect('/sales/create');
            }

            foreach ($requiredStock as $productId => $requiredQty) {
                $product = $productModel->findByIdAndBusiness((int) $productId, $businessId);

                if (!$product || (int) $product['stock_quantity'] < $requiredQty) {
                    $name = $product['name'] ?? 'Selected product';
                    $available = (int) ($product['stock_quantity'] ?? 0);

                    flash_message('error', $name . ' has insufficient stock. Available: ' . $available);
                    redirect('/sales/create');
                }
            }

            $saleModel = new Sale();
            $saleModel->beginTransaction();

            try {
                $saleId = $saleModel->createSale([
                    'business_id' => $businessId,
                    'customer_id' => $customerId > 0 ? $customerId : null,
                    'sale_number' => $invoice,
                    'sale_date' => $saleDate,
                    'total_amount' => $subtotal,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'final_amount' => $subtotal,
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'notes' => $notes,
                    'created_by' => $userId,
                ], $items);

                $stockMovement = new StockMovement();

                foreach ($items as $item) {
                    $productId = (int) $item['product_id'];
                    $qty = (int) $item['quantity'];

                    $product = $productModel->findByIdAndBusiness($productId, $businessId);

                    if (!$product) {
                        throw new \RuntimeException('A selected product is no longer available.');
                    }

                    $previousQty = (int) $product['stock_quantity'];
                    $newQty = $previousQty - $qty;

                    if (!$productModel->changeStockForBusiness($productId, $businessId, -$qty)) {
                        throw new \RuntimeException('Stock could not be updated for ' . $product['name'] . '.');
                    }

                    $stockMovement->log(
                        $businessId,
                        $productId,
                        $userId,
                        'sale',
                        $qty,
                        $previousQty,
                        $newQty,
                        'sale',
                        $saleId,
                        'Sale ' . $invoice
                    );

                    if ($newQty <= (int) $product['minimum_stock']) {
                        (new Notification())->createNotification(
                            $businessId,
                            $userId,
                            'low_stock',
                            'Low Stock Alert',
                            'Product "' . $product['name'] . '" is running low. Current stock: ' . $newQty
                        );
                    }
                }

                $saleModel->commit();
            } catch (\Throwable $e) {
                if ($saleModel->inTransaction()) {
                    $saleModel->rollBack();
                }

                error_log('Sale creation failed: ' . $e->getMessage());
                flash_message('error', 'Failed to process sale. Please check the server logs.');
                redirect('/sales/create');
            }

            (new ActivityLog())->log(
                $businessId,
                $userId,
                'create',
                'sale',
                $saleId,
                ($_SESSION['user_name'] ?? 'User') . ' created sale ' . $invoice
            );

            flash_message('success', 'Sale completed successfully. Invoice: ' . $invoice);
            redirect('/sales/' . $saleId);
        }

        require APP_ROOT . '/app/views/sales/create.php';
    }

    public function show(int $id): void {
        AuthMiddleware::requireStaff();
        $businessId = (int) $_SESSION['business_id'];

        $sale = (new Sale())->findByIdWithRelations($id, $businessId);
        if (!$sale) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }
        $items = (new Sale())->getItems($id);
        require APP_ROOT . '/app/views/sales/show.php';
    }

    public function invoice(int $id): void {
        AuthMiddleware::requireStaff();
        $businessId = (int) $_SESSION['business_id'];

        $sale = (new Sale())->findByIdWithRelations($id, $businessId);
        if (!$sale) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }
        $items = (new Sale())->getItems($id);
        require APP_ROOT . '/app/views/sales/invoice.php';
    }
}
