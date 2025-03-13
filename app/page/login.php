<?php 
require "../_base.php";

if (is_post()) {
    // Input
    $username = post('username');
    $password = post('password');

    $stmt = $_db->prepare('SELECT * FROM user WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Validate id
    if ($username == '') {
        $_err['username'] = 'Required';
    }elseif(!$user){
        $_err['username'] = 'Username not found';
    }elseif (!password_verify($password, $user->password)){
        $_err['password'] = 'Password Incorrect';
    }
    
    // Validate name
    if ($password == '') {
        $_err['password'] = 'Required';
    }

    // Output
    if (!$_err) {
        session_start();
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['username'] = $user->username;

        temp('info', "$username, Welcome to BookHero");

        $data = (object)compact('username');
        temp('data', $data);

        redirect('../index.php');
    }
}

include "../_head.php";
$_title = 'Login'
?>

<style>
    nav a{
        display: none;
    }
</style>

<form method="post" class="form">
    <h1><?= $_title ?></h1>
    <label for="username">User Name</label>
    <?= html_text('username') ?>
    <?= err('username') ?>

    <label for="password">Password</label>
    <?= html_password('password') ?>
    <?= err('password') ?><br>
    <a href="/" class="register" style="float: right; padding: 5px;">Forget Password</a>

    <section>
        <button style="width: 100%;">Login</button>
    </section><br>
    No account? &rarr;<a href="/" class="register"> Register now</a>
</form>

<?php
include "../_foot.php";