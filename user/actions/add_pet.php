<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $age = !empty($_POST['age']) ? intval($_POST['age']) : null;
    $breed = trim($_POST['breed']) ?: null;
    $favorite_activity = trim($_POST['favorite_activity']) ?: null;
    $medical_history = trim($_POST['medical_history']) ?: null;
    $user_id = $_SESSION['user_id'];
    $image = null;

    if (empty($name) || empty($type)) {
        header("Location: ../pets.php?error=Name and type are required");
        exit();
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 8 * 1024 * 1024;
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = 'pet_' . time() . '.' . $ext;
            $destination = '../../uploads/' . $image;
            if (!move_uploaded_file($file_tmp, $destination)) {
                header("Location: ../pets.php?error=Failed to upload image");
                exit();
            }
        } else {
            header("Location: ../pets.php?error=Invalid image type or size");
            exit();
        }
    }

    $stmt = $conn->prepare("INSERT INTO pets (user_id, name, type, age, breed, favorite_activity, medical_history, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississss", $user_id, $name, $type, $age, $breed, $favorite_activity, $medical_history, $image);

    if ($stmt->execute()) {
        header("Location: ../pets.php");
    } else {
        header("Location: ../pets.php?error=Failed to add pet");
    }
    $stmt->close();
}
$conn->close();
?>