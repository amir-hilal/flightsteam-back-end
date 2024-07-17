<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../../vendor/autoload.php'; // Ensure this path is correct based on your project structure
require_once '../../config/config.php';

function send_verification_email($email, $verification_code) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_EMAIL; // Use email from environment variable
        $mail->Password = SMTP_PASSWORD; // Use password from environment variable
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom(SMTP_EMAIL, 'Flightsteam'); // Replace with your name
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification Code';
        $mail->Body = "Your verification code is: <b>$verification_code</b>";
        $mail->AltBody = "Your verification code is: $verification_code";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
