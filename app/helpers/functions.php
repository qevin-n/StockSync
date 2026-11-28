<?php

function e(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string {
    return PUBLIC_URL . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string {
    return APP_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void {
    header("Location: " . url($path));
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function csrf_field(): string {
    $token = csrf_token();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . e($token) . '">';
}

function validate_csrf(): bool {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    $sessionToken = $_SESSION[CSRF_TOKEN_NAME] ?? '';
    if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, $token)) {
        return false;
    }
    return true;
}

function format_currency(float $amount): string {
    return DEFAULT_CURRENCY . number_format($amount, 2);
}

function format_date(string $date): string {
    return date(DEFAULT_DATE_FORMAT, strtotime($date));
}

function flash_message(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function get_flash_messages(): array {
    $flash = get_flash();
    return $flash !== null ? [$flash] : [];
}

function generate_sku(): string {
    return 'SKU-' . strtoupper(substr(uniqid(), -6));
}

function generate_invoice_number(): string {
    return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
}

function generate_po_number(): string {
    return 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
}

function generate_reference(): string {
    return 'REF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'n-a';
}

function upload_file(array $file, string $directory = 'images'): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > UPLOAD_MAX_SIZE) return null;

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!in_array($mime, UPLOAD_ALLOWED_TYPES)) return null;

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $ext = strtolower($ext);
    if (!in_array($ext, UPLOAD_ALLOWED_EXTS)) return null;

    $uploadDir = APP_ROOT . '/public/uploads/' . $directory . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $ext;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    return null;
}

function old(string $key, $default = ''): string {
    return e($_SESSION['old_input'][$key] ?? $default);
}

function clear_old_input(): void {
    unset($_SESSION['old_input']);
}

function set_old_input(array $data): void {
    $_SESSION['old_input'] = $data;
}

function get_old_input(): array {
    return $_SESSION['old_input'] ?? [];
}

function paginate(array $items, int $page, int $perPage): array {
    $total = count($items);
    $totalPages = (int) ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    $items = array_slice($items, $offset, $perPage);

    return [
        'items' => $items,
        'page' => $page,
        'perPage' => $perPage,
        'total' => $total,
        'totalPages' => $totalPages,
        'hasPrev' => $page > 1,
        'hasNext' => $page < $totalPages,
    ];
}

function now(): string {
    return date('Y-m-d H:i:s');
}

function today(): string {
    return date('Y-m-d');
}

function dd($var): void {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    exit;
}

function is_ajax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
function flash(string $type): ?string
{
    if (
        isset($_SESSION['flash']) &&
        is_array($_SESSION['flash']) &&
        ($_SESSION['flash']['type'] ?? '') === $type
    ) {
        $message = $_SESSION['flash']['message'] ?? '';
        unset($_SESSION['flash']);

        return $message;
    }

    return null;
}

function has_error(string $field): bool
{
    return !empty($_SESSION['errors'][$field]);
}

function show_error(string $field): string
{
    if (!has_error($field)) {
        return '';
    }

    return '<div class="invalid-feedback d-block">'
        . e((string) $_SESSION['errors'][$field])
        . '</div>';
}


function current_route(string $route): bool
{
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $route = '/' . trim($route, '/');

    if ($route === '/') {
        return $currentPath === '/';
    }

    return $currentPath === $route
        || str_starts_with($currentPath, $route . '/');
}


function is_admin(): bool
{
    $roleName = strtolower(trim((string) ($_SESSION['role_name'] ?? '')));

    if ($roleName !== '') {
        return $roleName === 'admin';
    }

    return (int) ($_SESSION['role_id'] ?? 0) === 1;
}


function is_manager(): bool
{
    $roleName = strtolower(trim((string) ($_SESSION['role_name'] ?? '')));

    if ($roleName !== '') {
        return in_array($roleName, ['admin', 'manager'], true);
    }

    return in_array((int) ($_SESSION['role_id'] ?? 0), [1, 2], true);
}


function pagination(
    int $totalPages,
    int $currentPage,
    int $total,
    callable $urlGenerator
): string {
    if ($totalPages <= 1) {
        return '';
    }

    $currentPage = max(1, min($currentPage, $totalPages));

    $html = '<nav aria-label="Product pages"><ul class="pagination pagination-sm mb-0">';

    $previousDisabled = $currentPage <= 1 ? ' disabled' : '';
    $previousUrl = $currentPage > 1
        ? e($urlGenerator($currentPage - 1))
        : '#';

    $html .= '<li class="page-item' . $previousDisabled . '">';
    $html .= '<a class="page-link" href="' . $previousUrl . '">Previous</a></li>';

    for ($page = 1; $page <= $totalPages; $page++) {
        $active = $page === $currentPage ? ' active' : '';
        $url = e($urlGenerator($page));

        $html .= '<li class="page-item' . $active . '">';
        $html .= '<a class="page-link" href="' . $url . '">' . $page . '</a></li>';
    }

    $nextDisabled = $currentPage >= $totalPages ? ' disabled' : '';
    $nextUrl = $currentPage < $totalPages
        ? e($urlGenerator($currentPage + 1))
        : '#';

    $html .= '<li class="page-item' . $nextDisabled . '">';
    $html .= '<a class="page-link" href="' . $nextUrl . '">Next</a></li>';

    $html .= '</ul></nav>';

    return $html;
}


function time_ago(string $datetime): string
{
    $timestamp = strtotime($datetime);

    if ($timestamp === false) {
        return 'Unknown time';
    }

    $seconds = max(0, time() - $timestamp);

    if ($seconds < 60) {
        return 'Just now';
    }

    if ($seconds < 3600) {
        $minutes = (int) floor($seconds / 60);
        return $minutes . ' minute' . ($minutes === 1 ? '' : 's') . ' ago';
    }

    if ($seconds < 86400) {
        $hours = (int) floor($seconds / 3600);
        return $hours . ' hour' . ($hours === 1 ? '' : 's') . ' ago';
    }

    if ($seconds < 604800) {
        $days = (int) floor($seconds / 86400);
        return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
    }

    if ($seconds < 2592000) {
        $weeks = (int) floor($seconds / 604800);
        return $weeks . ' week' . ($weeks === 1 ? '' : 's') . ' ago';
    }

    if ($seconds < 31536000) {
        $months = (int) floor($seconds / 2592000);
        return $months . ' month' . ($months === 1 ? '' : 's') . ' ago';
    }

    $years = (int) floor($seconds / 31536000);
    return $years . ' year' . ($years === 1 ? '' : 's') . ' ago';
}
