<?php
/**
 * Halaman dokumentasi penggunaan API.
 * Menampilkan base URL otomatis sesuai domain tempat file ini diakses.
 */

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$baseUrl = $scheme . '://' . $host . $basePath;

function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi API - API Logs</title>
    <link rel="icon" type="image/x-icon" href="https://log.mifpro.com/log.ico">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            background: #f4f6f9;
            color: #2c3e50;
            margin: 0;
            line-height: 1.6;
        }

        .topbar {
            background: #2c3e50;
            color: #fff;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .topbar h1 {
            font-size: 18px;
            margin: 0;
        }

        .topbar a {
            color: #fff;
            text-decoration: none;
            background: #3498db;
            padding: 7px 14px;
            border-radius: 5px;
            font-size: 14px;
        }

        .topbar a:hover {
            background: #2980b9;
        }

        .layout {
            display: flex;
            max-width: 1200px;
            margin: 0 auto;
            gap: 24px;
            padding: 24px;
            align-items: flex-start;
        }

        .sidebar {
            position: sticky;
            top: 24px;
            width: 240px;
            flex-shrink: 0;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.06);
            padding: 16px;
        }

        .sidebar h3 {
            font-size: 13px;
            text-transform: uppercase;
            color: #64748b;
            margin: 0 0 10px;
        }

        .sidebar a {
            display: block;
            color: #34495e;
            text-decoration: none;
            font-size: 14px;
            padding: 6px 8px;
            border-radius: 5px;
        }

        .sidebar a:hover {
            background: #eef2f7;
            color: #2980b9;
        }

        .content {
            flex: 1;
            min-width: 0;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.06);
            padding: 24px;
            margin-bottom: 24px;
        }

        .card h2 {
            margin-top: 0;
            font-size: 20px;
            border-bottom: 2px solid #eef2f7;
            padding-bottom: 10px;
        }

        .card h3 {
            font-size: 16px;
            margin-top: 24px;
        }

        .badge {
            display: inline-block;
            font-size: 12px;
            font-weight: bold;
            padding: 3px 10px;
            border-radius: 4px;
            color: #fff;
            margin-right: 8px;
            vertical-align: middle;
        }

        .badge.post {
            background: #27ae60;
        }

        .badge.get {
            background: #3498db;
        }

        .endpoint {
            font-family: monospace;
            background: #1e1e1e;
            color: #00ff90;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 14px;
            word-break: break-all;
            margin: 10px 0 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 14px;
        }

        th,
        td {
            border: 1px solid #e2e8f0;
            padding: 9px 12px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            font-weight: 600;
        }

        code {
            font-family: monospace;
            background: #eef2f7;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 13px;
            color: #c0392b;
        }

        .code-block {
            position: relative;
            margin: 12px 0;
        }

        pre {
            background: #1e1e1e;
            color: #e6e6e6;
            padding: 16px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }

        pre .c {
            color: #6a9955;
        }

        pre .s {
            color: #ce9178;
        }

        pre .k {
            color: #569cd6;
        }

        .copy-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #34495e;
            color: #fff;
            border: none;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
        }

        .copy-btn:hover {
            background: #2c3e50;
        }

        .note {
            background: #fff8e1;
            border-left: 4px solid #f39c12;
            padding: 12px 16px;
            border-radius: 4px;
            font-size: 14px;
            margin: 16px 0;
        }

        .note.info {
            background: #e8f4fd;
            border-left-color: #3498db;
        }

        .note.danger {
            background: #fdecea;
            border-left-color: #e74c3c;
        }

        .method-line {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        @media (max-width: 860px) {
            .layout {
                flex-direction: column;
            }

            .sidebar {
                position: static;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="topbar">
        <h1>📖 Dokumentasi API Logs</h1>
        <a href="index.php">← Kembali ke Dashboard</a>
    </div>

    <div class="layout">
        <nav class="sidebar">
            <h3>Daftar Isi</h3>
            <a href="#autentikasi">🔑 Autentikasi</a>
            <a href="#simpan-log">📥 Simpan Log</a>
            <a href="#ambil-log">📤 Ambil Log</a>
            <a href="#autocomplete">🔗 Autocomplete API</a>
            <a href="#error">⚠️ Kode Error</a>
            <a href="#catatan">📌 Catatan</a>
        </nav>

        <main class="content">

            <!-- ================= AUTENTIKASI ================= -->
            <section class="card" id="autentikasi">
                <h2>🔑 Autentikasi</h2>
                <p>Semua endpoint data wajib menyertakan header <code>mifpro-token</code> berisi token akun Anda.</p>

                <h3>Cara mendapatkan token</h3>
                <ol>
                    <li>Buka <a href="index.php">Dashboard</a> dan login dengan Google.</li>
                    <li>Token ditampilkan di header dashboard pada label <code>mifpro-token</code>.</li>
                    <li>Klik tombol <strong>Copy</strong> untuk menyalin token.</li>
                </ol>

                <h3>Format header</h3>
                <div class="code-block">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre>mifpro-token: TOKEN_AKUN_ANDA</pre>
                </div>

                <div class="note danger">
                    🔒 Jangan bagikan token Anda. Token bersifat rahasia dan hanya boleh dikirim melalui
                    <strong>HTTPS</strong>.
                </div>
            </section>

            <!-- ================= SIMPAN LOG ================= -->
            <section class="card" id="simpan-log">
                <h2>📥 Simpan Log Baru</h2>
                <div class="method-line">
                    <span class="badge post">POST</span>
                    <span class="endpoint" style="margin:0; flex:1;"><?= h($baseUrl) ?>/log.php</span>
                </div>

                <h3>Header</h3>
                <table>
                    <tr>
                        <th>Header</th>
                        <th>Wajib</th>
                        <th>Keterangan</th>
                    </tr>
                    <tr>
                        <td><code>mifpro-token</code></td>
                        <td>Ya</td>
                        <td>Token akun Anda</td>
                    </tr>
                    <tr>
                        <td><code>Content-Type</code></td>
                        <td>Ya</td>
                        <td><code>application/x-www-form-urlencoded</code> atau <code>multipart/form-data</code></td>
                    </tr>
                </table>

                <h3>Parameter Body</h3>
                <table>
                    <tr>
                        <th>Parameter</th>
                        <th>Tipe</th>
                        <th>Wajib</th>
                        <th>Keterangan</th>
                    </tr>
                    <tr>
                        <td><code>api_url</code></td>
                        <td>string</td>
                        <td>Ya</td>
                        <td>Endpoint / URL API yang dipanggil</td>
                    </tr>
                    <tr>
                        <td><code>request</code></td>
                        <td>string / JSON</td>
                        <td>Tidak</td>
                        <td>Payload request yang dikirim</td>
                    </tr>
                    <tr>
                        <td><code>response</code></td>
                        <td>string / JSON</td>
                        <td>Tidak</td>
                        <td>Response yang diterima</td>
                    </tr>
                </table>

                <h3>Contoh cURL</h3>
                <div class="code-block">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre>curl -X POST "<?= h($baseUrl) ?>/log.php" \
  -H "mifpro-token: TOKEN_AKUN_ANDA" \
  -d "api_url=https://api.domain.com/v1/orders" \
  -d 'request={"order_id": 12345, "amount": 50000}' \
  -d 'response={"status": "success", "code": 200}'</pre>
                </div>

                <h3>Contoh PHP (cURL)</h3>
                <div class="code-block">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre>$data = [
    'api_url'  => 'https://api.domain.com/v1/orders',
    'request'  => json_encode(['order_id' => 12345]),
    'response' => json_encode(['status' => 'success']),
];

$ch = curl_init('<?= h($baseUrl) ?>/log.php');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($data),
    CURLOPT_HTTPHEADER     => ['mifpro-token: TOKEN_AKUN_ANDA'],
]);
$result = curl_exec($ch);
curl_close($ch);
echo $result;</pre>
                </div>

                <h3>Contoh JavaScript (Fetch)</h3>
                <div class="code-block">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre>const formData = new FormData();
formData.append('api_url', 'https://api.domain.com/v1/orders');
formData.append('request', JSON.stringify({ order_id: 12345 }));
formData.append('response', JSON.stringify({ status: 'success' }));

fetch('<?= h($baseUrl) ?>/log.php', {
  method: 'POST',
  headers: { 'mifpro-token': 'TOKEN_AKUN_ANDA' },
  body: formData
})
  .then(res => res.json())
  .then(data => console.log(data));</pre>
                </div>

                <h3>Response Sukses</h3>
                <div class="code-block">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre>{ "status": true }</pre>
                </div>
            </section>

            <!-- ================= AMBIL LOG ================= -->
            <section class="card" id="ambil-log">
                <h2>📤 Ambil Data Log</h2>
                <div class="method-line">
                    <span class="badge get">GET</span>
                    <span class="endpoint" style="margin:0; flex:1;"><?= h($baseUrl) ?>/get_logs.php</span>
                </div>

                <h3>Header</h3>
                <table>
                    <tr>
                        <th>Header</th>
                        <th>Wajib</th>
                        <th>Keterangan</th>
                    </tr>
                    <tr>
                        <td><code>mifpro-token</code></td>
                        <td>Ya</td>
                        <td>Token akun Anda</td>
                    </tr>
                </table>

                <h3>Query Parameter</h3>
                <table>
                    <tr>
                        <th>Parameter</th>
                        <th>Tipe</th>
                        <th>Contoh</th>
                        <th>Keterangan</th>
                    </tr>
                    <tr>
                        <td><code>page</code></td>
                        <td>integer</td>
                        <td>1</td>
                        <td>Nomor halaman (default 1)</td>
                    </tr>
                    <tr>
                        <td><code>limit</code></td>
                        <td>integer</td>
                        <td>10</td>
                        <td>Jumlah data per halaman (default 10)</td>
                    </tr>
                    <tr>
                        <td><code>start_date</code></td>
                        <td>Y-m-d</td>
                        <td>2026-10-01</td>
                        <td>Filter tanggal mulai (Asia/Jakarta)</td>
                    </tr>
                    <tr>
                        <td><code>end_date</code></td>
                        <td>Y-m-d</td>
                        <td>2026-10-13</td>
                        <td>Filter tanggal selesai</td>
                    </tr>
                    <tr>
                        <td><code>api</code></td>
                        <td>string</td>
                        <td>order</td>
                        <td>Filter URL API (LIKE %api%)</td>
                    </tr>
                    <tr>
                        <td><code>request</code></td>
                        <td>string</td>
                        <td>%12345%</td>
                        <td>Pencarian pada request body (LIKE manual)</td>
                    </tr>
                    <tr>
                        <td><code>response</code></td>
                        <td>string</td>
                        <td>%success%</td>
                        <td>Pencarian pada response body (LIKE manual)</td>
                    </tr>
                </table>

                <h3>Contoh cURL</h3>
                <div class="code-block">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre>curl -X GET "<?= h($baseUrl) ?>/get_logs.php?page=1&limit=10&api=order" \
  -H "mifpro-token: TOKEN_AKUN_ANDA"</pre>
                </div>

                <h3>Response</h3>
                <div class="code-block">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre>{
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
}</pre>
                </div>
            </section>

            <!-- ================= AUTOCOMPLETE ================= -->
            <section class="card" id="autocomplete">
                <h2>🔗 Autocomplete API URL</h2>
                <div class="method-line">
                    <span class="badge get">GET</span>
                    <span class="endpoint"
                        style="margin:0; flex:1;"><?= h($baseUrl) ?>/get_api_list.php?q=keyword</span>
                </div>

                <h3>Header</h3>
                <table>
                    <tr>
                        <th>Header</th>
                        <th>Wajib</th>
                        <th>Keterangan</th>
                    </tr>
                    <tr>
                        <td><code>mifpro-token</code></td>
                        <td>Ya</td>
                        <td>Token akun Anda</td>
                    </tr>
                </table>

                <h3>Query Parameter</h3>
                <table>
                    <tr>
                        <th>Parameter</th>
                        <th>Tipe</th>
                        <th>Keterangan</th>
                    </tr>
                    <tr>
                        <td><code>q</code></td>
                        <td>string</td>
                        <td>Kata kunci (minimal 3 huruf)</td>
                    </tr>
                </table>

                <h3>Contoh cURL</h3>
                <div class="code-block">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre>curl -X GET "<?= h($baseUrl) ?>/get_api_list.php?q=order" \
  -H "mifpro-token: TOKEN_AKUN_ANDA"</pre>
                </div>

                <h3>Response</h3>
                <div class="code-block">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre>[
  "https://api.domain.com/v1/orders",
  "https://api.domain.com/v1/orders/detail"
]</pre>
                </div>
            </section>

            <!-- ================= ERROR ================= -->
            <section class="card" id="error">
                <h2>⚠️ Kode Error</h2>
                <table>
                    <tr>
                        <th>HTTP</th>
                        <th>Pesan</th>
                        <th>Penyebab</th>
                    </tr>
                    <tr>
                        <td>400</td>
                        <td>Parameter api_url wajib diisi</td>
                        <td>Body <code>api_url</code> kosong</td>
                    </tr>
                    <tr>
                        <td>401</td>
                        <td>Header mifpro-token wajib dikirim</td>
                        <td>Header token tidak ada</td>
                    </tr>
                    <tr>
                        <td>401</td>
                        <td>Token mifpro-token tidak valid</td>
                        <td>Token salah / tidak terdaftar</td>
                    </tr>
                    <tr>
                        <td>500</td>
                        <td>Database connection failed</td>
                        <td>Koneksi database gagal</td>
                    </tr>
                </table>
                <p>Format response error:</p>
                <div class="code-block">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre>{ "status": false, "message": "Token mifpro-token tidak valid" }</pre>
                </div>
            </section>

            <!-- ================= CATATAN ================= -->
            <section class="card" id="catatan">
                <h2>📌 Catatan</h2>
                <ul>
                    <li>Setiap akun hanya dapat membaca log miliknya sendiri (isolasi data per <code>account_id</code>).
                    </li>
                    <li>Gunakan HTTPS agar token tidak terekspos.</li>
                    <li>Filter <code>request</code> dan <code>response</code> mendukung wildcard <code>%</code> (contoh
                        <code>%keyword%</code>).</li>
                    <li>Zona waktu filter tanggal menggunakan <strong>Asia/Jakarta (UTC+7)</strong>.</li>
                </ul>
            </section>

        </main>
    </div>

    <script>
        function copyCode(btn) {
            const pre = btn.parentElement.querySelector('pre');
            navigator.clipboard.writeText(pre.innerText)
                .then(() => {
                    const old = btn.innerText;
                    btn.innerText = 'Copied!';
                    setTimeout(() => btn.innerText = old, 1200);
                })
                .catch(err => console.error(err));
        }
    </script>
</body>

</html>