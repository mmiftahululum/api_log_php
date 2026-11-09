# 📊 API Log System & Dashboard

Aplikasi pencatatan log API berbasis PHP native & MySQL yang dilengkapi dengan dashboard visual, filter tanggal, pencarian wildcard LIKE fleksibel, JSON viewer, dan navigasi halaman langsung (*jump to page*).

---

## 📁 Struktur Project

```
private_logApi/
├── .env                  # Kredensial lokal (TIDAK di-commit)
├── .env.example          # Template konfigurasi (di-commit)
├── .gitignore            # Mengabaikan .env, vendor, dll.
├── .htaccess             # Proteksi file/folder sensitif
├── config/
│   └── config.php        # Loader .env + konfigurasi aplikasi
├── src/
│   ├── db.php            # Koneksi database (mysqli)
│   └── auth.php          # Validasi header mifpro-token
├── database/
│   └── schema.sql        # Skema tabel accounts & api_logs
├── index.php             # Dashboard + login Google
├── docs.php              # Halaman dokumentasi API
├── auth_google.php       # Verifikasi login Google
├── log.php               # API simpan log (butuh mifpro-token)
├── get_logs.php          # API ambil log (butuh mifpro-token)
├── get_api_list.php      # API autocomplete (butuh mifpro-token)
├── setup.php             # Installer database & tabel
├── db.php / auth.php / google_config.php   # Shim kompatibilitas
└── README.md
```

> 🔐 **Keamanan `.env`:** File `.env` berisi kredensial dan **tidak akan ter-commit** ke GitHub (lihat `.gitignore`). Di produksi, isi `.env` langsung di server atau set variabel environment. Variabel environment server **tidak akan ditimpa** oleh `.env`.

---

## 🔑 1. Akses Dashboard Web

### URL Akses
Buka browser dan akses URL tempat aplikasi ini di-deploy, misalnya:
- **Lokal (XAMPP / Laragon / PHP Server):**
  ```
  http://localhost/private_logApi/
  ```
  atau jika menggunakan PHP built-in server (`php -S localhost:8000`):
  ```
  http://localhost:8000/
  ```
- **Hosting / Server / VPS:**
  ```
  https://domain-anda.com/path-ke-folder/index.php
  ```

### Login dengan Google
Dashboard menggunakan **Google Sign-In** (Google Identity Services). Tidak ada password yang disimpan.
- Klik tombol **Sign in with Google** pada halaman login.
- Akun dibuat otomatis saat login pertama kali.
- Setiap akun memiliki **`mifpro-token`** unik yang ditampilkan di header dashboard (tombol **Copy**).

> ⚙️ *Konfigurasi:* Isi `GOOGLE_CLIENT_ID` pada file `.env` (lihat `.env.example`). Tambahkan juga origin domain Anda di Google Cloud Console → OAuth consent screen → Authorized JavaScript origins.

### Isolasi Data
Semua log terikat ke `account_id` pemilik token. Akun **tidak dapat** membaca data akun lain.

---

## 📡 2. Pemanggilan API

### A. Simpan Log Baru (`log.php`)
Digunakan oleh aplikasi/backend lain untuk mengirim dan menyimpan data log Request & Response.

- **Method:** `POST`
- **Endpoint:** `http://domain-anda/log.php`
- **Header Wajib:** `mifpro-token: <token-akun-anda>`
- **Content-Type:** `application/x-www-form-urlencoded` atau `multipart/form-data`
- **Parameter Body:**
  | Parameter | Tipe | Wajib | Keterangan |
  |---|---|---|---|
  | `api_url` | string | Ya | URL API yang dipanggil (contoh: `/api/v1/payment/checkout`) |
  | `request` | string / JSON text | Ya | Payload body request yang dikirim |
  | `response` | string / JSON text | Ya | Respon yang diterima dari API |

