<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require dirname(__DIR__) . '/vendor/autoload.php'; // Correct path


function sendVerificationCode($email, $code)
{
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function ($str, $level) {
            file_put_contents('smtp_debug.log', "$str\n", FILE_APPEND);
        };
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'venturinaden930@gmail.com';
        $mail->Password = 'rzbjhbszzmghosvu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('venturinaden930@gmail.com', '(MVO)Municipal Vet Office');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code';
        $mail->Body = "<p>Your password reset code is: <strong>$code</strong></p>";
        $mail->AltBody = "Your password reset code is: $code";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}

function sendEmailNotification($email, $subject, $body)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'venturinaden930@gmail.com';
        $mail->Password = 'rzbjhbszzmghosvu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('venturinaden930@gmail.com', '(MVO)Municipal Vet Office');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = nl2br($body); // Converts \n to <br> for HTML
        $mail->AltBody = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Notification email error: {$mail->ErrorInfo}");
        return false;
    }
}
?>