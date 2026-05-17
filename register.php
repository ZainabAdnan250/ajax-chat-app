<?php
require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim($conn->real_escape_string($_POST['full_name'] ?? ''));
    $email      = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $phone      = trim($conn->real_escape_string($_POST['phone'] ?? ''));
    $cnic = trim($conn->real_escape_string($_POST['cnic'] ?? ''));
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    if (!$full_name || !$email || !$cnic || !$password) {
        $error = 'Please fill all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $check = $conn->query("SELECT id FROM users WHERE email='$email' OR cnic='$cnic'");
        if ($check->num_rows > 0) {
            $error = 'Email or CNIC already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $conn->query("INSERT INTO users (cnic, full_name, email, phone, password) VALUES ('$cnic','$full_name','$email','$phone','$hashed')");
            $success = 'Registration successful! <a href="login.php">Login now</a>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - EduChat</title>
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
    --green: #4CAF50;
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
    max-width: 480px;
    padding: 40px;
  }
  .logo {
    text-align: center;
    margin-bottom: 30px;
  }
  .logo-icon {
    width: 60px; height: 60px;
    background: var(--primary);
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
  }
  .logo-icon svg { width: 32px; height: 32px; fill: white; }
  .logo h1 { font-size: 24px; font-weight: 800; color: var(--primary); }
  .logo p { font-size: 14px; color: var(--muted); }
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .form-group { margin-bottom: 16px; }
  label { display: block; font-size: 13px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
  input {
    width: 100%;
    padding: 11px 14px;
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
    margin-top: 6px;
  }
  .btn:hover { background: var(--primary-light); transform: translateY(-1px); }
  .alert {
    padding: 11px 14px;
    border-radius: 10px;
    font-size: 13px;
    margin-bottom: 16px;
    font-weight: 600;
  }
  .alert-error { background: #FFEBEE; color: var(--red); border: 1px solid #FFCDD2; }
  .alert-success { background: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9; }
  .alert a { color: var(--primary); }
  .switch-link {
    text-align: center;
    margin-top: 20px;
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
    <h1>EduChat</h1>
    <p>Student Communication Platform</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-row">
      <div class="form-group">
        <label>Full Name *</label>
        <input type="text" name="full_name" placeholder="Zainab Adnan" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>CNIC *</label>
        <input type="text" name="cnic" placeholder="3310026164848" value="<?= htmlspecialchars($_POST['cnic'] ?? '') ?>" required>
      </div>
    </div>
    <div class="form-group">
      <label>Email Address *</label>
      <input type="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label>Phone Number</label>
      <input type="text" name="phone" placeholder="03001234567" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Password *</label>
        <input type="password" name="password" placeholder="Min. 6 characters" required>
      </div>
      <div class="form-group">
        <label>Confirm Password *</label>
        <input type="password" name="confirm_password" placeholder="Repeat password" required>
      </div>
    </div>
    <button type="submit" class="btn">Create Account</button>
  </form>
  <div class="switch-link">Already have an account? <a href="login.php">Login</a></div>
</div>
</body>
</html>