#### Contoh Pemanggilan (cURL):
```bash
curl -X POST "http://localhost/private_logApi/log.php" \
  -H "mifpro-token: TOKEN_AKUN_ANDA" \
  -d "api_url=https://api.domain.com/v1/orders" \
  -d 'request={"order_id": 12345, "amount": 50000}' \
  -d 'response={"status": "success", "code": 200, "message": "Order created"}'
```

#### Contoh Pemanggilan di PHP (cURL):
```php
$data = [
    'api_url'  => 'https://api.domain.com/v1/orders',
    'request'  => json_encode(['order_id' => 12345, 'amount' => 50000]),
    'response' => json_encode(['status' => 'success', 'code' => 200])
];

$ch = curl_init('http://localhost/private_logApi/log.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$response = curl_exec($ch);
curl_close($ch);
```

#### Contoh Pemanggilan di JavaScript (Fetch):
```javascript
const formData = new FormData();
formData.append('api_url', 'https://api.domain.com/v1/orders');
formData.append('request', JSON.stringify({ order_id: 12345 }));
formData.append('response', JSON.stringify({ status: 'success' }));

fetch('http://localhost/private_logApi/log.php', {
  method: 'POST',
  body: formData
})
.then(res => res.json())
.then(data => console.log(data));
```

#### Response Sukses:
```json
{
  "status": true
}
```

---

### B. Ambil Data Log Berfilter (`get_logs.php`)
Digunakan untuk mengambil daftar log dengan pagination dan berbagai kriteria pencarian.

- **Method:** `GET`
- **Endpoint:** `http://domain-anda/get_logs.php`
- **Header Wajib:** `mifpro-token: <token-akun-anda>`
- **Query Parameter:**
  | Parameter | Tipe | Contoh | Keterangan |
  |---|---|---|---|
  | `page` | integer | `1` | Nomor halaman (default: `1`) |
  | `limit` | integer | `10` | Jumlah data per halaman (default: `10`) |
  | `start_date` | string (Y-m-d) | `2026-10-13` | Filter tanggal mulai (Asia/Jakarta) |
  | `end_date` | string (Y-m-d) | `2026-10-13` | Filter tanggal selesai |
  | `api` | string | `order` | Filter URL API (LIKE `%api%`) |
  | `request` | string | `%12345%` | Pencarian pada Request body (LIKE manual) |
  | `response` | string | `%success%` | Pencarian pada Response body (LIKE manual) |

#### Contoh Request:
```
GET http://localhost/private_logApi/get_logs.php?page=1&limit=10&start_date=2026-10-01&end_date=2026-10-13&request=%order%
```

#### Response:
```json
{
  "data": [
    {
      "id": "1",
      "api_url": "https://api.domain.com/v1/orders",
      "request_body": "{\"order_id\": 12345}",
      "response_body": "{\"status\": \"success\"}",
      "created_at": "2026-10-13 10:15:30"
    }
  ],
  "page": 1,
  "limit": 10,
  "total": 1,
  "total_page": 1
}
```

---

### C. Autocomplete List API (`get_api_list.php`)
Mengambil rekomendasi daftar `api_url` yang pernah tercatat di database.

- **Method:** `GET`
- **Endpoint:** `http://domain-anda/get_api_list.php?q=keyword`
- **Header Wajib:** `mifpro-token: <token-akun-anda>`
- **Parameter:** `q` (minimal 3 huruf)
- **Response:** Array string API URL

---

## 🖥️ 3. Penjelasan Pemakaian Dashboard

