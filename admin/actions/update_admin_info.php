<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = !empty(trim($_POST['phone'])) ? trim($_POST['phone']) : null;
    $address = !empty(trim($_POST['address'])) ? trim($_POST['address']) : null;

    // Validate inputs
    if (empty($name) || empty($email)) {
        header("Location: ../settings.php?error=Name and email are required");
        exit();
    }

    // Check if email is unique (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        header("Location: ../settings.php?error=Email already exists");
        exit();
    }
    $stmt->close();

    // Update admin info
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ? AND role = 'admin'");
    $stmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);
    if ($stmt->execute()) {
        header("Location: ../settings.php?success=Account information updated successfully");
    } else {
        header("Location: ../settings.php?error=Failed to update account information: " . urlencode($conn->error));
    }
    $stmt->close();
} else {
    header("Location: ../settings.php?error=Invalid request");
}

$conn->close();
?>