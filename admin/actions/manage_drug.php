<?php
require_once '../../db/db_connect.php';

$action = $_POST['action'] ?? '';

if ($action === 'add_drug') {
    // Add new drug
    $name = trim($_POST['drug_name']);
    $stmt = $conn->prepare("INSERT INTO drugs (drug_name, inventory) VALUES (?, 0)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->close();
    header("Location: ../pages/inventory.php");
    exit();
}

if ($action === 'stock_in') {
    // Stock in
    $drug_id = intval($_POST['drug_id']);
    $count_in = intval($_POST['count_in']);
    $expiry = !empty($_POST['expiry']) ? $_POST['expiry'] : NULL;

    // Insert into drug_in
    $stmt = $conn->prepare("INSERT INTO drug_in (drug_id, count_in, expiry) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $drug_id, $count_in, $expiry);
    $stmt->execute();
    $stmt->close();

    // Update inventory
    $update = $conn->prepare("UPDATE drugs SET inventory = inventory + ? WHERE drug_id = ?");
    $update->bind_param("ii", $count_in, $drug_id);
    $update->execute();
    $update->close();

    header("Location: ../pages/inventory.php");
    exit();
}

if ($action === 'stock_out') {
    // Stock out
    $drug_id = intval($_POST['drug_id']);
    $count_out = intval($_POST['count_out']);

    // Check if enough stock
    $check = $conn->prepare("SELECT inventory FROM drugs WHERE drug_id = ?");
    $check->bind_param("i", $drug_id);
    $check->execute();
    $check->bind_result($current_stock);
    $check->fetch();
    $check->close();

    if ($count_out > $current_stock) {
        die("Error: Not enough stock!");
    }

    // Insert into drug_out
    $stmt = $conn->prepare("INSERT INTO drug_out (drug_id, count_out) VALUES (?, ?)");
    $stmt->bind_param("ii", $drug_id, $count_out);
    $stmt->execute();
    $stmt->close();

    // Update inventory
    $update = $conn->prepare("UPDATE drugs SET inventory = inventory - ? WHERE drug_id = ?");
    $update->bind_param("ii", $count_out, $drug_id);
    $update->execute();
    $update->close();

    header("Location: ../pages/inventory.php");
    exit();
}

if ($action === 'delete_drug') {
    // Delete drug and its records
    $drug_id = intval($_POST['drug_id']);
    
    $conn->query("DELETE FROM drug_in WHERE drug_id = $drug_id");
    $conn->query("DELETE FROM drug_out WHERE drug_id = $drug_id");
    $conn->query("DELETE FROM drugs WHERE drug_id = $drug_id");

    header("Location: ../pages/inventory.php");
    exit();
}

$conn->close();
?>
