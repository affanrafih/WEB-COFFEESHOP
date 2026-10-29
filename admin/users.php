<?php
require '../config.php'; wajib_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    // tidak boleh hapus diri sendiri / sesama admin
    db_exec("DELETE FROM users WHERE id=? AND role='pembeli'", 'i', [$id]);
    redirect('/admin/users.php');
}
$users = db_all('SELECT id,nama,email,role,created_at FROM users ORDER BY id DESC');
include '../inc/header.php';
?>
<h2>Daftar User</h2>
<table>
  <tr><th>Nama</th><th>Email</th><th>Role</th><th>Daftar</th><th>Aksi</th></tr>
  <?php foreach ($users as $u): ?>
  <tr>
    <td><?= e($u['nama']) ?></td><td><?= e($u['email']) ?></td>
    <td><?= e($u['role']) ?></td><td><?= e($u['created_at']) ?></td>
    <td>
      <?php if ($u['role'] === 'pembeli'): ?>
      <form method="post" onsubmit="return confirm('Hapus user ini?')">
        <input type="hidden" name="id" value="<?= $u['id'] ?>"><button class="red">Hapus</button>
      </form>
      <?php else: ?>-<?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php include '../inc/footer.php'; ?>
