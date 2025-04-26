<?php 
require "../_base.php";

if (is_post()) {
    $email = post('email');
    $password = post('password');
    $recaptchaResponse = post('g-recaptcha-response');

    $stmt = $_db->prepare('SELECT * FROM user WHERE email = ?');
    $stmt->execute([$email]);
    $emails = $stmt->fetch(PDO::FETCH_OBJ);

    $recaptchaSecret = '6LcCTCUrAAAAAFAuqfhYnpz_gIR7pFhmprM6COa1'; // Your secret key from Google
    $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $response = file_get_contents($recaptchaUrl . '?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse);
    $responseKeys = json_decode($response, true);

    if (intval($responseKeys["success"]) !== 1) {
        $_err['captcha'] = 'Please complete the CAPTCHA';
    }

    if ($email == '') {
        $_err['email'] = 'Required';
        echo json_encode(["status" => "error", "message" => $_err['email']]);
    } elseif (!$emails) {
        $_err['email'] = 'Email not found';
    } elseif ($emails->email_verified_at != 1){
        $_err['email'] = 'Your email has not been verified. <a href="#" id="resendVerification" data-email="'.htmlspecialchars($email).'" style="float: right;">Did not receive email?</a>';
    } elseif ($emails->role !== 'member'){
        $_err['email'] = 'You are not a member';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email format';
    }

    if (isset($_err['email'])) {
        $_err['password'] = '';
    } elseif ($password == '') {
        $_err['password'] = 'Required';
    } elseif (!password_verify($password, $emails->password)) {
        $blockCount = $emails->block_count + 1;

        $updateStmt = $_db->prepare('UPDATE user SET block_count = ? WHERE user_id = ?');
        $updateStmt->execute([$blockCount, $emails->user_id]);

        if ($blockCount >= 3) {
            $blockStmt = $_db->prepare('UPDATE user SET block = 1 WHERE user_id = ?');
            $blockStmt->execute([$emails->user_id]);
            $_err['password'] = 'Password Incorrect';
        } else {
            $_err['password'] = 'Password Incorrect';
        }
    }

    if ($emails->block == 1) {
        $_err['email'] = 'Your account is blocked. Please contact admin to unblock your account.';
    }

    if (!$_err) {
        session_start();
        $_SESSION['user'] = [
            'username' => $emails->username,
            'id' => $emails->user_id,
            'role' => $emails->role,
            'email' => $emails->email,
            'profile_image' => $emails->profile_image
        ];
    
        temp('info', "$emails->username, Welcome to BookHero");
    
        $data = (object)compact('email');
        temp('data', $data);
    
        if (isset($_POST['remember_me']) && $_POST['remember_me'] == '1') {
            $token = bin2hex(random_bytes(16));
            setcookie('remember_me', $token, time() + (86400 * 30), "/"); // 30 days
    
            $updateToken = $_db->prepare("UPDATE user SET remember_token = ? WHERE user_id = ?");
            $updateToken->execute([$token, $emails->user_id]);
        }

        $updateStmt = $_db->prepare('UPDATE user SET block_count = 0 AND block = 0 WHERE user_id = ?');
        $updateStmt->execute([$emails->user_id]);
    
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
            <i class="fa fa-eye"></i>
        </button>
    </div>
    <?= err('password') ?>
    <br>
    <label style="display: inline-block; margin-right: 10px;">
        <input type="checkbox" name="remember_me" value="1">
        Remember me
    </label>

    <a href="#" id="forgetPassword" class="register" style="float: right; padding: 5px; display: inline-block;">Forget Password</a><br>

    <br>
    <div style="text-align: center; width: 100%;">
        <div class="g-recaptcha" data-sitekey="6LcCTCUrAAAAAAi3NloxquVKcHXLFfFbbC_1zBtZ"></div>
    </div>
    <?= err('captcha') ?>

    <section>
        <button style="width: 100%;">Login</button>
    </section><br>
    No account? &rarr;<a href="register.php" class="register"> Register now</a>
    <a href="staffLogin.php" class="staffL">Staff login</a>
</form>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<?php
include "../_foot.php";