<?php
require_once 'includes/config.php';
requireLogin();
updateOnlineStatus($conn, $_SESSION['user_id'], 1);
$currentUser = getCurrentUser($conn);
// Fetch all users for group creation modal
$allUsers = [];
$res = $conn->query("SELECT id, full_name, cnic, phone FROM users WHERE id != ".(int)$_SESSION['user_id']." ORDER BY full_name");
while($r = $res->fetch_assoc()) $allUsers[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ChatApp - <?= htmlspecialchars($currentUser['full_name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --primary:#1565C0;--primary-dark:#0D47A1;--accent:#1976D2;
  --bg-main:#ECEFF1;--bg-sidebar:#FFFFFF;--bg-chat:#FFFFFF;
  --bg-msg-in:#E8F5E9;--bg-msg-out:#FFFFFF;
  --header-bg:#EEF4FF;--top-bar:#2C1654;
  --border:#E0E0E0;--text:#212121;--muted:#757575;
  --green:#4CAF50;--sidebar-active:#E3F2FD;
  --font:'Nunito',sans-serif;
}
html,body{height:100%;font-family:var(--font);background:var(--bg-main);overflow:hidden}

/* TOPBAR */
.topbar{background:var(--top-bar);height:62px;display:flex;align-items:center;padding:0 28px;gap:16px;position:fixed;top:0;left:0;right:0;z-index:100;box-shadow:0 2px 8px rgba(0,0,0,0.2)}
.topbar-left h2{color:#fff;font-size:18px;font-weight:800}
.topbar-left p{color:rgba(255,255,255,0.65);font-size:12px;font-weight:500}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:18px}
.topbar-icon{width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;border-radius:8px;transition:background .15s}
.topbar-icon:hover{background:rgba(255,255,255,0.12)}
.topbar-icon svg{width:22px;height:22px;fill:none;stroke:rgba(255,255,255,0.85);stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.avatar-topbar{width:36px;height:36px;border-radius:50%;background:#B0BEC5;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#37474F;cursor:pointer;flex-shrink:0;overflow:hidden;border:2px solid rgba(255,255,255,0.4)}
.avatar-topbar img{width:100%;height:100%;object-fit:cover}
.avatar-topbar:hover{border-color:white}
/* Avatar in sidebar contacts */
.contact-avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%}
/* Avatar upload modal */
.avatar-preview-wrap{text-align:center;margin-bottom:20px}
.avatar-preview-big{width:100px;height:100px;border-radius:50%;background:#B0BEC5;display:inline-flex;align-items:center;justify-content:center;font-size:32px;font-weight:800;color:white;overflow:hidden;border:4px solid var(--border);margin-bottom:12px}
.avatar-preview-big img{width:100%;height:100%;object-fit:cover}
.avatar-upload-btn{background:#F5F5F5;border:2px dashed #BDBDBD;border-radius:10px;padding:10px 20px;cursor:pointer;font-family:var(--font);font-size:13px;font-weight:700;color:var(--muted);transition:all .15s;display:inline-block}
.avatar-upload-btn:hover{border-color:var(--accent);color:var(--accent);background:#E3F2FD}
.avatar-remove-btn{background:none;border:none;color:#e53935;font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font);margin-top:6px;display:block;margin:6px auto 0}
.topbar-name{color:white;font-size:14px;font-weight:700}
.topbar-logout{background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:white;padding:5px 14px;border-radius:8px;font-family:var(--font);font-size:12px;font-weight:700;cursor:pointer;text-decoration:none;transition:background .15s}
.topbar-logout:hover{background:rgba(255,255,255,0.25)}

/* LAYOUT */
.app-layout{display:flex;height:calc(100vh - 62px);margin-top:62px}

/* SIDEBAR */
.sidebar{width:300px;min-width:260px;background:var(--bg-sidebar);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden}
.sidebar-header{display:flex;align-items:center;justify-content:space-between;padding:18px 20px 12px}
.sidebar-header h3{font-size:18px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px}
.btn-new-chat{width:32px;height:32px;background:var(--primary);border-radius:50%;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0}
.btn-new-chat svg{width:18px;height:18px;fill:white}
.search-box{margin:0 16px 12px;position:relative}
.search-box input{width:100%;padding:9px 14px 9px 36px;border:1px solid var(--border);border-radius:10px;font-family:var(--font);font-size:13px;color:var(--text);background:#F5F7FA;outline:none}
.search-box input:focus{border-color:var(--accent);background:white}
.search-icon{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:16px;height:16px;stroke:var(--muted);fill:none;stroke-width:2;stroke-linecap:round}
.sidebar-body{flex:1;overflow-y:auto;padding-bottom:16px}
.sidebar-body::-webkit-scrollbar{width:4px}
.sidebar-body::-webkit-scrollbar-thumb{background:#CFD8DC;border-radius:4px}
.section-header{display:flex;align-items:center;justify-content:space-between;padding:10px 20px 6px;cursor:pointer;user-select:none}
.section-header span{font-size:14px;font-weight:800;color:var(--text)}
.section-chevron{width:18px;height:18px;stroke:var(--muted);fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;transition:transform .2s}
.section-content.collapsed{display:none}
.contact-item{display:flex;align-items:center;padding:10px 16px;gap:12px;cursor:pointer;border-left:3px solid transparent;transition:background .15s;position:relative}
.contact-item:hover{background:#F5F7FA}
.contact-item.active{background:var(--sidebar-active);border-left-color:var(--primary)}
.contact-avatar{width:40px;height:40px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:white;flex-shrink:0;position:relative}
.contact-avatar.group-avatar{border-radius:10px;background:#607D8B}
.online-dot{width:11px;height:11px;background:var(--green);border-radius:50%;border:2px solid white;position:absolute;bottom:0;right:0}
.contact-info{flex:1;min-width:0}
.contact-name{font-size:14px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.contact-preview{font-size:12px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px}
.unread-badge{background:var(--primary);color:white;font-size:11px;font-weight:800;min-width:20px;height:20px;border-radius:10px;display:flex;align-items:center;justify-content:center;padding:0 5px;flex-shrink:0}

/* CHAT AREA */
.chat-area{flex:1;display:flex;flex-direction:column;background:var(--bg-chat);overflow:hidden}
.chat-header{height:64px;background:var(--header-bg);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 20px;gap:14px;flex-shrink:0}
.chat-header-avatar{width:42px;height:42px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;color:white;flex-shrink:0}
.chat-header-info{flex:1}
.chat-header-name{font-size:16px;font-weight:800;color:var(--text)}
.chat-header-sub{font-size:12px;color:var(--muted);font-weight:500}
.chat-header-actions{display:flex;gap:10px}
.hdr-btn{width:40px;height:40px;background:var(--primary);border:none;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer}
.hdr-btn svg{width:18px;height:18px;fill:white}
.hdr-btn.dark{background:#424242}
.hdr-btn.grey{background:#78909C}

/* DATE DIVIDER */
.date-divider{display:flex;align-items:center;gap:14px;padding:12px 20px;flex-shrink:0}
.date-divider::before,.date-divider::after{content:'';flex:1;height:1px;background:#E0E0E0}
.date-divider span{font-size:12px;color:var(--muted);font-weight:600;white-space:nowrap}

/* MESSAGES */
.messages-container{flex:1;overflow-y:auto;padding:8px 0;background:#FAFAFA}
.messages-container::-webkit-scrollbar{width:5px}
.messages-container::-webkit-scrollbar-thumb{background:#CFD8DC;border-radius:4px}
.msg-row{display:flex;padding:3px 20px;gap:10px;align-items:flex-end}
.msg-row.mine{flex-direction:row-reverse}
.msg-sender-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:white;flex-shrink:0}
.msg-body{max-width:68%}
.msg-sender-name{font-size:11px;font-weight:700;color:var(--muted);margin-bottom:2px;padding-left:2px}
.msg-row.mine .msg-sender-name{text-align:right}
.msg-bubble{background:var(--bg-msg-in);color:var(--text);padding:10px 14px;border-radius:12px 12px 12px 2px;font-size:14px;line-height:1.5;word-break:break-word;position:relative;display:inline-block;max-width:100%}
.msg-row.mine .msg-bubble{background:var(--bg-msg-out);border-radius:12px 12px 2px 12px;box-shadow:0 1px 4px rgba(0,0,0,0.08)}
.msg-meta{display:flex;align-items:center;gap:6px;margin-top:4px;padding:0 2px;flex-wrap:wrap}
.msg-row.mine .msg-meta{justify-content:flex-end}
.msg-time{font-size:11px;color:var(--muted)}

/* REACTIONS */
.reactions-bar{display:flex;gap:3px;flex-wrap:wrap;margin-top:4px}
.reaction-chip{background:white;border:1px solid #E0E0E0;border-radius:12px;padding:2px 7px;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:3px;transition:background .1s}
.reaction-chip:hover{background:#F0F0F0}
.reaction-chip .rc{font-size:11px;color:var(--muted);font-weight:700}
.reaction-chip.mine-react{background:#E3F2FD;border-color:#90CAF9}

/* EMOJI PICKER */
.emoji-picker-wrap{position:relative;display:inline-block}
.emoji-picker-popup{display:none;position:absolute;bottom:46px;right:0;background:white;border:1px solid var(--border);border-radius:16px;padding:10px;box-shadow:0 8px 30px rgba(0,0,0,0.15);z-index:200;width:320px}
.emoji-picker-popup.show{display:block}
.emoji-cats{display:flex;gap:4px;margin-bottom:8px;border-bottom:1px solid #eee;padding-bottom:6px}
.emoji-cat-btn{background:none;border:none;cursor:pointer;font-size:18px;padding:4px 6px;border-radius:8px;transition:background .1s}
.emoji-cat-btn:hover,.emoji-cat-btn.active{background:#F0F0F0}
.emoji-grid{display:grid;grid-template-columns:repeat(8,1fr);gap:2px;max-height:200px;overflow-y:auto}
.emoji-grid::-webkit-scrollbar{width:4px}
.emoji-grid::-webkit-scrollbar-thumb{background:#ddd;border-radius:4px}
.emoji-btn-item{background:none;border:none;cursor:pointer;font-size:22px;padding:4px;border-radius:8px;transition:background .1s;line-height:1}
.emoji-btn-item:hover{background:#F0F0F0}

/* MSG REACT POPUP */
.msg-react-popup{display:none;position:absolute;background:white;border:1px solid #ddd;border-radius:20px;padding:5px 8px;box-shadow:0 4px 16px rgba(0,0,0,0.12);z-index:100;white-space:nowrap}
.msg-react-popup.show{display:flex;gap:4px}
.msg-react-popup button{background:none;border:none;cursor:pointer;font-size:20px;padding:2px 4px;border-radius:8px;transition:background .1s}
.msg-react-popup button:hover{background:#F0F0F0;transform:scale(1.2)}

/* INPUT BAR */
.msg-input-bar{padding:14px 20px;background:white;border-top:1px solid var(--border);display:flex;align-items:center;gap:12px;flex-shrink:0}
.attach-btn{width:34px;height:34px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;border-radius:8px;transition:background .15s;flex-shrink:0}
.attach-btn:hover{background:#F5F5F5}
.attach-btn svg{width:22px;height:22px;stroke:var(--muted);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#msgInput{flex:1;border:1px solid var(--border);border-radius:24px;padding:10px 18px;font-family:var(--font);font-size:14px;color:var(--text);outline:none;background:#F8F9FA;transition:border-color .2s,background .2s}
#msgInput:focus{border-color:var(--accent);background:white}
.send-btn{width:42px;height:42px;background:var(--primary);border:none;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .2s,transform .1s}
.send-btn:hover{background:var(--primary-dark);transform:scale(1.05)}
.send-btn svg{width:18px;height:18px;fill:white}

/* NO CHAT */
#noChatSelected{display:flex;flex:1;flex-direction:column;align-items:center;justify-content:center;color:#90A4AE;gap:16px;background:#FAFAFA}
#noChatSelected svg{width:80px;height:80px;stroke:#CFD8DC;fill:none;stroke-width:1}
#noChatSelected h2{font-size:22px;font-weight:700}
#noChatSelected p{font-size:14px}

/* MODAL */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:500;align-items:center;justify-content:center}
.modal-overlay.show{display:flex}
.modal{background:white;border-radius:16px;width:100%;max-width:480px;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,0.2);overflow:hidden}
.modal-header{padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.modal-header h3{font-size:17px;font-weight:800;color:var(--text)}
.modal-close{background:none;border:none;cursor:pointer;font-size:22px;color:var(--muted);line-height:1;padding:2px 6px;border-radius:6px}
.modal-close:hover{background:#F0F0F0}
.modal-body{padding:20px 24px;overflow-y:auto;flex:1}
.modal-body::-webkit-scrollbar{width:4px}
.modal-body::-webkit-scrollbar-thumb{background:#ddd;border-radius:4px}
.modal-footer{padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:13px;font-weight:700;color:var(--text);margin-bottom:6px}
.form-group input,.form-group textarea{width:100%;padding:10px 13px;border:2px solid var(--border);border-radius:10px;font-family:var(--font);font-size:14px;color:var(--text);outline:none;transition:border-color .2s}
.form-group input:focus,.form-group textarea:focus{border-color:var(--accent)}
.form-group textarea{resize:vertical;min-height:60px}
.member-select-list{max-height:200px;overflow-y:auto;border:2px solid var(--border);border-radius:10px}
.member-select-list::-webkit-scrollbar{width:4px}
.member-select-list::-webkit-scrollbar-thumb{background:#ddd;border-radius:4px}
.member-select-item{display:flex;align-items:center;gap:10px;padding:9px 13px;cursor:pointer;transition:background .1s}
.member-select-item:hover{background:#F5F7FA}
.member-select-item input[type=checkbox]{width:16px;height:16px;accent-color:var(--primary)}
.member-avatar-sm{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:white;flex-shrink:0}
.member-info-sm{flex:1}
.member-name-sm{font-size:13px;font-weight:700;color:var(--text)}
.member-id-sm{font-size:11px;color:var(--muted)}
.btn-primary{background:var(--primary);color:white;border:none;border-radius:10px;padding:10px 22px;font-family:var(--font);font-size:14px;font-weight:700;cursor:pointer;transition:background .15s}
.btn-primary:hover{background:var(--primary-dark)}
.btn-secondary{background:#F5F5F5;color:var(--text);border:none;border-radius:10px;padding:10px 22px;font-family:var(--font);font-size:14px;font-weight:700;cursor:pointer}
.btn-secondary:hover{background:#EEEEEE}
.btn-danger{background:#e53935;color:white;border:none;border-radius:8px;padding:6px 12px;font-family:var(--font);font-size:12px;font-weight:700;cursor:pointer}
.members-list-item{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f0f0f0}
.members-list-item:last-child{border-bottom:none}
.online-tag{font-size:11px;color:var(--green);font-weight:700}
.offline-tag{font-size:11px;color:var(--muted);font-weight:700}
.badge-admin{background:#FFF3E0;color:#E65100;font-size:10px;font-weight:800;padding:2px 7px;border-radius:6px;margin-left:4px}
.seen-label{font-size:11px;color:#1565C0;font-weight:700;margin-top:2px;text-align:right;}
.sent-label{color:#90A4AE;}
.msg-row:not(.mine) .seen-label{text-align:left;}
.chat-empty svg{width:64px;height:64px;stroke:#CFD8DC;fill:none;stroke-width:1.5}
.chat-empty h3{font-size:18px;font-weight:700;color:#B0BEC5}
.loading{text-align:center;padding:30px;color:var(--muted);font-size:13px}
</style>
</head>
<body>

<!-- TOPBAR -->
<header class="topbar">
  <div class="topbar-left">
    <h2>Welcome, <?= htmlspecialchars($currentUser['full_name']) ?></h2>
    <p>Phone: <?= htmlspecialchars($currentUser['phone'] ?? 'N/A') ?></p>
  </div>
  <div class="topbar-right">
    <div class="topbar-icon"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg></div>
    <div class="topbar-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg></div>
    <div class="topbar-icon"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg></div>
    <div class="avatar-topbar" onclick="openModal('profileModal')" title="Edit Profile">
      <?php if($currentUser['avatar']): ?>
        <img src="uploads/avatars/<?= htmlspecialchars($currentUser['avatar']) ?>" alt="avatar">
      <?php else: ?>
        <?= getInitials($currentUser['full_name']) ?>
      <?php endif; ?>
    </div>
    <span class="topbar-name"><?= htmlspecialchars(explode(' ',$currentUser['full_name'])[0]) ?></span>
    <a href="logout.php" class="topbar-logout">Logout</a>
  </div>
</header>

<!-- LAYOUT -->
<div class="app-layout">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <h3>
        <svg style="width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:2.5;stroke-linecap:round" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        Messages
      </h3>

    </div>
    <div class="search-box">
      <svg class="search-icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="searchInput" placeholder="Search">
    </div>
    <div class="sidebar-body" id="sidebarBody"><div class="loading">Loading...</div></div>
  </aside>

  <!-- CHAT AREA -->
  <main class="chat-area" id="chatArea">
    <div id="noChatSelected" style="display:flex;flex:1;flex-direction:column;align-items:center;justify-content:center;color:#90A4AE;gap:16px;background:#FAFAFA">
      <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
      <h2 style="font-size:22px;font-weight:700">Select a chat to start</h2>
      <p style="font-size:14px">Choose a contact or group from the left to begin messaging</p>
    </div>
  </main>
</div>

<!-- CREATE GROUP MODAL -->
<div class="modal-overlay" id="createGroupModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Create New Group</h3>
      <button class="modal-close" onclick="closeModal('createGroupModal')">×</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label>Group Name *</label>
        <input type="text" id="newGroupName" placeholder="e.g. Study Group, Faculty...">
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea id="newGroupDesc" placeholder="What's this group about?"></textarea>
      </div>
      <div class="form-group">
        <label>Add Members</label>
        <div class="member-select-list" id="memberSelectList">
          <?php foreach($allUsers as $u): ?>
          <div class="member-select-item">
            <input type="checkbox" value="<?= $u['id'] ?>" class="member-checkbox">
            <div class="member-avatar-sm" style="background:<?= '#'.substr(md5($u['full_name']),0,6) ?>"><?= getInitials($u['full_name']) ?></div>
            <div class="member-info-sm">
              <div class="member-name-sm"><?= htmlspecialchars($u['full_name']) ?></div>
              <div class="member-id-sm">Phone: <?= htmlspecialchars($u['phone'] ?? 'N/A') ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('createGroupModal')">Cancel</button>
      <button class="btn-primary" onclick="createGroup()">Create Group</button>
    </div>
  </div>
</div>

<!-- GROUP MEMBERS MODAL -->
<div class="modal-overlay" id="groupMembersModal">
  <div class="modal">
    <div class="modal-header">
      <h3 id="membersModalTitle">Group Members</h3>
      <button class="modal-close" onclick="closeModal('groupMembersModal')">×</button>
    </div>
    <div class="modal-body" id="membersModalBody"><div class="loading">Loading...</div></div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('groupMembersModal')">Close</button>
      <button class="btn-primary" onclick="openAddMemberPanel()">+ Add Member</button>
    </div>
  </div>
</div>

<!-- PROFILE / AVATAR MODAL -->
<div class="modal-overlay" id="profileModal">
  <div class="modal" style="max-width:380px">
    <div class="modal-header">
      <h3>My Profile</h3>
      <button class="modal-close" onclick="closeModal('profileModal')">×</button>
    </div>
    <div class="modal-body">
      <div class="avatar-preview-wrap">
        <div class="avatar-preview-big" id="avatarPreviewBig">
          <?php if($currentUser['avatar']): ?>
            <img id="avatarPreviewImg" src="uploads/avatars/<?= htmlspecialchars($currentUser['avatar']) ?>" alt="avatar">
          <?php else: ?>
            <span id="avatarPreviewInitials"><?= getInitials($currentUser['full_name']) ?></span>
          <?php endif; ?>
        </div>
        <div>
          <label class="avatar-upload-btn" for="avatarFileInput">
            📷 Choose Photo
          </label>
          <input type="file" id="avatarFileInput" accept="image/*" style="display:none" onchange="previewAndUpload(this)">
          <?php if($currentUser['avatar']): ?>
          <button class="avatar-remove-btn" onclick="removeAvatar()">✕ Remove Photo</button>
          <?php endif; ?>
        </div>
      </div>
      <!-- Profile Info -->
      <div style="background:#F5F7FA;border-radius:12px;padding:14px 16px;font-size:13px;line-height:2">
        <div><strong>Name:</strong> <?= htmlspecialchars($currentUser['full_name']) ?></div>
        <div><strong>Phone:</strong> <?= htmlspecialchars($currentUser['phone'] ?? 'N/A') ?></div>
        <div><strong>Email:</strong> <?= htmlspecialchars($currentUser['email']) ?></div>
        <div><strong>CNIC:</strong> <?= htmlspecialchars($currentUser['cnic']) ?></div>
      </div>
      <div id="avatarMsg" style="text-align:center;margin-top:12px;font-size:13px;font-weight:700"></div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('profileModal')">Close</button>
    </div>
  </div>
</div>

<!-- ADD MEMBER MODAL -->
<div class="modal-overlay" id="addMemberModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Add Members</h3>
      <button class="modal-close" onclick="closeModal('addMemberModal')">×</button>
    </div>
    <div class="modal-body" id="addMemberBody"><div class="loading">Loading...</div></div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('addMemberModal')">Close</button>
    </div>
  </div>
</div>

<script>
const ME = {
  id: <?= (int)$_SESSION['user_id'] ?>,
  name: <?= json_encode($currentUser['full_name']) ?>,
  initials: <?= json_encode(getInitials($currentUser['full_name'])) ?>
};

let activeChat = null;
let lastMsgId   = 0;
let pollTimer   = null;
let contacts    = [];
let groups      = [];
let currentGroupId = null;

// ── UTILS ─────────────────────────────────────────────────────
function getInits(name){
  return name.split(' ').map(w=>w[0]||'').join('').toUpperCase().slice(0,2)||'?';
}
function colorFor(name){
  const cols=['#4CAF50','#2196F3','#9C27B0','#FF5722','#009688','#FF9800','#E91E63','#3F51B5'];
  let h=0; for(let c of name) h=(h*31+c.charCodeAt(0))%cols.length;
  return cols[Math.abs(h)];
}
function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

// ── MODALS ────────────────────────────────────────────────────
function closeModal(id){document.getElementById(id).classList.remove('show');}
function openModal(id){document.getElementById(id).classList.add('show');}

// ── SIDEBAR ───────────────────────────────────────────────────
async function loadSidebar(){
  const res  = await fetch('get_contacts.php');
  const data = await res.json();
  contacts = data.contacts||[];
  groups   = data.groups||[];
  renderSidebar(document.getElementById('searchInput').value);
}

function renderSidebar(filter=''){
  const q=filter.toLowerCase();
  const body=document.getElementById('sidebarBody');
  let html='';

  // Groups
  const fg=groups.filter(g=>!q||g.name.toLowerCase().includes(q));
  html+=`<div class="section-header">
    <span onclick="toggleSection('sg')" style="flex:1;cursor:pointer">Groups</span>
    <button onclick="openCreateGroup()" style="background:#1565C0;border:none;color:white;border-radius:20px;padding:3px 10px 3px 7px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:4px;margin-right:6px;font-family:inherit">
      <svg style="width:13px;height:13px;fill:white" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19" stroke="white" stroke-width="3" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="white" stroke-width="3" stroke-linecap="round"/></svg>
      Create Group
    </button>
    <svg class="section-chevron" id="chev-sg" onclick="toggleSection('sg')" style="cursor:pointer" viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg>
  </div><div class="section-content" id="sg">`;
  if(!fg.length) html+=`<div style="padding:8px 20px;font-size:13px;color:#aaa">No groups</div>`;
  for(const g of fg){
    const preview=g.last_message?esc(g.last_message).slice(0,34)+'...':'No messages yet';
    const isActive=activeChat&&activeChat.type==='group'&&activeChat.id==g.id;
    const unread=parseInt(g.unread)||0;
    html+=`<div class="contact-item ${isActive?'active':''}" onclick="openChat('group',${g.id},'${esc(g.name)}','Group')">
      <div class="contact-avatar group-avatar">
        <svg style="width:20px;height:20px;fill:white" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
      </div>
      <div class="contact-info">
        <div class="contact-name">${esc(g.name)}</div>
        <div class="contact-preview">${preview}</div>
      </div>
      ${unread>0?`<div class="unread-badge">${unread}</div>`:''}
    </div>`;
  }
  html+=`</div>`;

  // Contacts
  const fc=contacts.filter(c=>!q||c.full_name.toLowerCase().includes(q));
  html+=`<div class="section-header" onclick="toggleSection('sc')">
    <span>Chat</span>
    <svg class="section-chevron" id="chev-sc" viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg>
  </div><div class="section-content" id="sc">`;
  if(!fc.length) html+=`<div style="padding:8px 20px;font-size:13px;color:#aaa">No contacts</div>`;
  for(const c of fc){
    const preview=c.last_message?esc(c.last_message).slice(0,34)+'...':'Start a conversation';
    const isActive=activeChat&&activeChat.type==='private'&&activeChat.id==c.id;
    const unread=parseInt(c.unread)||0;
    const color=colorFor(c.full_name);
    const avatarHtml = c.avatar
      ? `<img src="uploads/avatars/${c.avatar}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">`
      : getInits(c.full_name);
    html+=`<div class="contact-item ${isActive?'active':''}" onclick="openChat('private',${c.id},'${esc(c.full_name)}','${esc(c.phone||'No phone')}','${c.avatar||''}')">
      <div class="contact-avatar" style="background:${c.avatar?'#eee':color}">
        ${avatarHtml}
        ${c.is_online?'<div class="online-dot"></div>':''}
      </div>
      <div class="contact-info">
        <div class="contact-name">${esc(c.full_name)}</div>
        <div class="contact-preview">${preview}</div>
      </div>
      ${unread>0?`<div class="unread-badge">${unread}</div>`:''}
    </div>`;
  }
  html+=`</div>`;
  body.innerHTML=html;
}

function toggleSection(id){
  document.getElementById(id).classList.toggle('collapsed');
  const chev=document.getElementById('chev-'+id);
  chev.style.transform=chev.style.transform==='rotate(-90deg)'?'':'rotate(-90deg)';
}

// ── OPEN CHAT ─────────────────────────────────────────────────
function openChat(type,id,name,sub,avatar=''){
  activeChat={type,id,name,sub,avatar};
  lastMsgId=0;
  renderSidebar(document.getElementById('searchInput').value);

  const ca=document.getElementById('chatArea');
  const color=colorFor(name);
  const isGroup=type==='group';

  const headerAvatarHtml = isGroup
    ? `<svg style="width:22px;height:22px;fill:white" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>`
    : (avatar ? `<img src="uploads/avatars/${avatar}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">` : getInits(name));

  const headerAvatarBg = isGroup ? '#607D8B' : (avatar ? '#eee' : color);

  ca.innerHTML=`
    <div class="chat-header">
      <div class="chat-header-avatar" style="background:${headerAvatarBg}">
        ${headerAvatarHtml}
      </div>
      <div class="chat-header-info">
        <div class="chat-header-name">${esc(name)}</div>
        <div class="chat-header-sub">${esc(sub)}</div>
      </div>
      <div class="chat-header-actions">
        <button class="hdr-btn" title="Voice Call"><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92z"/></svg></button>
        <button class="hdr-btn" title="Video Call"><svg viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg></button>
        ${isGroup?`<button class="hdr-btn grey" title="Members" onclick="openGroupMembers(${id},'${esc(name)}')"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></button>`:''}
        <button class="hdr-btn dark" title="More"><svg viewBox="0 0 24 24"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg></button>
      </div>
    </div>
    <div class="messages-container" id="msgContainer"><div class="loading">Loading…</div></div>
    <div class="msg-input-bar">
      <button class="attach-btn"><svg viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg></button>
      <input type="text" id="msgInput" placeholder="Type here..." autocomplete="off">
      <div class="emoji-picker-wrap">
        <button class="attach-btn" id="emojiToggleBtn" onclick="toggleEmojiPicker()" title="Emoji">
          <svg style="fill:#757575;stroke:none" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 13.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm4 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm3-5H7v-2h10v2z"/></svg>
        </button>
        <div class="emoji-picker-popup" id="emojiPickerPopup">
          <div class="emoji-cats">
            <button class="emoji-cat-btn active" onclick="showEmojiCat('smileys',this)">😀</button>
            <button class="emoji-cat-btn" onclick="showEmojiCat('gestures',this)">👍</button>
            <button class="emoji-cat-btn" onclick="showEmojiCat('hearts',this)">❤️</button>
            <button class="emoji-cat-btn" onclick="showEmojiCat('animals',this)">🐱</button>
            <button class="emoji-cat-btn" onclick="showEmojiCat('food',this)">🍕</button>
            <button class="emoji-cat-btn" onclick="showEmojiCat('activities',this)">⚽</button>
            <button class="emoji-cat-btn" onclick="showEmojiCat('objects',this)">💡</button>
          </div>
          <div class="emoji-grid" id="emojiGrid"></div>
        </div>
      </div>
      <button class="send-btn" onclick="sendMessage()"><svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg></button>
    </div>`;

  document.getElementById('msgInput').addEventListener('keydown',e=>{
    if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendMessage();}
  });

  // Init emoji picker
  showEmojiCat('smileys', document.querySelector('.emoji-cat-btn.active'));

  // Close emoji picker on outside click
  document.addEventListener('click', handleOutsideClick);

  if(pollTimer) clearInterval(pollTimer);
  fetchMessages(true);
  pollTimer=setInterval(()=>fetchMessages(false),2500);
}

// ── EMOJI CATEGORIES ──────────────────────────────────────────
const EMOJI_CATS = {
  smileys: ['😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇','🥰','😍','🤩','😘','😗','😚','😙','🥲','😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','😬','🤥','😌','😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🥴','😵','🤯','🤠','🥳','🥸','😎','🤓','🧐','😕','😟','🙁','☹️','😮','😯','😲','😳','🥺','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣','😞','😓','😩','😫','🥱','😤','😡','😠','🤬','😈','👿','💀','☠️'],
  gestures: ['👍','👎','👌','🤌','🤏','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','🖕','👇','☝️','👋','🤚','🖐️','✋','🖖','👏','🙌','🤲','🤝','🙏','✍️','💪','🦾','🦵','🦶','👂','🦻','👃','🫀','🫁','🧠','🦷','🦴','👀','👁️','👅','👄'],
  hearts: ['❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❣️','💕','💞','💓','💗','💖','💘','💝','💟','☮️','✝️','☪️','🕉️','✡️','🔯','🕎','☯️','☦️','🛐','⛎'],
  animals: ['🐱','🐶','🦊','🐻','🐼','🐨','🐯','🦁','🐮','🐷','🐸','🐵','🙈','🙉','🙊','🐔','🐧','🐦','🐤','🦆','🦅','🦉','🦇','🐺','🐗','🐴','🦄','🐝','🐛','🦋','🐌','🐞','🐜','🦟','🦗','🕷️','🦂','🐢','🐍','🦎','🦖','🦕','🐙','🦑','🦐','🦞','🦀','🐡','🐠','🐟','🐬','🐳','🐋','🦈','🐊','🐅','🐆','🦓','🦍','🦧','🦣','🐘','🦛','🦏','🐪','🐫','🦒','🦘','🦬','🐃','🐂','🐄','🐎','🐖','🐏','🐑','🦙','🐐','🦌','🐕','🐩','🦮','🐕‍🦺','🐈','🐈‍⬛','🪶','🐓','🦃','🦤','🦚','🦜','🦢','🦩','🕊️','🐇','🦝','🦨','🦡','🦫','🦦','🦥','🐁','🐀','🐿️','🦔'],
  food: ['🍕','🍔','🌮','🌯','🍟','🥗','🥙','🥪','🍱','🍣','🍜','🍝','🍛','🍲','🥘','🫕','🍿','🧆','🥚','🍳','🧈','🥞','🧇','🥓','🥩','🍗','🍖','🌭','🍦','🍧','🍨','🍩','🍪','🎂','🍰','🧁','🥧','🍫','🍬','🍭','🍮','🍯','🍼','🥛','☕','🫖','🍵','🧃','🥤','🧋','🍶','🍺','🍻','🥂','🍷','🥃','🍸','🍹','🧉','🍾','🧊','🥢','🍽️','🍴','🥄','🫙'],
  activities: ['⚽','🏀','🏈','⚾','🥎','🏐','🏉','🥏','🎾','🏸','🏒','🏑','🥍','🏏','🪃','🥅','⛳','🪁','🛝','🏹','🎣','🤿','🥊','🥋','🎽','🛹','🛼','🛷','⛸️','🥌','🎿','⛷️','🏂','🪂','🏋️','🤸','⛹️','🤺','🏇','🧘','🛀','🛌','🧗','🚵','🚴','🏆','🥇','🥈','🥉','🏅','🎖️','🏵️','🎗️','🎫','🎟️','🎪','🤹','🎭','🩰','🎨','🎬','🎤','🎧','🎼','🎵','🎶','🎹','🪘','🥁','🪗','🎷','🎺','🎸','🪕','🎻','🪈'],
  objects: ['💡','🔦','🕯️','💰','💴','💵','💸','💳','🪙','💎','🔑','🗝️','🔒','🔓','🔨','⛏️','⚒️','🛠️','🗡️','⚔️','🔫','🛡️','🔧','🔩','⚙️','🗜️','⚖️','🦯','🔗','⛓️','🧰','🪜','🧲','🪄','🔮','🧿','📱','💻','⌨️','🖥️','🖨️','🖱️','🕹️','📺','📷','📸','📹','🎥','📽️','📞','☎️','📟','📠','📡','🔋','🪫','🔌','💡','🔦','🕯️','🪔','📚','📖','📰','🗞️','📋','📝','✏️','🖊️','🖋️','📌','📍','🗺️','🗓️','📅','📆','🗒️','📁','📂','🗂️','🗃️','📥','📦','🗑️','🔍','🔎','🔐','🔏']
};

function showEmojiCat(cat, btn){
  document.querySelectorAll('.emoji-cat-btn').forEach(b=>b.classList.remove('active'));
  if(btn) btn.classList.add('active');
  const grid=document.getElementById('emojiGrid');
  if(!grid) return;
  grid.innerHTML=(EMOJI_CATS[cat]||[]).map(e=>`<button class="emoji-btn-item" onclick="insertEmoji('${e}')">${e}</button>`).join('');
}

function insertEmoji(emoji){
  const input=document.getElementById('msgInput');
  if(!input) return;
  const pos=input.selectionStart||input.value.length;
  input.value=input.value.slice(0,pos)+emoji+input.value.slice(pos);
  input.focus();
  input.setSelectionRange(pos+emoji.length,pos+emoji.length);
}

function toggleEmojiPicker(){
  const p=document.getElementById('emojiPickerPopup');
  if(p) p.classList.toggle('show');
}

function handleOutsideClick(e){
  const popup=document.getElementById('emojiPickerPopup');
  const btn=document.getElementById('emojiToggleBtn');
  if(popup && btn && !popup.contains(e.target) && !btn.contains(e.target)){
    popup.classList.remove('show');
  }
}

// ── FETCH MESSAGES ────────────────────────────────────────────
const QUICK_REACTIONS = ['👍','❤️','😂','😮','😢','🙏','🔥','😍'];

async function fetchMessages(initial=false){
  if(!activeChat) return;
  const url=`get_messages.php?type=${activeChat.type}&chat_id=${activeChat.id}&last_id=${lastMsgId}`;
  const res=await fetch(url);
  const data=await res.json();

  if(!data.messages||data.messages.length===0){
    if(initial){
      document.getElementById('msgContainer').innerHTML=`
        <div class="chat-empty">
          <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
          <h3>No messages yet</h3><p>Say hello 👋</p>
        </div>`;
    }
    return;
  }

  if(initial) document.getElementById('msgContainer').innerHTML='';

  let lastDate='';
  const container=document.getElementById('msgContainer');

  for(const msg of data.messages){
    if(msg.date!==lastDate){
      lastDate=msg.date;
      const div=document.createElement('div');
      div.className='date-divider';
      div.innerHTML=`<span>${esc(msg.date)}</span>`;
      container.appendChild(div);
    }

    const isMine=msg.is_mine;
    const color=colorFor(msg.sender_name);
    const nameLabel=isMine?`You ${msg.time}`:`${esc(msg.sender_name)} ${msg.time}`;

    // Build reactions display
    let reactHtml='';
    if(msg.reactions && Object.keys(msg.reactions).length>0){
      reactHtml='<div class="reactions-bar">';
      for(const [emoji,info] of Object.entries(msg.reactions)){
        const isMineReact=info.users && info.users.includes(ME.id);
        reactHtml+=`<span class="reaction-chip ${isMineReact?'mine-react':''}" data-emoji="${emoji}" data-msg-id="${msg.id}" onclick="reactToMsg(${msg.id},'${emoji}')">${emoji}<span class="rc">${info.count}</span></span>`;
      }
      reactHtml+='</div>';
    }

    // Build seen display (only for sender's own messages)
    let seenHtml='';
    if(isMine){
      if(activeChat.type==='private'){
        seenHtml = msg.seen
          ? `<div class="seen-label">Seen ✓✓</div>`
          : `<div class="seen-label sent-label">Sent ✓</div>`;
      } else {
        // Group: show names who have seen
        if(msg.seen_by && msg.seen_by.length > 0){
          const names = msg.seen_by.join(', ');
          seenHtml = `<div class="seen-label">Seen by ${esc(names)}</div>`;
        }
      }
    }

    const row=document.createElement('div');
    row.className=`msg-row ${isMine?'mine':''}`;
    row.dataset.msgId=msg.id;
    row.style.position='relative';

    row.innerHTML=`
      ${!isMine?`<div class="msg-sender-avatar" style="background:${color}">${getInits(msg.sender_name)}</div>`:''}
      <div class="msg-body">
        <div class="msg-sender-name">${nameLabel}</div>
        <div class="msg-bubble" ondblclick="showReactPopup(event,${msg.id})" title="Double-click to react">${esc(msg.message)}</div>
        ${reactHtml}
        <div class="msg-meta"><span class="msg-time">${msg.time}</span></div>
        ${seenHtml}
      </div>
      ${isMine?`<div class="msg-sender-avatar" style="background:${color}">${getInits(msg.sender_name)}</div>`:''}
    `;

    container.appendChild(row);
    lastMsgId=Math.max(lastMsgId,parseInt(msg.id));
  }

  container.scrollTop=container.scrollHeight;

  // Mark group as read
  if(activeChat.type==='group' && lastMsgId>0){
    const fd=new FormData();
    fd.append('action','mark_read');
    fd.append('group_id',activeChat.id);
    fd.append('last_msg_id',lastMsgId);
    fetch('group_actions.php',{method:'POST',body:fd});
  }

  loadSidebar();
}

// ── REACT POPUP (WhatsApp style) ──────────────────────────────
let currentReactMsgId=null;
let reactPopup=null;

function showReactPopup(e, msgId){
  // Remove existing popup
  if(reactPopup) reactPopup.remove();

  currentReactMsgId=msgId;
  reactPopup=document.createElement('div');
  reactPopup.className='msg-react-popup show';
  reactPopup.style.bottom='calc(100% + 6px)';
  reactPopup.style.left='0';

  reactPopup.innerHTML=QUICK_REACTIONS.map(r=>`<button onclick="reactToMsg(${msgId},'${r}');closeReactPopup()">${r}</button>`).join('');

  const bubble=e.target.closest('.msg-bubble');
  bubble.style.position='relative';
  bubble.appendChild(reactPopup);

  setTimeout(()=>document.addEventListener('click',closeReactPopupOutside),10);
}

function closeReactPopup(){
  if(reactPopup){reactPopup.remove();reactPopup=null;}
  document.removeEventListener('click',closeReactPopupOutside);
}
function closeReactPopupOutside(e){
  if(reactPopup && !reactPopup.contains(e.target)) closeReactPopup();
}

async function reactToMsg(msgId, emoji){
  // ── Step 1: IMMEDIATELY update UI ──
  const row = document.querySelector(`.msg-row[data-msg-id="${msgId}"]`);
  if(row){
    const msgBody = row.querySelector('.msg-body');
    let reactBar = msgBody.querySelector('.reactions-bar');

    // Check if user already had ANY reaction on this msg
    const existingMineChip = reactBar ? [...reactBar.querySelectorAll('.reaction-chip.mine-react')] : [];
    const hadSameEmoji = existingMineChip.find(c => c.dataset.emoji === emoji);

    // Remove user's previous reaction chip (one reaction per user rule)
    existingMineChip.forEach(chip => {
      const countEl = chip.querySelector('.rc');
      const newCount = parseInt(countEl.textContent) - 1;
      chip.classList.remove('mine-react');
      if(newCount <= 0) chip.remove();
      else countEl.textContent = newCount;
    });

    // If user clicked same emoji they already had = toggle off (done)
    // If different or new emoji = add it
    if(!hadSameEmoji){
      if(!reactBar){
        reactBar = document.createElement('div');
        reactBar.className = 'reactions-bar';
        const meta = msgBody.querySelector('.msg-meta');
        msgBody.insertBefore(reactBar, meta);
      }

      // Check if this emoji chip already exists (from others)
      let chip = [...reactBar.querySelectorAll('.reaction-chip')].find(c => c.dataset.emoji === emoji);
      if(chip){
        chip.classList.add('mine-react');
        chip.querySelector('.rc').textContent = parseInt(chip.querySelector('.rc').textContent) + 1;
      } else {
        chip = document.createElement('span');
        chip.className = 'reaction-chip mine-react';
        chip.dataset.emoji = emoji;
        chip.dataset.msgId = msgId;
        chip.innerHTML = `${emoji}<span class="rc">1</span>`;
        chip.onclick = () => reactToMsg(msgId, emoji);
        reactBar.appendChild(chip);
      }
    }

    // Clean up empty reactions bar
    if(reactBar && reactBar.children.length === 0) reactBar.remove();
  }

  // ── Step 2: Send to server in background ──
  const fd=new FormData();
  fd.append('msg_id',msgId);
  fd.append('reaction',emoji);
  fd.append('type',activeChat.type);
  fd.append('user_id',ME.id);
  fetch('react_message.php',{method:'POST',body:fd});
}

// ── SEND MESSAGE ──────────────────────────────────────────────
async function sendMessage(){
  if(!activeChat) return;
  const input=document.getElementById('msgInput');
  const msg=input.value.trim();
  if(!msg) return;
  input.value='';
  input.focus();
  const fd=new FormData();
  fd.append('type',activeChat.type);
  fd.append('chat_id',activeChat.id);
  fd.append('message',msg);
  await fetch('send_message.php',{method:'POST',body:fd});
  fetchMessages(false);
}

// ── GROUP MANAGEMENT ──────────────────────────────────────────
function openCreateGroup(){
  openModal('createGroupModal');
  document.getElementById('newGroupName').value='';
  document.getElementById('newGroupDesc').value='';
  document.querySelectorAll('.member-checkbox').forEach(cb=>cb.checked=false);
}

async function createGroup(){
  const name=document.getElementById('newGroupName').value.trim();
  const desc=document.getElementById('newGroupDesc').value.trim();
  if(!name){alert('Please enter a group name');return;}
  const members=[...document.querySelectorAll('.member-checkbox:checked')].map(cb=>cb.value);
  const fd=new FormData();
  fd.append('action','create_group');
  fd.append('name',name);
  fd.append('description',desc);
  fd.append('members',JSON.stringify(members));
  const res=await fetch('group_actions.php',{method:'POST',body:fd});
  const data=await res.json();
  if(data.success){
    closeModal('createGroupModal');
    await loadSidebar();
    openChat('group',data.group_id,data.name,'Group');
  } else {
    alert(data.error||'Failed to create group');
  }
}

function openGroupMembers(groupId, groupName){
  currentGroupId=groupId;
  document.getElementById('membersModalTitle').textContent=groupName+' — Members';
  openModal('groupMembersModal');
  loadGroupMembers(groupId);
}

async function loadGroupMembers(groupId){
  const body=document.getElementById('membersModalBody');
  body.innerHTML='<div class="loading">Loading...</div>';
  const res=await fetch(`group_actions.php?action=get_members&group_id=${groupId}`);
  const data=await res.json();
  const members=data.members||[];
  if(!members.length){body.innerHTML='<p style="color:#aaa;text-align:center;padding:20px">No members</p>';return;}
  const isAdmin=members.some(m=>parseInt(m.created_by)===ME.id);
  body.innerHTML=members.map(m=>{
    const isMe=parseInt(m.id)===ME.id;
    const isCreator=parseInt(m.created_by)===parseInt(m.id);
    return `<div class="members-list-item">
      <div class="member-avatar-sm" style="background:${colorFor(m.full_name)}">${getInits(m.full_name)}</div>
      <div class="member-info-sm" style="flex:1">
        <div class="member-name-sm">${esc(m.full_name)}${isCreator?'<span class="badge-admin">Admin</span>':''}${isMe?' (You)':''}</div>
        <div class="member-id-sm">${m.is_online?'<span class="online-tag">● Online</span>':'<span class="offline-tag">● Offline</span>'} · ${esc(m.phone||m.cnic)}</div>
      </div>
      ${isAdmin&&!isMe?`<button class="btn-danger" onclick="removeMember(${m.id})">Remove</button>`:''}
    </div>`;
  }).join('');
}

async function removeMember(memberId){
  if(!confirm('Remove this member from the group?')) return;
  const fd=new FormData();
  fd.append('action','remove_member');
  fd.append('group_id',currentGroupId);
  fd.append('member_id',memberId);
  const res=await fetch('group_actions.php',{method:'POST',body:fd});
  const data=await res.json();
  if(data.success) loadGroupMembers(currentGroupId);
  else alert(data.error||'Failed');
}

function openAddMemberPanel(){
  closeModal('groupMembersModal');
  openModal('addMemberModal');
  loadNonMembers();
}

async function loadNonMembers(){
  const body=document.getElementById('addMemberBody');
  body.innerHTML='<div class="loading">Loading...</div>';
  const res=await fetch(`group_actions.php?action=get_non_members&group_id=${currentGroupId}`);
  const data=await res.json();
  const users=data.users||[];
  if(!users.length){body.innerHTML='<p style="text-align:center;color:#aaa;padding:20px">All users are already in this group!</p>';return;}
  body.innerHTML=users.map(u=>`
    <div class="members-list-item">
      <div class="member-avatar-sm" style="background:${colorFor(u.full_name)}">${getInits(u.full_name)}</div>
      <div class="member-info-sm" style="flex:1">
        <div class="member-name-sm">${esc(u.full_name)}</div>
        <div class="member-id-sm">${esc(u.phone||u.cnic)}</div>
      </div>
      <button class="btn-primary" style="font-size:12px;padding:6px 14px" onclick="addMember(${u.id},this)">Add</button>
    </div>`).join('');
}

async function addMember(memberId, btn){
  btn.textContent='Adding...';
  btn.disabled=true;
  const fd=new FormData();
  fd.append('action','add_member');
  fd.append('group_id',currentGroupId);
  fd.append('member_id',memberId);
  const res=await fetch('group_actions.php',{method:'POST',body:fd});
  const data=await res.json();
  if(data.success){
    btn.textContent='Added ✓';
    btn.style.background='#4CAF50';
  } else {
    btn.textContent='Add';
    btn.disabled=false;
  }
}

// ── AVATAR FUNCTIONS ──────────────────────────────────────────
async function previewAndUpload(input){
  const file = input.files[0];
  if(!file) return;

  // Show preview immediately
  const reader = new FileReader();
  reader.onload = e => {
    const big = document.getElementById('avatarPreviewBig');
    big.innerHTML = `<img id="avatarPreviewImg" src="${e.target.result}" style="width:100%;height:100%;object-fit:cover">`;
    // Also update topbar avatar
    document.querySelector('.avatar-topbar').innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">`;
  };
  reader.readAsDataURL(file);

  // Upload to server
  const msg = document.getElementById('avatarMsg');
  msg.style.color = '#1565C0';
  msg.textContent = 'Uploading...';

  const fd = new FormData();
  fd.append('avatar', file);
  const res  = await fetch('upload_avatar.php', {method:'POST', body:fd});
  const data = await res.json();

  if(data.success){
    msg.style.color = '#4CAF50';
    msg.textContent = '✓ Photo updated successfully!';
    setTimeout(()=>msg.textContent='', 3000);
  } else {
    msg.style.color = '#e53935';
    msg.textContent = '✕ ' + (data.error || 'Upload failed');
  }
}

async function removeAvatar(){
  if(!confirm('Remove your profile photo?')) return;
  const fd = new FormData();
  fd.append('remove', '1');
  const res  = await fetch('upload_avatar.php', {method:'POST', body:fd});
  const data = await res.json();
  if(data.success){
    // Reset to initials
    const initials = ME.initials;
    document.getElementById('avatarPreviewBig').innerHTML = `<span>${initials}</span>`;
    document.querySelector('.avatar-topbar').innerHTML = initials;
    document.getElementById('avatarMsg').style.color='#4CAF50';
    document.getElementById('avatarMsg').textContent='Photo removed';
    setTimeout(()=>document.getElementById('avatarMsg').textContent='',2000);
  }
}
document.addEventListener('DOMContentLoaded',()=>{
  document.getElementById('searchInput').addEventListener('input',e=>renderSidebar(e.target.value));
  loadSidebar();
});
</script>
</body>
</html>

