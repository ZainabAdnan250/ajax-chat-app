<?php
require_once 'includes/config.php';
requireLogin();
header('Content-Type: application/json');

$user_id = (int)$_SESSION['user_id'];

// ── REMOVE AVATAR ─────────────────────────────────────────────
if (isset($_POST['remove'])) {
    $res = $conn->query("SELECT avatar FROM users WHERE id=$user_id");
    $row = $res->fetch_assoc();
    if ($row['avatar'] && file_exists('uploads/avatars/' . $row['avatar'])) {
        unlink('uploads/avatars/' . $row['avatar']);
    }
    $conn->query("UPDATE users SET avatar=NULL WHERE id=$user_id");
    echo json_encode(['success' => true]);
    exit;
}

// ── UPLOAD AVATAR ─────────────────────────────────────────────
if (!isset($_FILES['avatar'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$file    = $_FILES['avatar'];
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 2 * 1024 * 1024; // 2MB

if (!in_array($file['type'], $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Only JPG, PNG, GIF, WEBP allowed']);
    exit;
}
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'File too large. Max 2MB']);
    exit;
}
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Upload failed']);
    exit;
}

// Delete old avatar file
$res = $conn->query("SELECT avatar FROM users WHERE id=$user_id");
$row = $res->fetch_assoc();
if ($row['avatar'] && file_exists('uploads/avatars/' . $row['avatar'])) {
    unlink('uploads/avatars/' . $row['avatar']);
}

// Save new file
$ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
$dest     = 'uploads/avatars/' . $filename;

// Make sure folder exists
if (!is_dir('uploads/avatars')) {
    mkdir('uploads/avatars', 0755, true);
}

if (move_uploaded_file($file['tmp_name'], $dest)) {
    $conn->query("UPDATE users SET avatar='$filename' WHERE id=$user_id");
    echo json_encode(['success' => true, 'avatar' => $filename]);
} else {
    echo json_encode(['success' => false, 'error' => 'Could not save file. Check folder permissions.']);
}
?>