-- ========================================================
-- Skema Database API Logs (multi-akun / Google Auth)
-- Jalankan setelah database dibuat, atau gunakan setup.php
-- ========================================================

-- Tabel akun (dibuat otomatis saat login Google pertama kali)
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

-- Tabel api_logs (setiap log terikat ke satu akun)
CREATE TABLE IF NOT EXISTS `api_logs` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` BIGINT(20) UNSIGNED NULL COMMENT 'Pemilik log',
  `api_url` VARCHAR(255) NOT NULL COMMENT 'Endpoint / URL API yang dipanggil',
  `request_body` LONGTEXT DEFAULT NULL COMMENT 'Data request payload (JSON / text)',
  `response_body` LONGTEXT DEFAULT NULL COMMENT 'Data response payload (JSON / text)',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pencatatan log',
  PRIMARY KEY (`id`),
  KEY `idx_account_id` (`account_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_api_url` (`api_url`),
  CONSTRAINT `fk_logs_account` FOREIGN KEY (`account_id`)
    REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel histori request & response API';
