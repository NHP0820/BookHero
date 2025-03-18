<?php
require '../_base.php'; // Adjust path to your database connection file

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Check if the token exists and is not expired
    $stm = $_db->prepare('SELECT user_id FROM user WHERE email_verification_token = ? AND email_expired_at > NOW() AND email_verified_at = 0');
    $stm->execute([$token]);
    $user = $stm->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Update email_verified_at to 1
        $stm = $_db->prepare('UPDATE user SET email_verified_at = 1, email_verification_token = NULL, email_expired_at = NULL WHERE user_id = ?');
        $stm->execute([$user['user_id']]);
        
        temp ('info', 'Email verification successful!');
    } else {
        temp ('error', 'Invalid or expired verification link.');
    }
} else {
    temp ('errpr', 'No verification token provided.');
    
}

redirect('login.php');