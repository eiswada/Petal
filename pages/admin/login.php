<?php
require_once '../../includes/config.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Petal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
    <div class="container">
        <div class="login-card">
            <div class="text-center mb-4">
                <img src="../../assets/img/logo.png" alt="Petal" style="width: 60px; height: auto;" class="mb-2">
                <h2 class="petal-brand d-block mt-2">Petal Admin</h2>
                <p class="text-muted small">Sign in to manage messages</p>
            </div>

            <?php if ($error): ?>
            <div class="petal-alert-error mb-3">
                <?= $error ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="login-form">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="admin" autocomplete="username"
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                    <small class="field-error text-danger" id="error-username" style="display:none;"></small>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="••••••••" autocomplete="current-password">
                    <small class="field-error text-danger" id="error-password" style="display:none;"></small>
                </div>
                <button type="submit" class="petal-btn-primary w-100" style="padding:.85rem;">
                    Sign In →
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="small text-muted mb-1">Don't have an account? <a href="register.php" class="text-decoration-none" style="color:var(--purple-dark);font-weight:600;">Sign Up</a></p>
                <a href="<?= BASE_URL ?>" class="text-muted small text-decoration-none">← Back to Petal</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('login-form');
    form.addEventListener('submit', function(e) {
        let valid = true;
        const username = document.getElementById('username');
        const password = document.getElementById('password');

        document.querySelectorAll('.field-error').forEach(el => el.style.display = 'none');

        if (!username.value.trim()) {
            document.getElementById('error-username').textContent = 'Username harus diisi.';
            document.getElementById('error-username').style.display = 'block';
            valid = false;
        }
        if (!password.value) {
            document.getElementById('error-password').textContent = 'Password harus diisi.';
            document.getElementById('error-password').style.display = 'block';
            valid = false;
        }
        if (!valid) e.preventDefault();
    });
});
</script>
</body>
</html>
