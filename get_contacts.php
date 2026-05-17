<?php
require_once 'includes/config.php';
requireLogin();

header('Content-Type: application/json');
$user_id = (int)$_SESSION['user_id'];

// Private chats with phone numbers
$contacts = [];
$res = $conn->query("
    SELECT DISTINCT u.id, u.full_name, u.cnic, u.phone, u.is_online, u.avatar,
        (SELECT message FROM private_messages 
         WHERE (sender_id=u.id AND receiver_id=$user_id) OR (sender_id=$user_id AND receiver_id=u.id)
         ORDER BY created_at DESC LIMIT 1) AS last_message,
        (SELECT COUNT(*) FROM private_messages WHERE sender_id=u.id AND receiver_id=$user_id AND is_read=0) AS unread
    FROM users u
    WHERE u.id != $user_id
    ORDER BY u.full_name
");
while ($row = $res->fetch_assoc()) {
    $contacts[] = $row;
}

// Groups with unread counts
$groups = [];
$res = $conn->query("
    SELECT cg.id, cg.name, cg.description,
        (SELECT message FROM group_messages WHERE group_id=cg.id ORDER BY created_at DESC LIMIT 1) AS last_message,
        (SELECT COUNT(*) FROM group_messages gm2
         WHERE gm2.group_id=cg.id AND gm2.sender_id != $user_id
           AND gm2.id > COALESCE(
             (SELECT last_read_id FROM group_read_status WHERE group_id=cg.id AND user_id=$user_id), 0
           )
        ) AS unread
    FROM chat_groups cg
    JOIN group_members gm ON cg.id = gm.group_id
    WHERE gm.user_id = $user_id
    ORDER BY cg.name
");
while ($row = $res->fetch_assoc()) {
    $groups[] = $row;
}

echo json_encode(['contacts' => $contacts, 'groups' => $groups]);
?>