<?php
require '../../_base.php';
include '../../_head.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

$userId = $_SESSION['user']['id'];
$orderId = $_POST['order_id'] ?? null;
$payment_method = $_POST['payment_method'] ?? null;
$errors = [];

if (!$orderId) {
    header("Location: ../orders.php");
    exit();
}

// check the order is valid and not already paid
$orderStmt = $_db->prepare("SELECT * FROM `order` WHERE order_id = ? AND user_id = ? AND status_id = 1");
$orderStmt->execute([$orderId, $userId]);
$order = $orderStmt->fetch(PDO::FETCH_OBJ);

if (!$order) {
    $errors[] = "Invalid order or already paid.";
}

// check order
$orderItems = [];
if ($order) {
    $orderItemsStmt = $_db->prepare("
        SELECT od.*, p.name, p.author, p.price,
               (SELECT product_photo FROM product_photo 
                WHERE product_id = od.product_id 
                ORDER BY product_photo_id ASC 
                LIMIT 1) AS product_photo
        FROM order_detail od
        JOIN product p ON od.product_id = p.product_id
        WHERE od.order_id = ?
    ");
    $orderItemsStmt->execute([$orderId]);
    $orderItems = $orderItemsStmt->fetchAll(PDO::FETCH_OBJ);
}

// address
$addressStmt = $_db->prepare("SELECT * FROM address WHERE user_id = ? LIMIT 1");
$addressStmt->execute([$userId]);
$address = $addressStmt->fetch(PDO::FETCH_OBJ);

// payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $payment_method && empty($errors)) {
    try {
        $_db->beginTransaction();

        $payStmt = $_db->prepare("INSERT INTO payment (order_id, amount, payment_method, payment_date) VALUES (?, ?, ?, NOW())");
        $payStmt->execute([$order->order_id, $order->total_amount, $payment_method]);

        $updateOrder = $_db->prepare("UPDATE `order` SET status_id = 2 WHERE order_id = ?");
        $updateOrder->execute([$orderId]);

        $_db->commit();

        temp('info', 'Payment successful!');
        header("Location: ../orders.php?order_id=" . $orderId);
        exit();
    } catch (Exception $e) {
        $_db->rollBack();
        $errors[] = "Payment failed: " . $e->getMessage();
    }
}
?>

<link rel="stylesheet" href="../../css/checkout.css">
<head>
    <title>BookHero - Checkout</title>
</head>

<body>
<div class="checkout-container">
    <h1 class="page-title">Order Payment</h1>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($order): ?>
        <form method="post" action="">
            <input type="hidden" name="order_id" value="<?= $orderId ?>">

            <div class="checkout-section">
                <h2 class="section-title">Products in Order</h2>
                <table class="order-summary">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): 
                            $itemTotal = $item->price * $item->quantity;
                        ?>
                        <tr>
                            <td>
                                <div class="book-info">
                                    <img src="../../images/<?= htmlspecialchars($item->product_photo) ?>" class="book-image" alt="<?= htmlspecialchars($item->name) ?>">
                                    <div class="book-details">
                                        <strong><?= htmlspecialchars($item->name) ?></strong><br>
                                        <small><?= htmlspecialchars($item->author) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>RM<?= number_format($item->price, 2) ?></td>
                            <td><?= $item->quantity ?></td>
                            <td>RM<?= number_format($itemTotal, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="checkout-section">
                <h2 class="section-title">Total Amount</h2>
                <div class="summary-totals">
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>RM<?= number_format($order->total_amount, 2) ?></span>
                    </div>
                </div>
            </div>

            <div class="checkout-section">
                <h2 class="section-title">Delivery Address</h2>
                <?php if ($address): ?>
                    <div class="address-box">
                        <strong><?= htmlspecialchars($address->address_name ?? 'Default Address') ?></strong><br>
                        <?= htmlspecialchars($address->street) ?><br>
                        <?= htmlspecialchars($address->city) ?>, <?= htmlspecialchars($address->state) ?> <?= htmlspecialchars($address->zip_code) ?><br>
                        <?= htmlspecialchars($address->country) ?>
                    </div>
                <?php else: ?>
                    <p>No address found.</p>
                <?php endif; ?>
            </div>

            <div class="checkout-section">
                <h2 class="section-title">Select Payment Method</h2>
                <div class="payment-options">
                    <label><input type="radio" name="payment_method" value="Credit Card" checked> Credit Card</label><br>
                    <label><input type="radio" name="payment_method" value="Online Banking"> Online Banking</label><br>
                    <label><input type="radio" name="payment_method" value="E-Wallet"> E-Wallet</label>
                </div>
            </div>

            <button type="submit" class="place-order-button">Confirm Payment</button>
        </form>
        <a href="../orders.php" class="back-button">Back to Orders</a>
    <?php endif; ?>
</div>
</body>
</html>
