<?php
require __DIR__ . '/_boot.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

// Ubah satu baris produk dari database menjadi bentuk JSON
function format_produk($p) {
    $final = harga_final($p['harga']);
    return [
        'id'          => (int)$p['id'],
        'nama'        => $p['nama'],
        'kategori'    => $p['kategori'],
        'deskripsi'   => $p['deskripsi'],
        'harga'       => (int)$p['harga'],   // harga normal
        'harga_final' => $final,             // sudah diskon 10% kalau pembeli login
        'diskon'      => $final !== (int)$p['harga'],
        'gambar_url'  => BASE . '/uploads/' . $p['gambar'],
    ];
}

// ---------- GET: daftar produk, atau satu produk kalau ada ?id= ----------
if ($method === 'GET') {
    if ($id) {
        $p = db_one('SELECT * FROM produk WHERE id=?', 'i', [$id]);
        if (!$p) json_out(['error' => 'Produk tidak ditemukan'], 404);
        json_out(['produk' => format_produk($p)]);
    }

    $where = []; $types = ''; $params = [];
    $q   = trim($_GET['q'] ?? '');
    $kat = $_GET['kategori'] ?? '';
    if ($q !== '') {
        $where[] = '(nama LIKE ? OR deskripsi LIKE ?)';
        $types .= 'ss'; $params[] = "%$q%"; $params[] = "%$q%";
    }
    if (isset(KATEGORI[$kat])) {
        $where[] = 'kategori = ?';
        $types .= 's'; $params[] = $kat;
    }
    $rows = db_all('SELECT * FROM produk'
                 . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
                 . ' ORDER BY id DESC', $types, $params);
    json_out(['produk' => array_map('format_produk', $rows)]);
}

// ---------- POST: tambah produk + upload gambar (khusus admin) ----------
// Dikirim sebagai form-data (bukan JSON) karena ada file
if ($method === 'POST') {
    api_wajib_admin();
    $nama      = trim($_POST['nama'] ?? '');
    $kategori  = $_POST['kategori'] ?? '';
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga     = (int)($_POST['harga'] ?? 0);

    if ($nama === '' || $harga <= 0 || !isset(KATEGORI[$kategori]))
        json_out(['error' => 'Nama, kategori wajib diisi dan harga harus lebih dari 0.'], 422);
    if (empty($_FILES['gambar']['name']))
        json_out(['error' => 'Gambar wajib diunggah.'], 422);

    $error  = '';
    $gambar = upload_gambar($_FILES['gambar'], $error);
    if ($gambar === '') json_out(['error' => $error], 422);

    db_exec('INSERT INTO produk (nama,kategori,deskripsi,harga,gambar) VALUES (?,?,?,?,?)',
            'sssis', [$nama, $kategori, $deskripsi, $harga, $gambar]);
    $baru = db_one('SELECT * FROM produk WHERE id=?', 'i', [mysqli_insert_id($conn)]);
    json_out(['produk' => format_produk($baru)], 201);
}

// ---------- PUT ?id=3: ubah harga (field lain opsional) ----------
if ($method === 'PUT') {
    api_wajib_admin();
    if (!$id) json_out(['error' => 'Sertakan ?id= di URL'], 422);
    $p = db_one('SELECT * FROM produk WHERE id=?', 'i', [$id]);
    if (!$p) json_out(['error' => 'Produk tidak ditemukan'], 404);

    $d = body();
    // field yang tidak dikirim tetap memakai nilai lama
    $nama      = isset($d['nama'])      ? trim($d['nama'])      : $p['nama'];
    $kategori  = $d['kategori']         ?? $p['kategori'];
    $deskripsi = isset($d['deskripsi']) ? trim($d['deskripsi']) : $p['deskripsi'];
    $harga     = isset($d['harga'])     ? (int)$d['harga']      : (int)$p['harga'];

    if ($nama === '' || $harga <= 0 || !isset(KATEGORI[$kategori]))
        json_out(['error' => 'Data tidak valid (harga harus lebih dari 0, kategori harus dikenal).'], 422);

    db_exec('UPDATE produk SET nama=?,kategori=?,deskripsi=?,harga=? WHERE id=?',
            'sssii', [$nama, $kategori, $deskripsi, $harga, $id]);
    json_out(['produk' => format_produk(db_one('SELECT * FROM produk WHERE id=?', 'i', [$id]))]);
}

json_out(['error' => 'Method tidak didukung'], 405);