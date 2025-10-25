<?php
// index.php
session_start(); // Selalu mulai session di awal

// Cek apakah pengguna sudah login dan rolenya 'admin'
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // Jika tidak, tendang ke halaman login
    header('Location: login.php');
    exit;
}

// Jika lolos, halaman akan lanjut di-render
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Rekap Absensi Grid</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <header class="topbar">
      <h1>Rekap Absensi Harian</h1>
      <div class="controls">
        <label for="date">Tanggal</label>
        <input type="date" id="date" />
        <button id="refreshBtn" title="Refresh sekarang">⟳</button>
        <button id="themeToggle" title="Toggle theme">🌙/☀️</button>
        
        <a href="logout.php" title="Logout" style="padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--card); cursor: pointer; text-decoration: none;">Logout</a>
      </div>
    </header>

    <main>
      <div id="status" class="status">Memuat data...</div>
      <div class="grid-wrap" id="gridWrap" aria-live="polite"></div>
    </main>

    <footer>
      <small>Auto-refresh: <span id="autoRefreshInterval">60</span>s — Toggle theme untuk ganti Light/Dark</small>
    </footer>
  </div>

  <script src="script.js"></script>
</body>
</html>