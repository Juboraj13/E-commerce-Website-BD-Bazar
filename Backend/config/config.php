<?php
/**
 * config.php — Core Configuration & Global Bootstrap
 * Initializes sessions, global constants, URL routing paths and error handling.
 */

// 1. Hardened Session Settings
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// 2. Error Reporting
define('DEBUG_MODE', true);
if (DEBUG_MODE) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// 3. Site Constants
define('SITE_NAME', 'BD Bazar');
define('SITE_TAGLINE', 'Online Shopping Platform in Bangladesh');
define('SITE_PHONE', '+880 1711-000000');
define('SITE_EMAIL', 'support@bdbazar.com');
define('SITE_ADDRESS', 'Banani C/A, Dhaka-1213, Bangladesh');
define('CURRENCY', '৳');
define('PER_PAGE', 8);
define('SHIPPING_FEE', 80.00);
define('FREE_SHIPPING_OVER', 3000.00);

// 4. Filesystem Paths
define('ROOT_PATH', realpath(__DIR__ . '/../..'));
define('UPLOAD_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2 Megabytes

// 5. Automatic Dynamic BASE_URL detection
$docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
$base = '';
if ($docRoot) {
    $r = str_replace('\\', '/', ROOT_PATH);
    $d = str_replace('\\', '/', $docRoot);
    if (strpos($r, $d) === 0) {
        $rel = substr($r, strlen($d));
        // Encode each segment of the path so spaces become %20
        $segments = array_filter(explode('/', $rel));
        $encodedSegments = array_map('rawurlencode', $segments);
        $base = '/' . implode('/', $encodedSegments);
        $base = rtrim($base, '/');
    }
}
define('BASE_URL', $base);

// 6. Include Core Dependencies
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

