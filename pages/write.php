<?php
require_once '../includes/config.php';
require_once '../includes/mailer.php';
$page_title = 'Write a Petal';

$colors = ['pink','purple','mint','peach','blue','yellow','white'];
$color_map = [
  'pink'   => ['bg'=>'#f9a8c9','dark'=>'#e879a8'],
  'purple' => ['bg'=>'#c4b5fd','dark'=>'#7c5cbf'],
  'white'  => ['bg'=>'#ffffff','dark'=>'#6b7280'],
  'blue'   => ['bg'=>'#93c5fd','dark'=>'#2563eb'],
  'yellow' => ['bg'=>'#fde68a','dark'=>'#ca8a04'],
];

$selected_color = isset($_GET['color']) && in_array($_GET['color'], $colors) ? $_GET['color'] : 'pink';
$selected_type = isset($_GET['type']) && $_GET['type'] === 'public' ? 'public' : 'private';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $type  = isset($_POST['message_type']) ? $_POST['message_type'] : 'private';
  $color = isset($_POST['color']) && in_array($_POST['color'], $colors) ? $_POST['color'] : 'pink';

  if ($type === 'private') {
    $sender_name   = htmlspecialchars(trim($_POST['sender_name'] ?? ''));
    $email_tujuan  = htmlspecialchars(trim($_POST['email_tujuan'] ?? ''));
    $pesan         = htmlspecialchars(trim($_POST['pesan'] ?? ''));
    $tanggal_kirim = htmlspecialchars(trim($_POST['tanggal_kirim'] ?? ''));

    if (empty($sender_name) || empty($email_tujuan) || empty($pesan) || empty($tanggal_kirim)) {
      $error = 'Semua field harus diisi.';
    } elseif (!filter_var($email_tujuan, FILTER_VALIDATE_EMAIL)) {
      $error = 'Format email tidak valid.';
    } elseif (strtotime($tanggal_kirim) <= time()) {
      $error = 'Tanggal kirim harus di masa depan.';
    } else {
      $stmt = mysqli_prepare($conn, "INSERT INTO private_messages (sender_name, email_tujuan, pesan, tanggal_kirim, color) VALUES (?, ?, ?, ?, ?)");
      mysqli_stmt_bind_param($stmt, 'sssss', $sender_name, $email_tujuan, $pesan, $tanggal_kirim, $color);
      if (mysqli_stmt_execute($stmt)) {
            $success = 'private';
            sendFutureLetter($email_tujuan, $sender_name, $pesan, $tanggal_kirim, $color);
        } else {
            $error = 'Gagal menyimpan pesan. Coba lagi.';
        }
    }

  } else {
    $untuk_siapa = htmlspecialchars(trim($_POST['untuk_siapa'] ?? ''));
    $pesan       = htmlspecialchars(trim($_POST['pesan_public'] ?? ''));

    if (empty($untuk_siapa) || empty($pesan)) {
      $error = 'Semua field harus diisi.';
    } else {
      $stmt = mysqli_prepare($conn, "INSERT INTO public_messages (untuk_siapa, pesan, color) VALUES (?, ?, ?)");
      mysqli_stmt_bind_param($stmt, 'sss', $untuk_siapa, $pesan, $color);
      if (mysqli_stmt_execute($stmt)) { $success = 'public'; }
      else { $error = 'Gagal menyimpan pesan. Coba lagi.'; }
    }
  }

  if ($success) {
    $selected_color = $color;
    $selected_type = $type;
  }
}

$c = $color_map[$selected_color] ?? $color_map['pink'];
require_once '../includes/header.php';

// Set initial background color for the sheet based on selected color
$sheet_bg = '';
$sheet_border = '1px solid rgba(0, 0, 0, 0.05)';
if ($selected_color === 'pink') { $sheet_bg = '#fff0f6'; $sheet_border = 'none'; }
elseif ($selected_color === 'purple') { $sheet_bg = '#f3f0ff'; $sheet_border = 'none'; }
elseif ($selected_color === 'white') { $sheet_bg = '#ffffff'; $sheet_border = '1px solid rgba(0, 0, 0, 0.06)'; }
elseif ($selected_color === 'blue') { $sheet_bg = '#e7f5ff'; $sheet_border = 'none'; }
elseif ($selected_color === 'yellow') { $sheet_bg = '#fff9db'; $sheet_border = 'none'; }
?>

