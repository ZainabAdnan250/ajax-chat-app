<?php
require_once 'includes/config.php';
requireLogin();
header('Content-Type: application/json');

$action  = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = (int)$_SESSION['user_id'];

// ── GET group members ──────────────────────────────────────────
if ($action === 'get_members') {
    $group_id = (int)($_GET['group_id'] ?? 0);
    $res = $conn->query("
        SELECT u.id, u.full_name, u.phone, u.cnic, u.is_online,
               cg.created_by
        FROM group_members gm
        JOIN users u ON gm.user_id = u.id
        JOIN chat_groups cg ON cg.id = gm.group_id
        WHERE gm.group_id = $group_id
        ORDER BY u.full_name
    ");
    $members = [];
    while ($r = $res->fetch_assoc()) $members[] = $r;
    echo json_encode(['members' => $members]);
    exit;
}

// ── GET non-members (for adding) ──────────────────────────────
if ($action === 'get_non_members') {
    $group_id = (int)($_GET['group_id'] ?? 0);
    $res = $conn->query("
        SELECT u.id, u.full_name, u.phone, u.cnic
        FROM users u
        WHERE u.id != $user_id
          AND u.id NOT IN (
            SELECT user_id FROM group_members WHERE group_id = $group_id
          )
        ORDER BY u.full_name
    ");
    $users = [];
    while ($r = $res->fetch_assoc()) $users[] = $r;
    echo json_encode(['users' => $users]);
    exit;
}

// ── CREATE group ──────────────────────────────────────────────
if ($action === 'create_group') {
    $name    = trim($conn->real_escape_string($_POST['name'] ?? ''));
    $desc    = trim($conn->real_escape_string($_POST['description'] ?? ''));
    $members = json_decode($_POST['members'] ?? '[]', true);

    if (!$name) { echo json_encode(['success'=>false,'error'=>'Group name required']); exit; }

    $conn->query("INSERT INTO chat_groups (name, description, created_by) VALUES ('$name','$desc',$user_id)");
    $group_id = $conn->insert_id;

    // Add creator
    $conn->query("INSERT INTO group_members (group_id, user_id) VALUES ($group_id, $user_id)");

    // Add selected members
    foreach ($members as $mid) {
        $mid = (int)$mid;
        if ($mid > 0 && $mid !== $user_id) {
            $conn->query("INSERT IGNORE INTO group_members (group_id, user_id) VALUES ($group_id, $mid)");
        }
    }
    echo json_encode(['success'=>true, 'group_id'=>$group_id, 'name'=>$name]);
    exit;
}

// ── ADD member ────────────────────────────────────────────────
if ($action === 'add_member') {
    $group_id  = (int)($_POST['group_id'] ?? 0);
    $member_id = (int)($_POST['member_id'] ?? 0);
    $conn->query("INSERT IGNORE INTO group_members (group_id, user_id) VALUES ($group_id, $member_id)");
    echo json_encode(['success' => $conn->affected_rows > 0 || $conn->errno === 0]);
    exit;
}

// ── REMOVE member (admin only) ────────────────────────────────
if ($action === 'remove_member') {
    $group_id  = (int)($_POST['group_id'] ?? 0);
    $member_id = (int)($_POST['member_id'] ?? 0);
    // Check if current user is group creator
    $chk = $conn->query("SELECT created_by FROM chat_groups WHERE id=$group_id");
    $grp = $chk->fetch_assoc();
    if ($grp && (int)$grp['created_by'] === $user_id) {
        $conn->query("DELETE FROM group_members WHERE group_id=$group_id AND user_id=$member_id");
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false,'error'=>'Not authorized']);
    }
    exit;
}

// ── MARK group as read ────────────────────────────────────────
if ($action === 'mark_read') {
    $group_id   = (int)($_POST['group_id'] ?? 0);
    $last_msg_id = (int)($_POST['last_msg_id'] ?? 0);
    $conn->query("INSERT INTO group_read_status (group_id, user_id, last_read_id)
                  VALUES ($group_id, $user_id, $last_msg_id)
                  ON DUPLICATE KEY UPDATE last_read_id = GREATEST(last_read_id, $last_msg_id)");
    echo json_encode(['success'=>true]);
    exit;
}

echo json_encode(['error'=>'Unknown action']);
?>
