<?php

require '../_base.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'];
    $reason = $_POST['reason'];
    try {
        $cancelOrder = $_db->prepare("UPDATE `order` SET status_id = 4 , cancel_desc = ? WHERE order_id = ?");
        $cancelOrder->execute([$reason,$orderId]);

        if ($cancelOrder->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
