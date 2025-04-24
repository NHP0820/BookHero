<?php
require '../../_base.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? null;
    $userId = $_SESSION['user']['id'] ?? null;

    if ($productId && $userId) {
        $stmt = $_db->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart) {
            $cartId = $cart['cart_id'];

            $deleteStmt = $_db->prepare("DELETE FROM cart_item WHERE cart_id = ? AND product_id = ?");
            $success = $deleteStmt->execute([$cartId, $productId]);

            echo json_encode(['success' => $success]);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
