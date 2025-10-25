<?php
session_start();

// Jika sudah login sebagai admin, lempar ke index.php
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: index.php');
    exit;
}

$error = isset($_GET['error']) ? 'Email atau Password salah.' : '';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Login - Rekap Absensi</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* Style sederhana untuk form login */
    body {
      display: flex;
      align-items: center; /* Pusatkan form */
      justify-content: center;
      padding-top: 40px;
      padding-bottom: 40px;
    }
    .login-form {
      width: 100%;
      max-width: 330px;
      padding: 24px;
      border-radius: 12px;
      background: var(--card);
      border: 1px solid var(--border);
    }
    .login-form h1 { margin-top: 0; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; margin-bottom: 4px; font-weight: 500; }
    .form-group input {
      width: 100%;
      padding: 10px 12px;
      border-radius: 6px;
      border: 1px solid var(--border);
      background: var(--bg);
      color: var(--text);
    }
    .form-group button {
      width: 100%;
      padding: 10px 12px;
      border-radius: 6px;
      border: none;
      background: var(--accent);
      color: white;
      font-weight: 600;
      cursor: pointer;
    }
    .error-message {
      color: #e11d48; /* Merah */
      font-size: 0.9rem;
      text-align: center;
      margin-bottom: 12px;
    }
  </style>
</head>
<body>
  <div class="login-form">
    <form method="POST" action="handle_login.php">
      <h1 style="text-align: center;">Admin Login</h1>
      <?php if ($error): ?>
        <p class="error-message"><?php echo $error; ?></p>
      <?php endif; ?>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div class="form-group">
        <button type="submit">Login</button>
      </div>
    </form>
  </div>
</body>
</html>