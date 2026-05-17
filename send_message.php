<?php
require_once 'includes/config.php';
requireLogin();

header('Content-Type: application/json');

$type    = $_POST['type'] ?? 'private';
$chat_id = (int)($_POST['chat_id'] ?? 0);
$message = trim($conn->real_escape_string($_POST['message'] ?? ''));
$user_id = (int)$_SESSION['user_id'];

if (!$message || !$chat_id) {
    echo json_encode(['success' => false, 'error' => 'Missing fields']);
    exit;
}

if ($type === 'private') {
    $conn->query("INSERT INTO private_messages (sender_id, receiver_id, message) VALUES ($user_id, $chat_id, '$message')");
    $msg_id = $conn->insert_id;
} else {
    $conn->query("INSERT INTO group_messages (group_id, sender_id, message) VALUES ($chat_id, $user_id, '$message')");
    $msg_id = $conn->insert_id;
}

updateOnlineStatus($conn, $user_id);
echo json_encode(['success' => true, 'id' => $msg_id]);
?>
