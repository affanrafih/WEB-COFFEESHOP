<?php
require __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');   // warning PHP tidak boleh merusak JSON

// Semua error tak terduga dikembalikan sebagai JSON
set_exception_handler(function ($e) {
    json_out(['error' => 'Server error', 'detail' => $e->getMessage()], 500);
});

function json_out($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Baca body request: JSON kalau ada, kalau tidak pakai $_POST
function body() {
    $j = json_decode(file_get_contents('php://input'), true);
    return is_array($j) ? $j : $_POST;
}

// Penjaga akses
function api_wajib_login() {
    if (!is_login()) json_out(['error' => 'Harus login'], 401);
}
function api_wajib_admin() {
    api_wajib_login();
    if (!is_admin()) json_out(['error' => 'Hanya owner (admin)'], 403);
}
function api_wajib_staff() {
    api_wajib_login();
    if (!is_staff()) json_out(['error' => 'Hanya owner atau kasir'], 403);
}