<?php
require '../config.php'; wajib_admin();
$produk = db_all('SELECT * FROM produk ORDER BY id DESC');
include '../inc/header.php';
?>
<h2>Kelola Produk</h2>
<a class="btn" href="produk_form.php">+ Tambah Produk</a><br><br>
<table>
  <tr><th>Gambar</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Aksi</th></tr>
  <?php foreach ($produk as $p): ?>
  <tr>
    <td><img src="../uploads/<?= e($p['gambar']) ?>" width="70"></td>
    <td><?= e($p['nama']) ?></td><td><?= e($p['kategori'])?></td><td><?= rupiah($p['harga']) ?></td>
    <td>
      <a class="btn" href="produk_form.php?id=<?= $p['id'] ?>">Edit</a>
      <form method="post" action="produk_delete.php" style="display:inline" onsubmit="return confirm('Hapus produk ini?')">
        <input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="red">Hapus</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php include '../inc/footer.php'; ?>
