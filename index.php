<?php
// index.php
require_once __DIR__ . '/partials/header.php';
?>

<h2>Dashboard Utama</h2>
<p>Selamat datang di sistem absensi. Silakan pilih menu di atas untuk mengelola data.</p>

<div class="dashboard-widgets">
    <div class="widget">
        <h3>Total Pengguna</h3>
        <p id="total-users">Memuat...</p>
    </div>
    <div class="widget">
        <h3>Total Ruangan</h3>
        <p id="total-rooms">Memuat...</p>
    </div>
    <div class="widget">
        <h3>Absensi Hari Ini</h3>
        <p id="today-attendance">Memuat...</p>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", async () => {
    const totalUsersEl = document.getElementById('total-users');
    const totalRoomsEl = document.getElementById('total-rooms');
    const todayAttendanceEl = document.getElementById('today-attendance');

    try {
        const response = await fetch('/api/dashboard_stats.php');
        const stats = await response.json();

        if (stats.error) {
            throw new Error(stats.error);
        }

        totalUsersEl.textContent = stats.total_users;
        totalRoomsEl.textContent = stats.total_rooms;
        todayAttendanceEl.textContent = stats.today_attendance;

    } catch (error) {
        console.error("Gagal memuat statistik:", error);
        totalUsersEl.textContent = 'Error';
        totalRoomsEl.textContent = 'Error';
        todayAttendanceEl.textContent = 'Error';
    }
});
</script>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
