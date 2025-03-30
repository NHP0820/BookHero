<?php
require '_base.php';



//-----------------------------------------------------------------------------
$orderlist = $_db->prepare("
      SELECT 
    o.order_id, 
    u.username AS user_name, 
    o.order_date, 
    o.total_amount, 
    o.status_id, 
    o.expired_time, 
    GROUP_CONCAT(CONCAT(p.name, ' (x', od.quantity, ' RM', od.price_at_purchase, ')') SEPARATOR '<br>') AS order_details
FROM `order` o
JOIN user u ON o.user_id = u.user_id
JOIN order_detail od ON o.order_id = od.order_id
JOIN product p ON od.product_id = p.product_id
GROUP BY o.order_id;
");
$orderlist->execute();
$orders = $orderlist->fetchAll(PDO::FETCH_ASSOC);






include '_head.php';
?>

<link rel="stylesheet" href="/css/stafforder.css">
</head>
<?php
if (isset($_POST['mark_done'])) {
    $order_id = $_POST['order_id'];


    $updateOrder = $_db->prepare("UPDATE `order` SET status_id = 3 WHERE order_id = ?");
    $updateOrder->execute([$order_id]);


    header("Location: staffOrder.php");
    exit();
}

?>

<body>
<table class="order-table">
    <tr>
        <th>Order ID</th>
        <th>User</th>
        <th>Order Date</th>
        <th>Total Amount (RM)</th>
        <th>Status</th>
        <th>Expired Time</th>
        <th>Order Details</th>
        <th>Action</th>
    </tr>
    <?php foreach ($orders as $order) { ?>
    <tr>
        <td><?= $order['order_id'] ?></td>
        <td><?= $order['user_name'] ?></td>
        <td><?= $order['order_date'] ?></td>
        <td>RM<?= number_format($order['total_amount'], 2) ?></td>
        <td>
            <?php 
            if ($order['status_id'] == 1) {
                echo '<span class="status-pending">Pending Payment</span>';
            } elseif ($order['status_id'] == 2) {
                echo '<span class="status-delivery">Pending Delivery</span>';
            } elseif ($order['status_id'] == 3) {
                echo '<span class="status-done">Delivered</span>';
            } else {
                echo '<span class="status-cancelled">Cancelled</span>';
            }
            ?>
        </td>
        <td><?= $order['expired_time'] ?></td>
        <td><?= $order['order_details'] ?></td>
        <td>
            <?php if ($order['status_id'] == 2) { ?>
                <form method="post">
                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                    <button type="submit" name="mark_done" class="action-button btn-delivered">Mark as Delivered</button>
                </form>
            <?php } else { echo "N/A"; } ?>
        </td>
    </tr>
    <?php } ?>
</table>





</body>

<?php
include "_foot.php";
