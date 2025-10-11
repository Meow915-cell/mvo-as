<?php
session_start();
require_once '../../db/db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

$appointment_id = intval($_GET['id']);
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if ($is_admin) {
    // Admins can fetch any appointment
    $stmt = $conn->prepare("SELECT id, pet_id, service_id, appointment_date, appointment_time, reason 
                            FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $appointment_id);
} else {
    // Non-admins (customers) can only fetch their own appointments
    $stmt = $conn->prepare("SELECT id, pet_id, service_id, appointment_date, appointment_time, reason 
                            FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
}

$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Appointment not found']);
}

$stmt->close();
$conn->close();
?>