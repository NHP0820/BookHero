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
    } elseif ($user->email_verified_at != 1){
        $_err['username'] = 'Your email have not verify yet';
    }

    // Validate password (Only check if username is valid)
    if (isset($_err['username'])) {
        $_err['password'] = '';
    } elseif ($password == '') {
        $_err['password'] = 'Required';
    } elseif (!password_verify($password, $user->password)) {
        $_err['password'] = 'Password Incorrect';
    }

    // Output
    if (!$_err) {
        session_start();
        $_SESSION['user'] = [
            'username' => $user->username,
            'id' => $user->user_id
        ];

        temp('info', "$username, Welcome to BookHero");

        $data = (object)compact('username');
        temp('data', $data);

        redirect('../index.php');
    }
}

include "../_head.php";
$_title = 'Login'
?>

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
        <button style="width: 100%;">Login</button>
    </section><br>
    No account? &rarr;<a href="register.php" class="register"> Register now</a>
    <a href="staffLogin.php" class="staffL">Staff login</a>
</form>

<?php
include "../_foot.php";