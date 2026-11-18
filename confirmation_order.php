<?php
require 'config.php';
$o = ambil_pesanan((int)($_GET['id'] ?? 0));
if (!$o || !boleh_lihat_pesanan($o)) redirect('/index.php');
include 'inc/header.php';
?>
<h2>Pesanan #<?= $o['id'] ?> berhasil dibuat ✅</h2>
<p>
  Atas nama <b><?= e($o['nama_pemesan']) ?></b>
  <?= $o['diskon_persen'] ? '(diskon ' . (int)$o['diskon_persen'] . '% diterapkan)' : '' ?><br>
  Status: <span class="badge"><?= e(label_status($o['status'])) ?></span>
</p>

<table>
  <tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr>
  <?php foreach ($o['item'] as $it): ?>
  <tr>
    <td><?= e($it['nama_produk']) ?></td>
    <td><?= rupiah($it['harga']) ?></td>
    <td><?= $it['qty'] ?></td>
    <td><?= rupiah($it['subtotal']) ?></td>
  </tr>
  <?php endforeach; ?>
  <tr><th colspan="3">Total</th><th><?= rupiah($o['total']) ?></th></tr>
</table>

<p class="kecil">Pesananmu menunggu validasi kasir.</p>
<a class="btn" href="index.php">Pesan lagi</a>
<?php include 'inc/footer.php'; ?>