<?php
require '../../_base.php';

$user_id = $_SESSION['user']['id'] ?? null;
$user_role = $_SESSION['user']['role'] ?? null;
if (!$user_id || $user_role !== 'member') {
    temp('info', 'Please login first');
    redirect("../logout.php");
    redirect("../login.php");
    exit;
}

//$user_id = $_SESSION['user']['id'];
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

// get product quantity
$stmt = $_db->prepare("SELECT stock_quantity FROM product WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found.");
}

$stock_quantity = intval($product['stock_quantity']);

// get cart id
$stmt = $_db->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_id = $stmt->fetchColumn();

if (!$cart_id) {
    $stmt = $_db->prepare("INSERT INTO cart (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    $cart_id = $_db->lastInsertId();
}

// check if the product is already in the cart
$stmt = $_db->prepare("SELECT quantity FROM cart_item WHERE cart_id = ? AND product_id = ?");
$stmt->execute([$cart_id, $product_id]);
$existingQty = $stmt->fetchColumn();
$totalDesired = $quantity + intval($existingQty);

// check if the quantity is valid
if ($totalDesired > $stock_quantity) {
    temp('info', "Failed to add ,stock only remainding $stock_quantity ,(You had added $existingQty )");
    redirect("../productProfile.php?product_id=$product_id");
}

// add to cart
if ($existingQty !== false) {
    // if item exists, update quantity
    $stmt = $_db->prepare("UPDATE cart_item SET quantity = quantity + ? WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$quantity, $cart_id, $product_id]);
} else {
    // if no item
    $stmt = $_db->prepare("INSERT INTO cart_item (cart_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$cart_id, $product_id, $quantity]);
}

temp('info', "Added to your cart!!");
redirect("../productProfile.php?product_id=$product_id");
