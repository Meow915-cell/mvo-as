<?php
session_start();
require_once '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard");
            } else {
                header("Location: ../user/me.php");
            }
           
        } else {
            header("Location: ../login.php?error=Invalid password");
        }
    } else {
        header("Location: ../login.php?error=User not found");
    }
    $stmt->close();
}
$conn->close();
?>