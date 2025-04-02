<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

$arr = $_db->query('SELECT * FROM category')->fetchAll();

// ----------------------------------------------------------------------------

$_title = 'Category | Index';
include '../_head.php';
?>

<p>
    <button data-get="cinsert.php">Insert</button>
    <button data-get="../product/">View All Products</button>
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
            <button class="cbtn" data-get="cupdate.php?category=<?= $c->category ?>">Update</button>  <!-- Changed param name -->
            <button class="view-products" data-get="../product/?category=<?= $c->category ?>">View Products</button>  <!-- Changed param name -->
        </td>
    </tr>
    <?php endforeach ?>
</table>

<?php
include '../_foot.php';