<?php
require '_base.php';
//-----------------------------------------------------------------------------

$arr = $_db->query('SELECT * FROM product')->fetchAll();

// ----------------------------------------------------------------------------
include '_staffHead.php';
?>

</head>
<body>
<<<<<<< HEAD
=======
<a href="staffOrder.php">Order</a>
<a href="staffMember.php">Member</a>
>>>>>>> 47c7dfa5ae4c2c60158af675fc314f7ad235ca69


<?php
include "_foot.php";