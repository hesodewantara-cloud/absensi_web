<?php
// handle_login.php
session_start();

// ------- CONFIG: Sesuaikan dengan file get_data.php -------
$supabase_url = 'https://vvttumhvzdfliindaubi.supabase.co'; // ganti
$supabase_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZ2dHR1bWh2emRmbGlpbmRhdWJpIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjAyODk3OTAsImV4cCI6MjA3NTg2NTc5MH0.gAum44Q819Y20xw7oGd1eKwfYBKPnruyIBCiuOWYj1g'; // ganti
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];

// === TAHAP 1: OTENTIKASI (DAPATKAN TOKEN) ===
$auth_endpoint = $supabase_url . '/auth/v1/token?grant_type=password';
$ch_auth = curl_init($auth_endpoint);
curl_setopt($ch_auth, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_auth, CURLOPT_POST, true);
curl_setopt($ch_auth, CURLOPT_POSTFIELDS, json_encode([
    'email' => $email,
    'password' => $password
]));
curl_setopt($ch_auth, CURLOPT_HTTPHEADER, [
    "apikey: $supabase_key",
    "Content-Type: application/json"
]);

$response_auth = curl_exec($ch_auth);
curl_close($ch_auth);
$auth_data = json_decode($response_auth, true);

// Jika login gagal (email/pass salah)
if (!isset($auth_data['access_token'])) {
    header('Location: login.php?error=1');
    exit;
}

$access_token = $auth_data['access_token'];
$user_id = $auth_data['user']['id'];

// === TAHAP 2: OTORISASI (CEK ROLE DARI TABEL 'users') ===
// Kita harus cek ke tabel 'users' untuk memastikan rolenya 'admin'
$role_endpoint = $supabase_url . '/rest/v1/users?select=role&id=eq.' . $user_id;

$ch_role = curl_init($role_endpoint);
curl_setopt($ch_role, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_role, CURLOPT_HTTPHEADER, [
    "apikey: $supabase_key",
    "Authorization: Bearer " . $access_token, // Gunakan token yg baru didapat
    "Accept: application/json"
]);

$response_role = curl_exec($ch_role);
curl_close($ch_role);
$role_data = json_decode($response_role, true);

// Cek apakah data role ditemukan dan rolenya 'admin'
if (isset($role_data[0]['role']) && $role_data[0]['role'] === 'admin') {
    // SUKSES! Simpan data ke session
    $_SESSION['user'] = $auth_data['user'];
    $_SESSION['access_token'] = $access_token;
    $_SESSION['user_role'] = $role_data[0]['role'];

    // Arahkan ke halaman utama
    header('Location: index.php');
    exit;
} else {
    // Jika berhasil login tapi bukan admin (atau user tidak ditemukan di tabel 'users')
    header('Location: login.php?error=1');
    exit;
}

// --- TIDAK ADA KURUNG KAWAL '}' TAMBAHAN DI SINI ---