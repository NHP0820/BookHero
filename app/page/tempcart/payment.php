<?php
require '../../_base.php';


$order_id = $_GET['order_id'] ?? 0;
$user_id = $_SESSION['user']['id'] ?? null;

// get orderid
$stmt = $_db->prepare("SELECT * FROM `order` WHERE order_id = ? AND user_id = ? AND status_id = 1");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_OBJ);

if (!$order) {
    temp('info', 'Invalid or already paid order.');
    redirect('/page/orders.php');
}

// if pressed confirm payment button
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'] ?? 'Manual Payment';

    // insert payment record
    $pay = $_db->prepare("INSERT INTO payment (order_id, amount, payment_method, payment_date) VALUES (?, ?, ?, NOW())");
    $pay->execute([$order->order_id, $order->total_amount, $method]);

    // update order status to 2 (paid)
    $update = $_db->prepare("UPDATE `order` SET status_id = 2 WHERE order_id = ?");
    $update->execute([$order->order_id]);

    temp('info', 'Payment successful. Thank you!');
    redirect('/page/orders.php');
}

$_title = 'BookHero | Payment';
include '../../_head.php';
?>
<link rel="stylesheet" href="/css/temppayment.css">
</head>
<body>

<div class="payment-container">
    <h2>Payment for Order #<?= $order->order_id ?></h2>

    <div class="order-summary">
        <p><strong>Amount Due:</strong> RM<?= number_format($order->total_amount, 2) ?></p>
        <p><strong>Order Date:</strong> <?= $order->order_date ?></p>
    </div>

    <form method="post" class="payment-form">
        <label for="payment_method">Payment Method</label>
        <select name="payment_method" id="payment_method">
            <option value="Online Banking">Online Banking</option>
            <option value="Credit/Debit Card">Credit/Debit Card</option>
            <option value="E-Wallet">E-Wallet</option>
        </select>

        <button type="submit" class="pay-button">Confirm Payment</button>
    </form>

    <a href="../orders.php" class="back-link">← Back to Orders</a>
</div>

<?php include '../../_foot.php'; ?>
