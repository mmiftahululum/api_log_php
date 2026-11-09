<?php
/**
 * Endpoint login Google.
 * Menerima POST `credential` (ID token JWT dari Google Identity Services),
 * memverifikasinya ke Google, lalu membuat / memperbarui akun.
 *
 * Response JSON: { status, redirect } atau { status:false, message }
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/src/db.php';
$config = require __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Method tidak diizinkan']);
    exit;
}

$credential = trim($_POST['credential'] ?? '');
if ($credential === '') {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Credential Google tidak ditemukan']);
    exit;
}

/**
 * Verifikasi ID token ke endpoint tokeninfo Google.
 * @return array|null payload token jika valid
 */
function verify_google_id_token(string $idToken): ?array
{
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);

    $raw = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
    } else {
        $raw = @file_get_contents($url);
    }

    if ($raw === false) {
        return null;
    }

    $data = json_decode($raw, true);
    if (!is_array($data) || isset($data['error'])) {
        return null;
    }

    return $data;
}

$payload = verify_google_id_token($credential);
if ($payload === null) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Token Google tidak valid']);
    exit;
}

// Validasi audience & email
if (($payload['aud'] ?? '') !== $config['google']['client_id']) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Client ID tidak cocok']);
    exit;
}

if (($payload['email_verified'] ?? 'false') !== 'true' && ($payload['email_verified'] ?? false) !== true) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Email Google belum terverifikasi']);
    exit;
}

$googleId = (string) ($payload['sub'] ?? '');
$email = (string) ($payload['email'] ?? '');
$name = (string) ($payload['name'] ?? '');
$picture = (string) ($payload['picture'] ?? '');

if ($googleId === '' || $email === '') {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Data akun Google tidak lengkap']);
    exit;
}

// Cari akun berdasarkan google_id
$stmt = $conn->prepare('SELECT id, api_token FROM accounts WHERE google_id = ? LIMIT 1');
$stmt->bind_param('s', $googleId);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing) {
    $accountId = (int) $existing['id'];
    $apiToken = $existing['api_token'];

    $upd = $conn->prepare(
        'UPDATE accounts SET email = ?, name = ?, picture = ?, last_login = NOW() WHERE id = ?'
    );
    $upd->bind_param('sssi', $email, $name, $picture, $accountId);
    $upd->execute();
    $upd->close();
} else {
    // Buat token acak 64 hex (256-bit)
    $apiToken = bin2hex(random_bytes(32));

    $ins = $conn->prepare(
        'INSERT INTO accounts (google_id, email, name, picture, api_token, last_login)
         VALUES (?, ?, ?, ?, ?, NOW())'
    );
    $ins->bind_param('sssss', $googleId, $email, $name, $picture, $apiToken);
    if (!$ins->execute()) {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Gagal membuat akun: ' . $ins->error]);
        $ins->close();
        $conn->close();
        exit;
    }
    $accountId = (int) $ins->insert_id;
    $ins->close();
}

$conn->close();

// Simpan sesi
$_SESSION['account_id'] = $accountId;
$_SESSION['email'] = $email;
$_SESSION['name'] = $name;
$_SESSION['api_token'] = $apiToken;

echo json_encode([
    'status' => true,
    'redirect' => 'index.php',
]);
