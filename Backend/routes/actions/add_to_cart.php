<?php
/**
 * add_to_cart.php — AJAX Endpoint to add item to cart
 */
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}

// Support both JSON payload and standard POST
$input = json_decode(file_get_contents('php://input'), true);
$productId = filter_var($input['product_id'] ?? $_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);
$quantity = filter_var($input['quantity'] ?? $_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);

if (!$productId || $productId <= 0) {
    json_response(['success' => false, 'message' => 'Invalid product specified.'], 400);
}

if (!$quantity || $quantity <= 0) {
    $quantity = 1;
}

try {
    // Check product existence and available stock
    $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        json_response(['success' => false, 'message' => 'Product not found in catalog.'], 404);
    }

    if ($product['stock'] <= 0) {
        json_response(['success' => false, 'message' => 'Sorry, this product is currently out of stock.'], 400);
    }

    if (is_logged_in()) {
        $userId = $_SESSION['user']['user_id'];

        // Check existing cart quantity
        $checkStmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $checkStmt->execute([$userId, $productId]);
        $existing = $checkStmt->fetch();

        $currentInCart = $existing ? (int)$existing['quantity'] : 0;
        $newTotal = $currentInCart + $quantity;

        if ($newTotal > $product['stock']) {
            json_response([
                'success' => false, 
                'message' => 'Cannot add more. You have ' . $currentInCart . ' in cart and only ' . $product['stock'] . ' available in stock.'
            ], 400);
        }

        // Insert or update cart
        $cartStmt = $pdo->prepare("
            INSERT INTO cart (user_id, product_id, quantity) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE quantity = quantity + ?
        ");
        $cartStmt->execute([$userId, $productId, $quantity, $quantity]);

    } else {
        // Guest user: manage in session
        if (!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }

        $currentInCart = $_SESSION['guest_cart'][$productId] ?? 0;
        $newTotal = $currentInCart + $quantity;

        if ($newTotal > $product['stock']) {
            json_response([
                'success' => false, 
                'message' => 'Cannot add more. You have ' . $currentInCart . ' in cart and only ' . $product['stock'] . ' available in stock.'
            ], 400);
        }

        $_SESSION['guest_cart'][$productId] = $newTotal;
    }

    $cartCount = get_cart_count($pdo);

    json_response([
        'success'   => true,
        'message'   => '"' . $product['name'] . '" added to your cart!',
        'cartCount' => $cartCount
    ]);

} catch (PDOException $e) {
    error_log('Add to cart error: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Database error occurred. Please try again.'], 500);
}

