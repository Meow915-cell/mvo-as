<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        header("Location: ../settings.php?error=All fields are required");
        exit();
    }

    if ($new_password !== $confirm_password) {
        header("Location: ../settings.php?error=New password and confirmation do not match");
        exit();
    }

    if (strlen($new_password) < 8) {
        header("Location: ../settings.php?error=New password must be at least 8 characters long");
        exit();
    }

    // Fetch current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? AND role = 'admin'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Update password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_password_hash, $user_id);
            if ($stmt->execute()) {
                header("Location: ../settings.php?success=Password updated successfully");
            } else {
                header("Location: ../settings.php?error=Failed to update password: " . urlencode($conn->error));
            }
        } else {
            header("Location: ../settings.php?error=Current password is incorrect");
        }
    } else {
        header("Location: ../settings.php?error=User not found");
    }
    $stmt->close();
} else {
    header("Location: ../settings.php?error=Invalid request");
}

$conn->close();
?>