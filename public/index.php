<?php
declare(strict_types=1);

ob_start();
ini_set('pcre.jit', '0');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/config.php';
require_once APP_ROOT . '/app/config/database.php';
require_once APP_ROOT . '/app/helpers/functions.php';

session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => false,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
]);

// Session timeout after 2 hours of inactivity
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_destroy();
    header('Location: /login');
    exit;
}
$_SESSION['last_activity'] = time();

// Auto-login via remember me cookie
if (empty($_SESSION['user_id']) && !empty($_COOKIE['remember_me'])) {
    $parts = explode(':', base64_decode($_COOKIE['remember_me']), 2);
    if (count($parts) === 2) {
        $userId = (int) $parts[0];
        $hash = $parts[1];
        $userModel = new \App\Models\User();
        $user = $userModel->findById($userId);
        if ($user && hash_equals(hash('sha256', $user['password']), $hash) && (int) $user['is_active'] === 1) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['business_id'] = $user['business_id'];
            $_SESSION['user_role'] = $user['role_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['last_activity'] = time();
        }
    }
}

// Simple router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$uri = rtrim($uri, '/') ?: '/';

// Strip base path if not at root
$basePath = '';
$uri = substr($uri, strlen($basePath)) ?: '/';

// Register routes
$routes = require APP_ROOT . '/routes/web.php';

// Match route
$handler = null;
$params = [];
foreach ($routes as [$methods, $pattern, $handler]) {
    $allowedMethods = explode('|', (string) $methods);
    if (!in_array($method, $allowedMethods, true) && !in_array('ANY', $allowedMethods, true)) continue;
    if ($pattern === $uri) {
        $handler = $handler;
        break;
    }
    // Parameterized routes
    $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern);
    $regex = '#^' . $regex . '$#';
    if (preg_match($regex, $uri, $matches)) {
        $handler = $handler;
        foreach ($matches as $key => $value) {
            if (is_string($key)) $params[$key] = $value;
        }
        break;
    }
}

if (!$handler) {
    http_response_code(404);
    require APP_ROOT . '/app/views/errors/404.php';
    exit;
}

try {
    [$controllerClass, $action] = $handler;
    $controller = new $controllerClass();
    if (!empty($params)) {
        $firstParam = array_values($params)[0];
        $controller->$action(is_numeric($firstParam) ? (int) $firstParam : $firstParam);
    } else {
        $controller->$action();
    }
} catch (\Throwable $e) {
    error_log("StockSync exception: " . get_class($e) . " - " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);

    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo '<pre>';
        echo htmlspecialchars(
            get_class($e) . ': ' . $e->getMessage() .
            "\nFile: " . $e->getFile() .
            "\nLine: " . $e->getLine(),
            ENT_QUOTES,
            'UTF-8'
        );
        echo '</pre>';
    } else {
        require APP_ROOT . '/app/views/errors/500.php';
    }
}
