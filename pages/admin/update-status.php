<?php
require_once '../../includes/config.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$id     = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';
$tab    = isset($_POST['tab']) ? $_POST['tab'] : 'private';

$allowed = ['pending', 'sent', 'cancelled'];

if ($id && in_array($status, $allowed)) {
    $stmt = mysqli_prepare($conn, "UPDATE private_messages SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $id);
    mysqli_stmt_execute($stmt);
}

header("Location: dashboard.php?tab=$tab&updated=1");
exit;
