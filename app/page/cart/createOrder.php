<?php
require '../../_base.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['user']['id'];

// Get user cart
$stmt = $_db->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$userId]);
$cart = $stmt->fetch(PDO::FETCH_OBJ);

if (!$cart) {
    echo json_encode(['status' => 'error', 'message' => 'Cart not found']);
    exit();
}

// Get cart items
$cartStmt = $_db->prepare(
    "SELECT ci.*, p.price 
     FROM cart_item ci
     INNER JOIN product p ON ci.product_id = p.product_id
     WHERE ci.cart_id = ?"
);
$cartStmt->execute([$cart->cart_id]);
$cartItems = $cartStmt->fetchAll(PDO::FETCH_OBJ);

if (empty($cartItems)) {
    echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
    exit();
}

// Calculate total
$total = 4.99; // Shipping
foreach ($cartItems as $item) {
    $total += $item->price * $item->quantity;
}

// Get default address
$addressStmt = $_db->prepare("SELECT * FROM address WHERE user_id = ? ORDER BY address_id ASC LIMIT 1");
$addressStmt->execute([$userId]);
$address = $addressStmt->fetch(PDO::FETCH_OBJ);

$addressId = $address ? $address->address_id : null;

try {
    $_db->beginTransaction();

    // Insert into order
    $orderStmt = $_db->prepare(
        "INSERT INTO `order` (user_id, order_date, total_amount, status_id, address_id, expired_time)
         VALUES (?, NOW(), ?, 1, ?, DATE_ADD(NOW(), INTERVAL 6 HOUR))"
    );
    $orderStmt->execute([$userId, $total, $addressId]);
    $orderId = $_db->lastInsertId();

    // Insert each cart item into order_detail
    $orderDetailStmt = $_db->prepare(
        "INSERT INTO order_detail (order_id, product_id, quantity, price_at_purchase, voucher_id)
         VALUES (?, ?, ?, ?, NULL)"
    );

    foreach ($cartItems as $item) {
        $orderDetailStmt->execute([
            $orderId,
            $item->product_id,
            $item->quantity,
            $item->price
        ]);
    }

    // Clear cart
    $clearCartStmt = $_db->prepare("DELETE FROM cart_item WHERE cart_id = ?");
    $clearCartStmt->execute([$cart->cart_id]);

    $_db->commit();

    temp('info', 'You have successfully create order');
    echo json_encode(['status' => 'success', 'order_id' => $orderId]);
    exit();
} catch (Exception $e) {
    $_db->rollBack();
    temp('info', 'Your order fail to be create');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit();
}
?>
