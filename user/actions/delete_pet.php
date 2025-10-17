<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_id = intval($_POST['pet_id']);
    $user_id = $_SESSION['user_id'];

    // Get image filename before deletion
    $stmt = $conn->prepare("SELECT image FROM pets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $pet_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pet = $result->fetch_assoc();
    $stmt->close();

    // Delete pet from database
    $stmt = $conn->prepare("DELETE FROM pets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $pet_id, $user_id);

    if ($stmt->execute()) {
        // Delete image file if it exists
        if ($pet['image']) {
            $image_path = '../../Uploads/' . $pet['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        header("Location: ../pets.php");
    } else {
        header("Location: ../pets.php?error=Failed to delete pet");
    }
    $stmt->close();
}
$conn->close();
?>