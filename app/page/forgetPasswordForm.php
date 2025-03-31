<?php 
require "../_base.php";

$email = '';
$token = $_GET['token'] ?? ''; // Get token from URL

if (!empty($token)) {
    // Query the database to find the email linked to this token
    $stmt = $_db->prepare("SELECT email FROM user WHERE email_verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_OBJ);

    if ($user) {
        $email = $user->email;
    } else {
        die("Invalid or expired token."); // Stop execution if token is invalid
    }
}

if (is_post()) {
    // Input
    $password = post('password');
    $confirmPassword = post('confirmPassword');

    // Validation
    if (empty($password)) {
        $_err['password'] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $_err['password'] = 'Password must be at least 6 characters';
    }

    if ($confirmPassword !== $password) {
        $_err['confirmPassword'] = 'Passwords do not match';
    }

    // Output: If no errors, update the password
    if (empty($_err)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $updateStmt = $_db->prepare("UPDATE user SET password = ?, email_verification_token = NULL WHERE email = ?");
        $updateStmt->execute([$hashedPassword, $email]);

        temp('info', "Password has been reset. You can now log in.");

        redirect('../index.php'); // Redirect to login page after successful reset
    }
}

include "../_head.php";
$_title = 'Reset Password';
?>

<form method="post" class="form">
    <h1><?= $_title ?></h1>
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

    <label for="email">Email</label>
    <input type="text" name="email" id="email" value="<?= htmlspecialchars($email) ?>" readonly>
    <?= err('email') ?>

    <label for="password">New Password</label>
    <div class="password-container">
        <?= html_password('password', 'id="password"') ?>
        <button type="button" id="togglePassword">
            <i class="fa fa-eye"></i>
        </button>
    </div>
    <?= err('password') ?>

    <label for="confirmPassword">Confirm Password</label>
    <div class="password-container">
        <?= html_password('confirmPassword', 'id="confirmPassword"') ?>
        <button type="button" id="togglePassword2">
            <i class="fa fa-eye"></i>
        </button>
    </div>
    <?= err('confirmPassword') ?><br>

    <section>
        <button style="width: 100%;">Reset Password</button>
    </section><br>
</form>

<script>
document.getElementById("togglePassword").addEventListener("click", function() {
    let password = document.getElementById("password");
    password.type = password.type === "password" ? "text" : "password";
});

document.getElementById("togglePassword2").addEventListener("click", function() {
    let confirmPassword = document.getElementById("confirmPassword");
    confirmPassword.type = confirmPassword.type === "password" ? "text" : "password";
});
</script>

<?php
include "../_foot.php";
?>
