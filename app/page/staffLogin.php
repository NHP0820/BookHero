<?php 
require "../_base.php";

if (is_post()) {
    // Input
    $username = post('username');
    $password = post('password');

    $stmt = $_db->prepare('SELECT * FROM user WHERE username = ? OR email = ?');
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    // Validate username
    if ($username == '') {
        $_err['username'] = 'Required';
    } elseif (!$user) {
        $_err['username'] = 'Username or email not found';
    }

    // Validate password (Only check if username is valid)
    if (isset($_err['username'])) {
        $_err['password'] = '';
    } elseif ($password == '') {
        $_err['password'] = 'Required';
    } elseif (!password_verify($password, $user->password)) {
        $_err['password'] = 'Password Incorrect';
    }

    if (!isset($_err['username']) && !isset($_err['password'])) {
        if ($user->role !== 'admin') {
            $_err['username'] = 'You are not authorized to log in';
            $password = '';
        }
    }

    // Output
    if (!$_err) {
        session_start();
        $_SESSION['user'] = [
            'username' => $user->username,
            'role' => $user->role
        ];

        temp('info', "$username, Welcome to BookHero");

        $data = (object)compact('username');
        temp('data', $data);

        redirect('../index.php');
    }
}

include "../_head.php";
$_title = 'Staff Login'
?>

<style>
    nav a{
        display: none;
    }
</style>

<form method="post" class="form">
    <h1><?= $_title ?></h1>
    <label for="username">User Name / Email</label>
    <?= html_text('username') ?>
    <?= err('username') ?>

    <label for="password">Password</label>
    <div class="password-container">
        <?= html_password('password', 'id="password"') ?>
        <button type="button" id="togglePassword">
            <i class="fa fa-eye"></i> <!-- FontAwesome eye icon -->
        </button>
    </div>
    <?= err('password') ?>
    <a href="/" class="register" style="float: right; padding: 5px;">Forget Password</a><br>

    <section>
        <a href="../category/category.php"><button style="width: 100%;">Login</button></a>
    </section><br>
    <a href="login.php" class="register">Member login</a>
</form>

<?php
include "../_foot.php";