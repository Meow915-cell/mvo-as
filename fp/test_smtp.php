<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Adjust path if needed

$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug = 2; // Enable detailed debug output
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'venturinaden930@gmail.com'; // Your Gmail address
    $mail->Password = 'rzbjhbszzmghosvu'; // Your new App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('venturinaden930@gmail.com', 'Test');
    $mail->addAddress('test@example.com'); // Replace with a valid test email
    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body = '<p>This is a test email.</p>';

    $mail->send();
    echo 'Email sent successfully!';
} catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>