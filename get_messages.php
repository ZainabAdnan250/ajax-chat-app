<?php
require_once 'includes/config.php';
requireLogin();
header('Content-Type: application/json');

$type    = $_GET['type'] ?? 'private';
$chat_id = (int)($_GET['chat_id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];
$last_id = (int)($_GET['last_id'] ?? 0);

$messages = [];

if ($type === 'private') {
    $res = $conn->query("
        SELECT pm.*, u.full_name AS sender_name, u.cnic AS sender_sid
        FROM private_messages pm
        JOIN users u ON pm.sender_id = u.id
        WHERE pm.id > $last_id
          AND ((pm.sender_id=$user_id AND pm.receiver_id=$chat_id) OR (pm.sender_id=$chat_id AND pm.receiver_id=$user_id))
        ORDER BY pm.created_at ASC LIMIT 100
    ");
    // Mark messages as read when receiver opens chat
    $conn->query("UPDATE private_messages SET is_read=1 WHERE sender_id=$chat_id AND receiver_id=$user_id AND is_read=0");

} else {
    $res = $conn->query("
        SELECT gm.*, u.full_name AS sender_name, u.cnic AS sender_sid
        FROM group_messages gm
        JOIN users u ON gm.sender_id = u.id
        WHERE gm.group_id=$chat_id AND gm.id > $last_id
        ORDER BY gm.created_at ASC LIMIT 100
    ");

    // Record that this user has seen up to latest message in group
    $latestRes = $conn->query("SELECT MAX(id) as max_id FROM group_messages WHERE group_id=$chat_id");
    $latestRow = $latestRes->fetch_assoc();
    $latestId  = (int)($latestRow['max_id'] ?? 0);
    if ($latestId > 0) {
        $conn->query("INSERT INTO group_read_status (group_id, user_id, last_read_id)
                      VALUES ($chat_id, $user_id, $latestId)
                      ON DUPLICATE KEY UPDATE last_read_id = GREATEST(last_read_id, $latestId)");
    }
}

while ($row = $res->fetch_assoc()) {
    $row['is_mine']   = ((int)$row['sender_id'] === $user_id);
    $row['time']      = date('g:ia', strtotime($row['created_at']));
    $row['date']      = date('l', strtotime($row['created_at']));
    $row['reactions'] = json_decode($row['reaction'] ?? '{}', true) ?: [];

    // ── SEEN INFO ──
    if ($type === 'private') {
        // For sender's own messages: show if receiver has seen it
        if ($row['is_mine']) {
            $row['seen'] = (bool)$row['is_read'];
            $row['seen_by'] = $row['is_read'] ? [] : []; // private = just bool
        } else {
            $row['seen'] = false;
        }
    } else {
        // Group: show names of members who have seen this message (excluding sender)
        if ($row['is_mine']) {
            $seenRes = $conn->query("
                SELECT u.full_name 
                FROM group_read_status grs
                JOIN users u ON grs.user_id = u.id
                WHERE grs.group_id = $chat_id 
                  AND grs.last_read_id >= {$row['id']}
                  AND grs.user_id != $user_id
            ");
            $seenBy = [];
            while ($sr = $seenRes->fetch_assoc()) {
                $seenBy[] = $sr['full_name'];
            }
            $row['seen_by'] = $seenBy;
            $row['seen']    = count($seenBy) > 0;
        } else {
            $row['seen']    = false;
            $row['seen_by'] = [];
        }
    }

    $messages[] = $row;
}

echo json_encode(['messages' => $messages]);
?>