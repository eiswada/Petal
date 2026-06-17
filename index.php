<?php
require_once 'includes/config.php';
$page_title = 'Home';

$public_preview = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM v_public_messages_summary ORDER BY created_at DESC LIMIT 6"), MYSQLI_ASSOC);
$total_private  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM private_messages"))[0];
$total_public   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM public_messages"))[0];
$total_all      = mysqli_fetch_row(mysqli_query($conn, "SELECT get_total_messages()"))[0] ?? ($total_private + $total_public);

require_once 'includes/header.php';
?>

<!-- ===================== HERO ===================== -->
<section class="hero-scatter">

  <!-- Top text -->
  <div class="hs-top">
    <p class="hs-eyebrow">A gallery of future words</p>
    <h1 class="hs-heading">pick a petal,<br><em>write your story.</em></h1>
    <p class="hs-sub">Choose a color below and write your message — to yourself, or to the world.</p>
  </div>

  <!-- Scattered memo cards -->
  <div class="hs-cards-wrap">
    <div class="hs-cards">

      <a href="pages/write.php?color=pink" class="memo-card" data-color="pink" style="transform: rotate(-5deg) translate(-10px, 8px);">
        <div class="mc-icon-wrap"><i class="bi bi-flower1"></i></div>
      </a>

      <a href="pages/write.php?color=purple" class="memo-card" data-color="purple" style="transform: rotate(3deg) translate(8px, -12px);">
        <div class="mc-icon-wrap"><i class="bi bi-moon-stars"></i></div>
      </a>

      <a href="pages/write.php?color=white" class="memo-card" data-color="white" style="transform: rotate(6deg) translate(-4px, -8px);">
        <div class="mc-icon-wrap"><i class="bi bi-circle"></i></div>
      </a>

      <a href="pages/write.php?color=blue" class="memo-card" data-color="blue" style="transform: rotate(-4deg) translate(10px, 6px);">
        <div class="mc-icon-wrap"><i class="bi bi-droplet"></i></div>
      </a>

      <a href="pages/write.php?color=yellow" class="memo-card" data-color="yellow" style="transform: rotate(4deg) translate(-8px, 12px);">
        <div class="mc-icon-wrap"><i class="bi bi-sun"></i></div>
      </a>

    </div>
  </div>

  <!-- Bottom stats + wall link -->
  <div class="hs-bottom">
    <div class="hs-stats">
      <div class="hs-stat">
        <span class="hs-stat-num"><?= $total_private ?></span>
        <span class="hs-stat-label">Future Letters</span>
      </div>
      <div class="hs-divider"></div>
      <div class="hs-stat">
        <span class="hs-stat-num"><?= $total_public ?></span>
        <span class="hs-stat-label">World Messages</span>
      </div>
    </div>
    <a href="pages/public-wall.php" class="petal-btn-outline" style="font-size: 0.85rem;">See the Wall →</a>
  </div>

</section>

<hr class="petal-divider mx-5">

<!-- ===================== HOW IT WORKS ===================== -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="petal-section-title">How it works</h2>
      <p class="petal-section-sub">Simple, meaningful, forever.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="petal-card text-center">
          <h5 style="font-weight:700;">Write</h5>
          <p class="text-muted small">Pick your color, choose private or public, write from the heart.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="petal-card text-center">
          <h5 style="font-weight:700;">Plant</h5>
          <p class="text-muted small">Your message is safely stored. Private letters wait quietly until their time comes.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="petal-card text-center">
          <h5 style="font-weight:700;">Bloom</h5>
          <p class="text-muted small">On the chosen date, your letter arrives. Public messages live on the wall forever.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<hr class="petal-divider mx-5">

<!-- ===================== WALL PREVIEW ===================== -->
<?php if (!empty($public_preview)): ?>
<section class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <h2 class="petal-section-title">From the Wall</h2>
        <p class="petal-section-sub mb-0">Words left for the world.</p>
      </div>
      <a href="pages/public-wall.php" class="petal-btn-outline" style="font-size:.85rem;">See all →</a>
    </div>
    <div class="row g-4">
      <?php foreach ($public_preview as $msg): ?>
      <div class="col-sm-6 col-lg-4">
        <div class="petal-card petal-card--colored" data-color="<?= htmlspecialchars($msg['color'] ?? 'white') ?>">
          <div class="card-untuk">For <?= htmlspecialchars($msg['untuk_siapa']) ?></div>
          <div class="card-pesan" style="font-family: <?= $msg['font'] === 'serif' ? '\'Playfair Display\', serif' : ($msg['font'] === 'mono' ? '\'Courier New\', Courier, monospace' : 'inherit') ?>;">"<?= htmlspecialchars($msg['pesan']) ?>"</div>
          <div class="card-meta"><i class="bi bi-clock"></i> <?= date('d M Y', strtotime($msg['created_at'])) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===================== CTA ===================== -->
<section class="py-5 mb-5">
  <div class="container">
<div class="text-center p-5" style="background:#ffffff;border-radius:24px;border:1px solid var(--border);">
      <h2 class="petal-section-title mb-3">Ready to leave your mark?</h2>
      <p class="petal-section-sub mb-4">A few words today can mean the world tomorrow.</p>
      <a href="pages/write.php" class="petal-btn-primary">Write a Petal</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
