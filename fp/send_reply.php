<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientEmail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $replyMessage = strval(random_int(10000, 99999));

    if (!$recipientEmail || !$replyMessage) {
        echo "Invalid input. Please provide a valid email, message, and submission ID.";
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'venturinaden930@gmail.com';
        $mail->Password = 'rzbjhbszzmghosvu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('vetcarr@gmail.com', 'Pet Veterinary Services in Carranglan');
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = 'From ';
        $mail->Body = "<p>$replyMessage</p>";
        $mail->AltBody = strip_tags($replyMessage);

        $mail->send();

        $conn = new mysqli('localhost', 'root', '', 'capstone');

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("UPDATE users SET reset_code = ? WHERE email = ?");
        $stmt->bind_param("ss", $replyMessage, $recipientEmail);

        session_start();
        if ($stmt->execute()) {
            $_SESSION['verified'] = true;

            echo "Reply sent successfully";
            $_SESSION['email'] = $recipientEmail;
            header("Location: verify_code.php");
            exit();
        } else {
            echo "Reply sent, but error updating database: " . $stmt->error;
        }


        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid request method.";
}
?>