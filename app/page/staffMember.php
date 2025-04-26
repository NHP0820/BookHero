<?php
require '../_base.php';

$user_id = $_SESSION['user']['id'] ?? null;
$user_role = $_SESSION['user']['role'] ?? null;
if (!$user_id || $user_role !== 'admin') {
    temp('info', 'Please login first');
    redirect("login.php");
    exit;
}

$fields = [
    'user_id' => 'User ID',
    'username' => 'Name',
    'email' => 'Email',
    'role' => 'Role',
    'profile_image' => 'Image'
];

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'user_id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

$page = req('page', 1);
$name = req('name');
$view = req('view', 'table'); // default is table view

require_once '../lib/SimplePager.php';

$query = "SELECT * FROM user WHERE username LIKE ? ORDER BY $sort $dir";
$p = new SimplePager($query, ["%$name%"], 5, $page);
$members = $p->result;

if (is_post() && isset($_POST['action'])) {
    $user_id = req('user_id');
    $action = req('action');
    $view = req('view');

    if ($action == 'block') {
        $stmt = $_db->prepare("UPDATE user SET block = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
    } elseif ($action == 'unblock') {
        $stmt = $_db->prepare("UPDATE user SET block = 0, block_count = 0 WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }

    redirect($_SERVER['PHP_SELF'] . "?view=" . urlencode($view) . "&page=" . $page);
}

include '../_staffHead.php';
?>

<link rel="stylesheet" href="/css/staffmember.css">
<style>
.view-toggle {
    text-align: center;
    margin-bottom: 20px;
}
.view-toggle button {
    padding: 10px 20px;
    margin: 0 5px;
    border: none;
    background-color: #03A9F4;
    color: white;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
}
.view-toggle button.active {
    background-color: #0288D1;
}
.photo-view {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}
.photo-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    width: 220px;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}
.photo-card:hover {
    transform: scale(1.03);
}
.photo-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 6px;
}
</style>

</head>

<body>
<h1 style="text-align: center;">Admin Member Management</h1>

<div class="view-toggle">
    <a href="?view=table&page=<?= $page ?>" class="<?= $view === 'table' ? 'active' : '' ?>">
        <button class="<?= $view === 'table' ? 'active' : '' ?>">Table View</button>
    </a>
    <a href="?view=photo&page=<?= $page ?>" class="<?= $view === 'photo' ? 'active' : '' ?>">
        <button class="<?= $view === 'photo' ? 'active' : '' ?>">Photo View</button>
    </a>
</div>

<form class="search-form">
    <?= html_search('name', 'placeholder="Search by name..."') ?>
    <button>Search</button>
</form>

<div id="tableView" style="<?= $view === 'table' ? '' : 'display:none;' ?>">
    <table class="member-table">
        <tr>
            <?= table_headers($fields, $sort, $dir, "page=$page&view=$view") ?>
        </tr>
        <?php foreach ($members as $member) { ?>
            <tr>
                <td><?= $member->user_id ?></td>
                <td><?= $member->username ?></td>
                <td><?= $member->email ?></td>
                <td><?= $member->role ?></td>
                <td><img src="/images/<?= $member->profile_image ?? 'default.png' ?>" width="150" height="150"></td>
                <td>
                    <?php if ($member->block == 1): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="unblock">
                            <input type="hidden" name="user_id" value="<?= $member->user_id ?>">
                            <input type="hidden" name="view" value="<?= $view ?>">
                            <button type="submit" class="btn unblock-btn">Unblock</button>
                        </form>
                    <?php else: ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="block">
                            <input type="hidden" name="user_id" value="<?= $member->user_id ?>">
                            <input type="hidden" name="view" value="<?= $view ?>">
                            <button type="submit" class="btn block-btn">Block</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<div id="photoView" style="<?= $view === 'photo' ? '' : 'display:none;' ?>">
    <div class="photo-view">
        <?php foreach ($members as $member) { ?>
            <div class="photo-card">
                <img src="/images/<?= $member->profile_image ?? 'default.png' ?>" alt="Profile">
                <h3><?= $member->username ?></h3>
                <p><?= $member->email ?></p>
                <p><?= ucfirst($member->role) ?></p>
                <?php if ($member->block == 1): ?>
                    <form method="post" style="margin-top:10px;">
                        <input type="hidden" name="action" value="unblock">
                        <input type="hidden" name="user_id" value="<?= $member->user_id ?>">
                        <input type="hidden" name="view" value="<?= $view ?>">
                        <button type="submit" class="btn unblock-btn">Unblock</button>
                    </form>
                <?php else: ?>
                    <form method="post" style="margin-top:10px;">
                        <input type="hidden" name="action" value="block">
                        <input type="hidden" name="user_id" value="<?= $member->user_id ?>">
                        <input type="hidden" name="view" value="<?= $view ?>">
                        <button type="submit" class="btn block-btn">Block</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php } ?>
    </div>
</div>

<div style="margin-top:20px; text-align:center;">
    <?= $p->html("view=$view") ?>
</div>

<?php include '../_foot.php'; ?>
