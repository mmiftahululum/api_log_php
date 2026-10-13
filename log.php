<?php
header("Content-Type: application/json");

require_once "db.php";

// ambil langsung dari POST (form-data / x-www-form-urlencoded)
$api_url = trim($_POST['api_url'] ?? '');
$request = $_POST['request'] ?? '';
$response = $_POST['response'] ?? '';

// insert ke database
$stmt = $conn->prepare("
    INSERT INTO api_logs (api_url, request_body, response_body) 
    VALUES (?, ?, ?)
");

$stmt->bind_param("sss", $api_url, $request, $response);

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