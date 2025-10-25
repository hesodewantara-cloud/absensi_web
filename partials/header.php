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
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Sistem Absensi</title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
  <div class="container">
    <header class="main-header">
        <div class="logo">
            <a href="/index.php">Absensi<b>Sistem</b></a>
        </div>
        <nav class="main-nav">
            <a href="/index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Dashboard</a>
            <a href="/pages/ruangan.php" class="<?= $current_page == 'ruangan.php' ? 'active' : '' ?>">Ruangan</a>
            <a href="/pages/pengguna.php" class="<?= $current_page == 'pengguna.php' ? 'active' : '' ?>">Pengguna</a>
            <a href="/pages/izin.php" class="<?= $current_page == 'izin.php' ? 'active' : '' ?>">Izin</a>
            <a href="/pages/absensi.php" class="<?= $current_page == 'absensi.php' ? 'active' : '' ?>">Laporan</a>
            <a href="/pages/check_in.php" class="nav-button-checkin <?= $current_page == 'check_in.php' ? 'active' : '' ?>">Absen Masuk</a>
        </nav>
        <div class="header-controls">
            <button id="themeToggle" title="Toggle theme">🌙</button>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="/logout.php" class="button-logout">Logout</a>
            <?php endif; ?>
        </div>
        <button id="mobile-menu-toggle">☰</button>
    </header>
    <main class="content-wrapper">