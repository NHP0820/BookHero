<?php
require '../../_base.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user']['id'];
$errors = [];

$addressStmt = $_db->prepare("SELECT * FROM address WHERE user_id = ? LIMIT 1");
$addressStmt->execute([$userId]);
$address = $addressStmt->fetch(PDO::FETCH_OBJ);

if (!$address) {
    $errors[] = "You need to add an address before checkout";
}

$stmt = $_db->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$userId]);
$cart = $stmt->fetch(PDO::FETCH_OBJ);

if (!$cart) {
    header("Location: shoppingCart.php");
    exit();
}

$cartStmt = $_db->prepare(
    "SELECT ci.*, p.*, pp.product_photo
     FROM cart_item ci
     INNER JOIN product p ON ci.product_id = p.product_id
     LEFT JOIN product_photo pp ON pp.product_photo_id = (
         SELECT product_photo_id
         FROM product_photo
         WHERE product_id = p.product_id
         ORDER BY product_photo_id ASC  
         LIMIT 1
     )
     WHERE ci.cart_id = ?"
);
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $validOrder = true;
    $invalidProducts = [];
    
    foreach ($cartItems as $item) {
        if ($item->quantity > $item->stock_quantity) {
            $validOrder = false;
            $invalidProducts[] = $item->name;
        }
    }
    
    if (!$validOrder) {
        $errors[] = "The following products don't have enough stock: " . implode(", ", $invalidProducts);
    }
    
    if (empty($errors)) {
        try {
            $_db->beginTransaction();
            
            $orderStmt = $_db->prepare("INSERT INTO `order` (user_id, order_date, total_amount, status_id, address_id, expired_time) VALUES (?, CURDATE(), ?, 1, ?, DATE_ADD(NOW(), INTERVAL 6 HOUR))");
            $orderStmt->execute([$userId, $total, $address->address_id]);
            $orderId = $_db->lastInsertId();
            
            foreach ($cartItems as $item) {
                $orderDetailStmt = $_db->prepare("INSERT INTO order_detail (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
                $orderDetailStmt->execute([$orderId, $item->product_id, $item->quantity, $item->price]);
                
                $updateStockStmt = $_db->prepare("UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                $updateStockStmt->execute([$item->quantity, $item->product_id]);
            }
            
            $emptyCartStmt = $_db->prepare("DELETE FROM cart_item WHERE cart_id = ?");
            $emptyCartStmt->execute([$cart->cart_id]);
            
            $_db->commit();
            
            header("Location: ../orders.php?order_id=" . $orderId);
            exit();
            
        } catch (Exception $e) {
            $_db->rollBack();
            $errors[] = "An error occurred while processing your order: " . $e->getMessage();
        }
    }
}

include '../../_head.php';
?>

<link rel="stylesheet" href="../../css/checkout.css">
<head>
    <title>BookHero - Checkout</title>
</head>

<body>
    <div class="checkout-container">
        <h1 class="page-title">Checkout</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="checkout-section">
                <h2 class="section-title">Order Summary</h2>
                <table class="order-summary">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): 
                            $itemTotal = $item->price * $item->quantity;
                        ?>
                            <tr>
                                <td>
                                    <div class="book-info">
                                        <img src="../../images/<?= htmlspecialchars($item->product_photo) ?>" class="book-image" alt="<?= htmlspecialchars($item->name) ?>">
                                        <div class="book-details">
                                            <h3><?= htmlspecialchars($item->name) ?></h3>
                                            <p><?= htmlspecialchars($item->author) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="price">RM<?= number_format($item->price, 2) ?></td>
                                <td><?= $item->quantity ?></td>
                                <td class="price">RM<?= number_format($itemTotal, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="summary-totals">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>RM<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>RM<?= number_format($shipping, 2) ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>RM<?= number_format($total, 2) ?></span>
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
        <a href="addresses.php?edit=<?= $address->address_id ?>" class="edit-button">Edit Address</a>
    <?php else: ?>
        <p>You don't have any saved address. Please add an address to continue.</p>
        <a href="addresses.php" class="back-button">Add Address</a>
    <?php endif; ?>
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

            <?php if ($address): ?>
                <button type="submit" class="place-order-button">Place Order</button>
            <?php endif; ?>
        </form>
        
        <a href="shoppingCart.php" class="back-button">Back to Cart</a>
    </div>
</body>
</html>