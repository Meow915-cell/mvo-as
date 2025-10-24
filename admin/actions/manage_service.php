<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);

        $stmt = $conn->prepare("INSERT INTO services (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        if ($stmt->execute()) {
            header("Location: ../services/?success=Service added successfully");
        } else {
            header("Location: ../services/?error=Failed to add service");
        }
        $stmt->close();
    } elseif ($action === 'edit' && isset($_POST['service_id'])) {
        $service_id = intval($_POST['service_id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);

        $stmt = $conn->prepare("UPDATE services SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $description, $service_id);
        $stmt->bind_param("ssi", $name, $description, $service_id);
        if ($stmt->execute()) {
            header("Location: ../services/?success=Service updated successfully");
        } else {
            header("Location: ../services/?error=Failed to update service");
        }
        $stmt->close();
    } elseif ($action === 'delete' && isset($_POST['service_id'])) {
        $service_id = intval($_POST['service_id']);
        $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
        $stmt->bind_param("i", $service_id);
        if ($stmt->execute()) {
            header("Location: ../services/?success=Service deleted successfully");
        } else {
        header("Location: ../services/?error=Cannot delete service while it is still linked to existing appointments.");
        }
        $stmt->close();
    } else {
        header("Location: ../services/?error=Invalid action");
    }
} else {
    header("Location: ../services/?error=Invalid request");
}

$conn->close();
?>