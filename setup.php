<?php
/**
 * Script Setup / Installer Otomatis Database & Tabel
 * Konfigurasi diambil dari .env (lihat config/config.php).
 *
 * Jalankan via browser: http://localhost/private_logApi/setup.php
 * Atau via terminal:    php setup.php
 */

header('Content-Type: text/html; charset=utf-8');

$config = require __DIR__ . '/config/config.php';
$db = $config['db'];

$host = $db['host'];
$port = (int) $db['port'];
$user = $db['user'];
$pass = $db['pass'];
$dbName = $db['name'];

$messages = [];
$isSuccess = true;

try {
    // 1. Koneksi ke MySQL server (tanpa memilih database)
    $mysqli = @new mysqli($host, $user, $pass, '', $port);
    if ($mysqli->connect_error) {
        throw new Exception("Gagal terhubung ke MySQL Server: " . $mysqli->connect_error);
    }
    $messages[] = "✅ Berhasil terhubung ke MySQL Server ($host:$port)";

    // 2. Buat database jika belum ada
    $createDbSql = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($mysqli->query($createDbSql)) {
        $messages[] = "✅ Database <strong>`$dbName`</strong> siap digunakan.";
    } else {
        throw new Exception("Gagal membuat database: " . $mysqli->error);
    }

    // 3. Pilih database
    $mysqli->select_db($dbName);

    // 4. Buat tabel accounts (akun Google Auth)
    $createAccountsSql = "
        CREATE TABLE IF NOT EXISTS `accounts` (
          `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `google_id` VARCHAR(64) NOT NULL COMMENT 'Sub / ID unik dari Google',
          `email` VARCHAR(255) NOT NULL COMMENT 'Email Google',
          `name` VARCHAR(255) DEFAULT NULL COMMENT 'Nama tampilan',
          `picture` VARCHAR(512) DEFAULT NULL COMMENT 'URL foto profil',
          `api_token` CHAR(64) NOT NULL COMMENT 'Token API (header mifpro-token)',
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `last_login` TIMESTAMP NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uq_google_id` (`google_id`),
          UNIQUE KEY `uq_email` (`email`),
          UNIQUE KEY `uq_api_token` (`api_token`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Akun pengguna (Google Auth)';
    ";

    if ($mysqli->query($createAccountsSql)) {
        $messages[] = "✅ Tabel <strong>`accounts`</strong> berhasil dibuat / sudah ada.";
    } else {
        throw new Exception("Gagal membuat tabel accounts: " . $mysqli->error);
    }

    // 5. Buat tabel api_logs jika belum ada (dengan account_id)
    $createTableSql = "
        CREATE TABLE IF NOT EXISTS `api_logs` (
          `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `account_id` BIGINT(20) UNSIGNED NULL COMMENT 'Pemilik log',
          `api_url` VARCHAR(255) NOT NULL COMMENT 'Endpoint / URL API',
          `request_body` LONGTEXT DEFAULT NULL COMMENT 'Payload body request JSON/text',
          `response_body` LONGTEXT DEFAULT NULL COMMENT 'Response body JSON/text',
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu log dicatat',
          PRIMARY KEY (`id`),
          KEY `idx_account_id` (`account_id`),
          KEY `idx_created_at` (`created_at`),
          KEY `idx_api_url` (`api_url`),
          CONSTRAINT `fk_logs_account` FOREIGN KEY (`account_id`)
            REFERENCES `accounts` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel log request & response API';
    ";

    if ($mysqli->query($createTableSql)) {
        $messages[] = "✅ Tabel <strong>`api_logs`</strong> berhasil dibuat / sudah ada.";
    } else {
        throw new Exception("Gagal membuat tabel api_logs: " . $mysqli->error);
    }

    // 6. Migrasi: tambahkan kolom account_id jika tabel lama belum memilikinya
    $colCheck = $mysqli->query("SHOW COLUMNS FROM `api_logs` LIKE 'account_id'");
    if ($colCheck && $colCheck->num_rows === 0) {
        $alterSql = "
            ALTER TABLE `api_logs`
              ADD COLUMN `account_id` BIGINT(20) UNSIGNED NULL AFTER `id`,
              ADD KEY `idx_account_id` (`account_id`),
              ADD CONSTRAINT `fk_logs_account` FOREIGN KEY (`account_id`)
                REFERENCES `accounts` (`id`) ON DELETE CASCADE
        ";
        if ($mysqli->query($alterSql)) {
            $messages[] = "✅ Kolom <strong>`account_id`</strong> ditambahkan ke tabel lama.";
        } else {
            throw new Exception("Gagal menambah kolom account_id: " . $mysqli->error);
        }
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
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            margin-top: 0;
            color: #2c3e50;
        }

        ul {
            padding-left: 20px;
            line-height: 1.8;
            font-size: 14px;
        }

        .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .btn:hover {
            background: #2980b9;
        }
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