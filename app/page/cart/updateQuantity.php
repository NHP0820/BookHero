<?php
require '../../_base.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $userId = $_SESSION['user']['id'] ?? null;

    if ($productId && $quantity && $userId) {
        $productStmt = $_db->prepare("SELECT stock_quantity FROM product WHERE product_id = ?");
        $productStmt->execute([$productId]);
        $product = $productStmt->fetch(PDO::FETCH_OBJ);

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }

        if ($quantity > $product->stock_quantity) {
            echo json_encode(['success' => false, 'message' => 'Quantity exceeds available stock. Only ' . $product->stock_quantity . ' books available.']);
            exit;
        }
        
        $stmt = $_db->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $cart = $stmt->fetch(PDO::FETCH_OBJ);

        if ($cart) {
            $updateStmt = $_db->prepare("UPDATE cart_item SET quantity = ? WHERE cart_id = ? AND product_id = ?");
            $updateStmt->execute([$quantity, $cart->cart_id, $productId]);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cart not found.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing data.']);
    }
}