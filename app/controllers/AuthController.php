<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Business;
use App\Models\Role;
use App\Config\Database;
use Throwable;

class AuthController
{
    private User $userModel;
    private Business $businessModel;
    private Role $roleModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->businessModel = new Business();
        $this->roleModel = new Role();
    }

    /**
     * Redirect to a URL.
     */
    private function go(string $path): void
    {
        header('Location: ' . APP_URL . $path);
        exit;
    }

    /**
     * Store validation errors and old input.
     */
    private function storeErrors(array $errors, array $input = []): void
    {
        $_SESSION['errors'] = $errors;

        unset($input['password'], $input['confirm_password']);

        $_SESSION['old_input'] = $input;
    }

    /**
     * Clear validation errors and old input.
     */
    private function clearFormData(): void
    {
        unset($_SESSION['errors'], $_SESSION['old_input']);
    }

    /**
     * LOGIN
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $pageTitle = 'Login';

            require APP_ROOT . '/app/views/auth/login.php';
            return;
        }

        if (!validate_csrf()) {
            flash_message('error', 'Invalid form submission. Please try again.');
            $this->go('/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        if ($errors) {
            $this->storeErrors($errors, ['email' => $email]);
            $this->go('/login');
        }

        try {
            $user = $this->userModel->verifyPassword($email, $password);

            if (
                !$user ||
                (isset($user['is_active']) && (int) $user['is_active'] !== 1)
            ) {
                flash_message('error', 'Invalid email or password.');
                $this->go('/login');
            }

            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['business_id'] = (int) $user['business_id'];
            $_SESSION['role_id'] = (int) $user['role_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            if (isset($user['role_name'])) {
                $_SESSION['role_name'] = $user['role_name'];
            }

            if (isset($user['role'])) {
                $_SESSION['role_name'] = $user['role'];
            }

            $_SESSION['last_activity'] = time();

            $this->clearFormData();

            flash_message('success', 'Welcome back!');

            $this->go('/dashboard');

        } catch (Throwable $e) {
            error_log('Login error: ' . $e->getMessage());

            flash_message('error', 'Unable to log in. Please try again.');
            $this->go('/login');
        }
    }

    /**
     * REGISTER
     */
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $pageTitle = 'Create Account';

            require APP_ROOT . '/app/views/auth/register.php';
            return;
        }

        if (!validate_csrf()) {
            flash_message('error', 'Invalid form submission. Please try again.');
            $this->go('/register');
        }

        $name = trim($_POST['name'] ?? '');
        $businessName = trim($_POST['business_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Your name is required.';
        }

        if ($businessName === '') {
            $errors['business_name'] = 'Business name is required.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if (strlen($password) < 6) {
            $errors['password'] = 'Password must contain at least 6 characters.';
        }

        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        if ($email !== '' && $this->userModel->findByEmail($email)) {
            $errors['email'] = 'An account with this email already exists.';
        }

        if ($errors) {
            $this->storeErrors($errors, [
                'name' => $name,
                'business_name' => $businessName,
                'email' => $email,
                'phone' => $phone
            ]);

            $this->go('/register');
        }

        try {
            /*
             * Find the administrator role.
             * Your roles table stores this as lowercase "admin".
             */
            $role = $this->roleModel->findByName('admin');

            if (!$role) {
                throw new \RuntimeException(
                    'The admin role was not found in the roles table.'
                );
            }

            /*
             * Generate a unique business slug.
             */
            $baseSlug = slugify($businessName);
            $slug = $baseSlug;
            $counter = 1;

            while ($this->businessModel->findBySlug($slug)) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            /*
             * Create the business.
             * The businesses table supports phone, currency and tax_rate.
             */
            $businessId = $this->businessModel->create([
                'name' => $businessName,
                'slug' => $slug,
                'email' => $email,
                'phone' => $phone,
                'currency' => '$',
                'tax_rate' => 0.00
            ]);

            if (!$businessId) {
                throw new \RuntimeException('Business creation failed.');
            }

            /*
             * Create the user.
             *
             * IMPORTANT:
             * The users table does not contain phone, avatar or status.
             * It uses is_active instead.
             */
            $userId = $this->userModel->createUser([
                'business_id' => $businessId,
                'role_id' => $role['id'],
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'is_active' => 1
            ]);

            if (!$userId) {
                throw new \RuntimeException('User creation failed.');
            }

            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $userId;
            $_SESSION['business_id'] = (int) $businessId;
            $_SESSION['role_id'] = (int) $role['id'];
            $_SESSION['role_name'] = 'admin';
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['last_activity'] = time();

            $this->clearFormData();

            flash_message('success', 'Your StockSync account has been created.');

            $this->go('/dashboard');

        } catch (Throwable $e) {
            error_log('Registration error: ' . $e->getMessage());

            flash_message(
                'error',
                'Registration could not be completed. Please try again.'
            );

            $this->go('/register');
        }
    }

    /**
     * FORGOT PASSWORD
     */
    public function forgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require APP_ROOT . '/app/views/auth/forgot-password.php';
            return;
        }

        if (!validate_csrf()) {
            flash_message('error', 'Invalid form submission. Please try again.');
            $this->go('/forgot-password');
        }

        $email = strtolower(trim($_POST['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['errors'] = [
                'email' => 'Enter a valid email address.'
            ];

            $this->go('/forgot-password');
        }

        try {
            $user = $this->userModel->findByEmail($email);

            /*
             * Do not reveal whether an email exists.
             */
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $token);

                $db = Database::getConnection();

                $stmt = $db->prepare(
                    'INSERT INTO password_resets
                    (email, token, expires_at)
                    VALUES (:email, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
                );

                $stmt->execute([
                    'email' => $email,
                    'token' => $tokenHash
                ]);

                /*
                 * Development only:
                 * Display the reset URL instead of sending email.
                 * Configure email delivery before production.
                 */
                if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                    $resetUrl = APP_URL . '/reset-password?token=' . urlencode($token);

                    flash_message(
                        'success',
                        'Development reset link: ' . $resetUrl
                    );
                } else {
                    flash_message(
                        'success',
                        'If the email exists, password reset instructions will be sent.'
                    );
                }
            } else {
                flash_message(
                    'success',
                    'If the email exists, password reset instructions will be sent.'
                );
            }

            $this->go('/forgot-password');

        } catch (Throwable $e) {
            error_log('Forgot password error: ' . $e->getMessage());

            flash_message('error', 'Unable to process your request right now.');
            $this->go('/forgot-password');
        }
    }

    /**
     * RESET PASSWORD
     */
    public function resetPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $token = $_GET['token'] ?? '';

            if ($token === '') {
                flash_message('error', 'Invalid or missing reset token.');
                $this->go('/forgot-password');
            }

            require APP_ROOT . '/app/views/auth/reset-password.php';
            return;
        }

        if (!validate_csrf()) {
            flash_message('error', 'Invalid form submission. Please try again.');
            $this->go('/forgot-password');
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['password_confirm'] ?? '';

        $errors = [];

        if ($token === '') {
            $errors['password'] = 'Invalid reset token.';
        }

        if (strlen($password) < 6) {
            $errors['password'] = 'Password must contain at least 6 characters.';
        }

        if ($password !== $confirmPassword) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;
            $this->go('/reset-password?token=' . urlencode($token));
        }

        try {
            $db = Database::getConnection();

            $tokenHash = hash('sha256', $token);

            $stmt = $db->prepare(
                'SELECT * FROM password_resets
                 WHERE token = :token
                 AND expires_at > NOW()
                 ORDER BY id DESC
                 LIMIT 1'
            );

            $stmt->execute(['token' => $tokenHash]);

            $reset = $stmt->fetch();

            if (!$reset) {
                flash_message(
                    'error',
                    'This password reset link is invalid or has expired.'
                );

                $this->go('/forgot-password');
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $update = $db->prepare(
                'UPDATE users
                 SET password = :password
                 WHERE email = :email'
            );

            $update->execute([
                'password' => $hashedPassword,
                'email' => $reset['email']
            ]);

            $delete = $db->prepare(
                'DELETE FROM password_resets WHERE email = :email'
            );

            $delete->execute(['email' => $reset['email']]);

            flash_message(
                'success',
                'Your password has been updated. You can now log in.'
            );

            $this->go('/login');

        } catch (Throwable $e) {
            error_log('Reset password error: ' . $e->getMessage());

            flash_message('error', 'Unable to reset your password.');
            $this->go('/forgot-password');
        }
    }

    /**
     * LOGOUT
     */
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: ' . APP_URL . '/login');
        exit;
    }
}
