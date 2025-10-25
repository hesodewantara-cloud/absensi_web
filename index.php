<?php
// index.php
require_once __DIR__ . '/partials/header.php';
?>

<div class="page-header">
    <h2>Dashboard</h2>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <h3>Total Pengguna</h3>
        <p id="total-users">Memuat...</p>
        <div class="card-icon">👤</div>
    </div>
    <div class="stat-card">
        <h3>Total Ruangan</h3>
        <p id="total-rooms">Memuat...</p>
        <div class="card-icon">🏢</div>
    </div>
    <div class="stat-card">
        <h3>Absensi Hari Ini</h3>
        <p id="today-attendance">Memuat...</p>
        <div class="card-icon">✅</div>
    </div>
</div>

<div class="chart-container">
    <h3>Tren Absensi Mingguan</h3>
    <canvas id="attendanceChart"></canvas>
</div>


<script>
document.addEventListener("DOMContentLoaded", async () => {
    const totalUsersEl = document.getElementById('total-users');
    const totalRoomsEl = document.getElementById('total-rooms');
    const todayAttendanceEl = document.getElementById('today-attendance');

    async function loadStats() {
        try {
            const response = await fetch('/api/dashboard_stats.php');
            const stats = await response.json();

            if (stats.error) throw new Error(stats.error);

            totalUsersEl.textContent = stats.total_users;
            totalRoomsEl.textContent = stats.total_rooms;
            todayAttendanceEl.textContent = stats.today_attendance;

        } catch (error) {
            console.error("Gagal memuat statistik:", error);
            totalUsersEl.textContent = 'Error';
            totalRoomsEl.textContent = 'Error';
            todayAttendanceEl.textContent = 'Error';
        }
    }

    async function loadChart() {
        try {
            const response = await fetch('/api/chart_data.php');
            const chartData = await response.json();

            if (chartData.error) throw new Error(chartData.error);

            const ctx = document.getElementById('attendanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Jumlah Absensi',
                        data: chartData.data,
                        backgroundColor: 'rgba(52, 152, 219, 0.2)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        } catch (error) {
            console.error("Gagal memuat data grafik:", error);
            const chartContainer = document.querySelector('.chart-container');
            chartContainer.innerHTML = '<p>Gagal memuat grafik.</p>';
        }
    }

    loadStats();
    loadChart();
});
</script>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
