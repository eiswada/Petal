<?php
require_once '../../includes/config.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (strlen($username) < 4) {
        $error = 'Username must be at least 4 characters long.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if username already exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = 'Username is already taken.';
        } else {
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $ins = mysqli_prepare($conn, "INSERT INTO users (username, password) VALUES (?, ?)");
            mysqli_stmt_bind_param($ins, 'ss', $username, $hashed_password);
            
            if (mysqli_stmt_execute($ins)) {
                $success = 'Account created successfully! You can now log in.';
            } else {
                $error = 'Failed to create account. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register — Petal</title>
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
                <h2 class="petal-brand d-block mt-2">Create Admin Account</h2>
                <p class="text-muted small">Register a new administrator operator</p>
            </div>

            <?php if ($error): ?>
            <div class="petal-alert-error mb-3">
                <?= $error ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="petal-alert-success mb-3">
                <?= $success ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="register-form">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Enter username" autocomplete="username"
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                    <small class="field-error text-danger" id="error-username" style="display:none;"></small>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="••••••••" autocomplete="new-password">
                    <small class="field-error text-danger" id="error-password" style="display:none;"></small>
                </div>
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="••••••••" autocomplete="new-password">
                    <small class="field-error text-danger" id="error-confirm" style="display:none;"></small>
                </div>
                <button type="submit" class="petal-btn-primary w-100" style="padding:.85rem;">
                    Register Account →
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="small text-muted mb-1">Already have an account? <a href="login.php" class="text-decoration-none" style="color:var(--purple-dark);font-weight:600;">Sign In</a></p>
                <a href="<?= BASE_URL ?>" class="text-muted small text-decoration-none">← Back to Petal</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('register-form');
    form.addEventListener('submit', function(e) {
        let valid = true;
        const username = document.getElementById('username');
        const password = document.getElementById('password');
        const confirm = document.getElementById('confirm_password');

        document.querySelectorAll('.field-error').forEach(el => el.style.display = 'none');

        if (!username.value.trim()) {
            document.getElementById('error-username').textContent = 'Username is required.';
            document.getElementById('error-username').style.display = 'block';
            valid = false;
        } else if (username.value.trim().length < 4) {
            document.getElementById('error-username').textContent = 'Username must be at least 4 characters.';
            document.getElementById('error-username').style.display = 'block';
            valid = false;
        }

        if (!password.value) {
            document.getElementById('error-password').textContent = 'Password is required.';
            document.getElementById('error-password').style.display = 'block';
            valid = false;
        } else if (password.value.length < 6) {
            document.getElementById('error-password').textContent = 'Password must be at least 6 characters.';
            document.getElementById('error-password').style.display = 'block';
            valid = false;
        }

        if (!confirm.value) {
            document.getElementById('error-confirm').textContent = 'Confirm password is required.';
            document.getElementById('error-confirm').style.display = 'block';
            valid = false;
        } else if (password.value !== confirm.value) {
            document.getElementById('error-confirm').textContent = 'Passwords do not match.';
            document.getElementById('error-confirm').style.display = 'block';
            valid = false;
        }

        if (!valid) e.preventDefault();
    });
});
</script>
</body>
</html>
