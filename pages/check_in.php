<?php
require_once __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h2>Absen Masuk</h2>
</div>

<div id="check-in-form">
    <div class="form-group">
        <label for="room-select">Pilih Ruangan</label>
        <select id="room-select" disabled>
            <option>Ambil lokasi dulu...</option>
        </select>
    </div>

    <button id="getLocationBtn">Dapatkan Lokasi & Ruangan Terdekat</button>
    <p id="location-status"></p>

    <div id="camera-container" style="display:none; margin-top: 20px;">
        <video id="video" width="300" height="225" autoplay></video>
        <canvas id="canvas" width="300" height="225" style="display:none;"></canvas>
        <button id="snapBtn">Ambil Foto</button>
        <img id="photo-preview" src="" alt="pratinjau foto" style="display:none; max-width: 300px; margin-top: 10px;">
    </div>

    <button id="submitAttendanceBtn" style="display:none; margin-top: 20px;">Kirim Absensi</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const getLocationBtn = document.getElementById('getLocationBtn');
    const locationStatus = document.getElementById('location-status');
    const roomSelect = document.getElementById('room-select');
    const cameraContainer = document.getElementById('camera-container');
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const snapBtn = document.getElementById('snapBtn');
    const photoPreview = document.getElementById('photo-preview');
    const submitAttendanceBtn = document.getElementById('submitAttendanceBtn');
    let photoData = null;

    getLocationBtn.addEventListener('click', function() {
        locationStatus.textContent = 'Mendapatkan lokasi...';
        if (!navigator.geolocation) {
            locationStatus.textContent = 'Geolocation tidak didukung oleh browser Anda.';
            return;
        }
        navigator.geolocation.getCurrentPosition(success, error);
    });

    async function success(position) {
        const latitude  = position.coords.latitude;
        const longitude = position.coords.longitude;
        locationStatus.textContent = `Lokasi Anda: ${latitude}, ${longitude}`;

        // Ambil daftar ruangan dan cari yang terdekat
        try {
            const response = await fetch('/api/ruangan.php');
            const rooms = await response.json();

            let closestRoom = null;
            let minDistance = Infinity;

            rooms.forEach(room => {
                const distance = getDistance(latitude, longitude, room.latitude, room.longitude);
                if (distance < minDistance && distance < room.radius_meters) {
                    minDistance = distance;
                    closestRoom = room;
                }
            });

            if (closestRoom) {
                roomSelect.innerHTML = `<option value="${closestRoom.id}">${closestRoom.name} (Jarak: ${minDistance.toFixed(2)}m)</option>`;
                roomSelect.disabled = false;
                cameraContainer.style.display = 'block';
                startCamera();
            } else {
                roomSelect.innerHTML = '<option>Anda tidak berada dalam jangkauan ruangan manapun.</option>';
                roomSelect.disabled = true;
            }

        } catch (err) {
            locationStatus.textContent = 'Gagal mengambil data ruangan.';
        }
    }

    function error() {
        locationStatus.textContent = 'Tidak dapat mengambil lokasi Anda.';
    }

    // Fungsi untuk menghitung jarak antara dua titik koordinat
    function getDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // meter
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    async function startCamera() {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                video.srcObject = stream;
                video.play();
            } catch (err) {
                console.error("Error accessing camera: ", err);
                cameraContainer.innerHTML = "<p>Tidak dapat mengakses kamera.</p>";
            }
        }
    }

    snapBtn.addEventListener('click', function() {
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, 300, 225);
        photoData = canvas.toDataURL('image/png');
        photoPreview.src = photoData;
        photoPreview.style.display = 'block';
        video.style.display = 'none';
        submitAttendanceBtn.style.display = 'block';
    });

    submitAttendanceBtn.addEventListener('click', async function() {
        if (!photoData || roomSelect.value === '') {
            alert('Harap ambil foto dan pastikan ruangan terpilih.');
            return;
        }

        const formData = new FormData();
        formData.append('room_id', roomSelect.value);
        formData.append('photo', photoData);

        try {
            const response = await fetch('/api/absensi.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            });

            if (response.ok) {
                alert('Absensi berhasil!');
                window.location.href = '/pages/absensi.php';
            } else {
                const errorData = await response.json();
                alert('Gagal mengirim absensi: ' + (errorData.error || 'Unknown error'));
            }
        } catch (err) {
            console.error('Error:', err);
            alert('Terjadi kesalahan.');
        }
    });
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
