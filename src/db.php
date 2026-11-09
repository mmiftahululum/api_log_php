<?php
/**
 * Koneksi database MySQL menggunakan konfigurasi dari config/config.php.
 * Menghasilkan variabel $conn (mysqli).
 */

$config = require dirname(__DIR__) . '/config/config.php';
$db = $config['db'];

$conn = new mysqli($db['host'], $db['user'], $db['pass'], $db['name'], (int) $db['port']);

if ($conn->connect_error) {
    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode([
        'status' => false,
        'message' => 'Database connection failed',
    ]));
}

$conn->set_charset('utf8mb4');
