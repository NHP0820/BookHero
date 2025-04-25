<?php
require '../../_base.php';
include '../../_head.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

$userId = $_SESSION['user']['id'];

$stmt = $_db->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$userId]);
$cart = $stmt->fetch(PDO::FETCH_OBJ);

if (!$cart) {
    header("Location: shoppingCart.php");
    exit();
}

$cartStmt = $_db->prepare("
    SELECT ci.*, p.*
    FROM cart_item ci
    INNER JOIN product p ON ci.product_id = p.product_id
    WHERE ci.cart_id = ?
");
$cartStmt->execute([$cart->cart_id]);
$cartItems = $cartStmt->fetchAll(PDO::FETCH_OBJ);

if (count($cartItems) === 0) {
    header("Location: shoppingCart.php");
    exit();
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item->price * $item->quantity;
}
$shipping = 4.99;
$total = $subtotal + $shipping;
?>

<link rel="stylesheet" href="../../css/temppayment.css">

<head>
    <title>BookHero - Payment</title>
</head>

<body>
    <div class="payment-container">
        <h1 class="page-title">Create Order</h1>

        <div class="payment-summary">
            <p><strong>Subtotal:</strong> RM<?= number_format($subtotal, 2) ?></p>
            <p><strong>Shipping:</strong> RM<?= number_format($shipping, 2) ?></p>
            <p><strong>Total Amount:</strong> RM<?= number_format($total, 2) ?></p>
        </div>

        <form method="post" action="checkout.php">
            <div class="payment-method">
                <label>Select Payment Method</label>
                <select name="payment_method" required>
                    <option value="Online Banking">Online Banking</option>
                    <option value="Credit/Debit Card">Credit/Debit Card</option>
                    <option value="E-Wallet">E-Wallet</option>
                </select>
            </div>

            <button type="submit" class="confirm-payment-button">Confirm Payment</button>
        </form>

        <a href="shoppingCart.php" class="back-button">Back to Cart</a>
    </div>
</body>
</html>
