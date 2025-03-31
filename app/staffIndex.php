<?php
require '_base.php';
//-----------------------------------------------------------------------------

$arr = $_db->query('SELECT * FROM product')->fetchAll();

// ----------------------------------------------------------------------------
include '_staffHead.php';
?>

</head>
<body>
<a href="staffOrder.php">Order</a>


<?php
include "_foot.php";