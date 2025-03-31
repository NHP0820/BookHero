<?php
require '../_base.php';

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id === 0) {
    die("Invalid product ID.");
}

$stmt = $_db->prepare("SELECT * FROM product WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_OBJ);

$photoStmt = $_db->prepare("SELECT * FROM product_photo WHERE product_id = ?");
$photoStmt->execute([$product_id]);
$photos = $photoStmt->fetchAll(PDO::FETCH_OBJ);

$categoryStmt = $_db->prepare("
    SELECT c.name FROM category c
    INNER JOIN category_product cp ON c.category = cp.category_id
    WHERE cp.product_id = ?
");
$categoryStmt->execute([$product_id]);
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

include '../_head.php';
?>
<link rel="stylesheet" href="/css/productProfile.css">
<script>
    function changeMainImage(src) {
        document.getElementById("mainImage").src = src;
    }

    function toggleWishlist(button, productId) {
        button.classList.toggle("wishlisted");
        let isWishlisted = button.classList.contains("wishlisted");
        localStorage.setItem("wishlist_" + productId, isWishlisted ? "true" : "false");
    }

    document.addEventListener("DOMContentLoaded", function () {
        let wishlistBtn = document.getElementById("wishlist-btn");
        let productId = wishlistBtn.getAttribute("data-product-id");
        if (localStorage.getItem("wishlist_" + productId) === "true") {
            wishlistBtn.classList.add("wishlisted");
        }
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
        <h1><?= htmlspecialchars($product->name) ?>
            <button id="wishlist-btn" class="wishlist-btn" data-product-id="<?= $product->product_id ?>" onclick="toggleWishlist(this, <?= $product->product_id ?>)">
                <i class="fa fa-heart"></i>
            </button>
        </h1>
        <p class="price">RM<?= number_format($product->price, 2) ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($product->description) ?></p>
        <p><strong>Stock Quantity:</strong> <?= htmlspecialchars($product->stock_quantity) ?></p>
        <p><strong>Categories:</strong> <?= !empty($categories) ? implode(", ", $categories) : "Uncategorized" ?></p>

        <a href="../index.php" class="back-button">Back to Products</a>
    </div>
</div>

<?php include '../_foot.php'; ?>
