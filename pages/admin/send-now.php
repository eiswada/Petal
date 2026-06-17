<?php
require_once '../../includes/config.php';
require_once '../../includes/mailer.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM private_messages WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $msg = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($msg) {
            $result = sendFutureLetter(
            $msg['email_tujuan'],
            $msg['sender_name'],
            $msg['pesan'],
            $msg['tanggal_kirim'],
            $msg['color']
        );

        if ($result === true) {
            $upd = mysqli_prepare($conn, "UPDATE private_messages SET status = 'sent' WHERE id = ?");
            mysqli_stmt_bind_param($upd, 'i', $id);
            mysqli_stmt_execute($upd);
            header('Location: dashboard.php?tab=private&sent=1');
            exit;
        } else {
            // Show error detail instead of redirecting
            echo "<pre style='padding:2rem;font-family:monospace;'>";
            echo "<strong>❌ Send failed!</strong>\n\n";
            echo "To: " . htmlspecialchars($msg['email_tujuan']) . "\n";
            echo "Error: " . htmlspecialchars($result) . "\n";
            echo "</pre>";
            exit;
        }
    }
}

header('Location: dashboard.php?tab=private');
exit;