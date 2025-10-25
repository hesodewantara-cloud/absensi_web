<?php
// export/export_excel.php
require_once __DIR__ . '/../utils/supabase.php';
session_start();

// Validasi admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Akses ditolak.");
}

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$token = $_SESSION['access_token'];

// Ambil semua data absensi untuk tanggal yang dipilih
$date_start = $date . 'T00:00:00Z';
$date_end   = $date . 'T23:59:59Z';
$endpoint = '/rest/v1/attendance?select=*,users(name,email),rooms(name)&timestamp=gte.' . $date_start . '&timestamp=lte.' . $date_end;
$response = supabase_fetch($endpoint, 'GET', null, $token);
$attendance_data = $response['data'];

// Set header untuk download file Excel (CSV)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=absensi-' . $date . '.csv');

$output = fopen('php://output', 'w');

// Header kolom CSV
fputcsv($output, ['Nama', 'Email', 'Ruangan', 'Waktu', 'Status']);

// Threshold keterlambatan
$late_threshold = strtotime($date . ' 15:15:00');

foreach ($attendance_data as $row) {
    $timestamp = strtotime($row['timestamp']);
    $status = ($timestamp > $late_threshold) ? 'Telat' : 'Hadir';

    $csv_row = [
        $row['users']['name'] ?? '',
        $row['users']['email'] ?? '',
        $row['rooms']['name'] ?? '',
        date('Y-m-d H:i:s', $timestamp),
        $status
    ];
    fputcsv($output, $csv_row);
}

fclose($output);
exit;
?>