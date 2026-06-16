<?php
require_once '../includes/config.php';
$page_title = 'The Wall';

// Pagination
$per_page = 9;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Search
$search = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : '';
$where = '';
if ($search) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $where = "WHERE untuk_siapa LIKE '%$search_safe%' OR pesan LIKE '%$search_safe%'";
}

// Total count
$total = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM public_messages $where"))[0];
$total_pages = ceil($total / $per_page);

// Fetch messages
$result = mysqli_query($conn, "SELECT * FROM v_public_messages_summary $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
$messages = mysqli_fetch_all($result, MYSQLI_ASSOC);

require_once '../includes/header.php';
?>

<section class="py-5">
    <div class="container">

        <!-- Header -->
        <div class="text-center mb-5">
            <h1 class="petal-section-title">The Wall</h1>
            <p class="petal-section-sub">Words left for the world, one petal at a time.</p>
        </div>

        <!-- Search + CTA -->
        <div class="row justify-content-between align-items-center mb-4">
            <div class="col-md-5 mb-3 mb-md-0">
                <form method="GET" class="petal-search">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" id="search-wall" name="search" class="form-control" 
                           placeholder="Search messages..." value="<?= $search ?>">
                </form>
            </div>
            <div class="col-auto">
                <a href="write.php?type=public" class="petal-btn-primary">
                    + Leave a message
                </a>
            </div>
        </div>

        <!-- Total -->
        <p class="text-muted small mb-4">
            <?= $total ?> message<?= $total != 1 ? 's' : '' ?> on the wall
            <?= $search ? " for \"$search\"" : '' ?>
        </p>

        <!-- Messages Grid -->
        <?php if (empty($messages)): ?>
        <div class="text-center py-5">
            <div style="font-size:3rem; color: var(--accent);"><i class="bi bi-mailbox"></i></div>
            <h4 class="mt-3">No messages yet.</h4>
            <p class="text-muted">Be the first to leave a petal for the world.</p>
            <a href="write.php" class="petal-btn-primary mt-2">Write now</a>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($messages as $msg): ?>
            <div class="col-sm-6 col-lg-4">
                <div class="petal-card petal-card--colored" data-color="<?= htmlspecialchars($msg['color'] ?? 'white') ?>" data-searchable>
                    <div class="card-untuk">Untuk <?= htmlspecialchars($msg['untuk_siapa']) ?></div>
                    <div class="card-pesan" style="font-family: <?= $msg['font'] === 'serif' ? '\'Playfair Display\', serif' : ($msg['font'] === 'mono' ? '\'Courier New\', Courier, monospace' : 'inherit') ?>;">"<?= htmlspecialchars($msg['pesan']) ?>"</div>
                    <div class="card-meta">
                        <i class="bi bi-clock"></i> <?= date('d M Y', strtotime($msg['created_at'])) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav class="mt-5 d-flex justify-content-center">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">‹ Prev</a>
                </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">Next ›</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>

    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
