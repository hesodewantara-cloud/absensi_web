<?php
require_once __DIR__ . '/../partials/header.php';

// Hanya admin yang bisa mengakses halaman ini
if ($_SESSION['user_role'] !== 'admin') {
    echo "<p>Akses ditolak. Anda bukan admin.</p>";
    require_once __DIR__ . '/../partials/footer.php';
    exit;
}
?>

<div class="page-header">
    <h2>Manajemen Ruangan</h2>
    <button id="addRoomBtn">Tambah Ruangan</button>
</div>

<div id="room-list" class="table-wrapper">
    <!-- Daftar ruangan akan dimuat di sini oleh JavaScript -->
    <p>Memuat daftar ruangan...</p>
</div>

<!-- Modal untuk tambah/edit ruangan -->
<div id="roomModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3 id="modalTitle">Tambah Ruangan</h3>
        <form id="roomForm">
            <input type="hidden" id="roomId" name="id">
            <div class="form-group">
                <label for="name">Nama Ruangan</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description"></textarea>
            </div>
            <div class="form-group">
                <label for="latitude">Latitude</label>
                <input type="text" id="latitude" name="latitude" required>
            </div>
            <div class="form-group">
                <label for="longitude">Longitude</label>
                <input type="text" id="longitude" name="longitude" required>
            </div>
            <div class="form-group">
                <label for="radius_meters">Radius (meter)</label>
                <input type="number" id="radius_meters" name="radius_meters" required>
            </div>
            <button type="submit">Simpan</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomList = document.getElementById('room-list');
    const addRoomBtn = document.getElementById('addRoomBtn');
    const roomModal = document.getElementById('roomModal');
    const closeModalBtn = document.querySelector('.close-btn');
    const roomForm = document.getElementById('roomForm');
    const modalTitle = document.getElementById('modalTitle');

    // Fungsi untuk memuat daftar ruangan
    async function loadRooms() {
        roomList.innerHTML = '<p>Memuat...</p>';
        try {
            const response = await fetch('/api/ruangan.php');
            const rooms = await response.json();

            if (rooms.length === 0) {
                roomList.innerHTML = '<p>Belum ada ruangan yang ditambahkan.</p>';
                return;
            }

            const table = document.createElement('table');
            table.innerHTML = `
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Koordinat</th>
                        <th>Radius</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
            const tbody = table.querySelector('tbody');
            rooms.forEach(room => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${room.name}</td>
                    <td>${room.description || '-'}</td>
                    <td>${room.latitude}, ${room.longitude}</td>
                    <td>${room.radius_meters}m</td>
                    <td>
                        <button class="edit-btn" data-id="${room.id}">Edit</button>
                        <button class="delete-btn" data-id="${room.id}">Hapus</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
            roomList.innerHTML = '';
            roomList.appendChild(table);

        } catch (error) {
            roomList.innerHTML = '<p>Gagal memuat data ruangan.</p>';
            console.error(error);
        }
    }

    // Tampilkan modal
    function showModal(title, room = {}) {
        modalTitle.textContent = title;
        roomForm.reset();
        document.getElementById('roomId').value = room.id || '';
        document.getElementById('name').value = room.name || '';
        document.getElementById('description').value = room.description || '';
        document.getElementById('latitude').value = room.latitude || '';
        document.getElementById('longitude').value = room.longitude || '';
        document.getElementById('radius_meters').value = room.radius_meters || '';
        roomModal.style.display = 'block';
    }

    // Sembunyikan modal
    function hideModal() {
        roomModal.style.display = 'none';
    }

    addRoomBtn.addEventListener('click', () => showModal('Tambah Ruangan'));
    closeModalBtn.addEventListener('click', hideModal);

    // Event listener untuk form submit
    roomForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('roomId').value;
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        const url = id ? `/api/ruangan.php` : '/api/ruangan.php';
        const method = id ? 'PUT' : 'POST';

        if(id) data.id = id;

        try {
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                hideModal();
                loadRooms();
                alert('Data ruangan berhasil disimpan!');
            } else {
                alert('Gagal menyimpan data.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan.');
        }
    });

    // Event delegation untuk tombol edit dan hapus
    roomList.addEventListener('click', async function(e) {
        if (e.target.classList.contains('edit-btn')) {
            const id = e.target.dataset.id;
            // Ambil data ruangan untuk di-edit
            const response = await fetch('/api/ruangan.php');
            const rooms = await response.json();
            const room = rooms.find(r => r.id == id);
            if(room) showModal('Edit Ruangan', room);

        } else if (e.target.classList.contains('delete-btn')) {
            const id = e.target.dataset.id;
            if (confirm('Apakah Anda yakin ingin menghapus ruangan ini?')) {
                try {
                    const response = await fetch(`/api/ruangan.php?id=${id}`, {
                        method: 'DELETE'
                    });
                    if (response.ok) {
                        loadRooms();
                        alert('Ruangan berhasil dihapus.');
                    } else {
                        alert('Gagal menghapus ruangan.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan.');
                }
            }
        }
    });

    // Muat data saat halaman dibuka
    loadRooms();
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
