<?php
$cartCount = 0;
$cart_id = null;

if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    
    // Fetch cart_id associated with the user
    $stmt = $_db->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_id = $stmt->fetchColumn();

    // If a cart exists for the user, count the items in it
    if ($cart_id) {
        $stmt = $_db->prepare("SELECT COUNT(*) FROM cart_item WHERE cart_id = ?");
        $stmt->execute([$cart_id]);
        $cartCount = $stmt->fetchColumn();
    }
}

$wishlistCount = 0;

if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    
    // Fetch count of items in the wishlist for the user
    $stmt = $_db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $wishlistCount = $stmt->fetchColumn();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'BookHero' ?></title>
    <link rel="shortcut icon" href="/images/bookHero_logo.png">
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/js/app.js"></script>
</head>
<body>
    <!-- Flash message -->
    <div id="info"><?= temp('info') ?></div>

    <header>
        <h1><a href="/">BookHero</a></h1>

        <div class="dropdown">
            <?php if (!isset($_SESSION['user'])):?>
                <button class="dropbtn">GUEST</button>
                <div class="dropdown-content">
                    <a href="/page/login.php">Login</a>
                </div>
            <?php else: ?>
                <button class="dropbtn"><?= htmlspecialchars($_SESSION['user']['username']) ?></button>
                <div class="dropdown-content">
                    <a href="/page/memberProfile.php">Profile</a>
                    <a href="/page/orders.php">Orders</a>
                    <a href="/page/wishlist.php">Whishlist (<?= $wishlistCount ?>)</a>
                    <a href="/page/logout.php">Logout</a>
                </div>
            <?php endif;?>

        </div>
    </header>

    <nav>
        <div class="nav-links">
            <a href="/">Home Page</a>
            
            <?php if (isset($_SESSION['user'])): ?>
                <a href="/page/cart/shoppingCart.php">Cart (<?= $cartCount ?>)</a>
            <?php else: ?>
                <a href="/page/login.php">Cart</a>
            <?php endif; ?>
                
            <a href="/page/storeLocation.php">Our Store </a> 
        
        </div>
        


    </nav>
<main>