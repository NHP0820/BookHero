<?php
require '_base.php';

header('Content-Type: application/json');

if (!is_post()) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user']['id'];

$product_id = post('product_id');
$quantity = post('quantity', 1);

if (!$product_id || !is_numeric($product_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
    exit;
}

if (!is_numeric($quantity) || $quantity < 1) {
    $quantity = 1;
}

$stmtProduct = $_db->prepare("SELECT * FROM product WHERE product_id = ?");
$stmtProduct->execute([$product_id]);
$product = $stmtProduct->fetch(PDO::FETCH_OBJ);

if (!$product) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    exit;
}

if ($product->stock_quantity < $quantity) {
    echo json_encode(['status' => 'error', 'message' => 'Not enough inventory. Only ' . $product->stock_quantity . ' books available.']);
    exit;
}

$stmtCart = $_db->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
$stmtCart->execute([$user_id]);
$cart = $stmtCart->fetch(PDO::FETCH_OBJ);

if (!$cart) {
    $stmtCreateCart = $_db->prepare("INSERT INTO cart (user_id, created_at) VALUES (?, NOW())");
    $stmtCreateCart->execute([$user_id]);
    $cart_id = $_db->lastInsertId();
} else {
    $cart_id = $cart->cart_id;
}

$stmtCheckItem = $_db->prepare("SELECT * FROM cart_item WHERE cart_id = ? AND product_id = ?");
$stmtCheckItem->execute([$cart_id, $product_id]);
$existingItem = $stmtCheckItem->fetch(PDO::FETCH_OBJ);

if ($existingItem) {
    $newQuantity = $existingItem->quantity + $quantity;
    
    if ($product->stock_quantity < $newQuantity) {
        echo json_encode(['status' => 'error', 'message' => 'Cannot add more. Only ' . $product->stock_quantity . ' books available in total.']);
        exit;
    }
    
    $stmtUpdateItem = $_db->prepare("UPDATE cart_item SET quantity = ? WHERE cart_item_id = ?");
    $stmtUpdateItem->execute([$newQuantity, $existingItem->cart_item_id]);
} else {
    $stmtAddItem = $_db->prepare("INSERT INTO cart_item (cart_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmtAddItem->execute([$cart_id, $product_id, $quantity]);
}

$stmtCount = $_db->prepare("SELECT COUNT(*) FROM cart_item WHERE cart_id = ?");
$stmtCount->execute([$cart_id]);
$cart_count = $stmtCount->fetchColumn();

echo json_encode([
    'status' => 'success',
    'message' => 'Product added to cart',
    'cart_count' => $cart_count
]);
exit;
?>