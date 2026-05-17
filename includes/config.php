<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'chatapp');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$conn->set_charset('utf8mb4');

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: redirect if not logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Helper: get current user
function getCurrentUser($conn) {
    if (!isset($_SESSION['user_id'])) return null;
    $id = (int)$_SESSION['user_id'];
    $res = $conn->query("SELECT * FROM users WHERE id = $id");
    return $res->fetch_assoc();
}

// Helper: get initials from name
function getInitials($name) {
    $words = explode(' ', trim($name));
    $initials = '';
    foreach ($words as $w) {
        $initials .= strtoupper(substr($w, 0, 1));
        if (strlen($initials) >= 2) break;
    }
    return $initials ?: '?';
}

// Update online status
function updateOnlineStatus($conn, $userId, $status = 1) {
    $userId = (int)$userId;
    $status = (int)$status;
    $conn->query("UPDATE users SET is_online = $status, last_seen = NOW() WHERE id = $userId");
}
?>
