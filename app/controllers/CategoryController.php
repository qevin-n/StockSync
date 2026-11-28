<?php
namespace App\Controllers;

use App\Models\Category;
use App\Models\ActivityLog;
use App\Middleware\AuthMiddleware;

class CategoryController {
    public function index(): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];
        $categories = (new Category())->getAllByBusiness($businessId);
        require APP_ROOT . '/app/views/categories/index.php';
    }

    public function create(): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf()) {
                flash_message('error', 'Invalid request.');
                redirect('/categories');
            }

            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                flash_message('error', 'Category name is required.');
                redirect('/categories');
            }

            $existing = (new Category())->findByNameAndBusiness($name, $businessId);
            if ($existing) {
                flash_message('error', 'Category name already exists.');
                redirect('/categories');
            }

            $id = (new Category())->create([
                'business_id' => $businessId,
                'name' => $name,
                'description' => trim($_POST['description'] ?? ''),
                'status' => $_POST['status'] ?? 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            (new ActivityLog())->log($businessId, $userId, 'create', 'category', $id, $_SESSION['user_name'] . ' created category "' . $name . '"');
            flash_message('success', 'Category created successfully.');
            redirect('/categories');
        }
    }

    public function update(int $id): void {
        AuthMiddleware::requireManager();
        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $category = (new Category())->findByIdAndBusiness($id, $businessId);
        if (!$category) {
            flash_message('error', 'Category not found.');
            redirect('/categories');
        }

        if (!validate_csrf()) {
            flash_message('error', 'Invalid request.');
            redirect('/categories');
        }

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            flash_message('error', 'Category name is required.');
            redirect('/categories');
        }

        (new Category())->updateByBusiness($id, $businessId, [
            'name' => $name,
            'description' => trim($_POST['description'] ?? ''),
            'status' => $_POST['status'] ?? 'active',
            'updated_at' => now(),
        ]);

        (new ActivityLog())->log($businessId, $userId, 'update', 'category', $id, $_SESSION['user_name'] . ' updated category "' . $name . '"');
        flash_message('success', 'Category updated successfully.');
        redirect('/categories');
    }

    public function delete(int $id): void {
        AuthMiddleware::requireAdmin();
        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $category = (new Category())->findByIdAndBusiness($id, $businessId);
        if (!$category) {
            flash_message('error', 'Category not found.');
            redirect('/categories');
        }

        if (!validate_csrf()) {
            flash_message('error', 'Invalid request.');
            redirect('/categories');
        }

        (new Category())->deleteByBusiness($id, $businessId);
        (new ActivityLog())->log($businessId, $userId, 'delete', 'category', $id, $_SESSION['user_name'] . ' deleted category "' . $category['name'] . '"');
        flash_message('success', 'Category deleted successfully.');
        redirect('/categories');
    }
}
