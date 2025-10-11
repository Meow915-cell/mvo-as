<?php
require_once '../../db/db_connect.php';

if (isset($_GET['id'])) {
    $vet_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT id, name, email, phone, specialization FROM veterinarians WHERE id = ?");
    $stmt->bind_param("i", $vet_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($vet = $result->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode($vet);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Veterinarian not found']);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}

$conn->close();
?>