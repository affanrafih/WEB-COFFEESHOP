<?php
require 'config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = db_one('SELECT * FROM users WHERE email=?', 's', [trim($_POST['email'] ?? '')]);
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $u['id'], 'nama' => $u['nama'], 'role' => $u['role']];
        redirect($u['role'] === 'admin' ? '/admin/index.php' : '/index.php');
    }
    $error = 'Email atau password salah.';
}
$flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
include 'inc/header.php';
?>
<h2>Login</h2>
<?php if ($flash): ?><div class="alert ok"><?= e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
<form method="post">
  <input name="email" type="email" placeholder="Email" required><br>
  <input name="password" type="password" placeholder="Password" required><br>
  <button>Login</button>
</form>
<p>Belum punya akun? <a href="register.php">Register</a> dan dapat diskon 10%.</p>
<?php include 'inc/footer.php'; ?>
