<?php
session_start();
require_once '../../db/db_connect.php';
require_once '../restrict_access.php';

header('Content-Type: application/json');

// --- Security check: user must be logged in ---
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized access. Please log in.']);
    exit;
}

// --- Validate and sanitize ID ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'Invalid or missing walk-in ID.']);
    exit;
}

$walkin_id = intval($_GET['id']);

try {
    // --- Prepare query to fetch walk-in record ---
    $stmt = $conn->prepare("SELECT 
        id, 
        owner_name, 
        pet_name, 
        type, 
        age, 
        breed, 
        body_temp, 
        weight
        FROM walkin 
        WHERE id = ?");
    
    $stmt->bind_param("i", $walkin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // --- Check if record exists ---
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Walk-in record not found.']);
    } else {
        $walkin = $result->fetch_assoc();
        echo json_encode($walkin);
    }

    $stmt->close();
} catch (Exception $e) {
    // Log the error and return a generic message
    error_log("Error fetching walk-in record: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to retrieve walk-in record.']);
}

$conn->close();
?>