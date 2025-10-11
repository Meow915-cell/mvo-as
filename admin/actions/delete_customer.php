<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Get pet images before deletion
        $stmt = $conn->prepare("SELECT image FROM pets WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $images = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['image']) {
                $images[] = $row['image'];
            }
        }
        $stmt->close();

        // Delete related appointments
        $stmt = $conn->prepare("DELETE FROM appointments WHERE pet_id IN (SELECT id FROM pets WHERE user_id = ?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // Delete related pets
        $stmt = $conn->prepare("DELETE FROM pets WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Delete image files
            foreach ($images as $image) {
                $image_path = '../../Uploads/' . $image;
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            $conn->commit();
            echo json_encode(['success' => true]);
        } else {
            $conn->rollback();
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found or not a customer']);
        }

        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>