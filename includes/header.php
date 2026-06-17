<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
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
<div class="fixed-top d-flex justify-content-center p-2 p-md-3" style="pointer-events: none; z-index: 1030;">
    <nav class="navbar navbar-expand p-1.5 p-md-2 px-md-4 rounded-pill border bg-white shadow-sm floating-nav-container" style="pointer-events: auto; background: rgba(255, 255, 255, 0.85) !important; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); display: flex; align-items: center; justify-content: space-between; max-width: 95%;">
        <a href="<?= BASE_URL ?>" class="navbar-brand d-flex align-items-center fw-bold text-dark fs-6 fs-md-5 brand-logo-text" style="font-family: 'Playfair Display', serif; margin-right: 0;">
            <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Petal" class="me-1 me-md-2 logo-img" style="width: 20px; height: 20px; transition: transform 0.3s;" onmouseover="this.style.transform='rotate(15deg)'" onmouseout="this.style.transform='rotate(0deg)'"> Petal
        </a>

        <div class="nav nav-pills bg-light rounded-pill p-1 nav-pill-items gap-1">
            <a href="<?= BASE_URL ?>" 
               class="nav-link rounded-pill fw-semibold text-secondary nav-pill-item <?= $current_page == 'index.php' ? 'active bg-white text-dark shadow-sm' : '' ?>">
                Home
            </a>
            <a href="<?= BASE_URL ?>/pages/public-wall.php" 
               class="nav-link rounded-pill fw-semibold text-secondary nav-pill-item <?= $current_page == 'public-wall.php' ? 'active bg-white text-dark shadow-sm' : '' ?>">
                The Wall
            </a>
        </div>

        <a href="<?= BASE_URL ?>/pages/write.php" class="btn btn-sm rounded-pill text-decoration-none fw-bold cta-write-btn" style="background: rgba(220, 210, 245, 0.35); border: 1px solid rgba(200, 185, 235, 0.5); color: #5a3fa0; transition: all 0.2s;" onmouseover="this.style.transform='scale(1.03)';" onmouseout="this.style.transform='scale(1)';">
            Write
        </a>
    </nav>
</div>

<div style="height: 100px;"></div>