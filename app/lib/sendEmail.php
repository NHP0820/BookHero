<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer.php';
require __DIR__ . '/SMTP.php';
require __DIR__ . '/Exception.php';

function generateToken($length = 64) {
    return bin2hex(random_bytes($length / 2)); 
}

function sendEmail($recipient_email, $userName, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        if (empty($recipient_email)) {
            throw new Exception("Recipient email is required.");
        }
        if (empty($userName)) {
            throw new Exception("User name is required.");
        }

        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'choongkf-am21@student.tarc.edu.my';
        $mail->Password = 'gikw uqaj debd edtc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587; 
        
        // Recipients
        $mail->setFrom('choongkf-am21@student.tarc.edu.my', 'Do not reply me'); // Sender
        $mail->addAddress($recipient_email, $userName); // Dynamic recipient

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // Plain text version

        // Send Email
        return $mail->send();
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
