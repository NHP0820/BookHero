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

?>

