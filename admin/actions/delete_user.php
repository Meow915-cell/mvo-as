<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        header("Location: ../settings.php?success=User deleted successfully");
    } else {
        header("Location: ../settings.php?error=Failed to delete user");
    }
    $stmt->close();
}
$conn->close();
?>