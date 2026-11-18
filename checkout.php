<?php
require 'config.php';
if (is_staff()) redirect('/index.php');

$cart = $_SESSION['cart'] ?? [];
if (!$cart) redirect('/cart.php');

$in   = implode(',', array_fill(0, count($cart), '?'));
$rows = db_all("SELECT * FROM produk WHERE id IN ($in)", str_repeat('i', count($cart)), array_keys($cart));
if (!$rows) redirect('/cart.php');

$items = []; $payload = []; $total = 0;
foreach ($rows as $r) {
    $r['qty']         = $cart[$r['id']];
    $r['harga_final'] = harga_final($r['harga']);
    $r['subtotal']    = $r['harga_final'] * $r['qty'];
    $total += $r['subtotal'];
    $items[]   = $r;
    $payload[] = ['id' => (int)$r['id'], 'qty' => $r['qty']];   // yang dikirim ke API
}
include 'inc/header.php';
?>
<h2>Checkout</h2>

<?php if (is_pembeli()): ?>
  <div class="alert ok">Diskon 10% sudah diterapkan 🎉</div>
<?php else: ?>
  <div class="alert"><a href="login.php">Login</a> untuk diskon 10%, atau lanjut sebagai tamu (harga normal).</div>
<?php endif; ?>

<table>
  <tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr>
  <?php foreach ($items as $it): ?>
  <tr>
    <td><?= e($it['nama']) ?></td>
    <td><?= rupiah($it['harga_final']) ?></td>
    <td><?= $it['qty'] ?></td>
    <td><?= rupiah($it['subtotal']) ?></td>
  </tr>
  <?php endforeach; ?>
  <tr><th colspan="3">Total</th><th><?= rupiah($total) ?></th></tr>
</table>
<br>

<div id="pesan" class="alert err" style="display:none"></div>

<?php if (!is_login()): ?>
  <input id="nama" placeholder="Nama pemesan" maxlength="100"><br>
<?php endif; ?>
<a class="btn" href="cart.php" style="background:#999">Kembali ke keranjang</a>
<button id="btn">Buat Pesanan</button>

<script>
const items   = <?= json_encode($payload) ?>;
const perluNama = <?= is_login() ? 'false' : 'true' ?>;

document.getElementById('btn').addEventListener('click', async () => {
  const btn   = document.getElementById('btn');
  const pesan = document.getElementById('pesan');
  pesan.style.display = 'none';

  const data = { items };
  if (perluNama) data.nama_pemesan = document.getElementById('nama').value.trim();

  btn.disabled = true;
  try {
    const res  = await fetch('api/orders.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const json = await res.json();
    if (res.ok) {
      location.href = 'confirmation_order.php?id=' + json.pesanan.id;
      return;
    }
    pesan.textContent = json.error || 'Gagal membuat pesanan.';
  } catch (err) {
    pesan.textContent = 'Tidak bisa menghubungi server.';
  }
  pesan.style.display = 'block';
  btn.disabled = false;
});
</script>
<?php include 'inc/footer.php'; ?>