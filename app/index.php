<?php
require '_base.php';

checkRememberMe($_db);

// 获取用户请求参数
$name = req('search');
$page = req('page', 1);
$selectedCategories = isset($_GET['category_id']) ? (array) $_GET['category_id'] : [];
$sort = req('sort');

// 排序处理
$orderBy = '';
switch ($sort) {
    case 'name_asc':
        $orderBy = ' ORDER BY p.name ASC';
        break;
    case 'name_desc':
        $orderBy = ' ORDER BY p.name DESC';
        break;
}

// 获取所有分类数量
$categoryCount = $_db->query("SELECT COUNT(*) FROM category")->fetchColumn();
$selectAllCategories = count($selectedCategories) == $categoryCount;
$almostSelectAll = count($selectedCategories) >= ($categoryCount - 1); // ✅ 新增：几乎全选

require_once 'lib/SimplePager.php';

// 开始组装 SQL
$params = [];
$sql = "SELECT p.* FROM product p";

$where = [];

if (!empty($selectedCategories) && !$selectAllCategories && !$almostSelectAll) {
    // ✅ 只在明显选了少数分类时，加 EXISTS 条件
    $placeholders = implode(',', array_fill(0, count($selectedCategories), '?'));
    $where[] = "EXISTS (
                    SELECT 1 FROM category_product cp
                    WHERE cp.product_id = p.product_id
                      AND cp.category_id IN ($placeholders)
                )";
    $params = array_merge($params, $selectedCategories);
}

if ($name !== '') {
    $where[] = "(p.name LIKE ? OR p.author LIKE ? OR p.description LIKE ?)";
    $params = array_merge($params, ["%$name%", "%$name%", "%$name%"]);
}

// 最后拼接 WHERE
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= $orderBy;

// 把当前GET参数变成 href string
$query = $_GET;
unset($query['page']); // 先移除旧的 page
$href = http_build_query($query);


// 分页处理
$p = new SimplePager($sql, $params, 9, $page);
$arr = $p->result;

include '_head.php';
?>

</head>
<body>

<!-- Hero Section -->
<div class="hero-image">
  <div class="hero-text">
    <h1 style="font-size:50px">All Books You Need</h1>
    <p>An online book store</p>
    <a href="#topnav" style="scroll-behavior: smooth;"><button>Select some</button></a>
  </div>
</div>

<!-- Top Navigation -->
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

<!-- Sort -->
<div style="margin: 10px 0;">
    <strong>Sort by:</strong>
    <?php
    function makeSortUrl() {
        $query = $_GET;
        $currentSort = req('sort');

        if ($currentSort === 'name_asc') {
            $query['sort'] = 'name_desc';
        } else {
            $query['sort'] = 'name_asc';
        }

        return '?' . http_build_query($query);
    }
    ?>
    <a href="<?= makeSortUrl() ?>"
       style="<?= (req('sort') === 'name_asc' || req('sort') === 'name_desc') ? 'font-weight:bold;' : '' ?>">
        <?= req('sort') === 'name_desc' ? 'Z-A' : 'A-Z' ?>
    </a>
</div>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Sidebar: Category Filter -->
    <div class="filterCategory">
        <h2>Category</h2>
        <form method="GET" id="categoryForm">
            <div class="categoryList">
                <?php
                $categoryArr = $_db->query('SELECT * FROM category')->fetchAll(PDO::FETCH_OBJ);
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

    <!-- Product List -->
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
                        <img src="/images/default.png" alt="Default Image" onerror="this.onerror=null; this.src='/images/default.png';">
                    <?php else: ?>
                        <?php foreach ($photos as $productPhoto): ?>
                            <a href="../page/productProfile.php?product_id=<?= htmlspecialchars($product->product_id) ?>">
                                <img src="/images/<?= htmlspecialchars($productPhoto->product_photo) ?>" alt="Product Image" onerror="this.onerror=null; this.src='/images/default.png';">
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
    <?= $p->html($href) ?>
</div>

<?php include "_foot.php"; ?>
