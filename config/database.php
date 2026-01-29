<?php
// =======================
// DATABASE CONFIG (MySQL)
// =======================

// Use ENV if available (for hosting), fallback to local values
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'username';
$user = getenv('DB_USER') ?: 'username';
$password = getenv('DB_PASS') ?: 'password here';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// =======================
// SESSION & AUTH HELPERS
// =======================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php"); // correct redirect
        exit();
    }
}
?>
