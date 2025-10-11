<?php
session_start();
header('Content-Type: application/json');
require_once '../../db/db_connect.php';
require '../../fp/send_code.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['appointment_id'];
    $action = $_POST['action'];

    // Get user email and name
    $stmt = $conn->prepare("SELECT u.email, u.name, s.name AS service_name, a.appointment_date, a.appointment_time
                            FROM appointments a
                            JOIN users u ON a.user_id = u.id
                            JOIN services s ON a.service_id = s.id
                            WHERE a.id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $stmt->bind_result($email, $name, $service, $date, $time);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Appointment not found']);
        exit();
    }
    $stmt->close();

    $formattedTime = date('h:i A', strtotime($time));
    $subject = "";
    $message = "";

    // Handle actions
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE appointments SET status = 'confirmed' WHERE id = ?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $stmt->close();

        $subject = "Appointment Approved";
        $message = "Hi $name,\n\nYour appointment for $service on $date at $formattedTime has been approved.\n\nThank you!";
    } elseif ($action === 'cancel') {
        $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $stmt->close();

        $subject = "Appointment Cancelled";
        $message = "Hi $name,\n\nYour appointment for $service on $date at $formattedTime has been cancelled.\n\nPlease contact us if you have any questions.";
    } elseif ($action === 'reschedule') {
        if (!isset($_POST['appointment_date'], $_POST['appointment_time'])) {
            echo json_encode(['success' => false, 'error' => 'Missing date/time for reschedule']);
            exit();
        }

        $new_date = $_POST['appointment_date'];
        $new_time = $_POST['appointment_time'];

        $stmt = $conn->prepare("UPDATE appointments SET appointment_date = ?, appointment_time = ?, status = 'confirmed' WHERE id = ?");
        $stmt->bind_param("ssi", $new_date, $new_time, $appointment_id);
        $stmt->execute();
        $stmt->close();

        $formattedTime = date('h:i A', strtotime($new_time));
        $subject = "Appointment Rescheduled";
        $message = "Hi $name,\n\nYour appointment for $service has been rescheduled to $new_date at $formattedTime.\n\nThank you!";
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        exit();
    }

    // Send email notification
    if (!empty($email) && !empty($subject) && !empty($message)) {
        sendEmailNotification($email, $subject, $message);
    }

    echo json_encode(['success' => true, 'message' => "Appointment $action successfully."]);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);