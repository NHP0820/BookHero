<?php
require "../_base.php";
require_once "../lib/sendEmail.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stm = $_db->prepare("SELECT * FROM user WHERE email = ?");
    $stm->execute([$email]);
    $user = $stm->fetch(PDO::FETCH_OBJ);

    if (!$user) {
        echo json_encode(["status" => "error"]);
        exit;
    }

    $newToken = generateToken();
    $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $updateStm = $_db->prepare("UPDATE user SET email_verification_token = ?, email_expired_at = ? WHERE email = ?");
    $updateResult = $updateStm->execute([$newToken, $tokenExpiry, $email]);

    if ($updateResult) {
        $verificationLink = "http://localhost:8000/page/forgetPasswordForm.php?token=$newToken&email=" . urlencode($email);
        $subject = "Hello, " . htmlspecialchars($user->username) . "!";
        $body = '<h3>Hello ' . htmlspecialchars($user->username) . ',</h3>
                 <p>Click the link below to reset your password:</p>
                 <p><a href="' . $verificationLink . '">Reset Password</a></p>
                 <p>This link expires in 1 hour.</p>
                 <p>If you cannot click the link, copy and paste this URL:</p>
                 <p>' . $verificationLink . '</p>';

        if (sendEmail($email, $user->username, $subject, $body)) {
            temp('info', 'A forget password email has been sent to your registered email.');
            echo json_encode(["status" => "success"]);
        } else {
            temp('error', 'Email could not be sent.');
            echo json_encode(["status" => "error"]);
        }
    } else {
        temp('error', 'Failed to update token.');
        echo json_encode(["status" => "error"]);
    }
}
?>
