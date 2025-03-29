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
        <h1><a href="../../staffIndex.php">BookHero</a></h1>

        <div class="dropdown">
            <?php if (!isset($_SESSION['user'])):?>
                <button class="dropbtn">GUEST</button>
                <div class="dropdown-content">
                    <a href="/page/login.php">Login</a>
                </div>
            <?php else: ?>
                <button class="dropbtn"><?= htmlspecialchars($_SESSION['user']['username']) ?></button>
                <div class="dropdown-content">
                    <a href="#">Profile</a>
                    <a href="/page/logout.php">Logout</a>
                </div>
            <?php endif;?>

        </div>
    </header>

    <nav>
        <div class="nav-links">
            <a href="../../staffIndex.php">Home Page</a>
            <a href="/page/productlist/index.php">Maintain Product</a>            
        </div>
    </nav>

   