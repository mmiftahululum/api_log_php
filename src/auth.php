<?php
/**
 * Helper autentikasi API berbasis header `mifpro-token`.
 * Semua endpoint data (log.php, get_logs.php, get_api_list.php)
 * wajib memanggil require_account() agar data terisolasi per akun.
 */

$config = require dirname(__DIR__) . '/config/config.php';

if (!defined('TOKEN_HEADER')) {
    define('TOKEN_HEADER', $config['app']['token_header']);
}

/**
 * Kirim response JSON error lalu hentikan eksekusi.
 */
function json_fail(int $httpCode, string $message): void
{
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => false,
        'message' => $message,
    ]);
    exit;
}

/**
 * Ambil nilai header `mifpro-token` dari request.
 * Mendukung Apache/CGI (HTTP_MIFPRO_TOKEN) dan getallheaders().
 */
function get_mifpro_token(): ?string
{
    if (!empty($_SERVER['HTTP_MIFPRO_TOKEN'])) {
        return trim($_SERVER['HTTP_MIFPRO_TOKEN']);
    }

    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $key => $value) {
            if (strcasecmp($key, TOKEN_HEADER) === 0 && $value !== '') {
                return trim($value);
            }
        }
    }

    return null;
}

/**
 * Validasi token dan kembalikan data akun.
 * Jika token tidak ada / tidak valid -> response 401 dan exit.
 *
 * @return array{id:int,email:string,name:?string}
 */
function require_account(mysqli $conn): array
{
    $token = get_mifpro_token();

    if ($token === null || $token === '') {
        json_fail(401, 'Header ' . TOKEN_HEADER . ' wajib dikirim');
    }

    $stmt = $conn->prepare(
        'SELECT id, email, name FROM accounts WHERE api_token = ? LIMIT 1'
    );
    if (!$stmt) {
        json_fail(500, 'Gagal menyiapkan query autentikasi');
    }

    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $account = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$account) {
        json_fail(401, 'Token ' . TOKEN_HEADER . ' tidak valid');
    }

    $account['id'] = (int) $account['id'];
    return $account;
}
