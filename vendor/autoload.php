<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $parts = explode('\\', $relative);

    if (!$parts || $parts[0] === '') {
        return;
    }

    $parts[0] = strtolower($parts[0]);
    $file = dirname(__DIR__) . '/app/' . implode('/', $parts) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});
