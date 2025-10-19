<?php
session_start();
require_once '../../db/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $user_id = $_SESSION['user_id'];
    $appointment_id = intval($_POST['id']);

    // Verify appointment belongs to user and is cancellable
    $stmt = $conn->prepare("SELECT status FROM appointments WHERE id = ? AND user_id = ? AND status IN ('pending', 'confirmed')");
    $stmt->bind_param("ii", $appointment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Appointment not found or cannot be cancelled']);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // Update status to cancelled
    $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $appointment_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to cancel appointment']);
    }
    $stmt->close();
}
$conn->close();
?>
