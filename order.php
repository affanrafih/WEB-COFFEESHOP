<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || is_admin()) redirect('/index.php');

$qty = array_filter(array_map('intval', $_POST['qty'] ?? []), fn($q) => $q > 0);
if (!$qty) { $_SESSION['flash'] = 'Pilih minimal 1 kopi.'; redirect('/index.php'); }

$nama = is_login() ? $_SESSION['user']['nama'] : trim($_POST['nama_pemesan'] ?? '');
if ($nama === '') { $_SESSION['flash'] = 'Nama pemesan wajib diisi.'; redirect('/index.php'); }

// Harga SELALU diambil dari DB (jangan percaya harga dari form)
$in = implode(',', array_fill(0, count($qty), '?'));
$items = db_all("SELECT * FROM produk WHERE id IN ($in)", str_repeat('i', count($qty)), array_keys($qty));

$total = 0;
foreach ($items as &$it) {
    $it['harga_final'] = harga_final($it['harga']);
    $it['qty'] = $qty[$it['id']];
    $total += $it['harga_final'] * $it['qty'];
}
unset($it);

mysqli_begin_transaction($conn);
db_exec('INSERT INTO pesanan (user_id,nama_pemesan,diskon_persen,total) VALUES (?,?,?,?)', 'isii',
        [is_pembeli() ? $_SESSION['user']['id'] : null, $nama, is_pembeli() ? 10 : 0, $total]);
$id = mysqli_insert_id($conn);
foreach ($items as $it) {
    db_exec('INSERT INTO pesanan_item (pesanan_id,produk_id,nama_produk,harga,qty) VALUES (?,?,?,?,?)',
            'iisii', [$id, $it['id'], $it['nama'], $it['harga_final'], $it['qty']]);
}
mysqli_commit($conn);

include 'inc/header.php';
?>
<h2>Pesanan #<?= $id ?> berhasil ✅</h2>
<p>Atas nama: <b><?= e($nama) ?></b> <?= is_pembeli() ? '(diskon 10% diterapkan)' : '' ?></p>
<table>
  <tr><th>Kopi</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr>
  <?php foreach ($items as $it): ?>
  <tr><td><?= e($it['nama']) ?></td><td><?= rupiah($it['harga_final']) ?></td>
      <td><?= $it['qty'] ?></td><td><?= rupiah($it['harga_final'] * $it['qty']) ?></td></tr>
  <?php endforeach; ?>
  <tr><th colspan="3">Total</th><th><?= rupiah($total) ?></th></tr>
</table>
<p><a class="btn" href="index.php">Pesan lagi</a></p>
<?php include 'inc/footer.php'; ?>
