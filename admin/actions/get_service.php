<?php
require_once '../../db/db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT id, name, description FROM services WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($service = $result->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode($service);
    } else {
        echo json_encode(['error' => 'Service not found']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid service ID']);
}

$conn->close();
?>