Dashboard [index.php](file:///c:/Users/MiftahulUlum/Development/WebPhp5/private_logApi/index.php) dirancang untuk mempermudah monitoring dan debugging request/response API:

### 1. Filter Berdasarkan Tanggal
- **📅 Dari Tanggal**: Pilih tanggal awal pencarian.
- **📅 Sampai Tanggal**: Pilih tanggal akhir pencarian.
- *Tips:*
  - Jika Anda **hanya mengisi "Dari Tanggal"**, sistem otomatis mencari data log **seharian penuh** pada tanggal tersebut (`00:00:00` s/d `23:59:59`).
  - Jika mengisi kedua tanggal, sistem memfilter rentang tanggal tersebut.

### 2. Autocomplete API URL
- Kolom **🔗 API URL** akan memunculkan dropdown saran nama API secara otomatis ketika Anda mengetikkan minimal 3 karakter.
- Klik salah satu saran untuk langsung memfilter log berdasarkan endpoint tersebut.

### 3. Pencarian Request & Response dengan Karakter `%` (LIKE)
- Anda bebas menyisipkan simbol wildcard `%` langsung di form sesuai kebutuhan:
  - `%keyword%` : Mengandung kata *keyword* (paling sering digunakan).
  - `keyword%` : Diawali dengan kata *keyword*.
  - `%keyword` : Diakhiri dengan kata *keyword*.
  - `keyword` : Pencarian persis sama.
- **Tombol Cepat "Nempel di Form"**:
  - Di samping label Request dan Response terdapat tombol cepat:
    - **`%...%`** : Otomatis membungkus kata dengan `%` di awal dan akhir.
    - **`%...`** : Menambahkan `%` di awal kata.
    - **`...%`** : Menambahkan `%` di akhir kata.
- Tekan tombol **Enter** di kolom input mana saja untuk langsung menjalankan pencarian.
- Klik **🔄 Reset** untuk menghapus semua filter dan menampilkan data default.

### 4. Pindah Halaman Langsung (*Jump to Page*)
- Di baris bawah pagination, terdapat kolom:
  `Pindah ke Halaman: [ 5 ] [ Go ]`
- Ketik nomor halaman tujuan lalu klik tombol **Go** atau tekan **Enter** pada keyboard.
- Tombol navigasi **« Awal**, **‹ Prev**, **Next ›**, dan **Akhir »** mempermudah penjelajahan antar halaman dengan status *disabled* otomatis saat berada di batas halaman.

### 5. Membaca dan Menyalin JSON
- **Preview Ringkas:** Request dan Response ditampilkan ringkas di tabel.
- **Buka JSON Viewer:** Klik pada kotak teks request/response untuk membuka tampilan modal popup berformat JSON rapi (*pretty print* berwarna).
- **Salin Cepat (Copy):**
  - Klik tombol **Copy** di bawah kotak tabel untuk langsung menyalin raw string ke clipboard.
  - Klik tombol **Copy** di dalam modal popup untuk menyalin format JSON yang sudah terindentasi.

---

## 🗄️ 4. Setup & Pembuatan Database

### Langkah 1: Siapkan `.env`
Salin template lalu sesuaikan kredensial:
```bash
cp .env.example .env
```
Isi `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, dan `GOOGLE_CLIENT_ID`.
Contoh lokal (MySQL root tanpa password):
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=private_log_api
DB_USER=root
DB_PASS=
```

### Langkah 2: Buat Database & Tabel
**Cara 1 — Installer Otomatis (Direkomendasikan):**
Script [setup.php](file:///c:/Users/MiftahulUlum/Development/WebPhp5/private_logApi/setup.php) membaca `.env`, membuat database, lalu membuat tabel `accounts` & `api_logs`:
- **Lewat Browser:** Buka `http://localhost/private_logApi/setup.php`
- **Lewat Terminal:**
  ```bash
  php setup.php
  ```

**Cara 2 — Import Manual:**
Buat database terlebih dahulu, lalu import [database/schema.sql](file:///c:/Users/MiftahulUlum/Development/WebPhp5/private_logApi/database/schema.sql):
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS private_log_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root private_log_api < database/schema.sql
```

#### Skema Tabel `accounts`:
```sql
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `google_id` VARCHAR(64) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `picture` VARCHAR(512) DEFAULT NULL,
  `api_token` CHAR(64) NOT NULL COMMENT 'Token API (header mifpro-token)',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_google_id` (`google_id`),
  UNIQUE KEY `uq_email` (`email`),
  UNIQUE KEY `uq_api_token` (`api_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Skema Tabel `api_logs`:
```sql
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
