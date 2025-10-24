<?php
require_once '../../db/db_connect.php';

if (isset($_GET['drug_id'])) {
    $drug_id = intval($_GET['drug_id']);
    $query = "SELECT date_out, count_out FROM drug_out WHERE drug_id = ? ORDER BY date_out DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $drug_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }

    echo json_encode($history);
}
?>
