<?php
session_start();
require_once '../../db/db_connect.php';
require_once '../restrict_access.php'; 

// Function to redirect with an error message
function redirectWithError($conn, $message) {
    if ($conn) $conn->close();
    header("Location: ../customers/index.php?error=" . urlencode($message));
    exit();
}

// Function to redirect with a success message
function redirectWithSuccess($conn, $message) {
    if ($conn) $conn->close();
    header("Location: ../customers/index.php?success=" . urlencode($message));
    exit();
}

// Check if user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'schedule_follow_up') {
    // Admin is scheduling for $user_id, which is passed in the form
    $user_id = intval($_POST['user_id']); 
    $pet_id = intval($_POST['pet_id']);
    $service_id = intval($_POST['service_id']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = htmlspecialchars($_POST['reason']);

    // Validate inputs
    if (empty($user_id) || empty($pet_id) || empty($service_id) || empty($appointment_date) || empty($appointment_time) || empty($reason)) {
        redirectWithError($conn, "All fields are required.");
    }

    // Check if user exists and is a 'user' role
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'user'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        $stmt->close();
        redirectWithError($conn, "Invalid customer selected.");
    }
    $stmt->close();

    // Check if pet belongs to this user
    $stmt = $conn->prepare("SELECT id FROM pets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $pet_id, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        $stmt->close();
        redirectWithError($conn, "Invalid pet selected for this customer.");
    }
    $stmt->close();

    // Check if service exists
    $stmt = $conn->prepare("SELECT id FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        $stmt->close();
        redirectWithError($conn, "Invalid service selected.");
    }
    $stmt->close();

    // Check if date is a Sunday
    $selected_date = new DateTime($appointment_date);
    $is_sunday = $selected_date->format('w') === '0';
    if ($is_sunday) {
        redirectWithError($conn, "Selected date is unavailable (Sundays).");
    }

    // Check if time slot is restricted
    $stmt = $conn->prepare("SELECT restricted_date, start_time, end_time FROM restricted_dates WHERE restricted_date = ?");
    $stmt->bind_param("s", $appointment_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $is_restricted = false;
    while ($row = $result->fetch_assoc()) {
        if ($appointment_time >= $row['start_time'] && $appointment_time <= $row['end_time']) {
            $is_restricted = true;
            break;
        }
    }
    $stmt->close();

    if ($is_restricted) {
        redirectWithError($conn, "Selected time is unavailable (restricted time slot).");
    }

    // Insert appointment
    $status = 'confirmed'; 
    $stmt = $conn->prepare("INSERT INTO appointments (user_id, pet_id, service_id, appointment_date, appointment_time, reason, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissss", $user_id, $pet_id, $service_id, $appointment_date, $appointment_time, $reason, $status);

    if ($stmt->execute()) {
        redirectWithSuccess($conn, "Follow-up appointment scheduled successfully and confirmed.");
    } else {
        redirectWithError($conn, "Failed to schedule follow-up appointment: " . $conn->error);
    }
    $stmt->close();
} else {
    redirectWithError($conn, "Invalid request or action.");
}
$conn->close();
?>