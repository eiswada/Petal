<?php
require_once '../../includes/config.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Get current admin details
$stmt = mysqli_prepare($conn, "SELECT id, username, email FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, 's', $_SESSION['admin_username']);
mysqli_stmt_execute($stmt);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = htmlspecialchars(trim($_POST['username'] ?? ''));
        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email)) {
            $error = 'Username and Email are required.';
        } elseif (strlen($username) < 4) {
            $error = 'Username must be at least 4 characters long.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } else {
            // Check if username/email is taken by another user
            $check = mysqli_prepare($conn, "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            mysqli_stmt_bind_param($check, 'ssi', $username, $email, $admin['id']);
            mysqli_stmt_execute($check);
            mysqli_stmt_store_result($check);

            if (mysqli_stmt_num_rows($check) > 0) {
                $error = 'Username or Email is already taken by another admin.';
            } else {
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $error = 'New password must be at least 6 characters.';
                    } else {
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $upd = mysqli_prepare($conn, "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                        mysqli_stmt_bind_param($upd, 'sssi', $username, $email, $hashed, $admin['id']);
                    }
                } else {
                    $upd = mysqli_prepare($conn, "UPDATE users SET username = ?, email = ? WHERE id = ?");
                    mysqli_stmt_bind_param($upd, 'ssi', $username, $email, $admin['id']);
                }

                if (empty($error) && mysqli_stmt_execute($upd)) {
                    $_SESSION['admin_username'] = $username;
                    $success = 'Profile updated successfully!';
                    // Refresh data
                    $admin['username'] = $username;
                    $admin['email'] = $email;
                } elseif (empty($error)) {
                    $error = 'Failed to update profile.';
                }
            }
        }
    } elseif (isset($_POST['delete_account'])) {
        // Prevent deleting the very last admin
        $res = mysqli_query($conn, "SELECT COUNT(*) FROM users");
        $count = mysqli_fetch_row($res)[0];

        if ($count <= 1) {
            $error = 'Cannot delete the only administrator account. System requires at least one admin.';
        } else {
            $del = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
            mysqli_stmt_bind_param($del, 'i', $admin['id']);
            if (mysqli_stmt_execute($del)) {
                session_destroy();
                header('Location: login.php?deleted_account=1');
                exit;
            } else {
                $error = 'Failed to delete account.';
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
    <title>Admin Settings — Petal</title>
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
            <a href="dashboard.php?tab=private" class="nav-link">
                <i class="bi bi-envelope me-2"></i> Private Letters
            </a>
            <a href="dashboard.php?tab=public" class="nav-link">
                <i class="bi bi-globe me-2"></i> Public Messages
            </a>
            <a href="settings.php" class="nav-link active">
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
                <a href="dashboard.php?tab=private" class="btn btn-sm" style="background: transparent; color: #ffffff; border: 1px solid rgba(255,255,255,0.4);" title="Private Letters"><i class="bi bi-envelope"></i></a>
                <a href="dashboard.php?tab=public" class="btn btn-sm" style="background: transparent; color: #ffffff; border: 1px solid rgba(255,255,255,0.4);" title="Public Messages"><i class="bi bi-globe"></i></a>
                <a href="settings.php" class="btn btn-sm" style="background: rgba(255,255,255,0.9); color: var(--dark); border: 1px solid rgba(255,255,255,0.4);" title="Settings"><i class="bi bi-gear"></i></a>
                <a href="logout.php" class="btn btn-sm btn-danger" style="border: 1px solid transparent;" title="Logout"><i class="bi bi-box-arrow-left"></i></a>
            </div>
        </div>

        <h4 class="fw-bold mb-1">Account Settings</h4>
        <p class="text-muted small mb-4">Manage your administrator profile details</p>

        <?php if ($error): ?>
        <div class="petal-alert-error mb-3"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="petal-alert-success mb-3"><?= $success ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Profile Form Card -->
            <div class="col-md-7">
                <div class="p-4" style="background:#ffffff; border-radius:20px; border:1px solid var(--border);">
                    <h5 class="fw-bold mb-4">Edit Profile</h5>
                    <form method="POST" id="settings-form">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($admin['username']) ?>">
                            <small class="field-error text-danger" id="error-username" style="display:none;"></small>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($admin['email']) ?>">
                            <small class="field-error text-danger" id="error-email" style="display:none;"></small>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">New Password (leave blank to keep current)</label>
                            <div class="input-group" style="position:relative;">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="••••••••" style="padding-right: 45px;">
                                <button class="btn" type="button" id="toggle-password" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); border:none; background:transparent; padding:0; z-index:10; color:var(--gray); transition:color 0.2s;">
                                    <i class="bi bi-eye" style="font-size:1.1rem;"></i>
                                </button>
                            </div>
                            <small class="field-error text-danger" id="error-password" style="display:none;"></small>
                        </div>
                        <button type="submit" class="petal-btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>

            <!-- Danger Zone Card -->
            <div class="col-md-5">
                <div class="p-4" style="background:#ffffff; border-radius:20px; border:1px solid #fee2e2;">
                    <h5 class="fw-bold text-danger mb-2">Danger Zone</h5>
                    <p class="text-muted small mb-4">Once you delete your account, it cannot be undone. Please be certain.</p>
                    
                    <form method="POST" onsubmit="return confirm('WARNING: Are you absolutely sure you want to delete your admin account? This action is permanent and cannot be undone.');">
                        <input type="hidden" name="delete_account" value="1">
                        <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold" style="border-radius:12px;">
                            Delete Administrator Account
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation
    const form = document.getElementById('settings-form');
    form.addEventListener('submit', function(e) {
        let valid = true;
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const password = document.getElementById('password');

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

        if (!email.value.trim()) {
            document.getElementById('error-email').textContent = 'Email is required.';
            document.getElementById('error-email').style.display = 'block';
            valid = false;
        }

        if (password.value && password.value.length < 6) {
            document.getElementById('error-password').textContent = 'Password must be at least 6 characters.';
            document.getElementById('error-password').style.display = 'block';
            valid = false;
        }

        if (!valid) e.preventDefault();
    });

    // Eye toggle
    const togglePasswordBtn = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');
    togglePasswordBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('bi-eye');
        this.querySelector('i').classList.toggle('bi-eye-slash');
    });
});
</script>
</body>
</html>
