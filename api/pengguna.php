<?php
// api/pengguna.php
require_once __DIR__ . '/../utils/supabase.php';
session_start();
header('Content-Type: application/json');

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$token = $_SESSION['access_token'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Ambil semua pengguna dari tabel 'users'
    $response = supabase_fetch('/rest/v1/users?select=*', 'GET', null, $token);
    echo json_encode($response['data']);
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id']; // Ini adalah UUID dari tabel 'users'

    // Data yang boleh diubah
    $update_data = [
        'name' => $data['name'],
        'role' => $data['role']
    ];

    $response = supabase_fetch('/rest/v1/users?id=eq.' . $id, 'PATCH', $update_data, $token);
    http_response_code($response['status']);
    echo json_encode($response['data']);
}
?>
