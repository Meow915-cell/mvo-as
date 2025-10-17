<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized or missing ID']);
    exit();
}

$pet_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, name, type, age, breed, favorite_activity, medical_history, image FROM pets WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $pet_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    header('Content-Type: application/json');
    echo json_encode($result->fetch_assoc());
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Pet not found']);
}

$stmt->close();
$conn->close();
?>