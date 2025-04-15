<?php
require '../_base.php';

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$user_id = $_SESSION['user']['id'] ?? null;

// Handle AJAX wishlist toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wishlist_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['wishlist_action'];
    $product_id = intval($_POST['product_id']);

    if (!$user_id || !$product_id || !in_array($action, ['add', 'remove'])) {
        redirect("login.php");
        exit;
    }

    if ($action === 'add') {
        $stmt = $_db->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $product_id]);
        echo json_encode(['status' => 'success']);
        //temp('info', "Product added to wishlist.");
        exit;
    } elseif ($action === 'remove') {
        $stmt = $_db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        echo json_encode(['status' => 'success']);
        //temp('info', "Product removed from wishlist.");
        exit;
    }
}

// Check if valid product
if ($product_id === 0) {
    die("Invalid product ID.");
}

// Get product
$stmt = $_db->prepare("SELECT * FROM product WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_OBJ);

// Get photos
$photoStmt = $_db->prepare("SELECT * FROM product_photo WHERE product_id = ?");
$photoStmt->execute([$product_id]);
$photos = $photoStmt->fetchAll(PDO::FETCH_OBJ);

// Get categories
$categoryStmt = $_db->prepare("
    SELECT c.name FROM category c
    INNER JOIN category_product cp ON c.category = cp.category_id
    WHERE cp.product_id = ?
");
$categoryStmt->execute([$product_id]);
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

// Check if wishlisted
$isWishlisted = false;
if ($user_id) {
    $stmt = $_db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $isWishlisted = $stmt->fetchColumn() > 0;
}

include '../_head.php';
?>
<link rel="stylesheet" href="/css/productProfile.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() { //+ and - function
        $(".qty-btn").on("click", function() {
            var input = $("#quantity");
            var current = parseInt(input.val());
            var min = parseInt(input.attr("min"));
            var max = parseInt(input.attr("max"));
            var newVal = current;

            if ($(this).hasClass("plus")) {
                newVal = current + 1;
            } else if ($(this).hasClass("minus")) {
                newVal = current - 1;
            }

            // 限制范围
            if (newVal < min) newVal = min;
            if (newVal > max) newVal = max;

            input.val(newVal);
        });
    });


    function changeMainImage(src) {
        document.getElementById("mainImage").src = src;
    }

    function toggleWishlist(button, productId) {
        let isWishlisted = button.classList.contains("wishlisted");
        let action = isWishlisted ? "remove" : "add";

        const formData = new FormData();
        formData.append("wishlist_action", action);
        formData.append("product_id", productId);

        fetch("", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    button.classList.toggle("wishlisted");
                } else {
                    alert(data.message || "Failed to update wishlist.");
                }
            });
    }

    document.addEventListener("DOMContentLoaded", function() {
        const wishlistBtn = document.getElementById("wishlist-btn");
        const productId = wishlistBtn.getAttribute("data-product-id");
        wishlistBtn.addEventListener("click", function() {
            toggleWishlist(this, productId);
        });
    });
</script>
</head>

<body>
    <div class="product-container">
        <div class="left-section">
            <div class="main-image-container">
                <?php $mainPhoto = !empty($photos) ? $photos[0]->product_photo : 'default.png'; ?>
                <img id="mainImage" src="/images/<?= htmlspecialchars($mainPhoto) ?>" alt="Main Product Image">
            </div>

            <div class="thumbnail-container">
                <?php foreach ($photos as $photo): ?>
                    <img class="thumbnail" src="/images/<?= htmlspecialchars($photo->product_photo) ?>" alt="Thumbnail" onclick="changeMainImage(this.src)">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="right-section">
            <h1>
                <?= htmlspecialchars($product->name) ?>
                <button id="wishlist-btn" class="wishlist-btn <?= $isWishlisted ? 'wishlisted' : '' ?>" data-product-id="<?= $product->product_id ?>">
                    <i class="fa fa-heart"></i>
                </button>
            </h1>
            <p class="price">RM<?= number_format($product->price, 2) ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($product->description) ?></p>
            <p><strong>Stock Quantity:</strong> <?= htmlspecialchars($product->stock_quantity) ?></p>
            <p><strong>Categories:</strong> <?= !empty($categories) ? implode(", ", $categories) : "Uncategorized" ?></p>



            <form method="post" action="../page/tempcart/tempaddtocart.php" class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?= $product->product_id ?>">
                <div class="quantity-group">
                    <label for="quantity">Quantity</label>
                    <div class="quantity-controls">
                        <button type="button" class="qty-btn minus">-</button>
                        <input type="number" id="quantity" name="quantity" min="1" max="<?= $product->stock_quantity ?>" value="1">
                        <button type="button" class="qty-btn plus">+</button>
                    </div>
                </div>
                <button type="submit" class="add-to-cart-btn">Add to Cart</button>
            </form>



        </div>
    </div>

    <?php include '../_foot.php'; ?>