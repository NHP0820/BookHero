<?php
require '../../_base.php';
include '../../_head.php';

$userId = $_SESSION['user']['id'] ?? null;

if (!$userId) {
    temp('info', 'Please login first');
    redirect("../login.php");
    exit;
}


$stmt = $_db->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$userId]);
$cart = $stmt->fetch(PDO::FETCH_OBJ);

$cartItems = [];
$cartSize = 0;
$subtotal = 0;

if ($cart) {
    $cartStmt = $_db->prepare("
        SELECT ci.*, p.name, p.price, p.author, p.stock_quantity,
               (SELECT product_photo FROM product_photo WHERE product_id = p.product_id LIMIT 1) AS product_photo
        FROM cart_item ci
        JOIN product p ON ci.product_id = p.product_id
        WHERE ci.cart_id = ?
    ");
    $cartStmt->execute([$cart->cart_id]);
    $cartItems = $cartStmt->fetchAll(PDO::FETCH_OBJ);
    $cartSize = count($cartItems);

    foreach ($cartItems as $item) {
        $subtotal += $item->price * $item->quantity;
    }
}

$shipping = 4.99;
$total = $subtotal + $shipping;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    if (!$cart || empty($cartItems)) {
        temp('info', 'Cart is empty');
        redirect('shoppingCart.php');
    }

    
    $addrStmt = $_db->prepare("SELECT * FROM address WHERE user_id = ? LIMIT 1");
    $addrStmt->execute([$userId]);
    $address = $addrStmt->fetch(PDO::FETCH_OBJ);

    if (!$address) {
        temp('info', 'Please add your shipping address before proceeding.');
        redirect('shoppingCart.php');
    }

    
    foreach ($cartItems as $item) {
        if ($item->quantity > $item->stock_quantity) {
            temp('info', "Not enough stock for {$item->name}");
            redirect('shoppingCart.php');
        }
    }

    try {
        $_db->beginTransaction();

       
        $orderStmt = $_db->prepare("INSERT INTO `order` (user_id, order_date, total_amount, status_id, address_id, expired_time) VALUES (?, NOW(), ?, 2, ?, DATE_ADD(NOW(), INTERVAL 6 HOUR))");
        $orderStmt->execute([$userId, $total, $address->address_id]);
        $orderId = $_db->lastInsertId();

    
        foreach ($cartItems as $item) {
            $_db->prepare("INSERT INTO order_detail (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)")
                ->execute([$orderId, $item->product_id, $item->quantity, $item->price]);

            $_db->prepare("UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ?")
                ->execute([$item->quantity, $item->product_id]);
        }

        // Insert payment record (default method Online Banking)
        $_db->prepare("INSERT INTO payment (order_id, amount, payment_method, payment_date) VALUES (?, ?, 'Online Banking', NOW())")
            ->execute([$orderId, $total]);

        // clear cart
        $_db->prepare("DELETE FROM cart_item WHERE cart_id = ?")->execute([$cart->cart_id]);

        $_db->commit();
        temp('info', 'Your order has been placed successfully!');
        header("Location: ../orders.php?order_id=" . $orderId);
        exit();
    } catch (Exception $e) {
        $_db->rollBack();
        temp('info', 'Checkout failed: ' . $e->getMessage());
        redirect('shoppingCart.php');
    }
}
?>

<link rel="stylesheet" href="../../css/shoppingCart.css">
<head>
    <title>BookHero - Shopping Cart</title>
</head>

<body>
<div class="container">
    <h1 class="page-title">Shopping Cart (<?= $cartSize ?> items)</h1>

    <?php if ($cartSize > 0): ?>
        <div class="cart">
            <div class="cart-header">
                <div>Product</div>
                <div>Price</div>
                <div>Quantity</div>
                <div>Subtotal</div>
            </div>

            <?php foreach ($cartItems as $item): 
                $itemTotal = $item->price * $item->quantity;
            ?>
                <div class="cart-item">
                    <div class="book-info">
                        <div class="book-image">
                            <img src="../../images/<?= htmlspecialchars($item->product_photo) ?>" class="product-image" alt="<?= htmlspecialchars($item->name) ?>">
                        </div>
                        <div class="book-details">
                            <h3><?= htmlspecialchars($item->name); ?></h3>
                            <p><?= htmlspecialchars($item->author); ?></p>
                        </div>
                    </div>
                    <div class="price">RM<?= number_format($item->price, 2); ?></div>
                    <div class="quantity"><?= $item->quantity ?></div>
                    <div class="price">RM<?= number_format($itemTotal, 2); ?></div>
                </div>
            <?php endforeach; ?>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>RM<?= number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>RM<?= number_format($shipping, 2); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>RM<?= number_format($total, 2); ?></span>
                </div>

                <form method="post" action="shoppingCart.php">
                    <button type="submit" name="checkout" class="checkout-button">Proceed to Payment</button>
                </form>
                <a href="../../index.php" class="continue-shopping">Continue Shopping</a>
            </div>
        </div>
    <?php else: ?>
        <div class="cart">
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h3>Your cart is empty</h3>
                <a href="../../index.php" class="shop-now-button">Browse Books</a>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
