<?php

$host = "localhost";
$user = "u780303141_log";
$pass = "AaoJs@2Xj";
$db   = "u780303141_log";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode([
        "status" => false,
        "message" => "Database connection failed"
    ]));
}