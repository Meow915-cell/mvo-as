<?php
require_once '../../db/db_connect.php';

if (isset($_GET['drug_id'])) {
    $id = intval($_GET['drug_id']);

    // Fetch main drug info
    $query = "
        SELECT 
            d.drug_id,
            d.drug_name,
            d.inventory,
            (SELECT MAX(date_in) FROM drug_in WHERE drug_id = d.drug_id) AS last_in,
            (SELECT MAX(date_out) FROM drug_out WHERE drug_id = d.drug_id) AS last_out
        FROM drugs d
        WHERE d.drug_id = $id
    ";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();

    // Fetch IN records
    $inQuery = $conn->query("
        SELECT in_id, date_in, expiry, count_in 
        FROM drug_in 
        WHERE drug_id = $id
        ORDER BY date_in DESC
    ");

    $inRecords = [];
    while ($row = $inQuery->fetch_assoc()) {
        $inRecords[] = $row;
    }

    $data['in_records'] = $inRecords;

    echo json_encode($data);
}
?>
