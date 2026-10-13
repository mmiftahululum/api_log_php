<?php
header("Content-Type: application/json");

require_once "db.php";

$q = $_GET['q'] ?? '';
$q = $conn->real_escape_string($q);

// 🔥 pakai LOWER + TRIM + GROUP BY
$query = "
    SELECT LOWER(TRIM(api_url)) as api_url
    FROM api_logs
    WHERE LOWER(api_url) LIKE LOWER('%$q%')
    GROUP BY LOWER(TRIM(api_url))
    ORDER BY api_url ASC
    LIMIT 10
";

$result = $conn->query($query);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row['api_url'];
}

echo json_encode($data);
$conn->close();