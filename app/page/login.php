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
    } elseif (!$user || $email) {
        $_err['username'] = 'Username or email not found';
    }

    // Validate password (Only check if username is valid)
    if ($_err['username'] == 'Username or email not found'){
        $password = '';
        $_err['password'] = '';
    }elseif ($password == '') {
        $_err['password'] = 'Required';
    } elseif ($user && !password_verify($password, $user->password)) {
        $_err['password'] = 'Password Incorrect';
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
    <h1>Welcome to <?= $_title ?></h1>
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
    <?= err('password') ?><br>
    <a href="/" class="register" style="float: right; padding: 5px;">Forget Password</a>

    <section>
        <button style="width: 100%;">Login</button>
    </section><br>
    No account? &rarr;<a href="register.php" class="register"> Register now</a>
</form>

<?php
include "../_foot.php";