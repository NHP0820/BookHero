<?php
require '../../_base.php';

$_title = 'Your Shopping Cart';
include '../../_head.php';
?>

<h1><?php echo $_title; ?></h1>

<table class="table">
    <tr>
        <th> ID </th>
        <th> Name </th>
        <th> Price </th>
        <th> Description </th>
        <th> Subtotal (RM) </th>
    </tr>

<?php
$count = 0;
$total = 0;

// Assuming $_db is a PDO object
$stm = $_db->prepare('SELECT 
                        ci.cart_item_id,
                        ci.product_id,
                        ci.quantity,
                        p.name,
                        p.price,
                        p.description,
                        p.photo
                    FROM 
                        `user` u
                    JOIN 
                        `cart` c ON u.user_id = c.user_id
                    JOIN 
                        `cart_item` ci ON c.cart_id = ci.cart_id
                    JOIN
                        `product` p ON ci.product_id = p.product_id
                    WHERE 
                        u.user_id = :user_id');

// Use bindParam with PDO
$stm->bindParam(':user_id', $user_id, PDO::PARAM_INT);

// Execute the statement
$stm->execute();

// Fetch the cart items (using fetchAll to get all results)
$result = $stm->fetchAll(PDO::FETCH_ASSOC);

$count = 0;
$total = 0;

// Loop through the cart items and display them in the table
foreach ($result as $p): 
    $subtotal = $p['price'] * $p['quantity'];
    $count += $p['quantity'];
    $total += $subtotal;
?>

<tr>
    <td><?= $p['product_id'] ?></td>
    <td><?= $p['name'] ?></td>
    <td class="right"><?= sprintf('%.2f', $p['price']) ?></td>
    <td><?= $p['description'] ?></td>
    <td class="right">
        <?= sprintf('%.2f', $subtotal) ?>
        <img src="/products/<?= $p['photo'] ?>" class="popup"> <!-- Display the product photo -->
    </td>
</tr>

<?php endforeach; ?>

<tr>
    <th colspan="3"></th>
    <th class="right"><?= $count ?></th>
    <th class="right"><?= sprintf('%.2f', $total) ?></th>
</tr>
</table>

<p>
    <?php if ($count > 0): ?>
        <button data-post="?btn=clear">Clear</button>

        <?php if ($_user?->role == 'Member'): ?>
            <button data-post="checkout.php">Checkout</button>
        <?php else: ?>
        <?php endif ?>
    <?php endif ?>
</p>

<script>
    $('select').on('change', e => e.target.form.submit());
</script>

<?php
include '../../_foot.php';
?>
