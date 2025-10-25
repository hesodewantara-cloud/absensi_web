<?php
// get_data.php
session_start(); // Mulai session
header('Content-Type: application/json; charset=utf-8');

// === PERLINDUNGAN API ===
// Cek apakah pengguna login sebagai admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin' || !isset($_SESSION['access_token'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Akses ditolak. Anda harus login sebagai admin.']);
    exit;
}

// Ambil token admin dari session
$admin_token = $_SESSION['access_token'];

// ------- CONFIG: isi dengan project Supabase mu -------
$supabase_url = 'https://vvttumhvzdfliindaubi.supabase.co'; // ganti
$supabase_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZ2dHR1bWh2emRmbGlpbmRhdWJpIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjAyODk3OTAsImV4cCI6MjA3NTg2NTc5MH0.gAum44Q819Y20xw7oGd1eKwfYBKPnruyIBCiuOWYj1g'; // ganti (biarkan ini tetap kunci anon)
// -----------------------------------------------------

// Ambil tanggal dari parameter GET, jika tidak ada pakai hari ini
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Definisikan slot waktu
$timeSlots = [
    "07:00:00", "07:40:00", "08:20:00", "09:00:00", "09:55:00",
    "10:35:00", "11:15:00", "12:25:00", "13:05:00", "13:45:00",
    "14:25:00", "15:15:00"
];
$tolerance_seconds = 600; // Toleransi 10 menit (600 detik)

// Tentukan rentang waktu UTC untuk query
$date_start = $date . 'T00:00:00Z';
$date_end   = $date . 'T23:59:59Z';

// Buat URL endpoint untuk Supabase
$endpoint = $supabase_url . '/rest/v1/attendance'
    . '?select=id,user_id,room_id,timestamp,status,users(name,email),rooms(name)' // Minta data users dan rooms
    . '&timestamp=gte.' . urlencode($date_start) // Lebih besar atau sama dengan awal hari
    . '&timestamp=lte.' . urlencode($date_end)   // Lebih kecil atau sama dengan akhir hari
    . '&order=timestamp.asc';                   // Urutkan berdasarkan waktu

// === MODIFIKASI CURL ===
// Gunakan curl untuk memanggil Supabase MENGGUNAKAN TOKEN ADMIN
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $supabase_key", // Kunci anon tetap diperlukan
    "Authorization: Bearer $admin_token", // INI YANG PENTING! Gunakan token admin
    "Accept: application/json"
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to contact Supabase: ' . curl_error($ch)]);
    exit;
}
curl_close($ch);

// Decode data JSON
$data = json_decode($response, true);

// Jika data bukan array (kemungkinan error dari Supabase)
if (!is_array($data)) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid response from Supabase', 'http_code' => $httpcode, 'raw' => $response]);
    exit;
}

// Siapkan data untuk grid
$rooms = [];
$grid = [];

foreach ($data as $row) {
    // Ambil nama ruangan dan pengguna
    // --- INI ADALAH PERBAIKANNYA ---
    $roomName = isset($row['rooms']['name']) ? $row['rooms']['name'] : ('Room-' . ($row['room_id'] ?? 'unknown'));
    // -----------------------------
    $userName = isset($row['users']['name']) ? $row['users']['name'] : null;
    $userEmail = isset($row['users']['email']) ? $row['users']['email'] : null;
    $timestamp = isset($row['timestamp']) ? $row['timestamp'] : null;

    // Kumpulkan semua ruangan unik
    if (!in_array($roomName, $rooms)) $rooms[] = $roomName;
    
    // Inisialisasi grid untuk ruangan jika belum ada
    if (!isset($grid[$roomName])) {
        $grid[$roomName] = [];
        foreach ($timeSlots as $slot) $grid[$roomName][$slot] = [];
    }

    if ($timestamp) {
        $ts = strtotime($timestamp); // Waktu absensi (sudah UTC)
        $timeStr = gmdate('H:i:s', $ts); // Format H:i:s dari timestamp UTC
        $matchedSlot = null;

        // 1. Coba cari slot yang sama persis
        foreach ($timeSlots as $slot) {
            if ($timeStr === $slot) {
                $matchedSlot = $slot;
                break;
            }
        }

        // 2. Jika tidak ada, cari slot terdekat dalam toleransi
        if ($matchedSlot === null) {
            $bestDiff = 999999;
            $bestSlot = null;
            foreach ($timeSlots as $slot) {
                // Bandingkan waktu absensi (UTC) dengan slot waktu (dianggap UTC)
                $slotSec = strtotime($date . ' ' . $slot . ' UTC');
                $diff = abs($ts - $slotSec);
                
                if ($diff < $bestDiff) {
                    $bestDiff = $diff;
                    $bestSlot = $slot;
                }
            }
            // Jika slot terdekat masih dalam toleransi
            if ($bestDiff <= $tolerance_seconds) {
                $matchedSlot = $bestSlot;
            }
        }

        // Jika slot ditemukan, masukkan data ke grid
        if ($matchedSlot !== null) {
            $grid[$roomName][$matchedSlot][] = [
                'name' => $userName,
                'email' => $userEmail,
                'timestamp' => $timestamp,
                'status' => $row['status'] ?? null
            ];
        }
    }
}

// Siapkan data final untuk dikirim sebagai JSON
$response_payload = [
    'date' => $date,
    'timeSlots' => $timeSlots,
    'rooms' => $rooms,
    'grid' => $grid,
    'raw_count' => count($data)
];

// Kirim respons JSON
echo json_encode($response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// --- TIDAK ADA KARAKTER APAPUN SETELAH INI ---