<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=Unauthorized access");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $upload_dir = '../../Uploads/';
    $allowed_types = ['image/jpeg', 'image/png'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if ($_POST['action'] === 'add') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = !empty($_POST['phone']) ? $_POST['phone'] : null;
        $specialization = !empty($_POST['specialization']) ? $_POST['specialization'] : null;
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
                    header("Location: ../veterinarians.php?error=Failed to upload image");
                    exit();
                }
            } else {
                header("Location: ../veterinarians.php?error=Invalid image type or size");
                exit();
            }
        }

        // Check if email is unique
        $stmt = $conn->prepare("SELECT id FROM veterinarians WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            header("Location: ../veterinarians.php?error=Email already exists");
            exit();
        }
        $stmt->close();

        // Add veterinarian
        $stmt = $conn->prepare("INSERT INTO veterinarians (name, email, phone, specialization, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $specialization, $image);
        if ($stmt->execute()) {
            header("Location: ../veterinarians.php?success=Veterinarian added successfully");
        } else {
            header("Location: ../veterinarians.php?error=Failed to add veterinarian: " . urlencode($conn->error));
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'edit' && isset($_POST['vet_id'])) {
        $vet_id = intval($_POST['vet_id']);
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = !empty($_POST['phone']) ? $_POST['phone'] : null;
        $specialization = !empty($_POST['specialization']) ? $_POST['specialization'] : null;
        $image = null;

        // Fetch existing image
        $stmt = $conn->prepare("SELECT image FROM veterinarians WHERE id = ?");
        $stmt->bind_param("i", $vet_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $existing_image = $result['image'];
        $stmt->close();

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
                    // Delete old image if it exists
                    if ($existing_image && file_exists($upload_dir . $existing_image)) {
                        unlink($upload_dir . $existing_image);
                    }
                } else {
                    header("Location: ../veterinarians.php?error=Failed to upload image");
                    exit();
                }
            } else {
                header("Location: ../veterinarians.php?error=Invalid image type or size");
                exit();
            }
        } else {
            $image = $existing_image; // Keep existing image
        }

        // Check if email is unique (excluding current veterinarian)
        $stmt = $conn->prepare("SELECT id FROM veterinarians WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $vet_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            header("Location: ../veterinarians.php?error=Email already exists");
            exit();
        }
        $stmt->close();

        // Update veterinarian
        $stmt = $conn->prepare("UPDATE veterinarians SET name = ?, email = ?, phone = ?, specialization = ?, image = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $name, $email, $phone, $specialization, $image, $vet_id);
        if ($stmt->execute()) {
            header("Location: ../veterinarians.php?success=Veterinarian updated successfully");
        } else {
            header("Location: ../veterinarians.php?error=Failed to update veterinarian: " . urlencode($conn->error));
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'delete' && isset($_POST['vet_id'])) {
        $vet_id = intval($_POST['vet_id']);

        // Fetch image to delete
        $stmt = $conn->prepare("SELECT image FROM veterinarians WHERE id = ?");
        $stmt->bind_param("i", $vet_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $image = $result['image'];
        $stmt->close();

        // Delete veterinarian
        $stmt = $conn->prepare("DELETE FROM veterinarians WHERE id = ?");
        $stmt->bind_param("i", $vet_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Delete image if it exists
                if ($image && file_exists($upload_dir . $image)) {
                    unlink($upload_dir . $image);
                }
                header("Location: ../veterinarians.php?success=Veterinarian deleted successfully");
            } else {
                header("Location: ../veterinarians.php?error=No veterinarian was deleted");
            }
        } else {
            header("Location: ../veterinarians.php?error=Failed to delete veterinarian: " . urlencode($conn->error));
        }
        $stmt->close();
    } else {
        header("Location: ../veterinarians.php?error=Invalid request");
    }
} else {
    header("Location: ../veterinarians.php?error=Invalid request");
}

$conn->close();
?>