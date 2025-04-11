<?php
require '../_base.php';
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
    <?= html_search('name') ?>
    <button>Search</button>
</form>

    <table class="member-table">
        <tr>
            <?= table_headers($fields, $sort, $dir, "page=$page") ?>
        </tr>
        <?php foreach ($members as $member) { ?>
            <tr>
                <td><?= $member->user_id?></td>
                <td><?= $member->username ?></td>
                <td><?= $member->email ?></td>
                <td><?= $member->role ?></td>
                <td><img src="/images/<?=$member->profile_image ?? "default.png"  ?>"> </td>
                
            </tr>
        <?php } ?>
    </table>










    <?php
    include "../_foot.php";
