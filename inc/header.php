<?php require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kopi Kita</title>
<style>
body{font-family:system-ui,sans-serif;margin:0;background:#faf6f1;color:#3b2a1e}
nav{background:#4b2e1e;padding:12px 24px;display:flex;gap:16px;align-items:center}
nav a{color:#fff;text-decoration:none} nav .sp{flex:1}
main{max-width:960px;margin:24px auto;padding:0 16px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.card{background:#fff;border-radius:10px;padding:12px;box-shadow:0 1px 4px #0002}
.card img{width:100%;height:150px;object-fit:cover;border-radius:8px}
.coret{text-decoration:line-through;color:#999}
.promo{color:#2e7d32;font-weight:600}
.alert{padding:10px 14px;border-radius:8px;margin-bottom:12px}
.err{background:#fde8e8;color:#b00020}.ok{background:#e6f4ea;color:#1b5e20}
input,button{padding:8px;margin:4px 0;font:inherit}
button,.btn{background:#6f4e37;color:#fff;border:0;border-radius:6px;padding:8px 14px;cursor:pointer;text-decoration:none;display:inline-block}
.btn.red,button.red{background:#b00020}
table{width:100%;border-collapse:collapse;background:#fff}
td,th{padding:8px;border-bottom:1px solid #eee;text-align:left}
.badge{display:inline-block;background:#efe3d6;color:#6f4e37;border-radius:12px;padding:2px 10px;font-size:12px}
.kecil{font-size:13px;color:#777}
.filter{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.filter input,.filter select{margin:0}
</style></head><body>
<nav>
  <a href="<?= BASE ?>/index.php"><b>☕ KopiPiro</b></a>
  <?php if (is_admin()): ?>
    <a href="<?= BASE ?>/admin/index.php">Produk</a>
    <a href="<?= BASE ?>/admin/users.php">User</a>
  <?php endif; ?>
  <span class="sp"></span>
  <?php if (is_login()): ?>
    <span style="color:#fff">Halo, <?= e($_SESSION['user']['nama']) ?></span>
    <a href="<?= BASE ?>/logout.php">Logout</a>
  <?php else: ?>
    <a href="<?= BASE ?>/login.php">Login</a>
    <a href="<?= BASE ?>/register.php">Register</a>
  <?php endif; ?>
</nav><main>
