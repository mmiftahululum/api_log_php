<?php
header("Content-Type: application/json");
date_default_timezone_set('Asia/Jakarta');

require_once "db.php";

// ambil parameter
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if ($page < 1) $page = 1;
if ($limit < 1) $limit = 10;

$search    = $_GET['search'] ?? '';
$api       = $_GET['api'] ?? '';
$request   = $_GET['request'] ?? '';
$response  = $_GET['response'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate   = $_GET['end_date'] ?? '';

$offset = ($page - 1) * $limit;

// query dasar
$where = "WHERE 1=1";

// 1. Filter Tanggal (Asia/Jakarta timezone)
if (!empty($startDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
    if (!empty($endDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        $where .= " AND CONVERT_TZ(created_at, '+00:00', '+07:00') >= '$startDate 00:00:00' AND CONVERT_TZ(created_at, '+00:00', '+07:00') <= '$endDate 23:59:59'";
    } else {
        $where .= " AND CONVERT_TZ(created_at, '+00:00', '+07:00') >= '$startDate 00:00:00' AND CONVERT_TZ(created_at, '+00:00', '+07:00') <= '$startDate 23:59:59'";
    }
} elseif (!empty($endDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    $where .= " AND CONVERT_TZ(created_at, '+00:00', '+07:00') <= '$endDate 23:59:59'";
}

// 2. Filter API URL
if (!empty($api)) {
    $api = $conn->real_escape_string($api);
    $where .= " AND api_url LIKE '%$api%'";
}

// 3. Filter Request Body (wildcard % fleksibel dari form: awal, akhir, atau keduanya)
if (isset($_GET['request']) && trim($_GET['request']) !== '') {
    $req = $conn->real_escape_string(trim($_GET['request']));
    $where .= " AND request_body LIKE '$req'";
}

// 4. Filter Response Body (wildcard % fleksibel dari form: awal, akhir, atau keduanya)
if (isset($_GET['response']) && trim($_GET['response']) !== '') {
    $resp = $conn->real_escape_string(trim($_GET['response']));
    $where .= " AND response_body LIKE '$resp'";
}

// 5. Filter Umum (backward compatibility jika ada pemanggil query search lama)
if (!empty($search)) {
    $searchEscaped = $conn->real_escape_string($search);
    if (strpos($searchEscaped, '%') !== false) {
        $where .= " AND (request_body LIKE '$searchEscaped' OR response_body LIKE '$searchEscaped')";
    } else {
        $where .= " AND (request_body LIKE '%$searchEscaped%' OR response_body LIKE '%$searchEscaped%')";
    }
}

// ambil total data
$totalQuery = $conn->query("SELECT COUNT(*) as total FROM api_logs $where");
$total = $totalQuery ? (int)($totalQuery->fetch_assoc()['total'] ?? 0) : 0;

// ambil data paginated
$query = "
    SELECT 
        id,
        api_url,
        request_body,
        response_body,
        CONVERT_TZ(created_at, '+00:00', '+07:00') as created_at
    FROM api_logs
    $where
    ORDER BY created_at DESC
    LIMIT $limit OFFSET $offset
";

$result = $conn->query($query);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
        $data[] = $row;
    }
}

// response lengkap
echo json_encode([
    "data" => $data,
    "page" => $page,
    "limit" => $limit,
    "total" => $total,
    "total_page" => max(1, (int)ceil($total / $limit))
]);

$conn->close();