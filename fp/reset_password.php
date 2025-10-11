<?php
session_start();
require '../db/db_connect.php';

if (!isset($_SESSION['email'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = trim($_POST["password"]);
    $email = $_SESSION['email'];

    if (strlen($newPassword) < 6) {
        echo "Password must be at least 6 characters.";
        exit();
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL, reset_expiry = NULL WHERE email = ?");
    $stmt->bind_param("ss", $hashedPassword, $email);

    if ($stmt->execute()) {
        session_unset();
        session_destroy();
        echo "✅ Password reset successful! <a href='login.php'>Login</a>";
    } else {
        echo "❌ Failed to reset password. Please try again.";
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
    </style>
</head>

<body>
    <div class="container">
        <h2>Reset Password</h2>
        <form method="POST">
            <input type="password" name="password" required placeholder="Enter new password">
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>

</html>