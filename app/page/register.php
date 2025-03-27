<?php 
require "../_base.php";
require_once __DIR__ . '/../lib/sendEmail.php';

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
    } elseif (strlen($password) < 6) {
        $_err['password'] = 'Password must be at least 6 characters';
    }

    if ($confirmPassword == ''){
        $_err['confirmPassword'] = 'Required';
    }elseif ($confirmPassword !== $password){
        $_err['confirmPassword'] = 'Password not match';
    }

    if (!$_err){
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $verificationToken = generateToken();
        $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stm = $_db->prepare('INSERT INTO user
                              (username, email, password, role, profile_image, email_verified_at, email_verification_token, email_expired_at)
                              VALUES(?, ?, ?, ?, ?, ?, ?, ?)');
        $result = $stm->execute([$username, $email, $hashedPassword, 'member', null, '0', $verificationToken, $tokenExpiry]);
        
        if ($result) {
            $verificationLink = "http://localhost:8000/page/verifyEmail.php?token=$verificationToken";

            $subject = 'Verify Your Email, ' . $username . '!';
            $body = '<h3>Hello ' . htmlspecialchars($username) . ',</h3>
                     <p>Click the link below to verify your email before login:</p>
                     <p><a href="' . $verificationLink . '">Verify Your Email</a></p>
                     <p>This link expires in 1 hour.</p>
                     <p>If the link above cannot user plese click the link below</p>
                     <a href="' . $verificationLink . '">' . $verificationLink . '</a?>';
    
            // Include sendEmail function
            require_once '../lib/sendEmail.php';
    
            if (function_exists('sendEmail')) {
                $emailSent = sendEmail($email, $username, $subject, $body);
                
                if ($emailSent) {
                    temp('info', 'A verification email has been sent to your registered email.');
                } else {
                    temp('error', 'Record inserted, but email could not be sent.');
                }
            } else {
                temp('error', 'Email function not found.');
            }
        } else {
            temp('error', 'Registration failed.');
        }

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
    <p id="loadingText" style="display: none; color: blue;">Processing...</p>
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
        <button id="registerButton" style="width: 100%;">Register</button>
    </section><br>
    Already have an account &rarr;<a href="login.php" class="register"> Login</a>
</form>

<?php
include "../_foot.php";