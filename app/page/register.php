<?php 
require "../_base.php";

if (is_post()) {
    $username = req('username');
    $email = req('email');
    $password = req('password');
    $confirmPassword = req('confirmPassword');

    if ($username == ''){
        $_err['username'] = 'Required';
    }else if (!is_unique($username, 'user', 'username')) {
        $_err['username'] = 'This username exists';
    }

    if ($email == ''){
        $_err['email'] = 'Required';
    }else if (!is_unique($email, 'user', 'email')) {
        $_err['email'] = 'This email exists';
    }

    if ($password == ''){
        $_err['password'] = 'Required';
    }

    if ($confirmPassword == ''){
        $_err['confirmPassword'] = 'Required';
    }elseif ($confirmPassword !== $password){
        $_err['confirmPassword'] = 'Password not match';
    }

    if (!$_err){
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stm = $_db->prepare('INSERT INTO user
                              (username, email, password)
                              VALUES(?, ?, ?)');
        $stm->execute([$username, $email, $hashedPassword]);
        
        temp('info', 'Record inserted');
        redirect('login.php');
    }
}

include "../_head.php";
$_title = 'Member Register'
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

    <label for="email">Email</label>
    <?= html_text('email') ?>
    <?= err('email') ?>

    <label for="password">Password</label>
    <div class="password-container">
        <?= html_password('password', 'id="password"') ?>
        <button type="button" id="togglePassword">
            <i class="fa fa-eye"></i> <!-- FontAwesome eye icon -->
        </button>
    </div>
    <?= err('password') ?><br>

    <label for="confirmPassword">Confirm Password</label>
    <div class="password-container">
        <?= html_password('confirmPassword', 'id="confirmPassword"') ?>
        <button type="button" id="togglePassword2">
            <i class="fa fa-eye"></i> <!-- FontAwesome eye icon -->
        </button>
    </div>
    <?= err('confirmPassword') ?><br>

    <section>
        <button style="width: 100%;">Register</button>
    </section><br>
    Already have an account &rarr;<a href="login.php" class="register"> Login</a>
</form>

<?php
include "../_foot.php";