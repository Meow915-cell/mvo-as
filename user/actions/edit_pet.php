<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_id = intval($_POST['pet_id']);
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $age = !empty($_POST['age']) ? intval($_POST['age']) : null;
    $breed = trim($_POST['breed']) ?: null;
    $favorite_activity = trim($_POST['favorite_activity']) ?: null;
    $medical_history = trim($_POST['medical_history']) ?: null;
    $user_id = $_SESSION['user_id'];
    $image = null;

    if (empty($name) || empty($type)) {
        header("Location: ../pets.php?edit=$pet_id&error=Name and type are required");
        exit();
    }

    // Get current image (if any) to delete later
    $stmt = $conn->prepare("SELECT image FROM pets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $pet_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $old_image = $result->num_rows == 1 ? $result->fetch_assoc()['image'] : null;
    $stmt->close();

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 6 * 1024 * 1024;
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = 'pet_' . time() . '.' . $ext;
            $destination = '../../uploads/' . $image;
            if (move_uploaded_file($file_tmp, $destination)) {
                // Delete old image if it exists
                if ($old_image && file_exists('../../uploads/' . $old_image)) {
                    unlink('../../uploads/' . $old_image);
                }
            } else {
                header("Location: ../pets.php?edit=$pet_id&error=Failed to upload image");
                exit();
            }
        } else {
            header("Location: ../pets.php?edit=$pet_id&error=Invalid image type or size");
            exit();
        }
    }

    // Update pet (include image only if a new one was uploaded)
    if ($image) {
        $stmt = $conn->prepare("UPDATE pets SET name = ?, type = ?, age = ?, breed = ?, favorite_activity = ?, medical_history = ?, image = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssisssssi", $name, $type, $age, $breed, $favorite_activity, $medical_history, $image, $pet_id, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE pets SET name = ?, type = ?, age = ?, breed = ?, favorite_activity = ?, medical_history = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssisssii", $name, $type, $age, $breed, $favorite_activity, $medical_history, $pet_id, $user_id);
    }

    if ($stmt->execute()) {
        header("Location: ../pets.php");
    } else {
        header("Location: ../pets.php?edit=$pet_id&error=Failed to update pet");
    }
    $stmt->close();
}
$conn->close();
?>