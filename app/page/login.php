<?php 
require "../_base.php";

if (is_post()) {
    // Input
    $email = post('email');
    $password = post('password');

    $stmt = $_db->prepare('SELECT * FROM user WHERE email = ?');
    $stmt->execute([$email]);
    $emails = $stmt->fetch(PDO::FETCH_OBJ);

    // Validate username
    if ($email == '') {
        $_err['email'] = 'Required';
        echo json_encode(["status" => "error", "message" => $_err['email']]);
    } elseif (!$emails) {
        $_err['email'] = 'Email not found';
    } elseif ($emails->email_verified_at != 1){
        $_err['email'] = 'Your email has not been verified. <a href="#" id="resendVerification" data-email="'.htmlspecialchars($email).'" style="float: right;">Did not receive email?</a>';
    } elseif ($emails->role !== 'member'){
        $_err['email'] = 'You are not a member';
    }

    // Validate password (Only check if username is valid)
    if (isset($_err['email'])) {
        $_err['password'] = '';
    } elseif ($password == '') {
        $_err['password'] = 'Required';
    } elseif (!password_verify($password, $emails->password)) {
        $_err['password'] = 'Password Incorrect';
    }

    // Output
    if (!$_err) {
        session_start();
        $_SESSION['user'] = [
            'username' => $emails->username,
            'id' => $emails->user_id
        ];

        temp('info', "$emails->username, Welcome to BookHero");

        $data = (object)compact('email');
        temp('data', $data);

        redirect('../index.php');
    }
}

include "../_head.php";
$_title = 'Login'
?>

<form method="post" class="form">
    <h1><?= $_title ?></h1>
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
    <?= err('password') ?>
    
    <a href="#" id="forgetPassword" class="register" style="float: right; padding: 5px;">Forget Password</a><br>

    <section>
        <button style="width: 100%;">Login</button>
    </section><br>
    No account? &rarr;<a href="register.php" class="register"> Register now</a>
    <a href="staffLogin.php" class="staffL">Staff login</a>
</form>

<?php
include "../_foot.php";