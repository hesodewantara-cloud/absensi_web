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

function get_count($table, $token, $filter = '') {
    $headers = ['Prefer: count=exact'];
    $endpoint = "/rest/v1/$table?select=id" . ($filter ? "&$filter" : '') . '&limit=0';

    $response = supabase_fetch($endpoint, 'GET', null, $token, $headers);

    if (isset($response['headers']['content-range'])) {
        $range = $response['headers']['content-range'];
        // Formatnya adalah "0-0/TOTAL"
        return (int) explode('/', $range)[1];
    }
    return 0;
}

// Total Pengguna
$total_users = get_count('users', $token);

// Total Ruangan
$total_rooms = get_count('rooms', $token);

// Absensi Hari Ini
$today_start = date('Y-m-d') . 'T00:00:00Z';
$today_end = date('Y-m-d') . 'T23:59:59Z';
$attendance_filter = 'timestamp=gte.' . $today_start . '&timestamp=lte.' . $today_end;
$today_attendance = get_count('attendance', $token, $attendance_filter);

echo json_encode([
    'total_users' => $total_users,
    'total_rooms' => $total_rooms,
    'today_attendance' => $today_attendance,
]);
?>
