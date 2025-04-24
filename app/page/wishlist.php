<?php
require '../_base.php';

$user_id = $_SESSION['user']['id'] ?? null;
if (!$user_id) {
    redirect("login.php");
    exit;
}

$stmt = $_db->prepare("
    SELECT p.*, pp.product_photo
    FROM wishlist w
    JOIN product p ON p.product_id = w.product_id
    LEFT JOIN (
        SELECT product_id, MIN(product_photo) AS product_photo
        FROM product_photo
        GROUP BY product_id
    ) pp ON p.product_id = pp.product_id
    WHERE w.user_id = ?
");
$stmt->execute([$user_id]);
$wishlistItems = $stmt->fetchAll(PDO::FETCH_OBJ);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wishlist_action'])) {
    $action = $_POST['wishlist_action'];
    $product_id = intval($_POST['product_id']);

    if ($action === 'remove' && $product_id) {
        $stmt = $_db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        temp('info', 'Item removed from wishlist.');
        redirect("wishlist.php");
        exit;
    }
}

include '../_head.php';
?>

<link rel="stylesheet" href="/css/wishlist.css">

<div class="wishlist-container">
    <h1>Your Wishlist</h1>

    <?php if (empty($wishlistItems)): ?>
        <p style="text-align: center;">Your wishlist is empty.</p>
    <?php else: ?>
        <div class="wishlist-grid">
            <?php foreach ($wishlistItems as $item): ?>
                <div class="wishlist-card">
                    <a href="/page/productProfile.php?product_id=<?= $item->product_id ?>">
                        <img src="/images/<?= htmlspecialchars($item->product_photo ?? 'default.png') ?>" alt="<?= htmlspecialchars($item->name) ?>">
                    </a>
                    <div class="wishlist-details">
                        <h3><?= htmlspecialchars($item->name) ?></h3>
                        <p class="price">RM<?= number_format($item->price, 2) ?></p>
                        <form method="post" action="/page/wishlist.php?product_id=<?= $item->product_id ?>">
                            <input type="hidden" name="wishlist_action" value="remove">
                            <input type="hidden" name="product_id" value="<?= $item->product_id ?>">
                            <button type="submit" class="remove-btn">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../_foot.php'; ?>
