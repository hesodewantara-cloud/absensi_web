<?php
// partials/header.php
session_start();

// Cek jika pengguna tidak login, kecuali di halaman login
$is_login_page = basename($_SERVER['PHP_SELF']) == 'login.php';
if (!isset($_SESSION['user_role']) && !$is_login_page) {
    header('Location: /login.php');
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-t" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Sistem Absensi</title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
  <div class="container">
    <header class="topbar">
      <h1>Sistem Absensi</h1>
      <nav>
        <a href="/index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Dashboard</a>
        <a href="/pages/ruangan.php" class="<?= $current_page == 'ruangan.php' ? 'active' : '' ?>">Ruangan</a>
        <a href="/pages/pengguna.php" class="<?= $current_page == 'pengguna.php' ? 'active' : '' ?>">Pengguna</a>
        <a href="/pages/izin.php" class="<?= $current_page == 'izin.php' ? 'active' : '' ?>">Izin Sakit</a>
        <a href="/pages/absensi.php" class="<?= $current_page == 'absensi.php' ? 'active' : '' ?>">Laporan Absensi</a>
        <a href="/pages/check_in.php" class="button-checkin <?= $current_page == 'check_in.php' ? 'active' : '' ?>">Absen Masuk</a>
      </nav>
      <div class="controls">
        <?php if (isset($_SESSION['user'])): ?>
            <span style="margin-right: 15px;">Halo, <?= htmlspecialchars($_SESSION['user']['email']) ?></span>
            <a href="/logout.php" class="button-logout">Logout</a>
        <?php endif; ?>
      </div>
    </header>
    <main>