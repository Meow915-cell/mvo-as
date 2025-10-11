<?php
session_start();
require '../db/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if (!$email) {
        echo "Invalid email address!";
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $code = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        $stmt = $conn->prepare("UPDATE users SET reset_code = ?, reset_expiry = ? WHERE email = ?");
        $stmt->bind_param("sss", $code, $expiry, $email);
        if (!$stmt->execute()) {
            echo "Database error: " . $stmt->error;
            exit();
        }

        require 'send_code.php';
        if (sendVerificationCode($email, $code)) {
            $_SESSION['email'] = $email;
            header("Location: verify_code.php");
            exit();
        } else {
            echo "Failed to send verification code.";
        }
    } else {
        echo "Email not found!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, rgb(255, 255, 255), #c2e9fb);
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
            width: 320px;
            text-align: center;
        }

        h2 {
            margin-bottom: 15px;
            font-weight: 600;
        }

        p {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 8px;
            background: #2b99ff;
            color: white;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #0077ff;
        }

        .back-link {
            display: block;
            margin-top: 10px;
            font-size: 13px;
            color: #2b99ff;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Forgot Password?</h2>
        <p>Enter your email address and we'll send you a reset code.</p>
        <form action="forgot_password.php" method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Reset Code</button>
        </form>
        <a href="../index.php" class="back-link">Back to Login</a>
    </div>
</body>

</html>