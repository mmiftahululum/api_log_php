<?php
header("Content-Type: application/json");

require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

// Wajib header `mifpro-token` yang valid
$account = require_account($conn);
$accountId = (int) $account['id'];

$q = $_GET['q'] ?? '';
$q = $conn->real_escape_string($q);

// 🔥 pakai LOWER + TRIM + GROUP BY, dibatasi ke akun pemilik token
$query = "
    SELECT LOWER(TRIM(api_url)) as api_url
    FROM api_logs
    WHERE account_id = $accountId
      AND LOWER(api_url) LIKE LOWER('%$q%')
    GROUP BY LOWER(TRIM(api_url))
    ORDER BY api_url ASC
    LIMIT 10
";

$result = $conn->query($query);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row['api_url'];
    }
}

echo json_encode($data);
$conn->close();
