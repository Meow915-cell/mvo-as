<?php
session_start();
require_once '../../db/db_connect.php';

// Check admin session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

// Define upload directory and limits
$upload_dir = '../../Uploads/';
$allowed_types = ['image/jpeg', 'image/png'];
$max_size = 5 * 1024 * 1024; // 5MB

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../veterinarians/index.php?error=Invalid request (Not POST)");
    exit();
}

// Ensure 'action' field exists
if (!isset($_POST['action'])) {
    header("Location: ../veterinarians/index.php?error=Missing action parameter");
    exit();
}

$action = $_POST['action'];

// ADD VETERINARIAN
if ($action === 'add') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    $specialization = !empty($_POST['specialization']) ? trim($_POST['specialization']) : null;
    $image = null;

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_ext;

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                $image = $file_name;
            } else {
                header("Location: ../veterinarians/index.php?error=Failed to upload image");
                exit();
            }
        } else {
            header("Location: ../veterinarians/index.php?error=Invalid image type or size");
            exit();
        }
    }

    // Check email uniqueness
    $stmt = $conn->prepare("SELECT id FROM veterinarians WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        header("Location: ../veterinarians/index.php?error=Email already exists");
        exit();
    }
    $stmt->close();

    // Insert
    $stmt = $conn->prepare("INSERT INTO veterinarians (name, email, phone, specialization, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $phone, $specialization, $image);
    if ($stmt->execute()) {
        header("Location: ../veterinarians/index.php?success=Veterinarian added successfully");
    } else {
        header("Location: ../veterinarians/index.php?error=Failed to add veterinarian: " . urlencode($conn->error));
    }
    $stmt->close();
}

// EDIT VETERINARIAN
elseif ($action === 'edit' && isset($_POST['vet_id'])) {
    $vet_id = intval($_POST['vet_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    $specialization = !empty($_POST['specialization']) ? trim($_POST['specialization']) : null;

    // Fetch existing image
    $stmt = $conn->prepare("SELECT image FROM veterinarians WHERE id = ?");
    $stmt->bind_param("i", $vet_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $existing_image = $result ? $result['image'] : null;
    $stmt->close();

    $image = $existing_image;

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_ext;

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                $image = $file_name;
                // Delete old image if it exists
                if ($existing_image && file_exists($upload_dir . $existing_image)) {
                    unlink($upload_dir . $existing_image);
                }
            } else {
                header("Location: ../veterinarians/index.php?error=Failed to upload image");
                exit();
            }
        } else {
            header("Location: ../veterinarians/index.php?error=Invalid image type or size");
            exit();
        }
    }

    // Check email uniqueness (excluding current)
    $stmt = $conn->prepare("SELECT id FROM veterinarians WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $vet_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        header("Location: ../veterinarians/index.php?error=Email already exists");
        exit();
    }
    $stmt->close();

    // Update
    $stmt = $conn->prepare("UPDATE veterinarians SET name=?, email=?, phone=?, specialization=?, image=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $email, $phone, $specialization, $image, $vet_id);
    if ($stmt->execute()) {
        header("Location: ../veterinarians/index.php?success=Veterinarian updated successfully");
    } else {
        header("Location: ../veterinarians/index.php?error=Failed to update veterinarian: " . urlencode($conn->error));
    }
    $stmt->close();
}

// DELETE VETERINARIAN
elseif ($action === 'delete' && isset($_POST['vet_id'])) {
    $vet_id = intval($_POST['vet_id']);

    // Get image to delete
    $stmt = $conn->prepare("SELECT image FROM veterinarians WHERE id = ?");
    $stmt->bind_param("i", $vet_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $image = $result ? $result['image'] : null;
    $stmt->close();

    // Delete vet
    $stmt = $conn->prepare("DELETE FROM veterinarians WHERE id = ?");
    $stmt->bind_param("i", $vet_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        if ($image && file_exists($upload_dir . $image)) {
            unlink($upload_dir . $image);
        }
        header("Location: ../veterinarians/index.php?success=Veterinarian deleted successfully");
    } else {
        header("Location: ../veterinarians/index.php?error=Failed to delete veterinarian");
    }
    $stmt->close();
}

// UNKNOWN ACTION
else {
    header("Location: ../veterinarians/index.php?error=Invalid action or missing parameters");
}

$conn->close();
?>