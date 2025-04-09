<?php
require '_base.php';

$fields = [
    'order_id'         => 'Orderid',
    'user_id'       => 'User',
    'order_date'     => 'Order Date',
    'total_amount' => 'Total Amount',
    'expired_time' => 'Completed Date'
    
];

$fields2 = [
    'status_id' => 'Status'

];

$allFields = $fields + $fields2;

$sort = req('sort');
key_exists($sort, $allFields) || $sort = 'order_id';


$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

// (2) Paging
$page = req('page', 1);

require_once 'lib/SimplePager.php';
$subQuery = "
    SELECT 
        o.order_id, 
        u.username AS user_name, 
        u.user_id,
        o.order_date, 
        o.total_amount, 
        o.status_id, 
        o.expired_time, 
        o.cancel_desc,
        GROUP_CONCAT(CONCAT(p.name, ' (x', od.quantity, ' RM', od.price_at_purchase, ')') SEPARATOR '<br>') AS order_details
    FROM `order` o
    JOIN user u ON o.user_id = u.user_id
    JOIN order_detail od ON o.order_id = od.order_id
    JOIN product p ON od.product_id = p.product_id
    GROUP BY o.order_id
";

$query = "SELECT * FROM ($subQuery) AS orders_with_details ORDER BY $sort $dir";

$p = new SimplePager($query, [], 5, $page);
$orders = $p->result;





include '_staffHead.php';
?>

<link rel="stylesheet" href="/css/stafforder.css">
<link rel="stylesheet" href="/css/app.css">
</head>
<?php
if (isset($_POST['mark_done'])) {
    $order_id = $_POST['order_id'];


    $updateOrder = $_db->prepare("UPDATE `order` SET status_id = 3 , expired_time = NOW() WHERE order_id = ?");
    $updateOrder->execute([$order_id]);


    header("Location: staffOrder.php");
    exit();
}

?>

<body>
    <h1 style="text-align: center;">
        Admin Order Management
    </h1>

<table class="order-table">
    <tr>
    <?= table_headers($fields, $sort, $dir, "page=$page") ?>
    <th>Order Details</th>
    <?= table_headers($fields2, $sort, $dir, "page=$page") ?>
    <th>Action</th>
    </tr>
    <?php foreach ($orders as $order) { ?>
    <tr>
        <td><?= $order->order_id ?></td>
        <td><?= $order->user_name ?></td>
        <td><?= $order->order_date ?></td>
        <td>RM<?= number_format($order->total_amount, 2) ?></td>
        <td><?= !empty($order->expired_time) ? date('Y-m-d', strtotime($order->expired_time)) : '' ?></td>
        <td><?= $order->order_details ?></td>
        <td>
            <?php 
            if ($order->status_id == 1) {
                echo '<span class="status-pending">Pending Payment</span>';
            } elseif ($order->status_id == 2) {
                echo '<span class="status-delivery">Pending Delivery</span>';
            } elseif ($order->status_id == 3) {
                echo '<span class="status-done">Delivered</span>';
            } else {
                echo '<span class="status-cancelled">Cancelled</span>';
            }
            ?>
        </td>   
        <td>
            <?php if ($order->status_id == 2) { ?>
                <form method="post">
                    <input type="hidden" name="order_id" value="<?= $order->order_id ?>">
                    <button type="submit" name="mark_done" class="action-button btn-delivered" onclick="return confirm('Are you sure you want to mark order_id =  <?= $order->order_id ?> as Delivered?');">Mark as Delivered</button>
                </form>
            <?php } else { echo "N/A"; } ?>
        </td>
    </tr>
    <?php } ?>
</table>





</body>





<?= $p->html("sort=$sort&dir=$dir") ?>
<?php
include "_foot.php";
