<?php
session_start();

// ===== Koneksi database (mysqli) =====
$host = "localhost";
$user = "root";          // user bawaan XAMPP
$pass = "";              // password bawaan XAMPP (kosong)
$db   = "coffee_shop";
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // error SQL tampil jelas

const BASE   = '/kopi';   // ubah sesuai nama folder di htdocs
const DISKON = 0.10;      // 10%

// ===== Fungsi bantu query (prepared statement, aman dari SQL injection) =====
// $types: s = string, i = integer. Contoh: db_one('SELECT * FROM users WHERE email=?', 's', [$email])
function db_exec($sql, $types = '', $params = []) {
    global $conn;
    $stmt = mysqli_prepare($conn, $sql);
    if ($types !== '') mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    return $stmt;
}
function db_all($sql, $types = '', $params = []) {          // banyak baris
    $res = mysqli_stmt_get_result(db_exec($sql, $types, $params));
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}
function db_one($sql, $types = '', $params = []) {          // satu baris (atau null)
    $res = mysqli_stmt_get_result(db_exec($sql, $types, $params));
    return mysqli_fetch_assoc($res) ?: null;
}

// ===== Fungsi umum =====
function e($s)      { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }
function redirect($p) { header('Location: ' . BASE . $p); exit; }

function is_login()   { return isset($_SESSION['user']); }
function is_admin()   { return is_login() && $_SESSION['user']['role'] === 'admin'; }
function is_pembeli() { return is_login() && $_SESSION['user']['role'] === 'pembeli'; }
function wajib_admin() { if (!is_admin()) redirect('/login.php'); }

// INTI FITUR DISKON: pembeli login -> harga * 0.9, selain itu harga normal
function harga_final($harga) {
    return is_pembeli() ? (int) round($harga * (1 - DISKON)) : (int) $harga;
}
