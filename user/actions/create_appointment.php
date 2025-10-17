<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $pet_id = intval($_POST['pet_id']);
    $service_id = intval($_POST['service_id']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = htmlspecialchars($_POST['reason']);

    // Validate inputs
    if (empty($pet_id) || empty($service_id) || empty($appointment_date) || empty($appointment_time) || empty($reason)) {
        header("Location: ../my-appointments.php?error=All fields are required");
        exit();
    }

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

    // Check if date is a Sunday
    $selected_date = new DateTime($appointment_date);
    $is_sunday = $selected_date->format('w') === '0';
    if ($is_sunday) {
        header("Location: ../my-appointments.php?error=Selected date is unavailable (Sundays)");
        exit();
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
        header("Location: ../my-appointments.php?error=Selected time is unavailable (restricted time slot)");
        exit();
    }

    // Insert appointment
    $status = 'pending';
    $stmt = $conn->prepare("INSERT INTO appointments (user_id, pet_id, service_id, appointment_date, appointment_time, reason, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissss", $user_id, $pet_id, $service_id, $appointment_date, $appointment_time, $reason, $status);

    if ($stmt->execute()) {
        header("Location: ../my-appointments.php?success=Appointment created successfully");
    } else {
        header("Location: ../my-appointments.php?error=Failed to create appointment");
    }
    $stmt->close();
}
$conn->close();
?>