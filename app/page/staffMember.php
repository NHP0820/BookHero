<?php
require '../_base.php';

$user_id = $_SESSION['user']['id'] ?? null;
$user_role = $_SESSION['user']['role'] ?? null;
if (!$user_id || $user_role !== 'admin') {
    temp('info', 'Please login first');
    redirect("login.php");
    redirect("staffLogin.php");
    exit;
}

//-----------------------------------------------------------------------------
$fields = [
    'user_id'         => 'User_id',
    'username'     => 'Name',
    'email' => 'Email',
    'role' => 'Role',
    'profile_image' => 'Image'
];



$sort = req('sort');
key_exists($sort, $fields) || $sort = 'user_id';


$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

// (2) Paging
$page = req('page', 1);
$name = req('name');
require_once '../lib/SimplePager.php';


$query = "SELECT * FROM user WHERE username LIKE ?  ORDER BY $sort $dir";

$p = new SimplePager($query, ["%$name%"], 5, $page);
$members = $p->result;

if (is_post() && isset($_POST['action'])) {
    $user_id = req('user_id');
    $action = req('action');

    if ($action == 'block') {
        $stmt = $_db->prepare("UPDATE user SET block = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
    } elseif ($action == 'unblock') {
        $stmt = $_db->prepare("UPDATE user SET block = 0 WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }

    // Redirect to the same page to reflect changes
    redirect($_SERVER['REQUEST_URI']);
}

// ----------------------------------------------------------------------------
include '../_staffHead.php';
?>

<link rel="stylesheet" href="/css/staffmember.css">
</head>






<body>
    <h1 style="text-align: center;">
        Admin member Management
    </h1>

    <form class="search-form">
    <?= html_search('name','placeholder="Search by name..."') ?>
    <button>Search</button>
</form>

    <table class="member-table">
        <tr>
            <?= table_headers($fields, $sort, $dir, "page=$page") ?>
        </tr>
        <?php foreach ($members as $member) { ?>
            <tr>
                <td><?= $member->user_id ?></td>
                <td><?= $member->username ?></td>
                <td><?= $member->email ?></td>
                <td><?= $member->role ?></td>
                <td><img src="/images/<?=$member->profile_image ?? 'default.png'?>" width="150px" height="150px"></td>
                <td>
                    <!-- Block or Unblock buttons -->
                    <?php if ($member->block == 1): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="unblock">
                            <input type="hidden" name="user_id" value="<?= $member->user_id ?>">
                            <button type="submit" class="btn unblock-btn">Unblock</button>
                        </form>
                    <?php else: ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="block">
                            <input type="hidden" name="user_id" value="<?= $member->user_id ?>">
                            <button type="submit" class="btn block-btn">Block</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php } ?>
    </table>










    <?php
    include "../_foot.php";
