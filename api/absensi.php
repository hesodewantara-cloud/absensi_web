<?php
// api/absensi.php
require_once __DIR__ . '/../utils/supabase.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_role'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$token = $_SESSION['access_token'];
$user_id = $_SESSION['user']['id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $room_id = $_POST['room_id'];
    $photo_data = $_POST['photo']; // Base64 encoded image

    // Decode base64 string to image
    list($type, $photo_data) = explode(';', $photo_data);
    list(, $photo_data)      = explode(',', $photo_data);
    $photo_data = base64_decode($photo_data);

    // Handle file upload to Supabase Storage
    $file_name = 'attendance/' . uniqid() . '.png';
    $storage_endpoint = $supabase_url . '/storage/v1/object/' . $file_name;

    $ch_upload = curl_init($storage_endpoint);
    curl_setopt($ch_upload, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_upload, CURLOPT_POST, true);
    curl_setopt($ch_upload, CURLOPT_POSTFIELDS, $photo_data);
    curl_setopt($ch_upload, CURLOPT_HTTPHEADER, [
        "apikey: $supabase_key",
        "Authorization: Bearer $token",
        "Content-Type: image/png"
    ]);

    $upload_response = curl_exec($ch_upload);
    $http_code = curl_getinfo($ch_upload, CURLINFO_HTTP_CODE);
    curl_close($ch_upload);

    if ($http_code !== 200) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengunggah foto.', 'details' => json_decode($upload_response)]);
        exit;
    }

    // Dapatkan URL publik dari foto yang diunggah
    $public_url_res = supabase_fetch('/storage/v1/object/public/' . $file_name, 'GET', null, $token);
    $photo_url = $public_url_res['data']['publicURL'] ?? $storage_endpoint;

    // Masukkan data absensi ke database
    $attendance_data = [
        'user_id' => $user_id,
        'room_id' => $room_id,
        'photo_url' => $photo_url,
        'status' => 'Hadir' // Status default
    ];

    $response = supabase_fetch('/rest/v1/attendance', 'POST', $attendance_data, $token);
    http_response_code($response['status']);
    echo json_encode($response['data']);
}
?>
