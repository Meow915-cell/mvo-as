<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'] ?? null;
    $address = $_POST['address'] ?? null;
    $role = $_POST['role'];
    $permissions = json_encode($_POST['permissions'] ?? []);
    $password = password_hash($_POST['confirm_password'], PASSWORD_BCRYPT); // Default password

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role, permissions) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $email, $password, $phone, $address, $role, $permissions);
    
    if ($stmt->execute()) {
        header("Location: ../settings.php?success=User added successfully");
    } else {
        header("Location: ../settings.php?error=Failed to add user");
    }
    $stmt->close();
}
$conn->close();
?>