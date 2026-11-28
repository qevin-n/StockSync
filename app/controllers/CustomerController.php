<?php
namespace App\Controllers;

use App\Models\Customer;
use App\Models\ActivityLog;
use App\Middleware\AuthMiddleware;

class CustomerController {
    public function index(): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');

        $customers = (new Customer())->getAllWithStats($businessId, $page, 20, $search);
        require APP_ROOT . '/app/views/customers/index.php';
    }

    public function create(): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf()) {
                flash_message('error', 'Invalid request.');
                redirect('/customers');
            }

            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                flash_message('error', 'Customer name is required.');
                redirect('/customers');
            }

            $id = (new Customer())->create([
                'business_id' => $businessId,
                'name' => $name,
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?: null,
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'country' => trim($_POST['country'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            (new ActivityLog())->log($businessId, $userId, 'create', 'customer', $id, $_SESSION['user_name'] . ' created customer "' . $name . '"');
            flash_message('success', 'Customer created successfully.');
            redirect('/customers');
        }

        require APP_ROOT . '/app/views/customers/create.php';
    }

    public function show(int $id): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];

        $customer = (new Customer())->findByIdAndBusiness($id, $businessId);
        if (!$customer) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }

        $orderHistory = (new Customer())->getOrderHistory($id, $businessId);
        require APP_ROOT . '/app/views/customers/show.php';
    }

    public function edit(int $id): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $customer = (new Customer())->findByIdAndBusiness($id, $businessId);
        if (!$customer) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf()) {
                flash_message('error', 'Invalid request.');
                redirect('/customers/' . $id . '/edit');
            }

            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                flash_message('error', 'Customer name is required.');
                redirect('/customers/' . $id . '/edit');
            }

            (new Customer())->updateByBusiness($id, $businessId, [
                'name' => $name,
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?: null,
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'country' => trim($_POST['country'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
                'updated_at' => now(),
            ]);

            (new ActivityLog())->log($businessId, $userId, 'update', 'customer', $id, $_SESSION['user_name'] . ' updated customer "' . $name . '"');
            flash_message('success', 'Customer updated successfully.');
            redirect('/customers');
        }

        require APP_ROOT . '/app/views/customers/edit.php';
    }

    public function delete(int $id): void {
        AuthMiddleware::requireAdmin();
        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $customer = (new Customer())->findByIdAndBusiness($id, $businessId);
        if (!$customer) {
            flash_message('error', 'Customer not found.');
            redirect('/customers');
        }

        if (!validate_csrf()) {
            flash_message('error', 'Invalid request.');
            redirect('/customers');
        }

        (new Customer())->deleteByBusiness($id, $businessId);
        (new ActivityLog())->log($businessId, $userId, 'delete', 'customer', $id, $_SESSION['user_name'] . ' deleted customer "' . $customer['name'] . '"');
        flash_message('success', 'Customer deleted successfully.');
        redirect('/customers');
    }
}
