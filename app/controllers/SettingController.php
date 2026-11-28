<?php
namespace App\Controllers;

use App\Models\Business;
use App\Models\ActivityLog;
use App\Middleware\AuthMiddleware;

class SettingController {
    public function index(): void {
        AuthMiddleware::requireAdmin();

        $businessId = (int) $_SESSION['business_id'];
        $userId = (int) $_SESSION['user_id'];

        $businessModel = new Business();
        $business = $businessModel->findById($businessId);

        if (!$business) {
            flash_message('error', 'Business not found.');
            redirect('/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf()) {
                flash_message('error', 'Invalid request.');
                redirect('/settings');
                return;
            }

            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $taxNumber = trim($_POST['tax_number'] ?? '');
            $currency = trim($_POST['currency'] ?? '$');

            $allowedCurrencies = ['$', 'EUR', 'GBP', 'CAD', 'AUD'];

            if ($name === '') {
                flash_message('error', 'Business name is required.');
                redirect('/settings');
                return;
            }

            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash_message('error', 'Please enter a valid email address.');
                redirect('/settings');
                return;
            }

            if (!in_array($currency, $allowedCurrencies, true)) {
                flash_message('error', 'Invalid currency selected.');
                redirect('/settings');
                return;
            }

            $updated = $businessModel->update($businessId, [
                'name' => $name,
                'email' => $email !== '' ? $email : null,
                'phone' => $phone,
                'address' => $address,
                'tax_number' => $taxNumber,
                'currency' => $currency
            ]);

            if (!$updated) {
                flash_message('error', 'Could not update business settings.');
                redirect('/settings');
                return;
            }

            (new ActivityLog())->log(
                $businessId,
                $userId,
                'update',
                'setting',
                $businessId,
                $_SESSION['user_name'] . ' updated business settings.'
            );

            flash_message('success', 'Settings saved successfully.');
            redirect('/settings');
            return;
        }

        require APP_ROOT . '/app/views/settings/index.php';
    }
}
