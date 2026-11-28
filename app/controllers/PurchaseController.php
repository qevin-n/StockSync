<?php
namespace App\Controllers;

use App\Models\Purchase;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockMovement;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Middleware\AuthMiddleware;

class PurchaseController {
    public function index(): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];
        $page = (int) ($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $purchases = (new Purchase())->getAllWithRelations(
            $businessId,
            $page,
            20,
            $search,
            $status,
            $startDate,
            $endDate
        );
        require APP_ROOT . '/app/views/purchases/index.php';
    }

    public function create(): void {
        AuthMiddleware::requireManager();

        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $suppliers = (new Supplier())->findByBusiness($businessId);
        $products = (new Product())->findByBusiness($businessId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf()) {
                flash_message('error', 'Invalid request.');
                redirect('/purchases/create');
            }

            $supplierId = (int) ($_POST['supplier_id'] ?? 0);
            $purchaseDate = $_POST['purchase_date'] ?? today();
            $referenceNumber = trim($_POST['reference_number'] ?? '');

            if ($referenceNumber === '') {
                $referenceNumber = generate_po_number();
            }

            $notes = trim($_POST['notes'] ?? '');

            if ($supplierId > 0 && !(new Supplier())->findByIdAndBusiness($supplierId, $businessId)) {
                flash_message('error', 'The selected supplier is invalid.');
                redirect('/purchases/create');
            }

            $submittedItems = $_POST['items'] ?? [];

            if (!is_array($submittedItems) || empty($submittedItems)) {
                flash_message('error', 'Please add at least one product.');
                redirect('/purchases/create');
            }

            $productModel = new Product();
            $items = [];
            $totalAmount = 0.0;

            foreach ($submittedItems as $submittedItem) {
                if (!is_array($submittedItem)) {
                    continue;
                }

                $productId = (int) ($submittedItem['product_id'] ?? 0);
                $quantity = (int) ($submittedItem['quantity'] ?? 0);
                $costRaw = $submittedItem['unit_cost'] ?? null;

                if ($productId <= 0 || $quantity <= 0 || !is_numeric($costRaw)) {
                    continue;
                }

                $unitCost = (float) $costRaw;

                if ($unitCost < 0) {
                    flash_message('error', 'Purchase costs cannot be negative.');
                    redirect('/purchases/create');
                }

                $product = $productModel->findByIdAndBusiness($productId, $businessId);

                if (!$product) {
                    flash_message('error', 'A selected product was not found in this business.');
                    redirect('/purchases/create');
                }

                $lineTotal = round($unitCost * $quantity, 2);
                $totalAmount += $lineTotal;

                $items[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $unitCost,
                    'total_price' => $lineTotal,
                ];
            }

            if (empty($items)) {
                flash_message('error', 'Please add at least one valid product with a quantity and cost.');
                redirect('/purchases/create');
            }

            try {
                $purchaseId = (new Purchase())->createPurchase([
                    'business_id' => $businessId,
                    'supplier_id' => $supplierId > 0 ? $supplierId : null,
                    'purchase_number' => $referenceNumber,
                    'purchase_date' => $purchaseDate,
                    'total_amount' => $totalAmount,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'final_amount' => $totalAmount,
                    'status' => 'pending',
                    'notes' => $notes,
                    'created_by' => $userId,
                ], $items);

                (new ActivityLog())->log(
                    $businessId,
                    $userId,
                    'create',
                    'purchase',
                    $purchaseId,
                    ($_SESSION['user_name'] ?? 'User') . ' created purchase ' . $referenceNumber
                );

                flash_message('success', 'Purchase created successfully. Reference: ' . $referenceNumber);
                redirect('/purchases/' . $purchaseId);

            } catch (\Throwable $e) {
                error_log('Purchase creation failed: ' . $e->getMessage());
                flash_message('error', 'Failed to create purchase. Please check the server logs.');
                redirect('/purchases/create');
            }
        }

        require APP_ROOT . '/app/views/purchases/create.php';
    }

    public function show(int $id): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];

        $purchase = (new Purchase())->findByIdWithRelations($id, $businessId);
        if (!$purchase) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }
        $items = (new Purchase())->getItems($id);
        require APP_ROOT . '/app/views/purchases/show.php';
    }

    public function receive(int $id): void {
        AuthMiddleware::requireManager();

        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $purchaseModel = new Purchase();
        $purchase = $purchaseModel->findByIdWithRelations($id, $businessId);

        if (!$purchase) {
            flash_message('error', 'Purchase not found.');
            redirect('/purchases');
        }

        if (!validate_csrf()) {
            flash_message('error', 'Invalid request.');
            redirect('/purchases/' . $id);
        }

        if ($purchase['status'] !== 'pending') {
            flash_message('warning', 'Only pending purchases can be received.');
            redirect('/purchases/' . $id);
        }

        $items = $purchaseModel->getItems($id);

        if (empty($items)) {
            flash_message('error', 'This purchase has no items to receive.');
            redirect('/purchases/' . $id);
        }

        $productModel = new Product();
        $stockMovement = new StockMovement();

        try {
            $purchaseModel->beginTransaction();

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $quantity = (int) $item['quantity'];

                if ($productId <= 0 || $quantity <= 0) {
                    throw new \RuntimeException('A purchase item has invalid product or quantity data.');
                }

                $product = $productModel->findByIdAndBusiness($productId, $businessId);

                if (!$product) {
                    throw new \RuntimeException('A purchase product does not belong to this business.');
                }

                $previousQty = (int) $product['stock_quantity'];
                $newQty = $previousQty + $quantity;

                if (!$productModel->changeStockForBusiness($productId, $businessId, $quantity)) {
                    throw new \RuntimeException('Could not update stock for ' . $product['name'] . '.');
                }

                $stockMovement->log(
                    $businessId,
                    $productId,
                    $userId,
                    'purchase',
                    $quantity,
                    $previousQty,
                    $newQty,
                    'purchase',
                    $id,
                    'Purchase ' . $purchase['reference_number'] . ' received'
                );
            }

            if (!$purchaseModel->updateStatus($id, $businessId, 'received')) {
                throw new \RuntimeException('Could not update the purchase status.');
            }

            $purchaseModel->commit();

        } catch (\Throwable $e) {
            if ($purchaseModel->inTransaction()) {
                $purchaseModel->rollBack();
            }

            error_log('Purchase receiving failed: ' . $e->getMessage());
            flash_message('error', 'Failed to receive purchase. Please check the server logs.');
            redirect('/purchases/' . $id);
        }

        try {
            (new Notification())->createNotification(
                $businessId,
                $userId,
                'purchase',
                'Purchase Received',
                'Purchase ' . $purchase['reference_number'] . ' has been received.'
            );

            (new ActivityLog())->log(
                $businessId,
                $userId,
                'receive',
                'purchase',
                $id,
                ($_SESSION['user_name'] ?? 'User') . ' received purchase ' . $purchase['reference_number']
            );
        } catch (\Throwable $e) {
            error_log('Purchase post-receiving log failed: ' . $e->getMessage());
        }

        flash_message('success', 'Purchase received successfully. Stock updated.');
        redirect('/purchases/' . $id);
    }


    public function cancel(int $id): void {
        AuthMiddleware::requireManager();

        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $purchaseModel = new Purchase();
        $purchase = $purchaseModel->findByIdWithRelations($id, $businessId);

        if (!$purchase) {
            flash_message('error', 'Purchase not found.');
            redirect('/purchases');
        }

        if (!validate_csrf()) {
            flash_message('error', 'Invalid request.');
            redirect('/purchases/' . $id);
        }

        if ($purchase['status'] !== 'pending') {
            flash_message('error', 'Only pending purchases can be cancelled.');
            redirect('/purchases/' . $id);
        }

        if (!$purchaseModel->updateStatus($id, $businessId, 'cancelled')) {
            flash_message('error', 'Could not cancel this purchase.');
            redirect('/purchases/' . $id);
        }

        (new ActivityLog())->log(
            $businessId,
            $userId,
            'cancel',
            'purchase',
            $id,
            ($_SESSION['user_name'] ?? 'User') . ' cancelled purchase ' . $purchase['reference_number']
        );

        flash_message('success', 'Purchase cancelled.');
        redirect('/purchases');
    }

}
