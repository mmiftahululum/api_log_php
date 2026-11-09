<?php
session_start();

$config = require __DIR__ . '/config/config.php';

if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: index.php");
  exit;
}

if (!isset($_SESSION['account_id'])) {
  ?>
  <!DOCTYPE html>
  <html>

  <head>
    <meta charset="UTF-8">
    <title>Login - API Logs</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        background: #f4f6f9;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
      }

      .login-box {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
        width: 100%;
        max-width: 340px;
      }

      .login-box h2 {
        margin-top: 0;
        color: #2c3e50;
      }

      .sub {
        color: #64748b;
        font-size: 13px;
        margin-bottom: 20px;
      }

      .g_id_signin {
        display: flex;
        justify-content: center;
      }

      .error {
        color: #e74c3c;
        margin-top: 15px;
        font-size: 14px;
      }
    </style>

    <link rel="icon" type="image/x-icon" href="https://log.mifpro.com/log.ico">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
  </head>

  <body>
    <div class="login-box">
      <h2>🔒 Login dengan Google</h2>
      <p class="sub">Masuk untuk mengakses dashboard log API Anda.</p>

      <div id="g_id_onload" data-client_id="<?= htmlspecialchars($config['google']['client_id']) ?>"
        data-callback="handleCredentialResponse" data-auto_prompt="false"></div>
      <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline" data-text="signin_with"
        data-shape="rectangular" data-logo_alignment="left"></div>

      <p id="loginError" class="error" style="display:none;"></p>
    </div>

    <script>
      function handleCredentialResponse(response) {
        const form = new FormData();
        form.append('credential', response.credential);

        fetch('auth_google.php', { method: 'POST', body: form })
          .then(r => r.json())
          .then(res => {
            if (res.status) {
              window.location.href = res.redirect || 'index.php';
            } else {
              showError(res.message || 'Login gagal');
            }
          })
          .catch(() => showError('Gagal menghubungi server'));
      }

      function showError(msg) {
        const el = document.getElementById('loginError');
        el.textContent = msg;
        el.style.display = 'block';
      }
    </script>
  </body>

  </html>
  <?php
  exit;
}

$accountEmail = $_SESSION['email'] ?? '';
$accountName = $_SESSION['name'] ?? '';
$apiToken = $_SESSION['api_token'] ?? '';
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>API Logs Dashboard</title>

  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f9;
      padding: 20px;
      margin: 0;
    }

    h2 {
      margin-top: 0;
      margin-bottom: 15px;
    }

    .filter-card {
      background: white;
      padding: 16px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.06);
      margin-bottom: 20px;
    }

    .filter-row {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: flex-end;
    }

    .filter-group {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .filter-group label {
      font-size: 13px;
      font-weight: 600;
      color: #34495e;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .filter-group input {
      padding: 8px 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
      box-sizing: border-box;
      outline: none;
      transition: border-color 0.2s;
    }

    .filter-group input:focus {
      border-color: #3498db;
    }

    .chip-container {
      display: inline-flex;
      gap: 3px;
      margin-left: 8px;
    }

    .btn-chip {
      background: #eef2f7;
      color: #2c3e50;
      border: 1px solid #cfd7df;
      padding: 1px 6px;
      border-radius: 4px;
      font-size: 11px;
      font-family: monospace;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.15s, color 0.15s;
    }

    .btn-chip:hover {
      background: #3498db;
      color: white;
      border-color: #2980b9;
    }

    .filter-actions {
      display: flex;
      gap: 8px;
      align-items: flex-end;
    }

    .btn-search {
      background: #27ae60;
      color: white;
      border: none;
      padding: 9px 18px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
      font-size: 14px;
      transition: background 0.2s;
    }

    .btn-search:hover {
      background: #219150;
    }

    .btn-reset {
      background: #95a5a6;
      color: white;
      border: none;
      padding: 9px 14px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.2s;
    }

    .btn-reset:hover {
      background: #7f8c8d;
    }

    .filter-note {
      margin-top: 12px;
      padding-top: 10px;
      border-top: 1px dashed #e2e8f0;
      font-size: 12px;
      color: #64748b;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.06);
    }

    th,
    td {
      padding: 12px 10px;
      border-bottom: 1px solid #eee;
      text-align: left;
    }

    th {
      background: #2c3e50;
      color: white;
      font-size: 14px;
    }

    tr:hover {
      background: #f8fafc;
    }

    .json-box {
      max-height: 40px;
      overflow: hidden;
      cursor: pointer;
      font-family: monospace;
      background: #fafafa;
      padding: 5px;
      border-radius: 5px;
      font-size: 12px;
      word-break: break-all;
    }

    .pagination-bar {
      margin-top: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
      background: white;
      padding: 12px 16px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.06);
    }

    .pagination-nav {
      display: flex;
      gap: 6px;
      align-items: center;
    }

    .pagination-nav button {
      padding: 7px 12px;
      border: none;
      background: #3498db;
      color: white;
      border-radius: 5px;
      cursor: pointer;
      font-size: 13px;
      font-weight: 500;
      transition: background 0.2s;
    }

    .pagination-nav button:hover:not(:disabled) {
      background: #2980b9;
    }

    .pagination-nav button:disabled {
      background: #e2e8f0;
      color: #a0aec0;
      cursor: not-allowed;
    }

    .page-info {
      font-size: 14px;
      color: #2c3e50;
    }

    .pagination-jump {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      color: #34495e;
    }

    .pagination-jump input {
      width: 65px;
      padding: 6px;
      text-align: center;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 14px;
      box-sizing: border-box;
    }

    .btn-jump {
      background: #2c3e50;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
      transition: background 0.2s;
    }

    .btn-jump:hover {
      background: #1a252f;
    }

    #suggestions {
      position: absolute;
      top: 100%;
      left: 0;
      width: 100%;
      max-height: 250px;
      overflow-y: auto;
      background: white;
      border: 1px solid #ccc;
      border-radius: 0 0 6px 6px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      z-index: 100;
      box-sizing: border-box;
    }

    #suggestions div {
      padding: 8px 10px;
      cursor: pointer;
      font-size: 13px;
      line-height: 1.4;
      word-break: break-all;
    }

    #suggestions div:hover {
      background: #edf2f7;
    }
  </style>
