<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']) ?: null;
    $address = trim($_POST['address']) ?: null;

    if (empty($name) || empty($email)) {
        header("Location: ../settings.php?error=Name and email are required");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../settings.php?error=Invalid email format");
        exit();
    }

    // Check if email is already in use by another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: ../settings.php?error=Email already in use");
        exit();
    }
    $stmt->close();

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);

    if ($stmt->execute()) {
        header("Location: ../settings.php?success=Profile updated successfully");
    } else {
        header("Location: ../settings.php?error=Failed to update profile");
    }
    $stmt->close();
}
$conn->close();
?>