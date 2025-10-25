<?php
// api/dashboard_stats.php
require_once __DIR__ . '/../utils/supabase.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$token = $_SESSION['access_token'];

// Fungsi untuk mendapatkan hitungan
function get_count($table, $token) {
    // RPC count_estimate lebih cepat untuk tabel besar, tapi SELECT dengan head lebih sederhana
    $response = supabase_fetch("/rest/v1/$table?select=*", 'GET', null, $token, ['Prefer: count=exact']);
    // Header 'Range-Total' akan berisi total baris
    // Ini perlu modifikasi pada fungsi supabase_fetch untuk mengembalikan header
    // Untuk kesederhanaan sekarang, kita hitung saja hasilnya
    return count($response['data']);
}

// Untuk sementara, kita akan gunakan beberapa panggilan GET
// Total Pengguna
$users_res = supabase_fetch('/rest/v1/users?select=id', 'GET', null, $token);

// Total Ruangan
$rooms_res = supabase_fetch('/rest/v1/rooms?select=id', 'GET', null, $token);

// Absensi Hari Ini
$today_start = date('Y-m-d') . 'T00:00:00Z';
$today_end = date('Y-m-d') . 'T23:59:59Z';
$attendance_res = supabase_fetch('/rest/v1/attendance?select=id&timestamp=gte.' . $today_start . '&timestamp=lte.' . $today_end, 'GET', null, $token);

echo json_encode([
    'total_users' => is_array($users_res['data']) ? count($users_res['data']) : 0,
    'total_rooms' => is_array($rooms_res['data']) ? count($rooms_res['data']) : 0,
    'today_attendance' => is_array($attendance_res['data']) ? count($attendance_res['data']) : 0,
]);
?>
