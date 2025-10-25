<?php
require_once __DIR__ . '/../partials/header.php';

$is_admin = $_SESSION['user_role'] === 'admin';
?>

<div class="page-header">
    <h2>Manajemen Izin Sakit</h2>
    <?php if (!$is_admin): ?>
        <button id="addLeaveBtn">Ajukan Izin</button>
    <?php endif; ?>
</div>

<div id="leave-list">
    <!-- Daftar izin akan dimuat di sini oleh JavaScript -->
    <p>Memuat daftar izin...</p>
</div>

<!-- Modal untuk tambah/edit izin -->
<div id="leaveModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3 id="modalTitle">Ajukan Izin Sakit</h3>
        <form id="leaveForm" enctype="multipart/form-data">
            <input type="hidden" id="leaveId" name="id">
            <div class="form-group">
                <label for="reason">Alasan</label>
                <textarea id="reason" name="reason" required></textarea>
            </div>
            <div class="form-group">
                <label for="start_date">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="end_date">Tanggal Selesai</label>
                <input type="date" id="end_date" name="end_date" required>
            </div>
            <div class="form-group">
                <label for="attachment">Bukti (Gambar)</label>
                <input type="file" id="attachment" name="attachment" accept="image/*">
            </div>

            <?php if ($is_admin): ?>
            <hr>
            <h4>Admin Panel</h4>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="Menunggu">Menunggu</option>
                    <option value="Disetujui">Disetujui</option>
                    <option value="Ditolak">Ditolak</option>
                </select>
            </div>
            <div class="form-group">
                <label for="admin_notes">Catatan Admin</label>
                <textarea id="admin_notes" name="admin_notes"></textarea>
            </div>
            <?php endif; ?>

            <button type="submit">Kirim</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const leaveList = document.getElementById('leave-list');
    const addLeaveBtn = document.getElementById('addLeaveBtn');
    const leaveModal = document.getElementById('leaveModal');
    const closeModalBtn = leaveModal.querySelector('.close-btn');
    const leaveForm = document.getElementById('leaveForm');
    const isAdmin = <?= json_encode($is_admin) ?>;

    async function loadLeaves() {
        leaveList.innerHTML = '<p>Memuat...</p>';
        try {
            const response = await fetch('/api/izin.php');
            const leaves = await response.json();

            if (!leaves || leaves.length === 0) {
                leaveList.innerHTML = '<p>Belum ada data izin.</p>';
                return;
            }

            const table = document.createElement('table');
            let headers = '<th>Alasan</th><th>Tanggal</th><th>Status</th><th>Bukti</th>';
            if (isAdmin) headers = '<th>Pengguna</th>' + headers + '<th>Catatan Admin</th><th>Aksi</th>';

            table.innerHTML = `<thead><tr>${headers}</tr></thead><tbody></tbody>`;
            const tbody = table.querySelector('tbody');

            leaves.forEach(leave => {
                const row = document.createElement('tr');
                let rowData = `
                    <td>${leave.reason}</td>
                    <td>${leave.start_date} - ${leave.end_date}</td>
                    <td><span class="status-${leave.status.toLowerCase()}">${leave.status}</span></td>
                    <td>${leave.attachment_url ? `<a href="${leave.attachment_url}" target="_blank">Lihat</a>` : '-'}</td>
                `;
                if (isAdmin) {
                    rowData = `<td>${leave.users.name || leave.users.email}</td>` + rowData + `
                    <td>${leave.admin_notes || '-'}</td>
                    <td><button class="edit-btn" data-id="${leave.id}">Review</button></td>`;
                }
                row.innerHTML = rowData;
                tbody.appendChild(row);
            });
            leaveList.innerHTML = '';
            leaveList.appendChild(table);

        } catch (error) {
            leaveList.innerHTML = '<p>Gagal memuat data izin.</p>';
            console.error(error);
        }
    }

    function showModal(title, leave = {}) {
        leaveForm.reset();
        document.getElementById('leaveId').value = leave.id || '';
        // Non-admin fields
        document.getElementById('reason').value = leave.reason || '';
        document.getElementById('start_date').value = leave.start_date || '';
        document.getElementById('end_date').value = leave.end_date || '';

        // Admin-specific fields
        if (isAdmin) {
            document.getElementById('status').value = leave.status || 'Menunggu';
            document.getElementById('admin_notes').value = leave.admin_notes || '';

            // Disable form for admin review unless needed
            document.getElementById('reason').readOnly = true;
            document.getElementById('start_date').readOnly = true;
            document.getElementById('end_date').readOnly = true;
            document.getElementById('attachment').disabled = true;

        } else {
             document.getElementById('reason').readOnly = false;
            document.getElementById('start_date').readOnly = false;
            document.getElementById('end_date').readOnly = false;
            document.getElementById('attachment').disabled = false;
        }

        leaveModal.style.display = 'block';
    }

    function hideModal() {
        leaveModal.style.display = 'none';
    }

    if(addLeaveBtn) addLeaveBtn.addEventListener('click', () => showModal('Ajukan Izin Sakit'));
    closeModalBtn.addEventListener('click', hideModal);

    leaveForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('leaveId').value;

        if (isAdmin) { // Admin is updating status
            const data = {
                id: id,
                status: document.getElementById('status').value,
                admin_notes: document.getElementById('admin_notes').value
            };
            try {
                const response = await fetch('/api/izin.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                if (response.ok) {
                    hideModal();
                    loadLeaves();
                    alert('Status izin berhasil diperbarui.');
                } else {
                    alert('Gagal memperbarui status.');
                }
            } catch (error) {
                console.error(error);
            }

        } else { // User is submitting a new leave
            const formData = new FormData(this);
            try {
                const response = await fetch('/api/izin.php', {
                    method: 'POST',
                    body: formData
                });
                if (response.ok) {
                    hideModal();
                    loadLeaves();
                    alert('Izin berhasil diajukan.');
                } else {
                    const errorData = await response.json();
                    alert('Gagal mengajukan izin: ' + (errorData.error || 'Unknown error'));
                }
            } catch (error) {
                console.error(error);
            }
        }
    });

    leaveList.addEventListener('click', async function(e) {
        if (e.target.classList.contains('edit-btn') && isAdmin) {
            const id = e.target.dataset.id;
            const response = await fetch('/api/izin.php');
            const leaves = await response.json();
            const leave = leaves.find(l => l.id == id);
            if (leave) showModal('Review Izin', leave);
        }
    });

    loadLeaves();
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