<style>
  :root {
    --write-color: <?= $c['bg'] ?>;
    --write-dark:  <?= $c['dark'] ?>;
  }
  
  /* Background page wrapper */
  body {
    background: #ffffff !important;
  }

  .editor-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 180px);
    padding: 2rem 1rem;
  }

  /* Floating Editor Window */
  .editor-window {
    display: flex;
    background: #ffffff;
    border-radius: 28px;
    box-shadow: 0 24px 72px rgba(0, 0, 0, 0.06), 0 2px 8px rgba(0, 0, 0, 0.02);
    overflow: hidden;
    width: 100%;
    max-width: 980px;
    min-height: 620px;
    border: 1px solid rgba(0, 0, 0, 0.04);
  }

  /* Left Panel - Writing Sheet */
  .editor-sheet {
    flex: 1;
    padding: 3.5rem;
    display: flex;
    flex-direction: column;
    transition: background-color 0.4s cubic-bezier(0.16, 1, 0.3, 1), border 0.4s ease;
  }

  .editor-sheet-header {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2.5px;
    color: rgba(26, 26, 46, 0.35);
    margin-bottom: 2rem;
  }

  /* Borderless Input Fields */
  .editor-input-group {
    display: flex;
    align-items: center;
    border-bottom: 1px dashed rgba(26, 26, 46, 0.08);
    padding: 0.65rem 0;
    margin-bottom: 1.2rem;
    transition: border-bottom-color 0.3s;
  }

  .editor-input-group:focus-within {
    border-bottom-color: var(--write-dark);
    border-bottom-style: solid;
  }

  .editor-input-label {
    width: 110px;
    font-size: 0.8rem;
    font-weight: 700;
    color: rgba(26, 26, 46, 0.4);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    user-select: none;
  }

  .editor-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark);
    outline: none;
    padding: 0;
    font-family: inherit;
    transition: color 0.3s;
  }

  /* Date field specific clean reset */
  .editor-input[type="date"] {
    color: rgba(26, 26, 46, 0.7);
  }

  /* Main Textarea */
  .editor-textarea {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 1.25rem;
    line-height: 1.8;
    color: var(--dark);
    outline: none;
    resize: none;
    margin-top: 1.5rem;
    min-height: 280px;
    font-family: inherit;
  }

  /* Right Panel - Settings Sidebar */
  .editor-sidebar {
    width: 320px;
    background: #fafafa;
    border-left: 1px solid rgba(0, 0, 0, 0.05);
    padding: 3rem 2rem;
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
  }

  .sidebar-section {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
  }

  .sidebar-section-title {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: rgba(26, 26, 46, 0.45);
  }

  /* Segmented Toggle */
  .settings-type-toggle {
    display: flex;
    background: rgba(0, 0, 0, 0.04);
    border-radius: 12px;
    padding: 0.2rem;
    gap: 0.15rem;
  }

  .settings-type-btn {
    flex: 1;
    border: none;
    background: transparent;
    padding: 0.55rem 0.5rem;
    font-size: 0.78rem;
    font-weight: 700;
    border-radius: 9px;
    cursor: pointer;
    text-align: center;
    transition: all 0.25s ease;
    color: var(--gray);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .settings-type-btn.active {
    background: #ffffff;
    color: var(--dark);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
  }

  /* Theme Color Dots */
  .settings-colors {
    display: flex;
    gap: 0.6rem;
    flex-wrap: wrap;
    margin-top: 0.2rem;
  }

  .settings-color-dot {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 3px solid transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.1), 0 2px 4px rgba(0, 0, 0, 0.05);
  }

  .settings-color-dot.active {
    border-color: #1a1a2e;
    transform: scale(1.15);
  }

  /* Custom Font Selectors */
  .settings-fonts {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.4rem;
    margin-top: 0.2rem;
  }

  .settings-font-btn {
    border: 1px solid rgba(0, 0, 0, 0.06);
    background: #ffffff;
    border-radius: 10px;
    padding: 0.55rem 0.25rem;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s ease;
    color: var(--gray);
  }

  .settings-font-btn.active {
    background: #1a1a2e;
    color: #ffffff;
    border-color: #1a1a2e;
    box-shadow: 0 4px 10px rgba(26, 26, 46, 0.1);
  }

  /* Sidebar Submit Button */
  .settings-submit-btn {
    background: var(--write-dark);
    color: #ffffff !important;
    border: none;
    border-radius: 100px;
    padding: 0.95rem 2rem;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    margin-top: auto; /* Push to bottom of sidebar */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    text-transform: uppercase;
    letter-spacing: 1px;
    text-align: center;
    text-decoration: none;
  }

  .settings-submit-btn:hover {
    opacity: 0.92;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  }

  /* Alerts layout */
  .alert-container {
    width: 100%;
    max-width: 980px;
    margin: 0 auto 1.5rem;
  }

  /* Mobile Responsive */
  @media (max-width: 820px) {
    .editor-window {
      flex-direction: column;
      min-height: auto;
    }
    .editor-sidebar {
      width: 100%;
      border-left: none;
      border-top: 1px solid rgba(0, 0, 0, 0.05);
      padding: 2.5rem 2rem;
    }
    .editor-sheet {
      padding: 2.5rem;
    }
    .settings-submit-btn {
      margin-top: 1.5rem;
    }
  }
