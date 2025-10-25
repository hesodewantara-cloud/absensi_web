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
    <h2>Manajemen Pengguna</h2>
</div>

<div id="user-list">
    <!-- Daftar pengguna akan dimuat di sini oleh JavaScript -->
    <p>Memuat daftar pengguna...</p>
</div>

<!-- Modal untuk edit pengguna -->
<div id="userModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3 id="modalTitle">Edit Pengguna</h3>
        <form id="userForm">
            <input type="hidden" id="userId" name="id">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" readonly>
            </div>
            <div class="form-group">
                <label for="name">Nama</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit">Simpan</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userList = document.getElementById('user-list');
    const userModal = document.getElementById('userModal');
    const closeModalBtn = userModal.querySelector('.close-btn');
    const userForm = document.getElementById('userForm');

    // Fungsi untuk memuat daftar pengguna
    async function loadUsers() {
        userList.innerHTML = '<p>Memuat...</p>';
        try {
            const response = await fetch('/api/pengguna.php');
            const users = await response.json();

            if (users.length === 0) {
                userList.innerHTML = '<p>Tidak ada pengguna terdaftar.</p>';
                return;
            }

            const table = document.createElement('table');
            table.innerHTML = `
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
            const tbody = table.querySelector('tbody');
            users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.email}</td>
                    <td>${user.name || '-'}</td>
                    <td>${user.role}</td>
                    <td>
                        <button class="edit-btn" data-id="${user.id}">Edit</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
            userList.innerHTML = '';
            userList.appendChild(table);

        } catch (error) {
            userList.innerHTML = '<p>Gagal memuat data pengguna.</p>';
            console.error(error);
        }
    }

    // Tampilkan modal
    function showModal(user) {
        userForm.reset();
        document.getElementById('userId').value = user.id;
        document.getElementById('email').value = user.email;
        document.getElementById('name').value = user.name || '';
        document.getElementById('role').value = user.role;
        userModal.style.display = 'block';
    }

    // Sembunyikan modal
    function hideModal() {
        userModal.style.display = 'none';
    }

    closeModalBtn.addEventListener('click', hideModal);

    // Event listener untuk form submit
    userForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('userId').value;
        const data = {
            id: id,
            name: document.getElementById('name').value,
            role: document.getElementById('role').value
        };

        try {
            const response = await fetch('/api/pengguna.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                hideModal();
                loadUsers();
                alert('Data pengguna berhasil diperbarui!');
            } else {
                alert('Gagal memperbarui data.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan.');
        }
    });

    // Event delegation untuk tombol edit
    userList.addEventListener('click', async function(e) {
        if (e.target.classList.contains('edit-btn')) {
            const id = e.target.dataset.id;
            const response = await fetch('/api/pengguna.php');
            const users = await response.json();
            const user = users.find(u => u.id == id);
            if (user) showModal(user);
        }
    });

    loadUsers();
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
