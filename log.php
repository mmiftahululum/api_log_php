<?php
header("Content-Type: application/json");

require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

// Wajib header `mifpro-token` yang valid
$account = require_account($conn);

// ambil langsung dari POST (form-data / x-www-form-urlencoded)
$api_url = trim($_POST['api_url'] ?? '');
$request = $_POST['request'] ?? '';
$response = $_POST['response'] ?? '';

if ($api_url === '') {
    json_fail(400, 'Parameter api_url wajib diisi');
}

// insert ke database, terikat ke akun pemilik token
$stmt = $conn->prepare("
    INSERT INTO api_logs (account_id, api_url, request_body, response_body) 
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param("isss", $account['id'], $api_url, $request, $response);

if ($stmt->execute()) {
    echo json_encode(["status" => true]);
} else {
    echo json_encode([
        "status" => false,
        "error" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
