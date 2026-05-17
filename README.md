# 💬 ChatApp — Student Communication Platform

A real-time web-based chat application built for students using PHP, MySQL, and JavaScript.
Designed to work on a local server (XAMPP/WAMP).

---

## 🛠️ Technologies Used

| Layer | Technology |
|---|---|
| Backend | PHP |
| Database | MySQL |
| Frontend | HTML, CSS, JavaScript |
| Data Format | JSON |
| Server | Apache (via XAMPP) |
| Query Language | SQL |

---

## 📁 File Structure

```
chatapp/
│
├── index.php               ← Main chat UI (requires login)
├── login.php               ← Login page
├── register.php            ← Registration page
├── logout.php              ← Logout & session destroy
│
├── get_messages.php        ← AJAX: fetch messages + seen status
├── send_message.php        ← AJAX: send a message
├── get_contacts.php        ← AJAX: load sidebar contacts & groups
├── react_message.php       ← AJAX: add/toggle emoji reaction
├── upload_avatar.php       ← AJAX: upload or remove profile photo
├── group_actions.php       ← AJAX: group management (create, members, add, remove)
│
├── database.sql            ← Full database schema + sample data
├── patch.sql               ← Database updates (run after database.sql)
│
├── includes/
│   └── config.php          ← Database connection + helper functions
│
└── uploads/
    └── avatars/            ← Uploaded profile photos stored here
```

---

## ⚙️ Setup Instructions

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache server — XAMPP or WAMP recommended

### Step 1 — Import Database
1. Open `http://localhost/phpmyadmin`
2. Click **Import**
3. Select `database.sql` → Click **Go**
4. Then import `patch.sql` the same way

### Step 2 — Configure Database
Open `includes/config.php` and set your credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');         // your MySQL password
define('DB_NAME', 'chatapp');
```

### Step 3 — Place Files
Copy the `chatapp` folder into your web server root:
- XAMPP → `C:/xampp/htdocs/chatapp/`
- WAMP  → `C:/wamp64/www/chatapp/`

### Step 4 — Open in Browser
```
http://localhost/chatapp/login.php
```

---

## 🗄️ Database Tables

| Table | Purpose |
|---|---|
| `users` | Stores all registered users |
| `private_messages` | One-to-one chat messages |
| `group_messages` | Group chat messages |
| `chat_groups` | Group info (name, description, creator) |
| `group_members` | Which users belong to which group |
| `group_read_status` | Tracks last read message per user per group |

---

## ✨ Features

### 🔐 Authentication
- **Register** with Full Name, CNIC, Email, Phone, Password
- **Login** using Email OR CNIC
- Secure password hashing using PHP `password_hash()`
- Session-based authentication
- Auto redirect if not logged in

### 👤 Profile & Avatar
- Click your avatar in the top bar to open Profile modal
- Upload a profile photo (JPG, PNG, GIF, WEBP — max 2MB)
- Photo shows in top bar, sidebar, and chat header
- Remove photo anytime
- Profile shows Name, Phone, Email, CNIC

### 💬 Private Messaging
- One-to-one chat with any registered user
- Real-time messages via polling every 2.5 seconds
- Only new messages are fetched (efficient)
- **Seen status** — shows `Sent ✓` or `Seen ✓✓` (like Instagram)
- Unread message badge (red number) in sidebar

### 👥 Group Chat
- Send messages in groups
- **Unread badge** on groups showing how many new messages
- **Seen by** — shows names of members who have read your message
- Group messages update in real-time

### 😀 Emoji
- Full emoji picker with 7 categories:
  - 😀 Smileys (100+ emojis)
  - 👍 Gestures
  - ❤️ Hearts
  - 🐱 Animals
  - 🍕 Food
  - ⚽ Activities
  - 💡 Objects
- Click emoji to insert into message

### 🔥 Reactions (WhatsApp Style)
- **Double-click** any message to open reaction popup
- 8 quick reactions: 👍 ❤️ 😂 😮 😢 🙏 🔥 😍
- **One reaction per user** per message (rule enforced)
- Click a different emoji to switch your reaction
- Click same emoji again to remove reaction
- Reactions show **instantly** without page reload
- Reaction count shown on message

### 👥 Group Management
- **Create Group** — click `+ Create Group` button in sidebar
  - Enter group name and description
  - Select members with checkboxes
- **View Members** — click 👥 button in group chat header
  - See all members with online/offline status
  - Admin badge shown for group creator
- **Add Members** — add new users to existing group
- **Remove Members** — group admin (creator) can remove anyone

### 🔍 Search
- Search contacts and groups by name in real-time
- Filters both Groups and Chat sections simultaneously

### 🟢 Online Status
- Green dot shown on contacts who are currently online
- Status updates automatically on login/logout

### 📱 UI Design
- Dark purple top navigation bar
- Collapsible sidebar sections (Groups / Chat)
- Green message bubbles for received messages
- White message bubbles for sent messages
- Date dividers between messages (Monday, Friday, Today)
- Responsive clean layout

---

## 🔄 How Real-Time Works

The app uses **polling** — every 2.5 seconds the browser asks the server for new messages:

```
Browser → "Any new messages?" → Server (PHP) → MySQL
Browser ← "Yes, 2 new messages" ← Server
```

Only messages with `id > last_id` are fetched, keeping it fast and lightweight.

---

## 🔐 Security Features

- Passwords stored as **bcrypt hash** (never plain text)
- All user inputs sanitized with `real_escape_string()`
- Session-based login — unauthorized users redirected
- File upload validation (type + size check)
- SQL queries use integer casting for IDs

---

## 👩‍💻 Sample Login Credentials

| Name | Email | CNIC | Password |
|---|---|---|---|
| Zainab  | zainab@gmail.com | 11122-3344556-7 | password |
| Ayesha | ayesha@gmail.com |12345-6789012-3| password |
| Sajal | sajal@gmail.com |33110-4567890-8 | password |

---

## 🚀 Future Improvements

- WebSockets for true real-time (no polling delay)
- Voice and video call integration
- File/image sharing in chat
- Message delete and edit
- Push notifications
- Mobile responsive design
- Dark mode

---


## 👩‍🎓 Developed By

**Zainab Adnan**
Student ID: 11122-3344556-7
EduChat — Student Communication Platform


## 📄 License
This project is licensed under the MIT License.
