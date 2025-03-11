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
            <?php 
            if (!isset($_SESSION['user'])):
            ?>
                <button class="dropbtn">GUEST</button>
                <div class="dropdown-content">
                    <a href="/page/login.php">Login</a>
                </div>
            <?php else: ?>
                <button class="dropbtn"><?= htmlspecialchars($_SESSION['user']['username']) ?></button>
                <div class="dropdown-content"></div>
                    <a href="#">Profile</a>
                    <a href="#">Orders</a>
                    <a href="#">Whishlist</a>
                    <a href="#">Logout</a>
                </div>
            <?php endif;?>

            <button class="dropbtn">Menu</button>
            <div class="dropdown-content">
                <a href="#">Login</a>
                <a href="#">Profile</a>
                <a href="/orders.php">Orders</a>
                <a href="#">Whishlist</a>
                <a href="#">Logout</a>
            </div>

        </div>
    </header>

    <nav>
        <div class="nav-links">
            <a href="/">Home Page</a>

            <div class="dropdown"><a href="/">Category</a>
                <?php $categoryArr = $_db->query('SELECT * FROM category')->fetchAll(); ?>
                <div class="dropdown-category">
                    <?php foreach ($categoryArr as $category): ?>
                        <a href="/"><?= $category->name ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="/"><i class="fa fa-shopping-cart" aria-hidden="true"></i></a>
        </div>
        <?php if (basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
            <div class="topnav">
                <div class="search-container">
                    <form action="/action_page.php">
                    <input type="text" placeholder="Search.." name="search">
                    <button type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

            <div class="dropdown"><a href="/">
                <div class="dropdown-category">
                    <?php $categoryArr = $_db->query('SELECT * FROM category')->fetchAll(); ?>
                    <?php foreach ($categoryArr as $category): ?>
                        <a href="/"><?= $category->name ?></a>
                    <?php endforeach; ?>
                </div></a>
            </div>
            <a href="/">Cart</a>
        </div>
        <div class="topnav">
            <div class="search-container">
                <form action="/action_page.php">
                <input type="text" placeholder="Search.." name="search">
                <button type="submit"><i class="fa fa-search"></i></button>
                </form>
            </div>
        </div>

    </nav>

    <main>