<?php 
require "../_base.php";

if (is_post()) {
    $username = post('username');
    $email = post('email');
    $password = post('password');
    $confirmPassword = post('confirmPassword');

    if ($username = ''){
        $_err['username'] = 'Required';
    }

    if ($email = ''){
        $_err['email'] = 'Required';
    }

    if ($password = ''){
        $_err['password'] = 'Required';
    }

    if ($confirmPassword = ''){
        $_err['confirmPassword'] = 'Required';
    }

    if (!$_err){

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

    <label for="confirmPassword">Confirm Password</label>
    <div class="password-container">
        <?= html_password('password', 'id="password"') ?>
        <button type="button" id="togglePassword">
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