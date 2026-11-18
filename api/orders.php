<?php
require __DIR__ . '/_boot.php';

$method = $_SERVER['REQUEST_METHOD'];

// ---------- POST: buat pesanan (tamu atau pembeli login) ----------
if ($method === 'POST') {
    if (is_staff()) json_out(['error' => 'Owner dan kasir tidak membuat pesanan'], 403);

    $d     = body();
    $input = $d['items'] ?? [];
    $nama  = is_login() ? $_SESSION['user']['nama'] : trim($d['nama_pemesan'] ?? '');
    $nama  = mb_substr($nama, 0, 100);
    if ($nama === '') json_out(['error' => 'Nama pemesan wajib diisi.'], 422);
    if (!is_array($input) || !$input) json_out(['error' => 'Item pesanan kosong.'], 422);

    // Validasi item; id yang sama digabung jumlahnya
    $qty = [];
    foreach ($input as $it) {
        $pid = is_array($it) ? (int)($it['id'] ?? 0) : 0;
        $q   = is_array($it) ? (int)($it['qty'] ?? 0) : 0;
        if ($pid <= 0 || $q < 1 || $q > 99)
            json_out(['error' => 'Item tidak valid (id wajib, qty 1-99).'], 422);
        $qty[$pid] = min(99, ($qty[$pid] ?? 0) + $q);
    }

    // Harga SELALU diambil dari database, bukan dari klien
    $in   = implode(',', array_fill(0, count($qty), '?'));
    $rows = db_all("SELECT * FROM produk WHERE id IN ($in)", str_repeat('i', count($qty)), array_keys($qty));
    if (count($rows) !== count($qty)) json_out(['error' => 'Ada produk yang tidak ditemukan.'], 422);

    $total = 0;
    foreach ($rows as $r) $total += harga_final($r['harga']) * $qty[$r['id']];

    // Simpan pesanan + item dalam satu transaksi: sukses semua atau batal semua
    mysqli_begin_transaction($conn);
    try {
        db_exec('INSERT INTO pesanan (user_id,nama_pemesan,diskon_persen,total) VALUES (?,?,?,?)', 'isii',
                [is_pembeli() ? $_SESSION['user']['id'] : null, $nama,
                 is_pembeli() ? (int)round(DISKON * 100) : 0, $total]);
        $oid = mysqli_insert_id($conn);
        foreach ($rows as $r) {
            db_exec('INSERT INTO pesanan_item (pesanan_id,produk_id,nama_produk,harga,qty) VALUES (?,?,?,?,?)',
                    'iisii', [$oid, $r['id'], $r['nama'], harga_final($r['harga']), $qty[$r['id']]]);
        }
        mysqli_commit($conn);
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        throw $e;                       // diteruskan ke penangan error JSON di _boot.php
    }

    $_SESSION['pesanan_saya'][] = $oid; // pembuat boleh melihat pesanannya (termasuk tamu)
    $_SESSION['cart'] = [];             // keranjang dikosongkan
    json_out(['pesanan' => ambil_pesanan($oid)], 201);
}

json_out(['error' => 'Method tidak didukung'], 405);