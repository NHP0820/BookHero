<?php
require '../_base.php';

if (is_post()) {
    $id   = req('id');
    $unit = req('unit');
    update_cart($id, $unit);
    //temp('info', 'Item has been added to your cart');
    redirect();
}

$id  = req('id');
$stm = $_db->prepare('SELECT * FROM product WHERE product_id = ?');
$stm->execute([$id]);
$p = $stm->fetch();
if (!$p) redirect('productList.php');

$_title = 'Product Details';
include '../_head.php';

?>

<style>

#photo {
        display: block;
        border: 5px solid #000;
        width: 200px;
        height: 200px;
    }
</style>

<p>
    <img src="/products/<?= $p->photo ?>" id="photo">
</p>

<table class="table detail">
    <tr>
        <th>Id</th>
        <td><?= $p->product_id ?></td>
    </tr>
    <tr>
        <th>Name</th>
        <td><?= $p->name ?></td>
    </tr>
    <tr>
        <th>Price</th>
        <td>RM <?= $p->price ?></td>
    </tr>
    <tr>
        <th>Description</th>
        <td><?= $p->description ?></td>
    </tr>
    <tr>
        <th>Quantity</th>
        <td>
            <?php
            $cart = get_cart();
            $id   = $p->id;
            $unit = $cart[$p->id] ?? 0;
            ?>
            <form method="post">
                <?= html_hidden('id') ?> 
                <?= html_select('unit', $_units, '') ?>
                <?= $unit ? '✔️' : '' ?> 

            </form>
        </td>
    </tr>
</table>

<p>
    <button data-get="productList.php">Confirm</button>
</p>

<script>
    $('select').on('change', e => e.target.form.submit());
</script>

<?php
include '../_foot.php';