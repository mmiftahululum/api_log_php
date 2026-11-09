<?php
// Shim kompatibilitas: konfigurasi kini ada di config/config.php
$config = require __DIR__ . '/config/config.php';

if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', $config['google']['client_id']);
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', $config['google']['client_secret']);
}
if (!defined('TOKEN_HEADER')) {
    define('TOKEN_HEADER', $config['app']['token_header']);
}
