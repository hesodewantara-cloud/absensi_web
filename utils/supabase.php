<?php
// utils/supabase.php

// Muat kredensial dari file konfigurasi
require_once __DIR__ . '/../config.php';

function supabase_fetch($endpoint, $method = 'GET', $body = null, $token = null) {
    global $supabase_url, $supabase_key;

    $url = $supabase_url . $endpoint;
    $headers = [
        "apikey: $supabase_key",
        "Content-Type: application/json",
        "Accept: application/json"
    ];

    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'data' => json_decode($response, true),
        'status' => $http_code
    ];
}
?>