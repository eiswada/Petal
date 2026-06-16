<?php
require_once '../../includes/config.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Stats
$total_private = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM private_messages"))[0];
$total_public  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM public_messages"))[0];
$total_pending = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM private_messages WHERE status='pending'"))[0];

// Tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'private';

// Search
$search = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : '';

// Pagination
$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

if ($tab === 'private') {
    $where = '';
    if ($search) {
        $s = mysqli_real_escape_string($conn, $search);
        $where = "WHERE sender_name LIKE '%$s%' OR email_tujuan LIKE '%$s%'";
    }
    $total_rows = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM private_messages $where"))[0];
    $result = mysqli_query($conn, "SELECT *, is_delivery_due(tanggal_kirim, status) AS is_due FROM private_messages $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
    $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $where = '';
    if ($search) {
        $s = mysqli_real_escape_string($conn, $search);
        $where = "WHERE untuk_siapa LIKE '%$s%' OR pesan LIKE '%$s%'";
    }
    $total_rows = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM public_messages $where"))[0];
    $result = mysqli_query($conn, "SELECT * FROM public_messages $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
    $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$total_pages = ceil($total_rows / $per_page);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Petal Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="admin-sidebar d-none d-md-block" style="width:220px;min-width:220px;">
        <div class="petal-brand mb-4 d-flex align-items-center gap-2">
            <img src="../../assets/img/logo.png" alt="Petal" style="width: 28px; height: auto;"> Petal
        </div>
        <nav class="nav flex-column gap-1">
            <a href="?tab=private" class="nav-link <?= $tab=='private' ? 'active' : '' ?>">
                <i class="bi bi-envelope me-2"></i> Private Letters
            </a>
            <a href="?tab=public" class="nav-link <?= $tab=='public' ? 'active' : '' ?>">
                <i class="bi bi-globe me-2"></i> Public Messages
            </a>
            <a href="settings.php" class="nav-link">
                <i class="bi bi-gear me-2"></i> Settings
            </a>
            <hr style="border-color:rgba(255,255,255,.1);">
            <a href="logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left me-2"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="admin-content flex-grow-1">
        <!-- Top bar mobile -->
        <div class="d-flex d-md-none justify-content-between align-items-center mb-4 p-3" style="background:var(--dark);border-radius:12px;">
            <span class="text-white fw-bold d-flex align-items-center gap-2">
                <img src="../../assets/img/logo.png" alt="Petal" style="width: 24px; height: auto;"> Petal Admin
            </span>
            <div class="d-flex gap-1">
                <a href="dashboard.php?tab=private" class="btn btn-sm" style="background: <?= $tab=='private' ? 'rgba(255,255,255,0.9)' : 'transparent' ?>; color: <?= $tab=='private' ? 'var(--dark)' : '#ffffff' ?>; border: 1px solid rgba(255,255,255,0.4);" title="Private Letters"><i class="bi bi-envelope"></i></a>
                <a href="dashboard.php?tab=public" class="btn btn-sm" style="background: <?= $tab=='public' ? 'rgba(255,255,255,0.9)' : 'transparent' ?>; color: <?= $tab=='public' ? 'var(--dark)' : '#ffffff' ?>; border: 1px solid rgba(255,255,255,0.4);" title="Public Messages"><i class="bi bi-globe"></i></a>
                <a href="settings.php" class="btn btn-sm" style="background: transparent; color: #ffffff; border: 1px solid rgba(255,255,255,0.4);" title="Settings"><i class="bi bi-gear"></i></a>
                <a href="logout.php" class="btn btn-sm btn-danger" style="border: 1px solid transparent;" title="Logout"><i class="bi bi-box-arrow-left"></i></a>
            </div>
        </div>

        <h4 class="fw-bold mb-1">Dashboard</h4>
        <p class="text-muted small mb-4">Welcome back, <?= htmlspecialchars($_SESSION['admin_username']) ?> !</p>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_private ?></div>
                    <div class="stat-label">Private Letters</div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_public ?></div>
                    <div class="stat-label">Public Messages</div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_pending ?></div>
                    <div class="stat-label">Pending Delivery</div>
                </div>
            </div>
        </div>

        <!-- Flash message -->
        <?php if (isset($_GET['deleted'])): ?>
        <div class="petal-alert-success mb-3 auto-dismiss">Message deleted successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
        <div class="petal-alert-success mb-3 auto-dismiss">Status updated successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['sent']) && $_GET['sent'] == '1'): ?>
        <div class="petal-alert-success mb-3 auto-dismiss">Email sent successfully! Check Mailtrap inbox.</div>
        <?php elseif (isset($_GET['sent']) && $_GET['sent'] == '0'): ?>
        <div class="petal-alert-error mb-3 auto-dismiss">Failed to send email. Check mailer config.</div>
        <?php endif; ?>

        <!-- Search + Tab -->
        <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
            <form method="GET" class="petal-search" style="min-width:220px;">
                <input type="hidden" name="tab" value="<?= $tab ?>">
                <i class="bi bi-search search-icon"></i>
                <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= $search ?>">
            </form>
            <span class="text-muted small"><?= $total_rows ?> record(s)</span>
        </div>

        <!-- Table -->
        <div class="petal-table table-responsive">
            <?php if ($tab === 'private'): ?>
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Send Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No messages found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($messages as $msg): ?>
                    <tr class="<?= isset($msg['is_due']) && $msg['is_due'] == 1 ? 'table-danger' : '' ?>">
                        <td class="text-muted"><?= $msg['id'] ?></td>
                        <td>
                            <?= htmlspecialchars($msg['sender_name']) ?>
                            <?php if (isset($msg['is_due']) && $msg['is_due'] == 1): ?>
                                <span class="badge bg-danger ms-1 animate-pulse" style="font-size:0.65rem;">DUE</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($msg['email_tujuan']) ?></td>
                        <td><?= date('d M Y', strtotime($msg['tanggal_kirim'])) ?></td>
                        <td>
                            <span class="badge-<?= $msg['status'] ?>"><?= ucfirst($msg['status']) ?></span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <!-- Update status -->
                                <form method="POST" action="update-status.php" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" style="width:110px;" 
                                            onchange="this.form.submit()">
                                        <option value="pending" <?= $msg['status']=='pending'?'selected':'' ?>>Pending</option>
                                        <option value="sent" <?= $msg['status']=='sent'?'selected':'' ?>>Sent</option>
                                        <option value="cancelled" <?= $msg['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                                    </select>
                                    <input type="hidden" name="tab" value="private">
                                </form>
                                <?php if ($msg['status'] === 'pending'): ?>
                                <a href="send-now.php?id=<?= $msg['id'] ?>"
                                   class="btn btn-sm"
                                   style="background:var(--purple-light);color:var(--purple-dark);border:none;border-radius:8px;padding:.3rem .7rem;font-weight:600;"
                                   onclick="return confirm('Send this email now to <?= htmlspecialchars($msg['email_tujuan']) ?>?')">
                                    Send
                                </a>
                                <?php endif; ?>
                                <a href="delete.php?id=<?= $msg['id'] ?>&type=private&tab=private" 
                                   class="btn btn-sm btn-delete" 
                                   style="background:var(--pink-light);color:var(--pink-dark);border:none;border-radius:8px;padding:.3rem .7rem;">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php else: ?>
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>For</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No messages found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($messages as $msg): ?>
                    <tr>
                        <td class="text-muted"><?= $msg['id'] ?></td>
                        <td><?= htmlspecialchars($msg['untuk_siapa']) ?></td>
                        <td style="max-width:300px;">
                            <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;max-width:280px;">
                                <?= htmlspecialchars($msg['pesan']) ?>
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($msg['created_at'])) ?></td>
                        <td>
                            <a href="delete.php?id=<?= $msg['id'] ?>&type=public&tab=public" 
                               class="btn btn-sm btn-delete"
                               style="background:var(--pink-light);color:var(--pink-dark);border:none;border-radius:8px;padding:.3rem .7rem;">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav class="mt-4 d-flex justify-content-center">
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?tab=<?= $tab ?>&page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <!-- Activity Logs Section -->
        <div class="mt-5 pt-4 border-top">
            <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>System Activity Logs</h5>
            <div class="petal-table table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Admin Operator</th>
                            <th>Action Type</th>
                            <th>Target Table</th>
                            <th>Target Record ID</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $logs_res = mysqli_query($conn, "SELECT * FROM v_admin_activity_log ORDER BY created_at DESC LIMIT 10");
                        $logs = mysqli_fetch_all($logs_res, MYSQLI_ASSOC);
                        if (empty($logs)):
                        ?>
                        <tr><td colspan="6" class="text-center text-muted py-3">No activity logs recorded. Try deleting a message to see triggers and logs in action.</td></tr>
                        <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-muted"><?= $log['id'] ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($log['username'] ?? 'System') ?></span></td>
                            <td><code class="text-danger"><?= htmlspecialchars($log['action']) ?></code></td>
                            <td><?= htmlspecialchars($log['target_table']) ?></td>
                            <td><?= htmlspecialchars($log['target_id']) ?></td>
                            <td class="small text-muted"><?= date('d M Y H:i:s', strtotime($log['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/main.js"></script>
</body>
</html>