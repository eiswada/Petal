<?php
// ============================================
// PETAL - Database Configuration
// ============================================
// !! EXCLUDE THIS FILE FROM GIT !!

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'petal_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('<div style="text-align:center;padding:50px;font-family:sans-serif;">
        <h2>⚠️ Database Connection Failed</h2>
        <p>' . mysqli_connect_error() . '</p>
        <p>Please make sure XAMPP/Laragon is running and database is imported.</p>
    </div>');
}

mysqli_set_charset($conn, 'utf8mb4');

// Base URL helper
define('BASE_URL', 'http://localhost:8080/petal');
?>
