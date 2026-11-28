<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\StockMovement;
use App\Models\ActivityLog;
use App\Middleware\AuthMiddleware;

class ProductController
{
    public function index(): void
    {
        AuthMiddleware::requireStaff();

        $businessId = (int) $_SESSION['business_id'];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';

        $productModel = new Product();

        $products = $productModel->getAllWithRelations(
            $businessId,
            $page,
            20,
            $search,
            $category,
            $status
        );

        $categories = (new Category())->findByBusiness($businessId);

        require APP_ROOT . '/app/views/products/index.php';
    }

    public function create(): void
    {
        AuthMiddleware::requireManager();

        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $categories = (new Category())->findByBusiness($businessId);
        $suppliers = (new Supplier())->findByBusiness($businessId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf()) {
                flash_message('error', 'Invalid request.');
                redirect('/products/create');
            }

            $validation = $this->validateProduct($_POST, $businessId);

            if (!empty($validation['errors'])) {
                flash_message('error', implode(' ', $validation['errors']));
                set_old_input($_POST);
                redirect('/products/create');
            }

            $productModel = new Product();

            $sku = !empty($_POST['sku'])
                ? trim($_POST['sku'])
                : generate_sku();

            if ($productModel->findBySkuAndBusiness($sku, $businessId)) {
                flash_message('error', 'SKU already exists.');
                set_old_input($_POST);
                redirect('/products/create');
            }

            try {
                $productId = $productModel->create([
                    'business_id' => $businessId,
                    'category_id' => !empty($_POST['category_id'])
                        ? (int) $_POST['category_id']
                        : null,
                    'supplier_id' => !empty($_POST['supplier_id'])
                        ? (int) $_POST['supplier_id']
                        : null,
                    'name' => trim($_POST['name']),
                    'sku' => $sku,
                    'description' => trim($_POST['description'] ?? ''),
                    'unit' => trim($_POST['unit'] ?? 'pcs') ?: 'pcs',
                    'purchase_price' => (float) ($_POST['purchase_price'] ?? 0),
                    'selling_price' => (float) ($_POST['selling_price'] ?? 0),
                    'quantity' => max(0, (int) ($_POST['initial_stock'] ?? 0)),
                    'min_stock_level' => max(0, (int) ($_POST['minimum_stock'] ?? 0)),
                    'status' => $_POST['status'] ?? 'active'
                ]);

                $log = new ActivityLog();

                $log->log(
                    $businessId,
                    $userId,
                    'create',
                    'product',
                    $productId,
                    $_SESSION['user_name'] . ' created product "' .
                    trim($_POST['name']) . '"'
                );

                flash_message('success', 'Product created successfully.');
                redirect('/products');

            } catch (\Throwable $e) {
                error_log('Product creation error: ' . $e->getMessage());

                flash_message(
                    'error',
                    'Unable to create product. Please check the server logs.'
                );

                redirect('/products/create');
            }
        }

