<?php
require_once '../../includes/config.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$id   = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';
$tab  = isset($_GET['tab']) ? $_GET['tab'] : 'private';

if ($id && in_array($type, ['private', 'public'])) {
    $table = $type === 'private' ? 'private_messages' : 'public_messages';
    $stmt = mysqli_prepare($conn, "DELETE FROM $table WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
}

header("Location: dashboard.php?tab=$tab&deleted=1");
exit;
