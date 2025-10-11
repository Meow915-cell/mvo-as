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
        $price = floatval($_POST['price']);

        if (empty($name) || $price < 0) {
            header("Location: ../services.php?error=Invalid input data");
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO services (name, description, price) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $name, $description, $price);
        if ($stmt->execute()) {
            header("Location: ../services.php?success=Service added successfully");
        } else {
            header("Location: ../services.php?error=Failed to add service");
        }
        $stmt->close();
    } elseif ($action === 'edit' && isset($_POST['service_id'])) {
        $service_id = intval($_POST['service_id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);

        if (empty($name) || $price < 0) {
            header("Location: ../services.php?error=Invalid input data");
            exit();
        }

        $stmt = $conn->prepare("UPDATE services SET name = ?, description = ?, price = ? WHERE id = ?");
        $stmt->bind_param("ssdi", $name, $description, $price, $service_id);
        if ($stmt->execute()) {
            header("Location: ../services.php?success=Service updated successfully");
        } else {
            header("Location: ../services.php?error=Failed to update service");
        }
        $stmt->close();
    } elseif ($action === 'delete' && isset($_POST['service_id'])) {
        $service_id = intval($_POST['service_id']);
        $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
        $stmt->bind_param("i", $service_id);
        if ($stmt->execute()) {
            header("Location: ../services.php?success=Service deleted successfully");
        } else {
            header("Location: ../services.php?error=Failed to delete service");
        }
        $stmt->close();
    } else {
        header("Location: ../services.php?error=Invalid action");
    }
} else {
    header("Location: ../services.php?error=Invalid request");
}

$conn->close();
?>