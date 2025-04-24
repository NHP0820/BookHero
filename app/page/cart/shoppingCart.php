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
    $stmt->execute(params: [$userId]);
    $cart = $stmt->fetch(PDO::FETCH_OBJ);

    if ($cart) {
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

                <?php foreach ($cartProductList as $item) {
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
                            <button class="quantity-btn" data-action="decrease">-</button>
                            <input type="text" value="<?= $item->quantity; ?>" disabled>
                            <button class="quantity-btn" data-action="increase">+</button>
                        </div>
                        <div class="price subtotal-price">RM<?= number_format($itemSubtotal, 2); ?></div>
                        <div class="remove" title="Remove item">×</div>
                    </div>
                <?php } ?>

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

                    <button class="checkout-button" id="process-payment-btn">Proceed to Checkout</button>
                    <a href="../../index.php" class="continue-shopping">Continue Shopping</a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart">
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h3>Your cart is empty</h3>
                    <p>Seems like you haven't added any books to your cart yet. Explore our collection and find your favourite book!</p>
                    <a href="../../index.php" class="shop-now-button">Browse Books</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quantityButtons = document.querySelectorAll('.quantity-btn');

            quantityButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const cartItem = this.closest('.cart-item');
                    const productId = cartItem.dataset.productId;
                    const quantityInput = cartItem.querySelector('.quantity input');
                    let quantity = parseInt(quantityInput.value);

                    const action = this.dataset.action;
                    if (action === 'increase') {
                        quantity += 1;
                    } else if (action === 'decrease' && quantity > 1) {
                        quantity -= 1;
                    }

                    quantityInput.value = quantity;

                    const price = parseFloat(cartItem.querySelector('.price').innerText.replace('RM', ''));
                    const subtotal = price * quantity;
                    cartItem.querySelector('.subtotal-price').innerText = `RM${subtotal.toFixed(2)}`;

                    fetch('updateQuantity.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `product_id=${productId}&quantity=${quantity}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Failed to update quantity: ' + (data.message || 'Unknown error'));
                                quantityInput.value = quantity - (action === 'increase' ? 1 : -1);
                            } else {
                                updateCartTotals();
                            }
                        })
                        .catch(error => {
                            console.error('Error updating quantity:', error);
                            alert('Failed to update quantity. Please try again.');
                        });
                });
            });

            const removeButtons = document.querySelectorAll('.remove');

            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm("Are you sure you want to remove this item from your cart?")) {
                        const cartItem = this.closest('.cart-item');
                        const productId = cartItem.dataset.productId;

                        fetch('deleteCartItem.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `product_id=${productId}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {

                                    location.reload(); 

                                } else {
                                    alert('Failed to remove item: ' + (data.message || 'Unknown error'));
                                }
                            })
                            .catch(error => {
                                console.error('Error removing item:', error);
                                alert('Failed to remove item. Please try again.');
                            });
                    }
                });
            });

            function updateCartTotals() {
                let subtotal = 0;
                const shipping = 4.99;

                document.querySelectorAll('.cart-item').forEach(item => {
                    const itemSubtotal = parseFloat(item.querySelector('.subtotal-price').innerText.replace('RM', ''));
                    subtotal += itemSubtotal;
                });

                const total = subtotal + shipping;

                document.getElementById('cart-subtotal').innerText = `RM${subtotal.toFixed(2)}`;
                document.getElementById('cart-total').innerText = `RM${total.toFixed(2)}`;
            }


            const checkoutButton = document.getElementById('process-payment-btn');
            if (checkoutButton) {
                checkoutButton.addEventListener('click', function() {
                    window.location.href = 'checkout.php';
                });
            }
        });
    </script>
</body>

</html>