</head>

<body>

  <!-- MODAL JSON VIEWER -->
  <div id="modal" style="
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.6);
    justify-content:center;
    align-items:center;
    z-index:999;
  ">
    <div style="
      background:#1e1e1e;
      width:90%;
      height:90%;
      border-radius:10px;
      display:flex;
      flex-direction:column;
      overflow:hidden;
    ">
      <!-- HEADER -->
      <div style="
        padding:10px;
        background:#2c3e50;
        color:white;
        display:flex;
        justify-content:space-between;
        align-items:center;
      ">
        <button onclick="copyModal()"
          style="padding: 5px 10px; background:#3498db; color:white; border:none; border-radius:4px; cursor:pointer;">Copy</button>
        <span>JSON Viewer</span>
        <button onclick="closeModal()"
          style="padding: 5px 10px; background:#e74c3c; color:white; border:none; border-radius:4px; cursor:pointer;">Close</button>
      </div>

      <!-- CONTENT -->
      <div style="
        flex:1;
        overflow:hidden;
        padding:15px;
      ">
        <pre id="modalContent" style="
            color:#00ff90;
            font-family:monospace;
            font-size:13px;
            white-space:pre-wrap;
            word-break:break-word;
            overflow-y:auto;
            overflow-x:hidden;
            height:100%;
            margin:0;
          "></pre>
      </div>
    </div>
  </div>

  <!-- LOADING INDICATOR -->
  <div id="loading" style="
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.3);
    justify-content:center;
    align-items:center;
    z-index:999;
  ">
    <div style="
      background:white;
      padding:20px 30px;
      border-radius:10px;
      font-size:16px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    ">
      ⏳ Loading...
    </div>
  </div>

  <h2 style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
    <span>📊 API Logs Dashboard</span>
    <span style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: normal;">
      <span style="color: #2c3e50;">👤 <?= htmlspecialchars($accountEmail) ?></span>
      <span
        style="display: flex; align-items: center; gap: 6px; background: #eef2f7; border: 1px solid #cfd7df; border-radius: 5px; padding: 4px 8px;">
        <span style="color: #64748b;">mifpro-token:</span>
        <code id="apiToken"
          style="font-family: monospace; color: #2c3e50; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($apiToken) ?></code>
        <button onclick="copyToken()"
          style="padding: 2px 8px; font-size: 11px; background: #34495e; color: white; border: none; border-radius: 4px; cursor: pointer;">Copy</button>
      </span>
      <a href="docs.php"
        style="padding: 6px 14px; background: #3498db; color: white; text-decoration: none; border-radius: 5px;">📖
        Dokumentasi</a>
      <a href="?logout=true"
        style="padding: 6px 14px; background: #e74c3c; color: white; text-decoration: none; border-radius: 5px;">Logout</a>
    </span>
  </h2>

  <!-- FILTER PANEL -->
  <div class="filter-card">
    <!-- Baris 1: Filter Tanggal & API URL -->
    <div class="filter-row">
      <div class="filter-group">
        <label for="startDate">📅 Dari Tanggal</label>
        <input type="date" id="startDate" style="width: 160px;">
      </div>

      <div class="filter-group">
        <label for="endDate">📅 Sampai Tanggal</label>
        <input type="date" id="endDate" style="width: 160px;">
      </div>

      <div class="filter-group" style="position: relative; flex: 1; min-width: 250px;">
        <label for="apiSearch">🔗 API URL</label>
        <input type="text" id="apiSearch" placeholder="Ketik minimal 3 huruf untuk autocomplete..."
          style="width: 100%;">
        <div id="suggestions" style="display: none;"></div>
      </div>
    </div>

    <!-- Baris 2: Search Request & Response dengan Wildcard LIKE % -->
    <div class="filter-row" style="margin-top: 12px;">
      <div class="filter-group" style="flex: 1; min-width: 260px;">
        <label for="requestSearch">
          <span>📥 Search Request (LIKE)</span>
          <span class="chip-container">
            <button type="button" class="btn-chip" onclick="applyWildcard('requestSearch', 'both')"
              title="Bungkus dengan % (%kata%)">%...%</button>
            <button type="button" class="btn-chip" onclick="applyWildcard('requestSearch', 'start')"
              title="Tambah % di depan (%kata)">%...</button>
            <button type="button" class="btn-chip" onclick="applyWildcard('requestSearch', 'end')"
              title="Tambah % di belakang (kata%)">...%</button>
          </span>
        </label>
        <input type="text" id="requestSearch" placeholder="Cth: %user% atau user% atau %user" style="width: 100%;">
      </div>

      <div class="filter-group" style="flex: 1; min-width: 260px;">
        <label for="responseSearch">
          <span>📤 Search Response (LIKE)</span>
          <span class="chip-container">
            <button type="button" class="btn-chip" onclick="applyWildcard('responseSearch', 'both')"
              title="Bungkus dengan % (%kata%)">%...%</button>
            <button type="button" class="btn-chip" onclick="applyWildcard('responseSearch', 'start')"
              title="Tambah % di depan (%kata)">%...</button>
            <button type="button" class="btn-chip" onclick="applyWildcard('responseSearch', 'end')"
              title="Tambah % di belakang (kata%)">...%</button>
          </span>
        </label>
        <input type="text" id="responseSearch" placeholder="Cth: %200% atau %sukses%" style="width: 100%;">
      </div>

      <div class="filter-actions">
        <button class="btn-search" onclick="filterData()">🔍 Cari</button>
        <button class="btn-reset" onclick="resetFilter()">🔄 Reset</button>
      </div>
    </div>

    <div class="filter-note">
      💡 <b>Tips LIKE:</b> Anda dapat mengetik simbol <code>%</code> langsung di form Request / Response (di awal, di
      akhir, atau dua-duanya), atau klik tombol bantuan <code>%...%</code>, <code>%...</code>, <code>...%</code>.
    </div>
  </div>

  <!-- TABEL LOGS -->
  <table>
    <thead>
      <tr>
        <th style="width: 170px;">Tanggal</th>
        <th style="width: 240px;">API</th>
        <th>Request</th>
        <th>Response</th>
      </tr>
    </thead>
    <tbody id="tableBody"></tbody>
  </table>

  <!-- PAGINATION BAR & DIRECT JUMP -->
  <div class="pagination-bar">
    <div class="pagination-nav">
      <button id="btnFirst" onclick="firstPage()" title="Halaman Pertama">« Awal</button>
      <button id="btnPrev" onclick="prevPage()" title="Halaman Sebelumnya">‹ Prev</button>
      <button id="btnNext" onclick="nextPage()" title="Halaman Selanjutnya">Next ›</button>
      <button id="btnLast" onclick="lastPage()" title="Halaman Terakhir">Akhir »</button>
    </div>

    <div id="pageInfo" class="page-info">Halaman 1 / 1</div>

    <div class="pagination-jump">
      <label for="jumpPageInput">Pindah ke Halaman:</label>
      <input type="number" id="jumpPageInput" min="1" value="1">
      <button onclick="jumpToPage()" class="btn-jump">Go</button>
    </div>
  </div>

  <script>
    const API_TOKEN = <?= json_encode($apiToken) ?>;

    function copyToken() {
      navigator.clipboard.writeText(API_TOKEN)
        .then(() => alert('Token berhasil disalin!'))
        .catch(err => console.error(err));
    }

    let currentPage = 1;
    let totalPage = 1;
    const limit = 10;

    // ================= LOAD DATA =================
    async function loadData() {
      showLoading();

      try {
        const startDate = document.getElementById("startDate").value;
        const endDate = document.getElementById("endDate").value;
        const api = document.getElementById("apiSearch").value.trim();
        const requestVal = document.getElementById("requestSearch").value.trim();
        const responseVal = document.getElementById("responseSearch").value.trim();

        const params = new URLSearchParams({
          page: currentPage,
          limit: limit,
          start_date: startDate,
          end_date: endDate,
          api: api,
          request: requestVal,
          response: responseVal
        });

        const res = await fetch(`get_logs.php?${params.toString()}`, {
          headers: { 'mifpro-token': API_TOKEN }
        });
        const result = await res.json();

        totalPage = result.total_page || 1;
        currentPage = result.page || 1;

        renderTable(result.data || []);

        document.getElementById("pageInfo").innerHTML =
          `Halaman <b>${currentPage}</b> dari <b>${totalPage}</b> (Total: <b>${(result.total || 0).toLocaleString()}</b> data)`;

        // Update form lompat halaman
        const jumpInput = document.getElementById("jumpPageInput");
        jumpInput.value = currentPage;
        jumpInput.max = totalPage;

        // Disabled states tombol navigasi
        document.getElementById("btnFirst").disabled = (currentPage <= 1);
        document.getElementById("btnPrev").disabled = (currentPage <= 1);
        document.getElementById("btnNext").disabled = (currentPage >= totalPage);
        document.getElementById("btnLast").disabled = (currentPage >= totalPage);

      } catch (e) {
        console.error(e);
      } finally {
        hideLoading();
      }
    }

    // ================= RENDER =================
    function renderTable(data) {
      const tbody = document.getElementById("tableBody");
      tbody.innerHTML = "";

      if (!data || data.length === 0) {
        tbody.innerHTML = `
      <tr>
        <td colspan="4" style="text-align: center; padding: 30px; color: #7f8c8d;">
          Tidak ada data log yang sesuai dengan filter.
        </td>
      </tr>
    `;
        return;
      }

      data.forEach(log => {
        const tr = document.createElement("tr");

        tr.innerHTML = `
      <td>${log.created_at}</td>
      <td style="word-break: break-all; font-weight: 500;">${log.api_url}</td>
      <td>
        <div class="json-box" onclick="openModal('${encodeURIComponent(log.request_body)}')">
          ${(log.request_body || '').substring(0, 60)}...
        </div>
        <button onclick="copyText('${encodeURIComponent(log.request_body)}')" style="margin-top: 4px; padding: 3px 8px; font-size: 11px; background: #34495e; color: white; border: none; border-radius: 4px; cursor: pointer;">Copy</button>
      </td>
      <td>
        <div class="json-box" onclick="openModal('${encodeURIComponent(log.response_body)}')">
          ${(log.response_body || '').substring(0, 60)}...
        </div>
        <button onclick="copyText('${encodeURIComponent(log.response_body)}')" style="margin-top: 4px; padding: 3px 8px; font-size: 11px; background: #34495e; color: white; border: none; border-radius: 4px; cursor: pointer;">Copy</button>
      </td>
    `;

        tbody.appendChild(tr);
      });
    }

    // ================= FILTER & SEARCH =================
    function filterData() {
      currentPage = 1;
      loadData();
    }

    function resetFilter() {
      document.getElementById("startDate").value = "";
      document.getElementById("endDate").value = "";
      document.getElementById("apiSearch").value = "";
      document.getElementById("requestSearch").value = "";
      document.getElementById("responseSearch").value = "";
      currentPage = 1;
      loadData();
    }

    // Terapkan wildcard % ke input form ("nempel di form")
    function applyWildcard(inputId, mode) {
      const input = document.getElementById(inputId);
      let val = input.value.trim();

      // Hapus % terluar jika sudah ada untuk diganti mode baru
      val = val.replace(/^%+/, '').replace(/%+$/, '');

      if (!val) {
        if (mode === 'both') input.value = '%%';
        else if (mode === 'start') input.value = '%';
        else if (mode === 'end') input.value = '%';
        input.focus();
        if (mode === 'both') {
          input.setSelectionRange(1, 1);
        }
        return;
      }

      if (mode === 'both') {
        input.value = `%${val}%`;
      } else if (mode === 'start') {
        input.value = `%${val}`;
      } else if (mode === 'end') {
        input.value = `${val}%`;
      }

      input.focus();
      filterData();
    }

    // Trigger cari ketika tekan Enter pada input filter
    ["requestSearch", "responseSearch", "apiSearch", "startDate", "endDate"].forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.addEventListener("keydown", (e) => {
          if (e.key === "Enter") {
            filterData();
          }
        });
      }
    });

    // ================= PAGINATION =================
    function nextPage() {
      if (currentPage < totalPage) {
        currentPage++;
        loadData();
      }
    }

    function prevPage() {
      if (currentPage > 1) {
        currentPage--;
        loadData();
      }
    }

    function firstPage() {
      if (currentPage > 1) {
        currentPage = 1;
        loadData();
      }
    }

    function lastPage() {
      if (currentPage < totalPage) {
        currentPage = totalPage;
        loadData();
      }
    }

    // Lompat langsung ke halaman yang diinginkan
    function jumpToPage() {
      const input = document.getElementById("jumpPageInput");
      const target = parseInt(input.value);

      if (isNaN(target) || target < 1) {
        alert("Masukkan nomor halaman yang valid (minimal 1)");
        return;
      }

      if (target > totalPage) {
        alert(`Nomor halaman melebihi total halaman (${totalPage})`);
        return;
      }

      currentPage = target;
      loadData();
    }

    // Tekan Enter pada input lompat halaman
    document.getElementById("jumpPageInput").addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        jumpToPage();
      }
    });

    // ================= AUTOCOMPLETE API =================
    document.getElementById("apiSearch").addEventListener("input", async function () {
      const val = this.value.trim();
      const box = document.getElementById("suggestions");

      // Minimal 3 huruf
      if (val.length < 3) {
        box.innerHTML = "";
        box.style.display = "none";
        return;
      }

      box.style.display = "block";
      box.innerHTML = "<div style='color: #7f8c8d; padding: 8px;'>Memuat rekomendasi...</div>";

      try {
        const res = await fetch(`get_api_list.php?q=${encodeURIComponent(val)}`, {
          headers: { 'mifpro-token': API_TOKEN }
        });
        const data = await res.json();

        box.innerHTML = "";

        if (!data || data.length === 0) {
          box.innerHTML = "<div style='color: #7f8c8d; padding: 8px;'>Tidak ditemukan</div>";
          return;
        }

        data.forEach(api => {
          const div = document.createElement("div");
          div.textContent = api;

          div.onclick = () => {
            document.getElementById("apiSearch").value = api;
            box.innerHTML = "";
            box.style.display = "none";
            filterData();
          };

          box.appendChild(div);
        });

      } catch (e) {
        box.innerHTML = "<div style='color: #e74c3c; padding: 8px;'>Gagal memuat rekomendasi</div>";
      }
    });

    document.addEventListener("click", function (e) {
      const box = document.getElementById("suggestions");
      const input = document.getElementById("apiSearch");

      if (!input.contains(e.target) && !box.contains(e.target)) {
        box.style.display = "none";
      }
    });

    // ================= LOADING =================
    function showLoading() {
      document.getElementById("loading").style.display = "flex";
    }

    function hideLoading() {
      document.getElementById("loading").style.display = "none";
    }

    // ================= MODAL & COPY =================
    function copyText(text) {
      const decoded = decodeURIComponent(text);

      navigator.clipboard.writeText(decoded)
        .then(() => alert("Berhasil disalin ke clipboard!"))
        .catch(err => console.error(err));
    }

    function openModal(text) {
      const decoded = decodeURIComponent(text);

      let formatted;
      try {
        formatted = JSON.stringify(JSON.parse(decoded), null, 2);
      } catch (e) {
        formatted = decoded;
      }

      document.getElementById("modalContent").innerText = formatted;
      document.getElementById("modal").style.display = "flex";
    }

    function closeModal() {
      document.getElementById("modal").style.display = "none";
    }

    function copyModal() {
      const text = document.getElementById("modalContent").innerText;

      navigator.clipboard.writeText(text)
        .then(() => alert("Berhasil menyalin JSON modal!"));
    }

    // ================= INIT =================
    loadData();
  </script>

</body>

</html>