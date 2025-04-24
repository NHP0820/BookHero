<?php
require '../../_base.php';

$user_id = $_SESSION['user']['id'] ?? null;
$user_role = $_SESSION['user']['role'] ?? null;
if (!$user_id && $user_role !== 'member') {
    temp('info', 'Please login first');
    redirect("../login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// get user cart id
$cart = $_db->prepare("SELECT * FROM cart WHERE user_id = ?");
$cart->execute([$user_id]);
$cart_data = $cart->fetch();

if (!$cart_data) {
    temp('info', 'No items in cart');
    redirect('/page/tempcart/tempcart.php');
    exit;
}

$cart_items = $_db->prepare("
    SELECT ci.*, p.price, p.stock_quantity, p.name
    FROM cart_item ci
    JOIN product p ON ci.product_id = p.product_id
    WHERE ci.cart_id = ?");
$cart_items->execute([$cart_data->cart_id]);
$items = $cart_items->fetchAll();

if (!$items) {
    temp('info', 'No items in cart');
    redirect('/page/tempcart/tempcart.php');
    exit;
}

$total = 0;
foreach ($items as $item) {
    if ($item->quantity > $item->stock_quantity) {
        temp('info', "Not enought stock：{$item->name}  remainding only : $item->stock_quantity");
        redirect('/page/tempcart/tempcart.php');
        exit;
    }
    $total += $item->price * $item->quantity;
}

$_db->beginTransaction();
try {
    // create order
    $order_insert = $_db->prepare("
        INSERT INTO `order` (user_id, order_date, total_amount, status_id, address_id, expired_time)
        VALUES (?, CURDATE(), ?, 1, ?, DATE_ADD(NOW(), INTERVAL 6 HOUR))
    ");
    $order_insert->execute([$user_id, $total, $address_id]);
    $order_id = $_db->lastInsertId();

    // insert order details
    $detail_insert = $_db->prepare("
        INSERT INTO order_detail (order_id, product_id, quantity, price_at_purchase)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($items as $item) {
        $detail_insert->execute([$order_id, $item->product_id, $item->quantity, $item->price]);

        // - stock_quantity
        $_db->prepare("UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ?")
            ->execute([$item->quantity, $item->product_id]);
    }

    // after order created, delete cart items
    $_db->prepare("DELETE FROM cart_item WHERE cart_id = ?")->execute([$cart_data->cart_id]);

    $_db->commit();
    temp('info', 'order created successfully! Please pay within 6 hours.');
    redirect('/page/orders.php');
} catch (Exception $e) {
    $_db->rollBack();
    temp('info', 'Failed to create order');
    redirect('/page/tempcart/tempcart.php');
}
