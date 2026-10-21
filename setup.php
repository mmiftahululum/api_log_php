<?php
/**
 * Script Setup / Installer Otomatis Database & Tabel
 * Dapat dijalankan via browser: http://localhost/setup.php
 * Atau via terminal: php setup.php
 */

header('Content-Type: text/html; charset=utf-8');

// Load konfigurasi dari db.php
if (!file_exists(__DIR__ . '/db.php')) {
    die("❌ File db.php tidak ditemukan!");
}

// Ekstrak variabel kredensial dari db.php
$dbConfigContent = file_get_contents(__DIR__ . '/db.php');
preg_match('/\$host\s*=\s*["\'](.*?)["\'];/', $dbConfigContent, $matchHost);
preg_match('/\$user\s*=\s*["\'](.*?)["\'];/', $dbConfigContent, $matchUser);
preg_match('/\$pass\s*=\s*["\'](.*?)["\'];/', $dbConfigContent, $matchPass);
preg_match('/\$db\s*=\s*["\'](.*?)["\'];/', $dbConfigContent, $matchDb);

$host = $matchHost[1] ?? 'localhost';
$user = $matchUser[1] ?? 'root';
$pass = $matchPass[1] ?? '';
$db   = $matchDb[1] ?? 'u780303141_log';

$messages = [];
$isSuccess = true;

try {
    // 1. Coba koneksi ke MySQL server (tanpa memilih database terlebih dahulu)
    $mysqli = @new mysqli($host, $user, $pass);
    if ($mysqli->connect_error) {
        throw new Exception("Gagal terhubung ke MySQL Server: " . $mysqli->connect_error);
    }
    $messages[] = "✅ Berhasil terhubung ke MySQL Server ($host)";

    // 2. Buat database jika belum ada
    $createDbSql = "CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($mysqli->query($createDbSql)) {
        $messages[] = "✅ Database <strong>`$db`</strong> siap digunakan.";
    } else {
        throw new Exception("Gagal membuat database: " . $mysqli->error);
    }

    // 3. Pilih database
    $mysqli->select_db($db);

    // 4. Buat tabel api_logs jika belum ada
    $createTableSql = "
        CREATE TABLE IF NOT EXISTS `api_logs` (
          `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `api_url` VARCHAR(255) NOT NULL COMMENT 'Endpoint / URL API',
          `request_body` LONGTEXT DEFAULT NULL COMMENT 'Payload body request JSON/text',
          `response_body` LONGTEXT DEFAULT NULL COMMENT 'Response body JSON/text',
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu log dicatat',
          PRIMARY KEY (`id`),
          KEY `idx_created_at` (`created_at`),
          KEY `idx_api_url` (`api_url`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel log request & response API';
    ";

    if ($mysqli->query($createTableSql)) {
        $messages[] = "✅ Tabel <strong>`api_logs`</strong> berhasil dibuat / sudah ada.";
    } else {
        throw new Exception("Gagal membuat tabel api_logs: " . $mysqli->error);
    }

    $mysqli->close();

} catch (Exception $e) {
    $isSuccess = false;
    $messages[] = "❌ Error: " . $e->getMessage();
}

// Jika dijalankan via CLI
if (php_sapi_name() === 'cli') {
    foreach ($messages as $msg) {
        echo strip_tags($msg) . PHP_EOL;
    }
    exit($isSuccess ? 0 : 1);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Setup Database - API Logs</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        h2 { margin-top: 0; color: #2c3e50; }
        ul { padding-left: 20px; line-height: 1.8; font-size: 14px; }
        .btn { display: inline-block; margin-top: 15px; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="card">
        <h2>🛠️ Setup Database API Logs</h2>
        <ul>
            <?php foreach ($messages as $msg): ?>
                <li><?= $msg ?></li>
            <?php endforeach; ?>
        </ul>
        <?php if ($isSuccess): ?>
            <a href="index.php" class="btn">🚀 Buka Dashboard Web</a>
        <?php endif; ?>
    </div>
</body>
</html>