</style>

<div class="container">
  
  <!-- Alerts Container -->
  <div class="alert-container">
    <?php if ($success === 'private'): ?>
    <div class="petal-alert-success mb-0 auto-dismiss">
      <strong>Petal planted!</strong> Your letter has been saved and will be delivered on the chosen date.
    </div>
    <?php elseif ($success === 'public'): ?>
    <div class="petal-alert-success mb-0 auto-dismiss">
      <strong>Published!</strong> Your message is now on the public wall. <a href="public-wall.php">See it -></a>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="petal-alert-error mb-0 auto-dismiss"><?= $error ?></div>
    <?php endif; ?>
  </div>

  <!-- Main Workspace -->
  <div class="editor-wrapper">
    <form id="write-form" method="POST" style="width: 100%; display: flex; justify-content: center;">
      <input type="hidden" name="message_type" id="message_type" value="<?= $selected_type ?>">
      <input type="hidden" name="color" id="color-input" value="<?= $selected_color ?>">

      <div class="editor-window">
        
        <!-- Left Panel: Writing Pad -->
        <div class="editor-sheet" style="background-color: <?= $sheet_bg ?>; border: <?= $sheet_border ?>;">
          <div class="editor-sheet-header">New Petal Entry</div>

          <!-- PRIVATE FIELDS -->
          <div id="private-fields" style="display: <?= $selected_type === 'private' ? 'block' : 'none' ?>;">
            <div class="editor-input-group">
              <span class="editor-input-label">From</span>
              <input type="text" class="editor-input" id="sender_name" name="sender_name"
                     placeholder="Your name" maxlength="100"
                     value="<?= ($success) ? '' : (isset($_POST['sender_name']) ? htmlspecialchars($_POST['sender_name']) : '') ?>">
            </div>
            <div class="editor-input-group">
              <span class="editor-input-label">To Email</span>
              <input type="email" class="editor-input" id="email_tujuan" name="email_tujuan"
                     placeholder="email@example.com"
                     value="<?= ($success) ? '' : (isset($_POST['email_tujuan']) ? htmlspecialchars($_POST['email_tujuan']) : '') ?>">
            </div>
            <div class="editor-input-group">
              <span class="editor-input-label">Deliver on</span>
              <input type="date" class="editor-input" id="tanggal_kirim" name="tanggal_kirim"
                     min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                     value="<?= ($success) ? '' : (isset($_POST['tanggal_kirim']) ? htmlspecialchars($_POST['tanggal_kirim']) : '') ?>">
            </div>
            <textarea class="editor-textarea" id="pesan_private" name="pesan" 
                      placeholder="Write your letter here..." maxlength="2000"><?= ($success) ? '' : (isset($_POST['pesan']) ? htmlspecialchars($_POST['pesan']) : '') ?></textarea>
            <small class="field-error text-danger d-block mt-1" id="error-sender" style="display:none;"></small>
            <small class="field-error text-danger d-block mt-1" id="error-email" style="display:none;"></small>
            <small class="field-error text-danger d-block mt-1" id="error-tanggal" style="display:none;"></small>
            <small class="field-error text-danger d-block mt-1" id="error-pesan-private" style="display:none;"></small>
          </div>

          <!-- PUBLIC FIELDS -->
          <div id="public-fields" style="display: <?= $selected_type === 'public' ? 'block' : 'none' ?>;">
            <div class="editor-input-group">
              <span class="editor-input-label">For</span>
              <input type="text" class="editor-input" id="untuk_siapa" name="untuk_siapa"
                     placeholder="Recipient"
                     maxlength="150"
                     value="<?= ($success) ? '' : (isset($_POST['untuk_siapa']) ? htmlspecialchars($_POST['untuk_siapa']) : '') ?>">
            </div>
            <textarea class="editor-textarea" id="pesan_public" name="pesan_public" 
                      placeholder="Write something meaningful for the world..." maxlength="1000"><?= ($success) ? '' : (isset($_POST['pesan_public']) ? htmlspecialchars($_POST['pesan_public']) : '') ?></textarea>
            <small class="field-error text-danger d-block mt-1" id="error-untuk" style="display:none;"></small>
            <small class="field-error text-danger d-block mt-1" id="error-pesan-public" style="display:none;"></small>
          </div>
          
        </div>

        <!-- Right Panel: Controls Inspector -->
        <div class="editor-sidebar">
          
          <!-- Section: Message Type -->
          <div class="sidebar-section">
            <div class="sidebar-section-title">Message Type</div>
            <div class="settings-type-toggle">
              <button type="button" class="settings-type-btn <?= $selected_type === 'private' ? 'active' : '' ?>" data-type="private">Private</button>
              <button type="button" class="settings-type-btn <?= $selected_type === 'public' ? 'active' : '' ?>" data-type="public">Public</button>
            </div>
          </div>

          <!-- Section: Theme Style (Colors) -->
          <div class="sidebar-section">
            <div class="sidebar-section-title">Theme Style</div>
            <div class="settings-colors">
              <?php foreach ($color_map as $key => $val): ?>
              <button type="button" class="settings-color-dot <?= $key == $selected_color ? 'active' : '' ?>"
                      data-color="<?= $key ?>"
                      data-bg="<?= $val['bg'] ?>"
                      data-dark="<?= $val['dark'] ?>"
                      style="background: <?= $val['bg'] ?>;"
                      title="<?= ucfirst($key) ?>"></button>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Section: Custom Font -->
          <div class="sidebar-section">
            <div class="sidebar-section-title">Custom Font</div>
            <div class="settings-fonts">
              <button type="button" class="settings-font-btn active" data-font="sans">Sans</button>
              <button type="button" class="settings-font-btn" data-font="serif">Serif</button>
              <button type="button" class="settings-font-btn" data-font="mono">Mono</button>
            </div>
          </div>

          <!-- Submit Action Button -->
          <button type="submit" class="settings-submit-btn">Plant Petal</button>
          
        </div>

      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  
  // 1. Message Type Selector Toggle
  const typeBtns = document.querySelectorAll('.settings-type-btn');
  const privateFields = document.getElementById('private-fields');
  const publicFields = document.getElementById('public-fields');
  const messageTypeInput = document.getElementById('message_type');

  typeBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      typeBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      
      const type = this.dataset.type;
      messageTypeInput.value = type;
      
      if (type === 'private') {
        privateFields.style.display = 'block';
        publicFields.style.display = 'none';
      } else {
        privateFields.style.display = 'none';
        publicFields.style.display = 'block';
      }
    });
  });

  // 2. Color Theme Selector
  const colorDots = document.querySelectorAll('.settings-color-dot');
  const colorInput = document.getElementById('color-input');
  const editorSheet = document.querySelector('.editor-sheet');

  colorDots.forEach(dot => {
    dot.addEventListener('click', function() {
      colorDots.forEach(d => d.classList.remove('active'));
      this.classList.add('active');
      
      const color = this.dataset.color;
      const bg = this.dataset.bg;
      const dark = this.dataset.dark;
      
      colorInput.value = color;
      
      // Update variables for theme coloring
      document.documentElement.style.setProperty('--write-color', bg);
      document.documentElement.style.setProperty('--write-dark', dark);
      
      // Update background of editor sheet dynamically
      let sheetBg = '';
      let sheetBorder = 'none';
      if (color === 'pink') sheetBg = '#fff0f6';
      else if (color === 'purple') sheetBg = '#f3f0ff';
      else if (color === 'white') {
        sheetBg = '#ffffff';
        sheetBorder = '1px solid rgba(0, 0, 0, 0.06)';
      }
      else if (color === 'blue') sheetBg = '#e7f5ff';
      else if (color === 'yellow') sheetBg = '#fff9db';
      
      editorSheet.style.backgroundColor = sheetBg;
      editorSheet.style.border = sheetBorder;
    });
  });

  // 3. Custom Font Switcher
  const fontBtns = document.querySelectorAll('.settings-font-btn');
  const textareas = document.querySelectorAll('.editor-textarea');
  const inputs = document.querySelectorAll('.editor-input');

  fontBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      fontBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      
      const font = this.dataset.font;
      let fontFamily = '';
      if (font === 'sans') {
        fontFamily = "'Plus Jakarta Sans', sans-serif";
      } else if (font === 'serif') {
        fontFamily = "'Playfair Display', serif";
      } else if (font === 'mono') {
        fontFamily = "'Courier New', Courier, monospace";
      }
      
      textareas.forEach(ta => ta.style.fontFamily = fontFamily);
      inputs.forEach(inp => inp.style.fontFamily = fontFamily);
    });
  });

  // 4. Form Validation with JavaScript (addEventListener + DOM manipulation)
  const form = document.getElementById('write-form');
  form.addEventListener('submit', function(e) {
    let valid = true;
    const type = messageTypeInput.value;

    // Reset error messages
    document.querySelectorAll('.field-error').forEach(el => {
      el.textContent = '';
      el.style.display = 'none';
    });

    if (type === 'private') {
      const sender = document.getElementById('sender_name');
      const email = document.getElementById('email_tujuan');
      const tanggal = document.getElementById('tanggal_kirim');
      const pesan = document.getElementById('pesan_private');

      if (!sender.value.trim()) {
        const err = document.getElementById('error-sender');
        err.textContent = 'Name is required.';
        err.style.display = 'block';
        valid = false;
      }
      if (!email.value.trim()) {
        const err = document.getElementById('error-email');
        err.textContent = 'Email is required.';
        err.style.display = 'block';
        valid = false;
      }
      if (!tanggal.value.trim()) {
        const err = document.getElementById('error-tanggal');
        err.textContent = 'Delivery date is required.';
        err.style.display = 'block';
        valid = false;
      }
      if (!pesan.value.trim()) {
        const err = document.getElementById('error-pesan-private');
        err.textContent = 'Message content is required.';
        err.style.display = 'block';
        valid = false;
      }
    } else {
      const untuk = document.getElementById('untuk_siapa');
      const pesanPub = document.getElementById('pesan_public');

      if (!untuk.value.trim()) {
        const err = document.getElementById('error-untuk');
        err.textContent = 'Recipient field is required.';
        err.style.display = 'block';
        valid = false;
      }
      if (!pesanPub.value.trim()) {
        const err = document.getElementById('error-pesan-public');
        err.textContent = 'Message for the world is required.';
        err.style.display = 'block';
        valid = false;
      }
    }

    if (!valid) {
      e.preventDefault();
    }
  });

});
</script>

<?php require_once '../includes/footer.php'; ?>