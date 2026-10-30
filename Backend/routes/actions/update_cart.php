<?php
/**
 * update_cart.php — AJAX Endpoint to change product quantity in cart
 */
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$productId = filter_var($input['product_id'] ?? $_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);
$quantity = filter_var($input['quantity'] ?? $_POST['quantity'] ?? 0, FILTER_VALIDATE_INT);

if (!$productId || $productId <= 0) {
    json_response(['success' => false, 'message' => 'Invalid product.'], 400);
}

try {
    // If quantity is zero or less, delete item
    if ($quantity <= 0) {
        if (is_logged_in()) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user']['user_id'], $productId]);
        } else {
            unset($_SESSION['guest_cart'][$productId]);
        }

        json_response([
            'success'   => true,
            'message'   => 'Item removed from cart.',
            'cartCount' => get_cart_count($pdo),
            'removed'   => true
        ]);
    }

    // Verify stock
    $stmt = $pdo->prepare("SELECT price, stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        json_response(['success' => false, 'message' => 'Product not found.'], 404);
    }

    if ($quantity > $product['stock']) {
        json_response([
            'success'   => false, 
            'message'   => 'Requested quantity exceeds available stock (' . $product['stock'] . ').',
            'maxStock'  => (int)$product['stock']
        ], 400);
    }

    if (is_logged_in()) {
        $updateStmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $updateStmt->execute([$quantity, $_SESSION['user']['user_id'], $productId]);
    } else {
        $_SESSION['guest_cart'][$productId] = $quantity;
    }

    $itemTotal = (float)$product['price'] * $quantity;

    json_response([
        'success'        => true,
        'message'        => 'Cart updated successfully.',
        'cartCount'      => get_cart_count($pdo),
        'itemTotal'      => format_price($itemTotal),
        'itemTotalRaw'   => $itemTotal,
        'quantity'       => $quantity
    ]);

} catch (PDOException $e) {
    error_log('Update cart error: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Failed to update cart.'], 500);
}

