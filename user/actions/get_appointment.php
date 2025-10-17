<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $appointment_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT id, pet_id, service_id, appointment_date, appointment_time, reason FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $appointment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($appointment);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Appointment not found']);
    }

    $stmt->close();
}
$conn->close();
?>