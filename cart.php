<?php
require 'config.php';
if (is_staff()) redirect('/index.php');      // admin & kasir tidak berbelanja

$cart = $_SESSION['cart'] ?? [];             // [produk_id => qty]

// ---- Proses aksi (tambah / ubah / hapus / kosongkan) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $id   = (int)($_POST['id'] ?? 0);

    if ($aksi === 'tambah' && db_one('SELECT id FROM produk WHERE id=?', 'i', [$id])) {
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        $cart[$id] = min(99, ($cart[$id] ?? 0) + $qty);   // sudah ada -> jumlahnya ditambah
    } elseif ($aksi === 'ubah') {
        $qty = (int)($_POST['qty'] ?? 0);
        if ($qty > 0 && isset($cart[$id])) $cart[$id] = min(99, $qty);
        else unset($cart[$id]);                           // jumlah 0 = hapus
    } elseif ($aksi === 'hapus') {
        unset($cart[$id]);
    } elseif ($aksi === 'kosongkan') {
        $cart = [];
    }
    $_SESSION['cart'] = $cart;
    redirect('/cart.php');   // redirect supaya refresh tidak mengulang aksi
}

// ---- Ambil data produk di keranjang dari database ----
$items = []; $total = 0;
if ($cart) {
    $in   = implode(',', array_fill(0, count($cart), '?'));
    $rows = db_all("SELECT * FROM produk WHERE id IN ($in)", str_repeat('i', count($cart)), array_keys($cart));

    // buang produk yang sudah dihapus admin dari keranjang
    $ada = array_column($rows, 'id');
    foreach (array_keys($cart) as $k) if (!in_array($k, $ada)) unset($cart[$k]);
    $_SESSION['cart'] = $cart;

    foreach ($rows as $r) {
        $r['qty']         = $cart[$r['id']];
        $r['harga_final'] = harga_final($r['harga']);      // diskon 10% kalau pembeli login
        $r['subtotal']    = $r['harga_final'] * $r['qty'];
        $total += $r['subtotal'];
        $items[] = $r;
    }
}
include '../inc/header.php';
?>
<h2>Keranjang</h2>

<?php if (!$items): ?>
  <p>Keranjang masih kosong.</p>
  <a class="btn" href="index.php">Lihat menu</a>
<?php else: ?>

  <?php if (is_pembeli()): ?>
    <div class="alert ok">Diskon 10% sudah diterapkan 🎉</div>
  <?php else: ?>
    <div class="alert"><a href="login.php">Login</a> atau <a href="register.php">register</a> untuk diskon 10%.</div>
  <?php endif; ?>

  <table>
    <tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th><th></th></tr>
    <?php foreach ($items as $it): ?>
    <tr>
      <td><img src="uploads/<?= e($it['gambar']) ?>" width="50" style="vertical-align:middle"> <?= e($it['nama']) ?></td>
      <td>
        <?php if ($it['harga_final'] !== (int)$it['harga']): ?>
          <span class="coret"><?= rupiah($it['harga']) ?></span><br><?= rupiah($it['harga_final']) ?>
        <?php else: ?><?= rupiah($it['harga']) ?><?php endif; ?>
      </td>
      <td>
        <form method="post">
          <input type="hidden" name="aksi" value="ubah">
          <input type="hidden" name="id" value="<?= $it['id'] ?>">
          <input type="number" name="qty" min="0" max="99" value="<?= $it['qty'] ?>" style="width:60px">
          <button>Ubah</button>
        </form>
      </td>
      <td><?= rupiah($it['subtotal']) ?></td>
      <td>
        <form method="post">
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="id" value="<?= $it['id'] ?>">
          <button class="red">Hapus</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <tr><th colspan="3">Total</th><th colspan="2"><?= rupiah($total) ?></th></tr>
  </table>
  <br>
  <a class="btn" href="index.php" style="background:#999">Lanjut belanja</a>
  <form method="post" style="display:inline">
    <input type="hidden" name="aksi" value="kosongkan"><button class="red">Kosongkan</button>
  </form>
  <a class="btn" href="checkout.php">Checkout</a>
<?php endif; ?>
<?php include '../inc/footer.php'; ?>