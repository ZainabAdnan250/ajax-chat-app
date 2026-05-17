<?php
require_once 'includes/config.php';
requireLogin();
header('Content-Type: application/json');

$msg_id   = (int)($_POST['msg_id'] ?? 0);
$reaction = $_POST['reaction'] ?? '👍';
$type     = $_POST['type'] ?? 'private';
$user_id  = (int)$_SESSION['user_id'];

if (!$msg_id) { echo json_encode(['success' => false]); exit; }

$table = ($type === 'private') ? 'private_messages' : 'group_messages';

// Get current reactions JSON
$res = $conn->query("SELECT reaction FROM $table WHERE id=$msg_id");
$row = $res->fetch_assoc();
$reactions = json_decode($row['reaction'] ?? '{}', true) ?: [];

// ── ONE REACTION PER USER RULE ──
// Remove this user from ALL existing reactions first
foreach ($reactions as $emoji => &$data) {
    $idx = array_search($user_id, $data['users']);
    if ($idx !== false) {
        array_splice($data['users'], $idx, 1);
        $data['count']--;
    }
}
unset($data);

// Remove empty reactions
foreach ($reactions as $emoji => $data) {
    if ($data['count'] <= 0 || empty($data['users'])) {
        unset($reactions[$emoji]);
    }
}

// Check if user clicked same emoji they already had (toggle OFF)
// Since we removed it above, if $reaction was theirs before — it's now removed = done
// If it was a different emoji or new — add it now
$wasAlreadyThis = false; // We already removed, just add new one
// Actually: we need to know if user had THIS emoji before removing
// Re-fetch to check original
$res2 = $conn->query("SELECT reaction FROM $table WHERE id=$msg_id");
$row2 = $res2->fetch_assoc();
$originalReactions = json_decode($row2['reaction'] ?? '{}', true) ?: [];
$hadThisEmoji = isset($originalReactions[$reaction]) && 
                in_array($user_id, $originalReactions[$reaction]['users'] ?? []);

// If user did NOT have this emoji before → add it
if (!$hadThisEmoji) {
    if (!isset($reactions[$reaction])) {
        $reactions[$reaction] = ['count' => 0, 'users' => []];
    }
    $reactions[$reaction]['users'][] = $user_id;
    $reactions[$reaction]['count']++;
}
// If they DID have it → it's already removed above (toggle off)

$json = $conn->real_escape_string(json_encode($reactions));
$total = array_sum(array_column($reactions, 'count'));
$conn->query("UPDATE $table SET reaction='$json', reaction_count=$total WHERE id=$msg_id");

echo json_encode(['success' => true, 'removed' => $hadThisEmoji]);
?>