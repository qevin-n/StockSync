<?php

namespace App\Middleware;

use App\Models\User;

class AuthMiddleware
{
    public static function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            flash_message('warning', 'Please log in to access this page.');
            redirect('/login');
        }

        if (
            !empty($_SESSION['last_activity']) &&
            (time() - (int) $_SESSION['last_activity'] > SESSION_LIFETIME)
        ) {
            session_destroy();
            flash_message('warning', 'Your session has expired. Please log in again.');
            redirect('/login');
        }

        $_SESSION['last_activity'] = time();
    }

    public static function requireRole(array $roles): void
    {
        self::requireAuth();

        // Support both session keys used by previous authentication code.
        $userRole = strtolower(trim((string) (
            $_SESSION['role_name'] ?? $_SESSION['user_role'] ?? ''
        )));

        // Fall back to the role ID if no role name is stored.
        if ($userRole === '') {
            $roleId = (int) ($_SESSION['role_id'] ?? 0);

            $userRole = match ($roleId) {
                1 => 'admin',
                2 => 'manager',
                3 => 'staff',
                default => '',
            };
        }

        $allowedRoles = array_map(
            fn($role) => strtolower(trim((string) $role)),
            $roles
        );

        if (!in_array($userRole, $allowedRoles, true)) {
            http_response_code(403);
            require APP_ROOT . '/app/views/errors/403.php';
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireRole(['admin']);
    }

    public static function requireManager(): void
    {
        self::requireRole(['admin', 'manager']);
    }

    public static function requireStaff(): void
    {
        self::requireRole(['admin', 'manager', 'staff']);
    }

    public static function guestOnly(): void
    {
        if (!empty($_SESSION['user_id'])) {
            redirect('/dashboard');
        }
    }

    public static function getCurrentUser(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        $user = new User();

        return $user->findById((int) $_SESSION['user_id']);
    }
}
