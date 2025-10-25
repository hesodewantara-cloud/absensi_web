<?php
// utils/supabase.php

// Muat kredensial dari file konfigurasi
require_once __DIR__ . '/../config.php';

function supabase_fetch($endpoint, $method = 'GET', $body = null, $token = null, $extra_headers = []) {
    global $supabase_url, $supabase_key;

    $url = $supabase_url . $endpoint;
    $headers = array_merge([
        "apikey: $supabase_key",
        "Content-Type: application/json",
        "Accept: application/json"
    ], $extra_headers);

    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Tambahkan opsi untuk mengambil header respons
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
    } elseif ($method === 'PUT' || $method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $header_str = substr($response, 0, $header_size);
    $body_str = substr($response, $header_size);

    $headers = [];
    $header_rows = explode("\r\n", $header_str);
    foreach ($header_rows as $row) {
        if (strpos($row, ':') !== false) {
            list($key, $value) = explode(':', $row, 2);
            $headers[trim(strtolower($key))] = trim($value);
        }
    }

    return [
        'data' => json_decode($body_str, true),
        'status' => $http_code,
        'headers' => $headers
    ];
}
?>