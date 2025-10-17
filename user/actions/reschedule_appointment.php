<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $appointment_id = intval($_POST['appointment_id']);
    $pet_id = intval($_POST['pet_id']);
    $service_id = intval($_POST['service_id']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = htmlspecialchars($_POST['reason']);

    // Validate inputs
    if (empty($appointment_id) || empty($pet_id) || empty($service_id) || empty($appointment_date) || empty($appointment_time) || empty($reason)) {
        header("Location: ../my-appointments.php?error=All fields are required");
        exit();
    }

    // Check if appointment belongs to user
    $stmt = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $appointment_id, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        header("Location: ../my-appointments.php?error=Invalid appointment selected");
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Check if pet belongs to user
    $stmt = $conn->prepare("SELECT id FROM pets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $pet_id, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        header("Location: ../my-appointments.php?error=Invalid pet selected");
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Check if service exists
    $stmt = $conn->prepare("SELECT id FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        header("Location: ../my-appointments.php?error=Invalid service selected");
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Check if date is a Sunday or restricted
    $selected_date = new DateTime($appointment_date);
    $is_sunday = $selected_date->format('w') === '0';
    $stmt = $conn->prepare("SELECT restricted_date FROM restricted_dates WHERE restricted_date = ?");
    $stmt->bind_param("s", $appointment_date);
    $stmt->execute();
    $is_restricted = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($is_sunday || $is_restricted) {
        header("Location: ../my-appointments.php?error=Selected date is unavailable (Sundays or restricted dates)");
        exit();
    }

    // Update appointment
    $stmt = $conn->prepare("UPDATE appointments SET pet_id = ?, service_id = ?, appointment_date = ?, appointment_time = ?, reason = ?, status = 'pending' WHERE id = ?");
    $stmt->bind_param("iisssi", $pet_id, $service_id, $appointment_date, $appointment_time, $reason, $appointment_id);

    if ($stmt->execute()) {
        header("Location: ../my-appointments.php?success=Appointment rescheduled successfully");
    } else {
        header("Location: ../my-appointments.php?error=Failed to reschedule appointment");
    }
    $stmt->close();
}
$conn->close();
?>