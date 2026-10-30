<?php
/**
 * remove_from_cart.php — AJAX Endpoint to remove an item completely from cart
 */
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$productId = filter_var($input['product_id'] ?? $_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$productId || $productId <= 0) {
    json_response(['success' => false, 'message' => 'Invalid product specified.'], 400);
}

try {
    if (is_logged_in()) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user']['user_id'], $productId]);
    } else {
        if (isset($_SESSION['guest_cart'][$productId])) {
            unset($_SESSION['guest_cart'][$productId]);
        }
    }

    json_response([
        'success'   => true,
        'message'   => 'Item removed from your cart.',
        'cartCount' => get_cart_count($pdo)
    ]);

} catch (PDOException $e) {
    error_log('Remove from cart error: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to remove item.'], 500);
}

