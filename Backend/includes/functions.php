<?php
/**
 * functions.php — Helper Utility Functions
 * Security, sanitization, flash messaging, CSRF protection, cart stats & formatting.
 */

/**
 * XSS Sanitization helper
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Safe HTTP Redirect
 */
function redirect(string $url): void {
    header("Location: " . $url);
    exit;
}

/**
 * Flash messaging system
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'][$type] = $message;
}

function has_flash(string $type): bool {
    return isset($_SESSION['flash'][$type]);
}

function get_flash(string $type): ?string {
    if (isset($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}

/**
 * CSRF Protection
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token = null): bool {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * Price & Currency Formatter
 */
function format_price(float|int|string $amount): string {
    return CURRENCY . ' ' . number_format((float)$amount, 2);
}

/**
 * Authentication state helpers
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user']) && !empty($_SESSION['user']['user_id']);
}

function is_admin(): bool {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Cart Counter helper
 */
function get_cart_count(PDO $pdo): int {
    if (is_logged_in()) {
        $userId = $_SESSION['user']['user_id'];
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) AS total_count FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return (int)($row['total_count'] ?? 0);
    } else {
        // Guest cart stored in session
        $count = 0;
        if (!empty($_SESSION['guest_cart']) && is_array($_SESSION['guest_cart'])) {
            foreach ($_SESSION['guest_cart'] as $qty) {
                $count += (int)$qty;
            }
        }
        return $count;
    }
}

/**
 * JSON Response helper for AJAX
 */
function json_response(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Asset URL generator
 */
function asset_url(string $path): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Product Image Resolver
 */
function product_image_url(?string $filename): string {
    if (empty($filename)) {
        return asset_url('frontend/images/no-image.png');
    }
    // Check if file exists in uploads
    if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename)) {
        return asset_url('uploads/' . rawurlencode($filename));
    }
    return asset_url('uploads/' . rawurlencode($filename));
}