        require APP_ROOT . '/app/views/products/create.php';
    }

    public function show(int $id): void
    {
        AuthMiddleware::requireStaff();

        $businessId = (int) $_SESSION['business_id'];

        $productModel = new Product();

        $product = $productModel->findByIdWithRelations(
            $id,
            $businessId
        );

        if (!$product) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }

        $stockMovements = (new StockMovement())->getByProduct($id, 50);

        require APP_ROOT . '/app/views/products/show.php';
    }

    public function edit(int $id): void
    {
        AuthMiddleware::requireManager();

        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $productModel = new Product();

        $product = $productModel->findByIdAndBusiness(
            $id,
            $businessId
        );

        if (!$product) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }

        $categories = (new Category())->findByBusiness($businessId);
        $suppliers = (new Supplier())->findByBusiness($businessId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf()) {
                flash_message('error', 'Invalid request.');
                redirect('/products/' . $id . '/edit');
            }

            $validation = $this->validateProduct(
                $_POST,
                $businessId,
                $id
            );

            if (!empty($validation['errors'])) {
                flash_message('error', implode(' ', $validation['errors']));
                set_old_input($_POST);
                redirect('/products/' . $id . '/edit');
            }

            $updateData = [
                'category_id' => !empty($_POST['category_id'])
                    ? (int) $_POST['category_id']
                    : null,
                'supplier_id' => !empty($_POST['supplier_id'])
                    ? (int) $_POST['supplier_id']
                    : null,
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description'] ?? ''),
                'unit' => trim($_POST['unit'] ?? $product['unit']) ?: 'pcs',
                'purchase_price' => (float) ($_POST['purchase_price'] ?? 0),
                'selling_price' => (float) ($_POST['selling_price'] ?? 0),
                'min_stock_level' => max(
                    0,
                    (int) ($_POST['minimum_stock'] ?? 0)
                ),
                'status' => $_POST['status'] ?? 'active',
                'updated_at' => now()
            ];

            if (!empty($_POST['sku'])) {
                $newSku = trim($_POST['sku']);

                if ($newSku !== $product['sku']) {
                    $existing = $productModel->findBySkuAndBusiness(
                        $newSku,
                        $businessId,
                        $id
                    );

                    if ($existing) {
                        flash_message('error', 'SKU already exists.');
                        set_old_input($_POST);
                        redirect('/products/' . $id . '/edit');
                    }

                    $updateData['sku'] = $newSku;
                }
            }

            try {
                $productModel->updateByBusiness(
                    $id,
                    $businessId,
                    $updateData
                );

                $log = new ActivityLog();

                $log->log(
                    $businessId,
                    $userId,
                    'update',
                    'product',
                    $id,
                    $_SESSION['user_name'] . ' updated product "' .
                    trim($_POST['name']) . '"'
                );

                flash_message('success', 'Product updated successfully.');
                redirect('/products');

            } catch (\Throwable $e) {
                error_log('Product update error: ' . $e->getMessage());

                flash_message(
                    'error',
                    'Unable to update product. Please check the server logs.'
                );

                redirect('/products/' . $id . '/edit');
            }
        }

        require APP_ROOT . '/app/views/products/edit.php';
    }

    public function delete(int $id): void
    {
        AuthMiddleware::requireAdmin();

        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $productModel = new Product();

        $product = $productModel->findByIdAndBusiness(
            $id,
            $businessId
        );

        if (!$product) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }

        if (!validate_csrf()) {
            flash_message('error', 'Invalid request.');
            redirect('/products');
        }

        try {
            $productModel->deleteByBusiness($id, $businessId);

            $log = new ActivityLog();

            $log->log(
                $businessId,
                $userId,
                'delete',
                'product',
                $id,
                $_SESSION['user_name'] . ' deleted product "' .
                $product['name'] . '"'
            );

            flash_message('success', 'Product deleted successfully.');

        } catch (\Throwable $e) {
            error_log('Product deletion error: ' . $e->getMessage());

            flash_message(
                'error',
                'Unable to delete this product. It may be linked to existing transactions.'
            );
        }

        redirect('/products');
    }

    private function validateProduct(
        array $post,
        int $businessId,
        ?int $excludeId = null
    ): array {
        $errors = [];

        if (trim($post['name'] ?? '') === '') {
            $errors[] = 'Product name is required.';
        }

        if (empty($post['category_id'])) {
            $errors[] = 'Category is required.';
        }

        if ((float) ($post['purchase_price'] ?? 0) < 0) {
            $errors[] = 'Purchase price cannot be negative.';
        }

        if ((float) ($post['selling_price'] ?? 0) < 0) {
            $errors[] = 'Selling price cannot be negative.';
        }

        if ((int) ($post['initial_stock'] ?? 0) < 0) {
            $errors[] = 'Initial stock cannot be negative.';
        }

        if ((int) ($post['minimum_stock'] ?? 0) < 0) {
            $errors[] = 'Minimum stock cannot be negative.';
        }

        if (
            isset($post['status']) &&
            !in_array($post['status'], ['active', 'inactive'], true)
        ) {
            $errors[] = 'Invalid product status.';
        }

        if (!empty($post['sku'])) {
            $existing = (new Product())->findBySkuAndBusiness(
                trim($post['sku']),
                $businessId,
                $excludeId
            );

            if ($existing) {
                $errors[] = 'SKU already exists.';
            }
        }

        return ['errors' => $errors];
    }
}
