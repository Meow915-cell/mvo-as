<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $permissions = json_encode($_POST['permissions'] ?? []);

    $stmt = $conn->prepare("UPDATE users SET permissions = ? WHERE id = ?");
    $stmt->bind_param("si", $permissions, $user_id);
    
    if ($stmt->execute()) {
        header("Location: ../users.php?success=Permissions updated successfully");
    } else {
        header("Location: ../users.php?error=Failed to update permissions");
    }
    $stmt->close();
}
$conn->close();
?>