<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

$arr = $_db->query('SELECT * FROM category')->fetchAll();

// ----------------------------------------------------------------------------

$_title = 'Category | Index';
include 'C:\xampp\htdocs\dashboard\bookHero\app\_staffHead.php';
?>

<style>
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
    <button class="btnp" data-get="cinsert.php">Insert</button>
    <button class="btnp" data-get="../productlist/index.php">View All Products</button>
</p>

<table class="table">
    <tr>
        <th>Category ID</th>
        <th>Name</th>
        <th>Actions</th>
    </tr>
    
    <?php foreach ($arr as $c): ?>
    <tr>
        <td><?= $c->category ?></td>  <!-- Changed from category_id -->
        <td><?= $c->name ?></td>      <!-- Changed from category_name -->
        <td>
            <button class="btnp" data-get="cupdate.php?category=<?= $c->category ?>">Update</button>  <!-- Changed param name -->
            <button class="btnp" data-get="../?category=<?= $c->category ?>">View Products</button>  <!-- Changed param name -->
        </td>
    </tr>
    <?php endforeach ?>
</table>

<?php
include '../../_foot.php';