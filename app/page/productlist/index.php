<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

$arr = $_db->query('
    SELECT p.*, pp.product_photo 
    FROM product p
    LEFT JOIN product_photo pp ON p.product_id = pp.product_id
')->fetchAll();

// ----------------------------------------------------------------------------

$_title = 'Product | Index';
include '../../_staffHead.php';
?>

<style>
    .popup {
        width: 100px;
        height: 100px;
    }
</style>

<p>
    <button data-get="insert.php">Insert</button>
</p>

<p><?= count($arr) ?> record(s)</p>

<table class="table">
    <tr>
        <th>Id</th>
        <th>Name</th>
        <th>Description</th>
        <th>Price</th>
        <th>Stock Quantity</th>
        <th>Photo</th>
        <th></th>
    </tr>

    <?php foreach ($arr as $p): ?>
        <tr>
            <td><?= $p->product_id ?></td>
            <td><?= $p->name ?></td>
            <td><?= substr($p->description, 0, 50) . (strlen($p->description) > 50 ? '...' : '') ?></td>
            <td><?= $p->price ?></td>
            <td><?= $p->stock_quantity ?></td>
            <td>
                <?php if ($p->product_photo): ?>
                    <img src="../../images/<?= $p->product_photo ?>" class="popup">
                <?php endif; ?>
            </td>
            <td>
                <button data-get="update.php?id=<?= $p->product_id ?>">Update</button>
                <button data-post="delete.php?id=<?= $p->product_id ?>" data-confirm="Delete this product?">Delete</button>
            </td>
        </tr>
    <?php endforeach ?>
</table>

<?php
include '../../_foot.php';
