<?php
require '../../_base.php';

if (!isset($_SESSION['user'])) {
    redirect('/page/login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

// get user cart id
$cartStmt = $_db->prepare("SELECT * FROM cart WHERE user_id = ?");
$cartStmt->execute([$user_id]);
$cart = $cartStmt->fetch(PDO::FETCH_OBJ);

// if no cart, create one
if (!$cart) {
    $cart_id = null;
    $cart_items = [];
} else {
    $cart_id = $cart->cart_id;

    // get cart items
    $itemStmt = $_db->prepare("
        SELECT ci.cart_item_id, ci.quantity, p.name, p.price, ci.product_id,
            (SELECT pp.product_photo 
            FROM product_photo pp 
            WHERE pp.product_id = p.product_id 
            LIMIT 1) AS product_photo
        FROM cart_item ci
        JOIN product p ON ci.product_id = p.product_id
        WHERE ci.cart_id = ?
    ");
    $itemStmt->execute([$cart_id]);
    $cart_items = $itemStmt->fetchAll(PDO::FETCH_OBJ);
}

// remove item from cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_product_id'])) {
    $product_id = intval($_POST['remove_product_id']);


    $stmt = $_db->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart) {
        $cart_id = $cart['cart_id'];


        $deleteStmt = $_db->prepare("DELETE FROM cart_item WHERE cart_id = ? AND product_id = ?");
        $deleteStmt->execute([$cart_id, $product_id]);

        temp('info', 'Item removed from cart.');
    }


    redirect('tempcart.php');
}


?>






<?php include '../../_head.php'; ?>
<link rel="stylesheet" href="/css/tempcart.css">
</head>

<body>
    <div class="cart-container">
        <div class="cart-title">Your Shopping Cart</div>

        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price (RM)</th>
                    <th>Total (RM)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $grandTotal = 0; ?>
                <?php foreach ($cart_items as $item): ?>
                    <?php $grandTotal += $item->price * $item->quantity; ?>
                    <tr class="cart-row">
                        <td>
                            <img src="/images/<?= $item->product_photo ?>" alt="<?= $item->name ?>">
                            <span class="cart-product-name"><?= $item->name ?></span>
                        </td>
                        <td><?= $item->quantity ?></td>
                        <td><?= number_format($item->price, 2) ?></td>
                        <td><?= number_format($item->price * $item->quantity, 2) ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="remove_product_id" value="<?= $item->product_id ?>">
                                <button type="submit" class="remove-btn">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-total">Grand Total: RM <?= number_format($grandTotal, 2) ?></div>
        <form method="post" action="checkout.php" class="checkout-form">
            <button type="submit" name="checkout" class="checkout-btn">Create Order!</button>
        </form>

    </div>

    <?php include '../../_foot.php'; ?>