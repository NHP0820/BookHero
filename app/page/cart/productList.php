<?php
require '../_base.php';

// Handle the POST request if updating the cart
if (is_post()) {
    $id   = req('id');
    $unit = req('unit');
    update_cart($id, $unit);
    redirect();
}

// Query to fetch all products from the database
$stmt = $_db->prepare('SELECT * FROM product');
$stmt->execute();

// Fetch all products
$_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

$_title = 'Books';
include '../_head.php';
?>

<h1><?php echo $_title; ?></h1>

<style>
    #products {
        display: flex;
        gap: 100px;
        flex-wrap: wrap;
    }

    .product {
        border: 1px solid #000;
        width: 250px;
        height: 250px;
        position: relative;
    }

    .product img {
        display: block;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    .product form,
    .product div {
        position: absolute;
        background: #0009;
        color: #fff;
        padding: 5px;
        text-align: center;
    }

    .product form {
        inset: 0 0 auto auto;
    }

    .product div {
        inset: auto 0 0 0;
    }
</style>

<div id="products">
    <?php foreach ($_arr as $p): ?>
        <div class="product">
            <!-- Wrap the image in an <a> tag that points to the product details page -->
            <a href="/cart/detail.php?id=<?= $p['id'] ?>">
                <img src="/products/<?= htmlspecialchars($p['photo']) ?>" 
                     alt="<?= htmlspecialchars($p['name']) ?>" />
            </a>
            
            <!-- Displaying the product name and price -->
            <div><?= htmlspecialchars($p['name']) ?> | RM <?= number_format($p['price'], 2) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<?php
include '../_foot.php';
?>
