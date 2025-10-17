<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        header("Location: ../settings.php?error=All password fields are required");
        exit();
    }

    if ($new_password !== $confirm_password) {
        header("Location: ../settings.php?error=New passwords do not match");
        exit();
    }

    if (strlen($new_password) < 8) {
        header("Location: ../settings.php?error=New password must be at least 8 characters");
        exit();
    }

    // Verify old password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($old_password, $user['password'])) {
        header("Location: ../settings.php?error=Incorrect old password");
        exit();
    }

    $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password_hash, $user_id);

    if ($stmt->execute()) {
        header("Location: ../settings.php?success=Password changed successfully");
    } else {
        header("Location: ../settings.php?error=Failed to change password");
    }
    $stmt->close();
}
$conn->close();
?>