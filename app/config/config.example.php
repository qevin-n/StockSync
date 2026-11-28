<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'stocksync');
define('DB_USER', 'stocksync_user');
define('DB_PASS', 'CHANGE_ME_BEFORE_USE');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'StockSync');
define('APP_URL', 'http://stocksync.local');
define('APP_ROOT', dirname(dirname(__DIR__)));
define('PUBLIC_URL', APP_URL);

define('SESSION_NAME', 'stocksync_session');
define('SESSION_LIFETIME', 7200);

define('CSRF_TOKEN_NAME', 'csrf_token');

define('DEFAULT_CURRENCY', '$');
define('DEFAULT_DATE_FORMAT', 'M d, Y');

define('UPLOAD_MAX_SIZE', 2097152);
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_ALLOWED_EXTS', ['jpg', 'jpeg', 'png', 'webp']);

define('ENVIRONMENT', 'development');

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
