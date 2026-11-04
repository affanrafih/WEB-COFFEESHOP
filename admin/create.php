<?php
require '../config.php';
wajib_admin();

$error = '';
$p = ['nama' => '', 'kategori' => 'kopi', 'deskripsi' => '', 'harga' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p['nama'] = trim($_POST['nama'] ?? '');
    $p['harga'] = (int) ($_POST['harga'] ?? 0);
    $p['kategori'] = $_POST['kategori'] ?? '';
    $p['deskripsi'] = trim($_POST['deskripsi'] ?? '');
    
    if ($p['nama'] === '' || $p['harga'] <= 0 || !isset(KATEGORI[$p['kategori']])) {
        $error = 'Nama, kategori wajib diisi, harga harus lebih dari 0.';
    } elseif (empty($_FILES['gambar']['name'])) {
        $error = 'Gambar wajib diupload.';
    } else {
        $gambar = upload_gambar($_FILES['gambar'], $error);
        if ($gambar !== '') {
            db_exec("INSERT INTO produk (nama,harga,kategori,deskripsi,gambar) VALUES (?,?,?,?,?)",
                    'sisss', [$p['nama'], $p['harga'], $p['kategori'], $p['deskripsi'], $gambar]);
            redirect('/admin/index.php');
        }
    }
}

include '../inc/header.php';
?>
<h2>Tambah Produk</h2>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
    <input name="nama" placeholder="Nama produk" value="<?= e($p['nama']) ?>" required><br>
    <select name="kategori" >
        <?php foreach (KATEGORI as $k => $label): ?>
            <option value="<?= $k ?>" <?= $p['kategori'] === $k ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select><br>
    <textarea name="deskripsi" rows="4" placeholder="Deskripsi"><?= e($p['deskripsi']) ?></textarea><br>
    <input name="harga" type="number" min="1" placeholder="Harga (Rp)" value="<?= e($p['harga']) ?>" required><br>
    <input type="file" name="gambar" accept="image/*" required><br>
    <button>Simpan</button> <a href="index.php">Batal</a>
</form>
<?php include '../inc/footer.php'; ?>