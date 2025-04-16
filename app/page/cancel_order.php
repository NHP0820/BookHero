<?php
require '../_base.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'];
    $reason = $_POST['reason'];

    try {
        $_db->beginTransaction(); 

        // cancel order
        $cancelOrder = $_db->prepare("UPDATE `order` SET status_id = 4, cancel_desc = ? WHERE order_id = ?");
        $cancelOrder->execute([$reason, $orderId]);

        if ($cancelOrder->rowCount() === 0) {
            throw new Exception("Order not found or already cancelled.");
        }

        // find all the order items
        $getItems = $_db->prepare("SELECT product_id, quantity FROM order_detail WHERE order_id = ?");
        $getItems->execute([$orderId]);
        $items = $getItems->fetchAll(PDO::FETCH_ASSOC);

        // add back stock
        foreach ($items as $item) {
            $updateStock = $_db->prepare("UPDATE product SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
            $updateStock->execute([$item['quantity'], $item['product_id']]);
        }

        $_db->commit(); 
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $_db->rollBack(); 
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
