<?php
include '../../_base.php';

$user_id = $_SESSION['user']['id'] ?? null;
$user_role = $_SESSION['user']['role'] ?? null;
if (!$user_id || $user_role != 'admin') {
    temp('info', 'Please login first');
    redirect("../staffLogin.php");
    exit;
}

// ----------------------------------------------------------------------------

// Get category filter if exists
$category = req('category');

// Build query with correct column names
$query = "
    SELECT p.*, pp.product_photo, 
           GROUP_CONCAT(c.name SEPARATOR ', ') AS categories,
           GROUP_CONCAT(c.category SEPARATOR ',') AS category_ids
    FROM product p
    LEFT JOIN product_photo pp ON p.product_id = pp.product_id
    LEFT JOIN category_product cp ON p.product_id = cp.product_id
    LEFT JOIN category c ON cp.category_id = c.category  /* Changed to category_id */
";

$params = [];

if ($category) {
    $query .= " WHERE cp.category_id = ?";  /* Changed to category_id */
    $params[] = $category;
}

$query .= " GROUP BY p.product_id ORDER BY p.name";

$stm = $_db->prepare($query);
$stm->execute($params);
$arr = $stm->fetchAll();

// Get category name for heading if filtered
$categoryName = '';
if ($category) {
    $stm = $_db->prepare('SELECT name FROM category WHERE category = ?');
    $stm->execute([$category]);
    $categoryName = $stm->fetchColumn();
}

// ----------------------------------------------------------------------------

$_title = 'Product | Index';
include 'C:\xampp\htdocs\dashboard\bookHero\app\_staffHead.php';

?>

<!-- Rest of your HTML remains exactly the same -->

<style>
    .popup {
        width: 100px;
        height: 100px;
    }
    .category-badge {
        display: inline-block;
        background: #eee;
        padding: 2px 5px;
        margin: 2px;
        border-radius: 3px;
        font-size: 0.8em;
    }
    .action-buttons {
        white-space: nowrap;
    }

    .btnp {
    display: inline-block;
    margin-top: 15px;
    padding: 10px 15px;
    background-color: #007bff;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    transition: background 0.3s ease;
}
</style>

<p>
    <button class="btnp" data-get="../productlist/insert.php">Insert Product</button>
    <button class="btnp" class="" data-get="/page/category/category.php">Manage Categories</button>
    <?php if ($category): ?>
        <button data-get="../product/">View All Products</button>
    <?php endif; ?>
</p>

<?php if ($categoryName): ?>
    <h2>Products in Category: <?= $categoryName ?></h2>
<?php endif; ?>

<p><?= count($arr) ?> record(s)</p>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Author</th>
            <th>Description</th>
            <th>Price (RM)</th>
            <th>Stock</th>
            <th>Categories</th>
            <th>Photo</th>
            <th class="action-buttons">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($arr as $p): ?>
        <tr>
            <td><?= $p->product_id ?></td>
            <td><?= $p->name ?></td>
            <td><?= $p->author ?></td>
            <td><?= substr($p->description, 0, 50) . (strlen($p->description) > 50 ? '...' : '') ?></td>
            <td><?= number_format($p->price, 2) ?></td>
            <td><?= $p->stock_quantity ?></td>
            <td>
                <?php 
                $categoryNames = explode(', ', $p->categories);
                $categoryIds = explode(',', $p->category_ids);
                foreach ($categoryNames as $index => $name): 
                ?>
                    <span class="category-badge" title="ID: <?= $categoryIds[$index] ?>">
                        <?= $name ?>
                    </span>
                <?php endforeach ?>
            </td>
            <td>
                <?php if ($p->product_photo): ?>
                    <img src="../../images/<?= $p->product_photo ?>" class="popup">
                <?php else: ?>
                    <span class="text-muted">No image</span>
                <?php endif; ?>
            </td>
            <td class="action-buttons">
                <button class="btnp" data-get="update.php?id=<?= $p->product_id ?>">Update</button>
                <button class="btnp" data-post="../productlist/delete.php?id=<?= $p->product_id ?>" 
                        data-confirm="Delete this product?" class="btn-sm btn-danger">Delete</button>
            </td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>

<script>
$(document).ready(function() {
    // Enhance the table with DataTables if needed
    $('.table').DataTable({
        responsive: true,
        columnDefs: [
            { responsivePriority: 1, targets: 0 }, // ID
            { responsivePriority: 2, targets: 1 }, // Name
            { responsivePriority: 3, targets: -1 } // Actions
        ]
    });
});
</script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>

<?php
include '../../_foot.php';