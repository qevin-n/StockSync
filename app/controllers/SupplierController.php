<?php
namespace App\Controllers;

use App\Models\Supplier;
use App\Models\ActivityLog;
use App\Middleware\AuthMiddleware;

class SupplierController {
    public function index(): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $suppliers = (new Supplier())->getAllWithStats(
            $businessId,
            $page,
            20,
            $search,
            $status
        );

        require APP_ROOT . '/app/views/suppliers/index.php';
    }

    public function create(): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf()) {
                flash_message('error', 'Invalid request.');
                redirect('/suppliers');
            }

            $name = trim($_POST['company_name'] ?? '');
            if (empty($name)) {
                flash_message('error', 'Company name is required.');
                redirect('/suppliers');
            }

            $id = (new Supplier())->create([
                'business_id' => $businessId,
                'company_name' => $name,
                'contact_person' => trim($_POST['contact_person'] ?? ''),
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?: null,
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'website' => trim($_POST['website'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            (new ActivityLog())->log($businessId, $userId, 'create', 'supplier', $id, $_SESSION['user_name'] . ' created supplier "' . $name . '"');
            flash_message('success', 'Supplier created successfully.');
            redirect('/suppliers');
        }

        require APP_ROOT . '/app/views/suppliers/create.php';
    }

    public function show(int $id): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];

        $supplier = (new Supplier())->findByIdAndBusiness($id, $businessId);
        if (!$supplier) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }

        $purchaseHistory = (new Supplier())->getPurchaseHistory($id, $businessId);
        require APP_ROOT . '/app/views/suppliers/show.php';
    }

    public function edit(int $id): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $supplier = (new Supplier())->findByIdAndBusiness($id, $businessId);
        if (!$supplier) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf()) {
                flash_message('error', 'Invalid request.');
                redirect('/suppliers/' . $id . '/edit');
            }

            $name = trim($_POST['company_name'] ?? '');
            if (empty($name)) {
                flash_message('error', 'Company name is required.');
                redirect('/suppliers/' . $id . '/edit');
            }

            (new Supplier())->updateByBusiness($id, $businessId, [
                'company_name' => $name,
                'contact_name' => trim($_POST['contact_name'] ?? ''),
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?: null,
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'country' => trim($_POST['country'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
                'updated_at' => now(),
            ]);

            (new ActivityLog())->log($businessId, $userId, 'update', 'supplier', $id, $_SESSION['user_name'] . ' updated supplier "' . $name . '"');
            flash_message('success', 'Supplier updated successfully.');
            redirect('/suppliers');
        }

        require APP_ROOT . '/app/views/suppliers/edit.php';
    }

    public function delete(int $id): void {
        AuthMiddleware::requireAdmin();
        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $supplier = (new Supplier())->findByIdAndBusiness($id, $businessId);
        if (!$supplier) {
            flash_message('error', 'Supplier not found.');
            redirect('/suppliers');
        }

        if (!validate_csrf()) {
            flash_message('error', 'Invalid request.');
            redirect('/suppliers');
        }

        (new Supplier())->deleteByBusiness($id, $businessId);
        (new ActivityLog())->log($businessId, $userId, 'delete', 'supplier', $id, $_SESSION['user_name'] . ' deleted supplier "' . $supplier['company_name'] . '"');
        flash_message('success', 'Supplier deleted successfully.');
        redirect('/suppliers');
    }
}
