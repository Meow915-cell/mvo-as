<?php
session_start();
require '../db/db_connect.php';

if (!isset($_SESSION['email'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['email'];
    $code = trim($_POST['code']);
    $new_password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT reset_code, reset_expiry FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Debug: Log stored code and expiry
    file_put_contents('verify_debug.log', "Stored Code: {$user['reset_code']}, Expiry: {$user['reset_expiry']}, Entered Code: $code, Current Time: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

    if ($user && $user['reset_code'] == $code && (is_null($user['reset_expiry']) || strtotime($user['reset_expiry']) > time())) {
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL, reset_expiry = NULL WHERE email = ?");
        $stmt->bind_param("ss", $new_password, $email);
        $stmt->execute();

        $_SESSION['status'] = 'Password reset successful! Please log in.';
        $_SESSION['status_type'] = 'success';

        session_unset();
        session_destroy();

        header("Location: ../index.php");
        exit();
    } else {
        $_SESSION['status'] = 'Invalid or expired reset code. Please try again.';
        $_SESSION['status_type'] = 'error';
        header("Location: verify_code.php");
        exit();
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
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
            padding: 15px;
            background: linear-gradient(135deg, rgb(255, 255, 255), #c2e9fb);
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
            width: 100%;
            max-width: 350px;
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

        .alert {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Reset Password</h2>

        <?php
        if (isset($_SESSION['status'])) {
            $statusType = $_SESSION['status_type'] == 'success' ? 'alert-success' : 'alert-error';
            echo "<div class='alert $statusType'>{$_SESSION['status']}</div>";
            unset($_SESSION['status']);
            unset($_SESSION['status_type']);
        }
        ?>

        <form method="post">
            <div>
                <label class="form-label">Enter Reset Code</label>
                <input type="text" name="code" class="form-control" required>
            </div>
            <div>
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit">Reset Password</button>
        </form>

        <a href="../index.php" class="back-link">Back to Login</a>
    </div>
</body>

</html>