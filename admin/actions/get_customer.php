<?php
require_once '../../db/db_connect.php';

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

$stmt = $conn->prepare("
    SELECT u.id, u.name, u.email, u.phone, u.address 
    FROM users u 
    WHERE u.id = ? AND u.role = 'user'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$pets_stmt = $conn->prepare("
    SELECT p.id, p.name, p.image 
    FROM pets p 
    WHERE p.user_id = ?
");
$pets_stmt->bind_param("i", $user_id);
$pets_stmt->execute();
$pets = $pets_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$appointments_stmt = $conn->prepare("
    SELECT a.appointment_date AS date, a.appointment_time AS time, p.name AS pet_name, s.name AS service_name, a.reason, a.status
    FROM appointments a
    JOIN pets p ON a.pet_id = p.id
    JOIN services s ON a.service_id = s.id
    WHERE a.user_id = ? AND a.status IN ('pending', 'completed')
    ORDER BY a.appointment_date DESC
");
$appointments_stmt->bind_param("i", $user_id);
$appointments_stmt->execute();
$appointments = $appointments_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$response = [
    'name' => $user['name'] ?? '',
    'email' => $user['email'] ?? '',
    'phone' => $user['phone'] ?? '',
    'address' => $user['address'] ?? '',
    'pets' => $pets,
    'appointments' => $appointments
];

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>