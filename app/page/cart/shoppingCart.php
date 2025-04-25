<?php
require '../../_base.php';
include '../../_head.php';

$subtotal = 0;
$cartSize = 0;
$cartProductList = [];

if (isset($_SESSION['user'])) {
    $username = $_SESSION['user']['username'];
    $userId = $_SESSION['user']['id'];

    $stmt = $_db->prepare("SELECT * FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $cart = $stmt->fetch(PDO::FETCH_OBJ);

    if ($cart) {
        $cartStmt = $_db->prepare("
            SELECT ci.*, p.*, pp.product_photo
            FROM cart_item ci
            INNER JOIN product p ON ci.product_id = p.product_id
            LEFT JOIN product_photo pp ON pp.product_photo_id = (
                SELECT product_photo_id
                FROM product_photo
                WHERE product_id = p.product_id
                ORDER BY product_photo_id ASC  
                LIMIT 1
            )
            WHERE ci.cart_id = ?
        ");
        $cartStmt->execute([$cart->cart_id]);
        $cartProductList = $cartStmt->fetchAll(PDO::FETCH_OBJ);
        $cartSize = count($cartProductList);
    }
} else {
    header("Location: ../../login.php");
    exit();
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
                    <div></div>
                </div>

                <?php foreach ($cartProductList as $item): 
                    $itemSubtotal = $item->price * $item->quantity;
                    $subtotal += $itemSubtotal;
                ?>
                    <div class="cart-item" data-product-id="<?= $item->product_id ?>">
                        <div class="book-info">
                            <div class="book-image">
                                <img src="../../images/<?= htmlspecialchars($item->product_photo) ?>" class="product-image" alt="<?= htmlspecialchars($item->name); ?>">
                            </div>
                            <div class="book-details">
                                <h3><?= htmlspecialchars($item->name); ?></h3>
                                <p><?= htmlspecialchars($item->author); ?></p>
                            </div>
                        </div>
                        <div class="price">RM<?= number_format($item->price, 2); ?></div>
                        <div class="quantity">
                            <input type="text" value="<?= $item->quantity; ?>" disabled>
                        </div>
                        <div class="price subtotal-price">RM<?= number_format($itemSubtotal, 2); ?></div>
                    </div>
                <?php endforeach; ?>

                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="cart-subtotal">RM<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>RM4.99</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span id="cart-total">RM<?php echo number_format($subtotal + 4.99, 2); ?></span>
                    </div>

                    <form method="post" action="../tempcart/payment.php">
                        <button type="submit" class="checkout-button">Proceed to Payment</button>
                    </form>
                    <a href="../../index.php" class="continue-shopping">Continue Shopping</a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart">
                <div class="empty-cart">
                    <h3>Your cart is empty</h3>
                    <a href="../../index.php" class="shop-now-button">Browse Books</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
