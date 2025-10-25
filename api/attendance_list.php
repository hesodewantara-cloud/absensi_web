<?php
// api/attendance_list.php
require_once __DIR__ . '/../utils/supabase.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_role'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$token = $_SESSION['access_token'];
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$date_start = $date . 'T00:00:00Z';
$date_end   = $date . 'T23:59:59Z';

$endpoint = '/rest/v1/attendance?select=*,users(name,email),rooms(name)'
    . '&timestamp=gte.' . urlencode($date_start)
    . '&timestamp=lte.' . urlencode($date_end)
    . '&order=timestamp.asc';

$response = supabase_fetch($endpoint, 'GET', null, $token);

if ($response['status'] === 200) {
    echo json_encode($response['data']);
} else {
    http_response_code($response['status']);
    echo json_encode(['error' => 'Gagal mengambil data absensi', 'details' => $response['data']]);
}
?>
