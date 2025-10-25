<?php
require_once __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h2>Laporan Absensi</h2>
    <div class="controls">
        <label for="date-filter">Tanggal:</label>
        <input type="date" id="date-filter" value="<?= date('Y-m-d') ?>">
        <button id="export-excel">Export ke Excel</button>
        <button id="print-data">Cetak</button>
    </div>
</div>

<h3>Absensi Tepat Waktu (Sebelum 15:15)</h3>
<div id="attendance-list-ontime" class="table-wrapper">
    <p>Memuat data...</p>
</div>

<h3 style="margin-top: 30px;">Absensi Terlambat (Setelah 15:15)</h3>
<div id="attendance-list-late" class="table-wrapper">
    <p>Memuat data...</p>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    const ontimeList = document.getElementById('attendance-list-ontime');
    const lateList = document.getElementById('attendance-list-late');
    const dateFilter = document.getElementById('date-filter');

    async function loadAttendance() {
        const selectedDate = dateFilter.value;
        ontimeList.innerHTML = '<p>Memuat...</p>';
        lateList.innerHTML = '<p>Memuat...</p>';

        try {
            const response = await fetch(`/api/attendance_list.php?date=${selectedDate}`);
            const attendanceData = await response.json();

            if (attendanceData.error) {
                throw new Error(attendanceData.error);
            }

            const ontimeData = [];
            const lateData = [];
            // Waktu diubah ke UTC untuk perbandingan yang konsisten dengan data Supabase
            const lateThreshold = new Date(`${selectedDate}T15:15:00Z`).getTime();

            attendanceData.forEach(att => {
                const attTime = new Date(att.timestamp).getTime();
                if (attTime > lateThreshold) {
                    lateData.push(att);
                } else {
                    ontimeData.push(att);
                }
            });

            renderTable(ontimeList, ontimeData, false);
            renderTable(lateList, lateData, true);

        } catch (error) {
            ontimeList.innerHTML = `<p>Gagal memuat data: ${error.message}</p>`;
            lateList.innerHTML = `<p>Gagal memuat data: ${error.message}</p>`;
            console.error(error);
        }
    }

    function renderTable(element, data, isLate) {
        if (data.length === 0) {
            element.innerHTML = '<p>Tidak ada data absensi.</p>';
            return;
        }

        const table = document.createElement('table');
        table.innerHTML = `
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th>Foto</th>
                </tr>
            </thead>
            <tbody></tbody>
        `;
        const tbody = table.querySelector('tbody');
        data.forEach(att => {
            const row = document.createElement('tr');
            row.className = isLate ? 'late' : 'ontime';
            const time = new Date(att.timestamp).toLocaleTimeString('id-ID');

            row.innerHTML = `
                <td>${att.name || 'N/A'}</td>
                <td>${att.email || 'N/A'}</td>
                <td>${time}</td>
                <td><span class="status-${isLate ? 'late' : 'hadir'}">${isLate ? 'Telat' : 'Hadir'}</span></td>
                <td>${att.photo_url ? `<img src="${att.photo_url}" alt="foto" width="50">` : 'N/A'}</td>
            `;
            tbody.appendChild(row);
        });

        element.innerHTML = '';
        element.appendChild(table);
    }

    dateFilter.addEventListener('change', loadAttendance);
    loadAttendance();

    const exportBtn = document.getElementById('export-excel');
    const printBtn = document.getElementById('print-data');

    exportBtn.addEventListener('click', function() {
        const selectedDate = dateFilter.value;
        window.location.href = `/export/export_excel.php?date=${selectedDate}`;
    });

    printBtn.addEventListener('click', function() {
        window.print();
    });
});
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .page-header, .page-header *,
    #attendance-list-ontime, #attendance-list-ontime *,
    #attendance-list-late, #attendance-list-late * {
        visibility: visible;
    }
    #attendance-list-ontime, #attendance-list-late {
        position: absolute;
        left: 0;
        top: 0;
    }
    .page-header .controls {
        display: none;
    }
}
</style>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
