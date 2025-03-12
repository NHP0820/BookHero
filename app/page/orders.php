<?php
require '../_base.php';
//-----------------------------------------------------------------------------

$arr = $_db->query('SELECT * FROM product')->fetchAll();


$_SESSION['user_id'] = 1;
$user_id = $_SESSION['user_id'];


include '../_head.php';
?>
<link rel="stylesheet" href="/css/orders.css">
</head>



<?php
$orderlist = $_db->prepare('SELECT 
    o.order_id, 
    o.user_id, 
    o.order_date, 
    o.total_amount, 
    o.status_id, 
    o.address_id, 
    od.order_detail_id, 
    od.product_id, 
    p.name AS product_name,
    p.description AS product_description,
    p.price AS product_price,
    p.stock_quantity AS product_stock_quantity,
    p.category_id AS product_category_id,
    (SELECT pp.product_photo FROM product_photo pp WHERE pp.product_id = p.product_id LIMIT 1) AS product_photo,
    od.quantity, 
    od.price_at_purchase, 
    od.voucher_id
FROM `order` o
JOIN order_detail od ON o.order_id = od.order_id
JOIN product p ON od.product_id = p.product_id
WHERE o.user_id = ? AND o.status_id = 1;');
$orderlist->execute([$user_id]);

?>



<body>
    <nav class="order-nav">
        <button onclick="showTable('pending-payment', this)" class="active">Pending Payment</button>
        <button onclick="showTable('pending-delivery', this)">Pending Delivery</button>
        <button onclick="showTable('done', this)">Done</button>
    </nav>
    <div id="pending-payment" class="order-table">
        <?php
        $groupedOrders = [];
        foreach ($orderlist as $orderItem) {
            $groupedOrders[$orderItem->order_id][] = $orderItem;
        }
        ?>

        <?php foreach ($groupedOrders as $orderId => $products): ?>
            <?php
            $totalAmount = array_sum(array_map(function ($product) {
                return $product->price_at_purchase;
            }, $products));
            ?>
            <details class="order-dropdown">
                <summary style="display: flex; justify-content: space-between;">
                    <span>Order No : <?= $orderId ?></span>
                    <span class="total-amount">Total: RM<?= $totalAmount ?></span>
                    <a href="payment.php?order_id=<?= $orderId ?>" class="pay-button">Pay</a>
                </summary>
                <div class="order-products">
                    <?php
                    $address = $_db->prepare('SELECT * FROM address WHERE address_id = ?');
                    $address->execute([$orderItem->product_id]);
                    $address_name = $address->fetch(PDO::FETCH_ASSOC);

                    ?>
                    <?= "Delivery To " . $address_name['street'] . ' ' . $address_name['city'] . ' ' . $address_name['state'] . ' ' . $address_name['zip_code'] . ' ' . $address_name['country'] ?>
                    <?php foreach ($products as $orderItem): ?>

                        <div class="product-item">
                            <img src="data:image/jpeg;base64,<?= base64_encode($orderItem->product_photo) ?>" width="180px" alt="Product Image">
                            <div class="product-details">
                                <h3><?= $orderItem->product_name ?></h3>
                                <p><?= $orderItem->product_description ?></p>
                                <p>x <?= $orderItem->quantity ?></p>
                                <?php
                                $productList = $_db->prepare('SELECT price FROM product WHERE product_id = ?');
                                $productList->execute([$orderItem->product_id]);
                                $product = $productList->fetch(PDO::FETCH_ASSOC);
                                ?>
                                <p class="price-container">
                                    <span class="price">
                                        <del><?php if ($product['price'] != $orderItem->price_at_purchase) echo "RM " . $product['price']; ?></del>
                                        RM<?= $orderItem->price_at_purchase ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>



                <div class="order-total">Total: RM<?= $totalAmount ?></div>
            </details>
        <?php endforeach; ?>
    </div>








    <?php
$orderlist = $_db->prepare('SELECT 
    o.order_id, 
    o.user_id, 
    o.order_date, 
    o.total_amount, 
    o.status_id, 
    o.address_id, 
    od.order_detail_id, 
    od.product_id, 
    p.name AS product_name,
    p.description AS product_description,
    p.price AS product_price,
    p.stock_quantity AS product_stock_quantity,
    p.category_id AS product_category_id,
    (SELECT pp.product_photo FROM product_photo pp WHERE pp.product_id = p.product_id LIMIT 1) AS product_photo,
    od.quantity, 
    od.price_at_purchase, 
    od.voucher_id
FROM `order` o
JOIN order_detail od ON o.order_id = od.order_id
JOIN product p ON od.product_id = p.product_id
WHERE o.user_id = ? AND o.status_id = 2;');
$orderlist->execute([$user_id]);

?>




    <div id="pending-delivery" class="order-table">
        <?php
        $groupedOrders = [];
        foreach ($orderlist as $orderItem) {
            $groupedOrders[$orderItem->order_id][] = $orderItem;
        }
        ?>

        <?php foreach ($groupedOrders as $orderId => $products): ?>
            <?php
            $totalAmount = array_sum(array_map(function ($product) {
                return $product->price_at_purchase;
            }, $products));
            ?>
            <details class="order-dropdown">
                <summary style="display: flex; justify-content: space-between;">
                    <span>Order No : <?= $orderId ?></span>
                    <span class="total-amount">Total: RM<?= $totalAmount ?></span>
                    <a href="payment.php?order_id=<?= $orderId ?>" class="pay-button">Pay</a>
                </summary>
                <div class="order-products">
                    <?php
                    $address = $_db->prepare('SELECT * FROM address WHERE address_id = ?');
                    $address->execute([$orderItem->product_id]);
                    $address_name = $address->fetch(PDO::FETCH_ASSOC);

                    ?>
                    <?= "Delivery To " . $address_name['street'] . ' ' . $address_name['city'] . ' ' . $address_name['state'] . ' ' . $address_name['zip_code'] . ' ' . $address_name['country'] ?>
                    <?php foreach ($products as $orderItem): ?>

                        <div class="product-item">
                            <img src="data:image/jpeg;base64,<?= base64_encode($orderItem->product_photo) ?>" width="180px" alt="Product Image">
                            <div class="product-details">
                                <h3><?= $orderItem->product_name ?></h3>
                                <p><?= $orderItem->product_description ?></p>
                                <p>x <?= $orderItem->quantity ?></p>
                                <?php
                                $productList = $_db->prepare('SELECT price FROM product WHERE product_id = ?');
                                $productList->execute([$orderItem->product_id]);
                                $product = $productList->fetch(PDO::FETCH_ASSOC);
                                ?>
                                <p class="price-container">
                                    <span class="price">
                                        <del><?php if ($product['price'] != $orderItem->price_at_purchase) echo "RM " . $product['price']; ?></del>
                                        RM<?= $orderItem->price_at_purchase ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>



                <div class="order-total">Total: RM<?= $totalAmount ?></div>
            </details>
        <?php endforeach; ?>
    </div>




   




    <?php
$orderlist = $_db->prepare('SELECT 
    o.order_id, 
    o.user_id, 
    o.order_date, 
    o.total_amount, 
    o.status_id, 
    o.address_id, 
    od.order_detail_id, 
    od.product_id, 
    p.name AS product_name,
    p.description AS product_description,
    p.price AS product_price,
    p.stock_quantity AS product_stock_quantity,
    p.category_id AS product_category_id,
    (SELECT pp.product_photo FROM product_photo pp WHERE pp.product_id = p.product_id LIMIT 1) AS product_photo,
    od.quantity, 
    od.price_at_purchase, 
    od.voucher_id
FROM `order` o
JOIN order_detail od ON o.order_id = od.order_id
JOIN product p ON od.product_id = p.product_id
WHERE o.user_id = ? AND o.status_id = 3;');
$orderlist->execute([$user_id]);

?>



    <div id="done" class="order-table">
        <?php
        $groupedOrders = [];
        foreach ($orderlist as $orderItem) {
            $groupedOrders[$orderItem->order_id][] = $orderItem;
        }
        ?>

        <?php foreach ($groupedOrders as $orderId => $products): ?>
            <?php
            $totalAmount = array_sum(array_map(function ($product) {
                return $product->price_at_purchase;
            }, $products));
            ?>
            <details class="order-dropdown">
                <summary style="display: flex; justify-content: space-between;">
                    <span>Order No : <?= $orderId ?></span>
                    <span class="total-amount">Total: RM<?= $totalAmount ?></span>
                    <a href="payment.php?order_id=<?= $orderId ?>" class="pay-button">Pay</a>
                </summary>
                <div class="order-products">
                    <?php
                    $address = $_db->prepare('SELECT * FROM address WHERE address_id = ?');
                    $address->execute([$orderItem->product_id]);
                    $address_name = $address->fetch(PDO::FETCH_ASSOC);

                    ?>
                    <?= "Delivery To " . $address_name['street'] . ' ' . $address_name['city'] . ' ' . $address_name['state'] . ' ' . $address_name['zip_code'] . ' ' . $address_name['country'] ?>
                    <?php foreach ($products as $orderItem): ?>

                        <div class="product-item">
                            <img src="data:image/jpeg;base64,<?= base64_encode($orderItem->product_photo) ?>" width="180px" alt="Product Image">
                            <div class="product-details">
                                <h3><?= $orderItem->product_name ?></h3>
                                <p><?= $orderItem->product_description ?></p>
                                <p>x <?= $orderItem->quantity ?></p>
                                <?php
                                $productList = $_db->prepare('SELECT price FROM product WHERE product_id = ?');
                                $productList->execute([$orderItem->product_id]);
                                $product = $productList->fetch(PDO::FETCH_ASSOC);
                                ?>
                                <p class="price-container">
                                    <span class="price">
                                        <del><?php if ($product['price'] != $orderItem->price_at_purchase) echo "RM " . $product['price']; ?></del>
                                        RM<?= $orderItem->price_at_purchase ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>



                <div class="order-total">Total: RM<?= $totalAmount ?></div>
            </details>
        <?php endforeach; ?>
    </div>


    <style>
        .order-table {
            display: none;
        }
    </style>

    <script>
        function showTable(tableId, button) {
            document.querySelectorAll('.order-table').forEach(table => {
                table.style.display = 'none';
            });
            document.getElementById(tableId).style.display = 'block';

            document.querySelectorAll('.order-nav button').forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');
        }

        // 默认显示第一个表格
        showTable('pending-payment', document.querySelector('.order-nav button'));
    </script>


    <?php
    include "../_foot.php";
