<?php
require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($conn->real_escape_string($_POST['login'] ?? ''));
    $password = $_POST['password'] ?? '';

    $res = $conn->query("SELECT * FROM users WHERE email='$login' OR cnic='$login' LIMIT 1");
    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            updateOnlineStatus($conn, $user['id'], 1);
            header('Location: index.php');
            exit;
        } else {
            $error = 'Incorrect password.';
        }
    } else {
        $error = 'No account found with that email or CNIC.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - ChatApp</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --primary: #1565C0;
    --primary-light: #1976D2;
    --accent: #2196F3;
    --bg: #F0F4F8;
    --white: #FFFFFF;
    --text: #1a1a2e;
    --muted: #6B7280;
    --border: #E5E7EB;
    --red: #e53935;
  }
  body {
    font-family: 'Nunito', sans-serif;
    background: var(--bg);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }
  .auth-container {
    background: var(--white);
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(21,101,192,0.12);
    width: 100%;
    max-width: 420px;
    padding: 44px 40px;
  }
  .logo { text-align: center; margin-bottom: 32px; }
  .logo-icon {
    width: 64px; height: 64px;
    background: var(--primary);
    border-radius: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    box-shadow: 0 4px 20px rgba(21,101,192,0.3);
  }
  .logo-icon svg { width: 34px; height: 34px; fill: white; }
  .logo h1 { font-size: 26px; font-weight: 800; color: var(--primary); }
  .logo p { font-size: 14px; color: var(--muted); margin-top: 4px; }
  .form-group { margin-bottom: 18px; }
  label { display: block; font-size: 13px; font-weight: 700; color: var(--text); margin-bottom: 7px; }
  input {
    width: 100%;
    padding: 12px 14px;
    border: 2px solid var(--border);
    border-radius: 10px;
    font-family: inherit;
    font-size: 14px;
    color: var(--text);
    transition: border-color 0.2s;
    outline: none;
  }
  input:focus { border-color: var(--accent); }
  .btn {
    width: 100%;
    padding: 13px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 10px;
    font-family: inherit;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
    margin-top: 4px;
  }
  .btn:hover { background: var(--primary-light); transform: translateY(-1px); }
  .alert {
    padding: 11px 14px;
    border-radius: 10px;
    font-size: 13px;
    margin-bottom: 18px;
    font-weight: 600;
    background: #FFEBEE;
    color: var(--red);
    border: 1px solid #FFCDD2;
  }
  .switch-link {
    text-align: center;
    margin-top: 22px;
    font-size: 14px;
    color: var(--muted);
  }
  .switch-link a { color: var(--primary); font-weight: 700; text-decoration: none; }

</style>
</head>
<body>
<div class="auth-container">
  <div class="logo">
    <div class="logo-icon">
      <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
    </div>
    <h1>ChatApp</h1>
    <p>Welcome back! Sign in to continue.</p>
  </div>


  <?php if ($error): ?>
    <div class="alert"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>Email or CNIC</label>
      <input type="text" name="login" placeholder="zainab@example.com " value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required autofocus>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="Enter your password" required>
    </div>
    <button type="submit" class="btn">Sign In</button>
  </form>
  <div class="switch-link">New student? <a href="register.php">Create an account</a></div>
</div>
</body>
</html>
