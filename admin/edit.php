<?php
require '../config.php';
wajib_admin();

$id = (int)($_GET['id'] ?? 0);
$p = db_one('SELECT * FROM produk WHERE id=?', 'i', [$id]);
if (!$p) redirect('/admin/index.php');          // id tidak ada -> kembali ke daftar

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p['nama']      = trim($_POST['nama'] ?? '');
    $p['kategori']  = $_POST['kategori'] ?? '';
    $p['deskripsi'] = trim($_POST['deskripsi'] ?? '');
    $p['harga']     = (int)($_POST['harga'] ?? 0);
    $gambar = $p['gambar'];                      // default: tetap pakai gambar lama

    if ($p['nama'] === '' || $p['harga'] <= 0 || !isset(KATEGORI[$p['kategori']])) {
        $error = 'Nama, kategori wajib diisi dan harga harus lebih dari 0.';
    } else {
        if (!empty($_FILES['gambar']['name'])) { // ada gambar baru
            $baru = upload_gambar($_FILES['gambar'], $error);
            if ($baru !== '') {
                @unlink(__DIR__ . '/../uploads/' . $gambar);   // hapus gambar lama
                $gambar = $baru;
            }
        }
        if ($error === '') {
            db_exec('UPDATE produk SET nama=?,kategori=?,deskripsi=?,harga=?,gambar=? WHERE id=?',
                    'sssisi', [$p['nama'], $p['kategori'], $p['deskripsi'], $p['harga'], $gambar, $id]);
            redirect('/admin/index.php');
        }
    }
}
include '../inc/header.php';
?>
<h2>Edit Produk</h2>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
  <input name="nama" placeholder="Nama produk" value="<?= e($p['nama']) ?>" required><br>
  <select name="kategori">
    <?php foreach (KATEGORI as $k => $label): ?>
      <option value="<?= $k ?>" <?= $p['kategori'] === $k ? 'selected' : '' ?>><?= $label ?></option>
    <?php endforeach; ?>
  </select><br>
  <textarea name="deskripsi" rows="4" placeholder="Deskripsi produk"><?= e($p['deskripsi']) ?></textarea><br>
  <input name="harga" type="number" min="1" placeholder="Harga (Rp)" value="<?= e($p['harga']) ?>" required><br>
  <img src="../uploads/<?= e($p['gambar']) ?>" width="120"><br>
  <small>Ganti gambar (opsional):</small><br>
  <input type="file" name="gambar" accept="image/*"><br>
  <button>Simpan</button> <a href="index.php">Batal</a>
</form>
<?php include '../inc/footer.php'; ?>