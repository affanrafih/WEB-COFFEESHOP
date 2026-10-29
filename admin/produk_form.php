<?php
require '../config.php'; wajib_admin();

$id = (int)($_GET['id'] ?? 0);
$p = ['nama' => '', 'kategori' => 'kopi','deskripsi' => '','harga' => '', 'gambar' => ''];
if ($id) {
    $p = db_one('SELECT * FROM produk WHERE id=?', 'i', [$id]) ?: redirect('/admin/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama'] ?? '');
    $kategori = $_POST['kategori' ?? ''];
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga = (int)($_POST['harga'] ?? 0);
    $gambar = $p['gambar'];

    if ($nama === '' || $harga <= 0 || !in_array($kategori, ['kopi', 'non-kopi', 'makanan'])) $error = 'Nama, kategori wajib diisi dan harga harus lebih dari 0.';

    if (!$error && !empty($_FILES['gambar']['name'])) {
        $f = $_FILES['gambar'];
        $mime = $f['error'] === UPLOAD_ERR_OK ? (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) : '';
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
        if (!$ext || $f['size'] > 2 * 1024 * 1024) {
            $error = 'Gambar harus JPG/PNG/WEBP maksimal 2MB.';
        } else {
            $baru = bin2hex(random_bytes(8)) . '.' . $ext;
            move_uploaded_file($f['tmp_name'], __DIR__ . '/../uploads/' . $baru);
            if ($gambar) @unlink(__DIR__ . '/../uploads/' . $gambar); // hapus gambar lama
            $gambar = $baru;
        }
    } elseif (!$error && !$id) {
        $error = 'Gambar wajib diunggah untuk produk baru.';
    }

    if (!$error) {
        if ($id) db_exec('UPDATE produk SET nama=?,kategori=?,deskripsi=?,harga=?,gambar=? WHERE id=?', 'sssisi', [$nama, $kategori, $deskripsi, $harga, $gambar, $id]);
        else     db_exec('INSERT INTO produk (nama,kategori,deskripsi,harga,gambar) VALUES (?,?,?,?,?)', 'sssis', [$nama, $kategori, $deskripsi, $harga, $gambar]);
        redirect('/admin/index.php');
    }
    $p['nama'] = $nama; $p['harga'] = $harga; $p['kategori'] = $kategori; $p['deskripsi'] = $deskripsi;
}
include '../inc/header.php';
?>
<h2><?= $id ? 'Edit' : 'Tambah' ?> Produk</h2>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
  <input name="nama" placeholder="Nama produk" value="<?= e($p['nama']) ?>" required><br>
  <select name="kategori">
    <?php foreach (['kopi' => "Kopi", 'non-kopi' => "Non-Kopi", 'makanan' => "Makanan (toast, dll)"] as $k => $label): ?>
        <option value="<?= $k ?>" <?= $p['kategori'] === $k ? 'selected' : '' ?>><?= $label ?></option>
    <?php endforeach; ?>    
    </select><br>
    <textarea name="deskripsi" rows="4" placeholder="Deskripsi produk"><?= e($p['deskripsi']) ?></textarea><br>
  <input name="harga" type="number" min="1" placeholder="Harga (Rp)" value="<?= e($p['harga']) ?>" required><br>
  <?php if ($p['gambar']): ?><img src="../uploads/<?= e($p['gambar']) ?>" width="120"><br><?php endif; ?>
  <input type="file" name="gambar" accept="image/*"><br>
  <button>Simpan</button> <a href="index.php">Batal<a>
</form>
<?php include '../inc/footer.php'; ?>
