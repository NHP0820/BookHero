<?php
require '_base.php';

$user_id = $_SESSION['user']['id'] ?? null;
$user_role = $_SESSION['user']['role'] ?? null;
if (!$user_id || $user_role !== 'admin') {
    temp('info', 'Please login first');
    redirect("page/staffLogin.php");
    exit;
}

//-----------------------------------------------------------------------------

$arr = $_db->query('SELECT * FROM product')->fetchAll();

// ----------------------------------------------------------------------------
include '_staffHead.php';
?>

</head>
<body>

<?php
include "_foot.php";