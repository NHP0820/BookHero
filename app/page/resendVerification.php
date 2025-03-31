<?php
require "../_base.php";
require_once "../lib/sendEmail.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if email exists
    $stm = $_db->prepare("SELECT * FROM user WHERE email = ?");
    $stm->execute([$email]);
    $user = $stm->fetch(PDO::FETCH_OBJ);

    // Generate a new token and update database
    $newToken = generateToken();
    $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $updateStm = $_db->prepare("UPDATE user SET email_verification_token = ?, email_expired_at = ? WHERE email = ?");
    $updateResult = $updateStm->execute([$newToken, $tokenExpiry, $email]);

    if ($updateResult) {
        $verificationLink = "http://localhost:8000/page/verifyEmail.php?token=$newToken";
        $subject = "Resend: Verify Your Email, " . htmlspecialchars($user->username) . "!";
        $body = '<h3>Hello ' . htmlspecialchars($user->username) . ',</h3>
                 <p>Click the link below to verify your email before logging in:</p>
                 <p><a href="' . $verificationLink . '">Verify Your Email</a></p>
                 <p>This link expires in 1 hour.</p>
                 <p>If the link above cannot user plese click the link below</p>
                 <a href="' . $verificationLink . '">' . $verificationLink . '</a?>';

        if (sendEmail($email, $user->username, $subject, $body)) {
            temp('info', 'A verification email has been sent to your registered email.');
            echo json_encode(["status" => "success"]);
        } else {
            temp('error', 'Email registed but could not be sent.');
            echo json_encode(["status" => "error"]);
        }
    } else {
        temp('error', 'Failed to update token.');
        echo json_encode(["status" => "error"]);
    }
}
?>
