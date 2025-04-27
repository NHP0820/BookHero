<?php
require '../../_base.php';
include '../../_head.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

$userId = $_SESSION['user']['id'];
$orderId = $_POST['order_id'] ?? $_SESSION['current_order_id'] ?? null;
if ($orderId) {
    $_SESSION['current_order_id'] = $orderId;
}
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
$addressStmt = $_db->prepare("SELECT * FROM address WHERE user_id = ?");
$addressStmt->execute([$userId]);
$addresses = $addressStmt->fetchAll(PDO::FETCH_OBJ);

$addressStmt = $_db->prepare("SELECT * FROM address WHERE user_id = ? AND defaults = 1 LIMIT 1");
$addressStmt->execute([$userId]);
$address = $addressStmt->fetch(PDO::FETCH_OBJ);

// payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $payment_method && empty($errors)) {
    if (!$address) {
        $errors[] = "Please add a delivery address before proceeding with payment.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $payment_method && empty($errors)) {
    try {
        $_db->beginTransaction();

        $payStmt = $_db->prepare("INSERT INTO payment (order_id, amount, payment_method, payment_date) VALUES (?, ?, ?, NOW())");
        $payStmt->execute([$order->order_id, $order->total_amount, $payment_method]);

        $updateOrder = $_db->prepare("UPDATE `order` SET status_id = 2 WHERE order_id = ?");
        $updateOrder->execute([$orderId]);

        $updateAddress = $_db->prepare('UPDATE `order` SET address_id = ? WHERE order_id = ?');
        $updateAddress->execute([$address->address_id, $orderId]);

        $_db->commit();

        temp('info', 'Payment successful!');
        header("Location: ../orders.php?order_id=" . $orderId);
        exit();
    } catch (Exception $e) {
        $_db->rollBack();
        $errors[] = "Payment failed: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'set_default_address') {
    $newDefaultId = intval($_POST['set_default_address_id']);
    $orderId = $_POST['order_id']; 

    $unsetStmt = $_db->prepare("UPDATE address SET defaults = 0 WHERE user_id = ?");
    $unsetStmt->execute([$userId]);

    $setStmt = $_db->prepare("UPDATE address SET defaults = 1 WHERE user_id = ? AND address_id = ?");
    $setStmt->execute([$userId, $newDefaultId]);

    header("Location: checkout.php");
    exit;
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

    <div class="checkout-section">
        <h2 class="section-title">Delivery Address</h2>

        <?php if (!empty($addresses)): ?>
            <form method="post" action="checkout.php">
                <input type="hidden" name="action" value="set_default_address">
                <input type="hidden" name="order_id" value="<?= $orderId ?>">
                <?php foreach ($addresses as $addr): ?>
                    <div class="address-box" style="margin-bottom: 10px;">
                        <label>
                            <input type="radio" name="set_default_address_id" value="<?= $addr->address_id ?>"
                                <?= $addr->defaults ? 'checked' : '' ?> onchange="this.form.submit()">
                            <strong><?= htmlspecialchars($addr->address_name ?? 'Address') ?></strong><br>
                            <?= htmlspecialchars($addr->street) ?>,
                            <?= htmlspecialchars($addr->zip_code) ?>,
                            <?= htmlspecialchars($addr->city) ?>,
                            <?= htmlspecialchars($addr->state) ?>,
                            <?= htmlspecialchars($addr->country) ?>
                            <?= $addr->defaults ? '<span style="color: green;"> (Default)</span>' : '' ?>
                        </label>
                    </div>
                <?php endforeach; ?>
                <a href="addresses.php" class="back-button">Add New Address</a>
            </form>
        <?php else: ?>
            <p>You don't have any saved addresses. Please add one to continue.</p>
            <a href="addresses.php" class="back-button">Add Address</a>
        <?php endif; ?>
    </div>

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
                <h2 class="section-title">Payment Method</h2>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="credit_card" checked>
                        Credit Card / Debit Card
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="online_banking">
                        Online Banking
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="e_wallet">
                        E-Wallet (Touch 'n Go, GrabPay, etc.)
                    </label>
                </div>
                <p><small>* Payment details will be collected on the next page</small></p>
            </div>

            <button type="submit" class="place-order-button">Confirm Payment</button>
        </form>
        <a href="../orders.php" class="back-button">Back to Orders</a>
    <?php endif; ?>
</div>
</body>
</html>
