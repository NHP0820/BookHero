<?php
require '_base.php';

$name = req('search');
$page = req('page', 1);
$selectedCategories = isset($_GET['category_id']) ? (array) $_GET['category_id'] : [];

require_once 'lib/SimplePager.php';

if (!empty($selectedCategories)) {
    $placeholders = implode(',', array_fill(0, count($selectedCategories), '?'));
    $sql = "
        SELECT DISTINCT p.*
        FROM product p
        INNER JOIN category_product cp ON p.product_id = cp.product_id
        WHERE cp.category_id IN ($placeholders)
        AND (p.name LIKE ? OR p.author LIKE ? OR p.description LIKE ?)
    ";
    $params = array_merge($selectedCategories, ["%$name%", "%$name%", "%$name%"]);
} else {
    $sql = 'SELECT * FROM product WHERE name LIKE ? OR author LIKE ? OR description LIKE ?';
    $params = ["%$name%", "%$name%", "%$name%"];
}

// Apply pagination
$p = new SimplePager($sql, $params, 9, $page);
$arr = $p->result;

include '_head.php';
?>

</head>
<body>

<div class="hero-image">
  <div class="hero-text">
    <h1 style="font-size:50px">All Books You Need</h1>
    <p>An online book store</p>
    <a href="#topnav" style="scroll-behavior: smooth;"><button>Select some</button></a>
  </div>
</div>

<div class="topnav" id="topnav">
    <p>
        <?= $p->count ?> of <?= $p->item_count ?> record(s) |
        Page <?= $p->page ?> of <?= $p->page_count ?>
    </p>
    <div class="search-container">
        <form method="get">
            <?= html_search('search') ?>
            <button type="submit"><i class="fa fa-search"></i></button>
        </form>
    </div>
</div>

<div class="content-wrapper">
    <!-- Sidebar Category List -->
    <div class="filterCategory">
        <h2>Category</h2>
        <form method="GET" id="categoryForm">
            <div class="categoryList">
                <?php
                $categoryArr = $_db->query('SELECT * FROM category')->fetchAll(PDO::FETCH_OBJ);
                $selectedCategories = isset($_GET['category_id']) ? (array) $_GET['category_id'] : [];

                foreach ($categoryArr as $category): ?>
                    <label>
                        <input type="checkbox" name="category_id[]" value="<?= htmlspecialchars($category->category) ?>"
                            <?= in_array($category->category, $selectedCategories) ? 'checked' : '' ?>
                            onchange="document.getElementById('categoryForm').submit();">
                        <?= htmlspecialchars($category->name) ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
        </form>
    </div>

    <div class="responsive">
        <?php if (empty($arr)): ?>
            <p>No products available.</p>
        <?php else: ?>
            <?php foreach ($arr as $product): ?>    
                <div class="gallery" id="gallery">
                    <div class="desc" style="display: none;">
                        <?= htmlspecialchars($product->product_id) ?>
                    </div>

                    <?php
                    $stmt = $_db->prepare('SELECT * FROM product_photo WHERE product_id = ? LIMIT 1');
                    $stmt->execute([$product->product_id]);
                    $photos = $stmt->fetchAll(PDO::FETCH_OBJ);
                    ?>

                    <?php if (empty($photos)): ?>
                        <img src="/images/default.png" 
                            alt="Default Image" 
                            onerror="this.onerror=null; this.src='/images/default.png';">
                    <?php else: ?>
                        <?php foreach ($photos as $productPhoto): ?>
                            <a href="../page/productProfile.php?product_id=<?= htmlspecialchars($product->product_id) ?>">
                                <img src="/images/<?= htmlspecialchars($productPhoto->product_photo) ?>" 
                                    alt="Product Image" 
                                    onerror="this.onerror=null; this.src='/images/default.png';">
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Pagination -->
<div style="display: flex; justify-content: center;">
    <?= $p->html() ?>
</div>

<?php include "_foot.php"; ?>
