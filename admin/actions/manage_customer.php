<?php
session_start();
require_once '../../db/db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    error_log("Unauthorized access attempt: " . json_encode($_SESSION));
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    error_log("Attempting to delete user_id: $user_id");

    // Verify user exists and is not an admin
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'user'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        error_log("User not found or is an admin: user_id=$user_id");
        header("Location: ../customers.php?error=User not found or is an admin");
        exit();
    }
    $stmt->close();

    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            error_log("User deleted successfully: user_id=$user_id");
            header("Location: ../customers/index.php?success=User and related data deleted successfully");
        } else {
            error_log("No user was deleted: user_id=$user_id");
            header("Location: ../customers.php?error=No user was deleted");
        }
    } else {
        $error_message = $conn->error;
        error_log("Failed to delete user_id=$user_id: $error_message");
        header("Location: ../customers.php?error=Failed to delete user: " . urlencode($error_message));
    }
    $stmt->close();
} else {
    error_log("Invalid request: " . json_encode($_POST));
    header("Location: ../customers.php?error=Invalid request");
}

$conn->close();
?>