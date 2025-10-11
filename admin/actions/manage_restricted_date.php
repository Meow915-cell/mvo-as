<?php
/* ------------- security / bootstrap ------------------ */
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

/* ------------- small helper for redirects ------------ */
function go($msg, $ok = true) {
    // If the request came from Fetch/Ajax we just echo instead of redirect
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo $ok ? 'OK' : 'ERROR: ' . $msg;
        exit();
    }

    $type = $ok ? 'success' : 'error';
    header('Location: ../schedules.php?' . $type . '=' . urlencode($msg));
    exit();
}

/* ------------- validate request ---------------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    go('Invalid request', false);
}

$action = $_POST['action'];

/* ------------- common input -------------------------- */
$reason      = isset($_POST['reason'])        ? trim($_POST['reason']) : '';
$start_time  = isset($_POST['start_time'])    ? $_POST['start_time']   : null;
$end_time    = isset($_POST['end_time'])      ? $_POST['end_time']     : null;
$date_id     = isset($_POST['date_id'])       ? intval($_POST['date_id']) : 0;
$restr_date  = isset($_POST['restricted_date']) ? $_POST['restricted_date'] : null;

/* ------------- add ----------------------------------- */
if ($action === 'add') {
    if (!$restr_date || !$start_time || !$end_time) {
        go('Missing required fields', false);
    }

    if (strtotime($start_time) >= strtotime($end_time)) {
        go('End-time must be later than start-time', false);
    }

    /* block duplicates for the same calendar day */
    $stmt = $conn->prepare(
        'SELECT id FROM restricted_dates WHERE restricted_date = ?'
    );
    $stmt->bind_param('s', $restr_date);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows) {
        $stmt->close();
        go('The selected date already has a restriction – use Edit instead', false);
    }
    $stmt->close();

    $stmt = $conn->prepare(
        'INSERT INTO restricted_dates (restricted_date, reason, start_time, end_time)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->bind_param('ssss', $restr_date, $reason, $start_time, $end_time);
    $stmt->execute();
    $stmt->close();

    go('Restricted date added');
}

/* ------------- update -------------------------------- */
if ($action === 'update') {
    if (!$date_id || !$start_time || !$end_time) {
        go('Missing required fields', false);
    }
    if (strtotime($start_time) >= strtotime($end_time)) {
        go('End-time must be later than start-time', false);
    }

    $stmt = $conn->prepare(
        'UPDATE restricted_dates
           SET reason = ?, start_time = ?, end_time = ?
         WHERE id = ?'
    );
    $stmt->bind_param('sssi', $reason, $start_time, $end_time, $date_id);
    $stmt->execute();
    $stmt->close();

    go('Restricted date updated');
}

/* ------------- delete -------------------------------- */
if ($action === 'delete') {
    if (!$date_id) {
        go('Missing record id', false);
    }

    $stmt = $conn->prepare(
        'DELETE FROM restricted_dates WHERE id = ?'
    );
    $stmt->bind_param('i', $date_id);
    $stmt->execute();
    $stmt->close();

    go('Restricted date deleted');
}

/* ------------- fallback ------------------------------ */
go('Invalid action', false);