<?php
// api/izin.php
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
$is_admin = $_SESSION['user_role'] === 'admin';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $query = '/rest/v1/sick_leaves?select=*,users(name,email)';
    if (!$is_admin) {
        $query .= '&user_id=eq.' . $user_id;
    }
    $response = supabase_fetch($query, 'GET', null, $token);
    echo json_encode($response['data']);
}

if ($method === 'POST') {
    $user_id_to_insert = $is_admin && isset($_POST['user_id']) ? $_POST['user_id'] : $user_id;

    $data = [
        'user_id' => $user_id_to_insert,
        'reason' => $_POST['reason'],
        'start_date' => $_POST['start_date'],
        'end_date' => $_POST['end_date'],
        'status' => 'Menunggu' // Default status
    ];

    // Handle file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $file = $_FILES['attachment'];
        $file_name = uniqid() . '-' . basename($file['name']);
        $bucket = 'attachments'; // Ganti dengan nama bucket Anda
        $upload_path = $bucket . '/' . $file_name;

        // Dapatkan URL untuk unggah dari Supabase Storage
        $storage_endpoint = $supabase_url . '/storage/v1/object/' . $upload_path;

        // Gunakan cURL untuk mengunggah file
        $ch_upload = curl_init($storage_endpoint);
        curl_setopt($ch_upload, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_upload, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch_upload, CURLOPT_POSTFIELDS, file_get_contents($file['tmp_name']));
        curl_setopt($ch_upload, CURLOPT_HTTPHEADER, [
            "apikey: $supabase_key",
            "Authorization: Bearer $token",
            "Content-Type: " . mime_content_type($file['tmp_name'])
        ]);
        $upload_response = curl_exec($ch_upload);
        $http_code = curl_getinfo($ch_upload, CURLINFO_HTTP_CODE);
        curl_close($ch_upload);

        if ($http_code == 200) {
            $public_url_res = supabase_fetch('/storage/v1/object/public/' . $upload_path, 'GET', null, $token);
            if(isset($public_url_res['data']['publicURL'])) {
                 $data['attachment_url'] = $public_url_res['data']['publicURL'];
            } else {
                 $data['attachment_url'] = $storage_endpoint; // Fallback
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal mengunggah file.', 'details' => json_decode($upload_response)]);
            exit;
        }
    }

    $response = supabase_fetch('/rest/v1/sick_leaves', 'POST', $data, $token);
    http_response_code($response['status']);
    echo json_encode($response['data']);
}

if ($method === 'PUT' && $is_admin) {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = $input['id'];

    $update_data = [
        'status' => $input['status'],
        'admin_notes' => $input['admin_notes']
    ];

    $response = supabase_fetch('/rest/v1/sick_leaves?id=eq.' . $id, 'PATCH', $update_data, $token);
    http_response_code($response['status']);
    echo json_encode($response['data']);
}
?>