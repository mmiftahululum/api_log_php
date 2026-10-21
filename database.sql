-- ========================================================
-- Script Pembuatan Database & Tabel untuk API Logs
-- ========================================================

-- 1. Buat Database (opsional jika belum ada)
CREATE DATABASE IF NOT EXISTS `u780303141_log` 
DEFAULT CHARACTER SET utf8mb4 
DEFAULT COLLATE utf8mb4_unicode_ci;

-- 2. Pilih Database yang digunakan
USE `u780303141_log`;

-- 3. Buat Tabel api_logs
CREATE TABLE IF NOT EXISTS `api_logs` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `api_url` VARCHAR(255) NOT NULL COMMENT 'Endpoint / URL API yang dipanggil',
  `request_body` LONGTEXT DEFAULT NULL COMMENT 'Data request payload (JSON / text)',
  `response_body` LONGTEXT DEFAULT NULL COMMENT 'Data response payload (JSON / text)',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pencatatan log',
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_api_url` (`api_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel histori request & response API';
