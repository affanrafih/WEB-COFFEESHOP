<?php
require 'config.php';
$q = trim($_GET['q'] ?? '');
$kat = $_GET['kategori'] ?? '';
$urut = $_GET['urut'] ?? 'terbaru';

$daftarKat = ['kopi' => 'Kopi', 'non-kopi' => 'Non-Kopi', 'makanan' => 'Makanan'];

$orderBy = [
  'terbaru' => 'id DESC',
  'harga_asc' => 'harga ASC',
  'harga_desc' => 'harga DESC',
][$urut] ?? 'id DESC';

$where = []; $types = ''; $params = [];
if($q !== '') {
  $where[] = '(nama LIKE ? OR deskripsi LIKE ?)';
  $types .= 'ss';
  $params[] = "%$q%"; 
  $params[] = "%$q%";
}

if(isset($daftarKat[$kat])) {
  $where[] = 'kategori = ?';
  $types .= 's';
  $params[] = $kat;
}

$sql = "SELECT * FROM produk"
  . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
  . " ORDER BY $orderBy";
$produk = db_all($sql, $types, $params);

$flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
include 'inc/header.php';
?>
<h2>Menu Kopipiro</h2>
<?php if ($flash): ?><div class="alert err"><?= e($flash) ?></div><?php endif; ?>
<?php if (is_pembeli()): ?>
  <div class="alert ok">Kamu login, semua harga sudah <b>diskon 10%</b> 🎉</div>
<?php elseif (!is_login()): ?>
  <div class="alert">Harga normal. <a href="login.php">Login</a> / <a href="register.php">register</a> untuk diskon 10%.</div>
<?php endif; ?>

<!-- Form pencarian: method GET supaya pilihan muncul di URL -->
<form method="get" class="filter">
  <input name="q" placeholder="Cari menu..." value="<?= e($q) ?>">
  <select name="kategori">
    <option value="">Semua kategori</option>
    <?php foreach ($daftarKat as $k => $label): ?>
      <option value="<?= $k ?>" <?= $kat === $k ? 'selected' : '' ?>><?= $label ?></option>
    <?php endforeach; ?>
  </select>
  <select name="urut">
    <option value="terbaru"    <?= $urut === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
    <option value="harga_asc"  <?= $urut === 'harga_asc' ? 'selected' : '' ?>>Harga termurah</option>
    <option value="harga_desc" <?= $urut === 'harga_desc' ? 'selected' : '' ?>>Harga termahal</option>
  </select>
  <button>Cari</button>
  <a href="index.php" class="btn" style="background:#999">Reset</a>
</form>

<!-- Form pemesanan membungkus semua kartu (tidak untuk admin) -->
<?php if (!is_admin()): ?><form method="post" action="order.php"><?php endif; ?>
<div class="grid">
<?php foreach ($produk as $p): $final = harga_final($p['harga']); ?>
  <div class="card">
    <img src="uploads/<?= e($p['gambar']) ?>" alt="<?= e($p['nama']) ?>">
    <span class="badge"><?= e($daftarKat[$p['kategori']] ?? $p['kategori']) ?></span>
    <h3 style="margin:6px 0"><?= e($p['nama']) ?></h3>
    <div class="kecil"><?= e(mb_strimwidth((string)($p['deskripsi'] ?? ''), 0, 70, '...')) ?></div>

    <?php if ($final !== (int)$p['harga']): ?>
      <span class="coret"><?= rupiah($p['harga']) ?></span>
      <span class="promo"><?= rupiah($final) ?></span>
    <?php else: ?>
      <b><?= rupiah($p['harga']) ?></b>
    <?php endif; ?>

    <?php if (!is_admin()): ?>
      <br>Jumlah: <input type="number" name="qty[<?= $p['id'] ?>]" min="0" value="0" style="width:60px">
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>
<?php if (!$produk): ?><p>Menu tidak ditemukan.</p><?php endif; ?>

<?php if (!is_admin() && $produk): ?>
  <p>
    <?php if (!is_login()): ?><input name="nama_pemesan" placeholder="Nama pemesan" required><?php endif; ?>
    <button>Pesan Sekarang</button>
  </p>
</form>
<?php endif; ?>
<?php include 'inc/footer.php'; ?>
