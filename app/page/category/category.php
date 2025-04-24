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
        <td><?= $c->category ?></td>
        <td><?= $c->name ?></td>    
        <td>
            <button class="btnp" data-get="cupdate.php?category=<?= $c->category ?>">Update</button> 
            <button class="btnp" data-get="../productlist/?category=<?= $c->category ?>">View Products</button>  
        </td>
    </tr>
    <?php endforeach ?>
</table>

<?php
include '../../_foot.php';