<?php
require __DIR__ . '/_boot.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// POST ?action=register
if ($method === 'POST' && $action === 'register') {
    $d     = body();
    $nama  = trim($d['nama'] ?? '');
    $email = trim($d['email'] ?? '');
    $pass  = $d['password'] ?? '';
    if ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6)
        json_out(['error' => 'Isi nama, email valid, dan password minimal 6 karakter.'], 422);
    if (db_one('SELECT id FROM users WHERE email=?', 's', [$email]))
        json_out(['error' => 'Email sudah terdaftar.'], 409);

    db_exec("INSERT INTO users (nama,email,password,role) VALUES (?,?,?,'pembeli')",
            'sss', [$nama, $email, password_hash($pass, PASSWORD_DEFAULT)]);
    json_out(['pesan' => 'Registrasi berhasil'], 201);
}

// POST ?action=login
if ($method === 'POST' && $action === 'login') {
    $d = body();
    $u = db_one('SELECT * FROM users WHERE email=?', 's', [trim($d['email'] ?? '')]);
    if (!$u || !password_verify($d['password'] ?? '', $u['password']))
        json_out(['error' => 'Email atau password salah.'], 401);

    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => $u['id'], 'nama' => $u['nama'], 'role' => $u['role']];
    json_out(['user' => $_SESSION['user']]);
}

// GET ?action=me
if ($method === 'GET' && $action === 'me') {
    json_out(['user' => $_SESSION['user'] ?? null]);
}

// POST ?action=logout
if ($method === 'POST' && $action === 'logout') {
    session_destroy();
    json_out(['pesan' => 'Logout berhasil']);
}

json_out(['error' => 'Endpoint tidak dikenal'], 404);