<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use App\Middleware\AuthMiddleware;

class UserController {
    public function index(): void {
        AuthMiddleware::requireAdmin();
        $businessId = (int) $_SESSION['business_id'];
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $users = (new User())->getAllWithRole(
            $businessId,
            $page,
            20,
            $search,
            $status
        );

        require APP_ROOT . '/app/views/users/index.php';
    }

    public function show(string $id): void {
        $id = (int) $id;
        AuthMiddleware::requireAdmin();

        $businessId = (int) $_SESSION['business_id'];
        $user = (new User())->findByIdAndBusiness($id, $businessId);

        if (!$user) {
            flash_message('error', 'User not found.');
            redirect('/users');
        }

        require APP_ROOT . '/app/views/users/show.php';
    }

    public function create(): void {
        AuthMiddleware::requireAdmin();
        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $roles = (new Role())->findAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf()) {
                flash_message('error', 'Invalid request.');
                redirect('/users/create');
            }

            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $name = trim($firstName . ' ' . $lastName);
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            $roleId = (int) ($_POST['role_id'] ?? 0);

            $validRole = false;
            foreach ($roles as $role) {
                if ((int) $role['id'] === $roleId) {
                    $validRole = true;
                    break;
                }
            }

            if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
                || $password === '' || $roleId <= 0 || !$validRole) {
                flash_message('error', 'Please complete all fields with valid information.');
                redirect('/users/create');
            }

            if (strlen($password) < 6) {
                flash_message('error', 'Password must be at least 6 characters.');
                redirect('/users/create');
            }

            if ($password !== $passwordConfirm) {
                flash_message('error', 'Passwords do not match.');
                redirect('/users/create');
            }

            $userModel = new User();

            if ($userModel->emailExists($email)) {
                flash_message('error', 'Email is already registered.');
                redirect('/users/create');
            }

            $id = $userModel->createUser([
                'business_id' => $businessId,
                'role_id' => $roleId,
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            (new ActivityLog())->log(
                $businessId,
                $userId,
                'create',
                'user',
                $id,
                $_SESSION['user_name'] . ' created user "' . $name . '"'
            );

            flash_message('success', 'User created successfully.');
            redirect('/users');
        }

        require APP_ROOT . '/app/views/users/create.php';
    }

    public function edit(int $id): void {
        AuthMiddleware::requireAdmin();
        $businessId = (int) $_SESSION['business_id'];
        $currentUserId = (int) $_SESSION['user_id'];

        $userModel = new User();
        $user = $userModel->findByIdAndBusiness($id, $businessId);

        if (!$user) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }

        $roles = (new Role())->findAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf()) {
                flash_message('error', 'Invalid request.');
                redirect('/users/' . $id . '/edit');
            }

            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $name = trim($firstName . ' ' . $lastName);
            $email = trim($_POST['email'] ?? '');
            $roleId = (int) ($_POST['role_id'] ?? 0);
            $status = $_POST['status'] ?? 'active';
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            $validRole = false;
            foreach ($roles as $role) {
                if ((int) $role['id'] === $roleId) {
                    $validRole = true;
                    break;
                }
            }

            if ($firstName === '' || $lastName === ''
                || !filter_var($email, FILTER_VALIDATE_EMAIL)
                || $roleId <= 0 || !$validRole
                || !in_array($status, ['active', 'inactive'], true)) {
                flash_message('error', 'Please complete all fields with valid information.');
                redirect('/users/' . $id . '/edit');
            }

            if ($userModel->emailExists($email, $id)) {
                flash_message('error', 'That email address is already in use.');
                redirect('/users/' . $id . '/edit');
            }

            $update = [
                'name' => $name,
                'email' => $email,
                'role_id' => $roleId,
                'is_active' => $status === 'active' ? 1 : 0,
                'updated_at' => now(),
            ];

            if ($password !== '') {
                if (strlen($password) < 6 || $password !== $passwordConfirm) {
                    flash_message('error', 'Check the new password and confirmation. Passwords must match and contain at least 6 characters.');
                    redirect('/users/' . $id . '/edit');
                }

                $update['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $userModel->updateByBusiness($id, $businessId, $update);

            if ($id === $currentUserId) {
                $_SESSION['user_name'] = $name;
            }

            (new ActivityLog())->log(
                $businessId,
                $currentUserId,
                'update',
                'user',
                $id,
                $_SESSION['user_name'] . ' updated user "' . $name . '"'
            );

            flash_message('success', 'User updated successfully.');
            redirect('/users');
        }

        require APP_ROOT . '/app/views/users/edit.php';
    }

    public function delete(int $id): void {
        AuthMiddleware::requireAdmin();
        $businessId = (int) $_SESSION['business_id'];
        $currentUserId = (int) $_SESSION['user_id'];

        if ($id === $currentUserId) {
            flash_message('error', 'You cannot delete yourself.');
            redirect('/users');
        }

        $user = (new User())->findByIdAndBusiness($id, $businessId);
        if (!$user) {
            flash_message('error', 'User not found.');
            redirect('/users');
        }

        if (!validate_csrf()) {
            flash_message('error', 'Invalid request.');
            redirect('/users');
        }

        (new User())->deleteByBusiness($id, $businessId);
        (new ActivityLog())->log($businessId, $currentUserId, 'delete', 'user', $id, $_SESSION['user_name'] . ' deleted user "' . $user['name'] . '"');
        flash_message('success', 'User deleted successfully.');
        redirect('/users');
    }

    public function profile(): void {
        AuthMiddleware::requireAuth();

        $businessId = (int) $_SESSION['business_id'];
        $currentUserId = (int) $_SESSION['user_id'];
        $userModel = new User();

        $user = $userModel->findByIdAndBusiness($currentUserId, $businessId);

        if (!$user) {
            redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf()) {
                flash_message('error', 'Invalid request.');
                redirect('/profile');
            }

            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash_message('error', 'Enter a valid name and email address.');
                redirect('/profile');
            }

            if ($userModel->emailExists($email, $currentUserId)) {
                flash_message('error', 'That email address is already in use.');
                redirect('/profile');
            }

            $update = [
                'name' => $name,
                'email' => $email,
                'updated_at' => now(),
            ];

            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['password_confirm'] ?? '';
            $currentPassword = $_POST['current_password'] ?? '';

            if ($newPassword !== '' || $confirmPassword !== '' || $currentPassword !== '') {
                if ($currentPassword === '' || !password_verify($currentPassword, $user['password'])) {
                    flash_message('error', 'Your current password is incorrect.');
                    redirect('/profile');
                }

                if (strlen($newPassword) < 6 || $newPassword !== $confirmPassword) {
                    flash_message('error', 'The new password must be at least 6 characters and both password fields must match.');
                    redirect('/profile');
                }

                $update['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            $userModel->updateByBusiness($currentUserId, $businessId, $update);
            $_SESSION['user_name'] = $name;

            flash_message('success', 'Profile updated successfully.');
            redirect('/profile');
        }

        require APP_ROOT . '/app/views/users/profile.php';
    }

}
