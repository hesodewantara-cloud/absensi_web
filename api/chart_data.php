<?php
// api/chart_data.php
require_once __DIR__ . '/../utils/supabase.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$token = $_SESSION['access_token'];
$labels = [];
$data_points = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('D, M j', strtotime($date));

    $start_of_day = $date . 'T00:00:00Z';
    $end_of_day = $date . 'T23:59:59Z';

    // Kita gunakan SELECT untuk menghitung jumlah, ini kurang efisien tapi bekerja tanpa RLS count
    $endpoint = "/rest/v1/attendance?select=id&timestamp=gte.{$start_of_day}&timestamp=lte.{$end_of_day}";
    $response = supabase_fetch($endpoint, 'GET', null, $token);

    $count = is_array($response['data']) ? count($response['data']) : 0;
    $data_points[] = $count;
}

echo json_encode([
    'labels' => $labels,
    'data' => $data_points,
]);
?>
