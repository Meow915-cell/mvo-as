<?php
session_start();
require_once '../../db/db_connect.php';
require_once '../restrict_access.php'; // Assuming this is needed for access control

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and has access to the 'services' or 'walkin' module
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$module = 'services'; // Or a more appropriate module name like 'walkin_management'
// Note: You would need to check restrictAccess here if you want to enforce role-based access.
// Example: $access = restrictAccess($conn, $_SESSION['user_id'], $module);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- Sanitize and Validate common input fields ---
    $owner_name = isset($_POST['owner_name']) ? trim($_POST['owner_name']) : null;
    $pet_name = isset($_POST['pet_name']) ? trim($_POST['pet_name']) : null;
    $type = isset($_POST['type']) ? trim($_POST['type']) : null;
    $age = isset($_POST['age']) ? floatval($_POST['age']) : null;
    $breed = isset($_POST['breed']) ? trim($_POST['breed']) : null;
    $body_temp = isset($_POST['body_temp']) ? floatval($_POST['body_temp']) : null;
    $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : null;
    $walkin_id = isset($_POST['walkin_id']) ? intval($_POST['walkin_id']) : null;

    // Optional fields (favorite_activity, medical_history) from the table definition
    $favorite_activity = isset($_POST['favorite_activity']) ? trim($_POST['favorite_activity']) : null;
    $medical_history = isset($_POST['medical_history']) ? trim($_POST['medical_history']) : null;


    if ($action === 'add') {
        // --- ADD Logic ---
        if (empty($owner_name) || empty($pet_name)) {
            header("Location: ../walkin/index.php?error=Owner and Pet name are required");
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO walkin (owner_name, pet_name, type, age, breed, favorite_activity, medical_history, body_temp, weight) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdssdss", $owner_name, $pet_name, $type, $age, $breed, $favorite_activity, $medical_history, $body_temp, $weight);

        if ($stmt->execute()) {
            header("Location: ../walkin/index.php?success=Walk-in record added successfully");
        } else {
            error_log("Failed to add walk-in record: " . $stmt->error);
            header("Location: ../walkin/index.php?error=Failed to add record");
        }
        $stmt->close();

    } elseif ($action === 'edit') {
        // --- EDIT Logic ---
        if (empty($walkin_id) || empty($owner_name) || empty($pet_name)) {
            header("Location: ../walkin/index.php?error=ID, Owner, and Pet name are required for edit");
            exit();
        }

        $stmt = $conn->prepare("UPDATE walkin SET owner_name = ?, pet_name = ?, type = ?, age = ?, breed = ?, favorite_activity = ?, medical_history = ?, body_temp = ?, weight = ? WHERE id = ?");
        $stmt->bind_param("sssdssdssi", $owner_name, $pet_name, $type, $age, $breed, $favorite_activity, $medical_history, $body_temp, $weight, $walkin_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                header("Location: ../walkin/index.php?success=Walk-in record updated successfully");
            } else {
                header("Location: ../walkin/index.php?info=No changes made to the record");
            }
        } else {
            error_log("Failed to edit walk-in record ID $walkin_id: " . $stmt->error);
            header("Location: ../walkin/index.php?error=Failed to update record");
        }
        $stmt->close();

    } elseif ($action === 'delete') {
        // --- DELETE Logic ---
        if (empty($walkin_id)) {
            header("Location: ../walkin/index.php?error=ID is required for deletion");
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM walkin WHERE id = ?");
        $stmt->bind_param("i", $walkin_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                error_log("Walk-in record deleted successfully: ID=$walkin_id");
                header("Location: ../walkin/index.php?success=Walk-in record deleted successfully");
            } else {
                error_log("No walk-in record was deleted: ID=$walkin_id");
                header("Location: ../walkin/index.php?error=Record not found or already deleted");
            }
        } else {
            $error_message = $conn->error;
            error_log("Failed to delete walk-in record ID $walkin_id: $error_message");
            header("Location: ../walkin/index.php?error=Failed to delete record: " . urlencode($error_message));
        }
        $stmt->close();

    } else {
        error_log("Invalid action received: $action");
        header("Location: ../walkin/index.php?error=Invalid action");
    }

} else {
    error_log("Invalid request method or missing action.");
    header("Location: ../walkin/index.php?error=Invalid request");
}

$conn->close();
?>