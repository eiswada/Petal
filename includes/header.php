<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petal— <?= isset($page_title) ? htmlspecialchars($page_title) : 'Leave a message for the future' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- FLOATING PILL NAVBAR -->
<div class="floating-nav-wrap">
    <nav class="floating-nav">
        <a href="<?= BASE_URL ?>" class="floating-brand">
            <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Petal" class="nav-logo"> Petal
        </a>

        <div class="floating-nav-items">
            <a href="<?= BASE_URL ?>" 
               class="floating-nav-item <?= $current_page == 'index.php' ? 'active' : '' ?>">
                Home
            </a>
            <a href="<?= BASE_URL ?>/pages/public-wall.php" 
               class="floating-nav-item <?= $current_page == 'public-wall.php' ? 'active' : '' ?>">
                The Wall
            </a>
        </div>

        <a href="<?= BASE_URL ?>/pages/write.php" class="floating-nav-cta">
            Write
        </a>
    </nav>
</div>

<div style="height: 120px;"></div>