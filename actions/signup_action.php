<?php
session_start();
require_once '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = $_POST['role'] == 'admin' ? 'admin' : 'user';

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: ../signup.php?error=Email already exists");
        exit();
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $password, $phone, $address, $role);
    
    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['role'] = $role;
        header("Location: ../user/me.php");
    } else {
        header("Location: ../signup.php?error=Signup failed");
    }
    $stmt->close();
}
$conn->close();
?>