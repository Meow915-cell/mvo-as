<?php
require_once '../../db/db_connect.php';

if (!isset($_GET['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = intval($_GET['user_id']);

$stmt = $conn->prepare("SELECT permissions FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

$permissions = json_decode($result['permissions'] ?? '[]', true);
echo json_encode($permissions);
?>
