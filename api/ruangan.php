<?php
// api/ruangan.php
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
    $response = supabase_fetch('/rest/v1/rooms?select=*', 'GET', null, $token);
    echo json_encode($response['data']);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    // Validasi data di sini jika perlu

    $response = supabase_fetch('/rest/v1/rooms', 'POST', $data, $token);
    http_response_code($response['status']);
    echo json_encode($response['data']);
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    unset($data['id']);

    $response = supabase_fetch('/rest/v1/rooms?id=eq.' . $id, 'PATCH', $data, $token); // Supabase menggunakan PATCH untuk update
    http_response_code($response['status']);
    echo json_encode($response['data']);
}

if ($method === 'DELETE') {
    $id = $_GET['id'];
    $response = supabase_fetch('/rest/v1/rooms?id=eq.' . $id, 'DELETE', null, $token);
    http_response_code($response['status']);
    echo json_encode($response['data']);
}
?>