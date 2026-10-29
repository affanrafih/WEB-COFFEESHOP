<?php
require 'config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    if ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
        $error = 'Isi nama, email valid, dan password minimal 6 karakter.';
    } else {
        if (db_one('SELECT id FROM users WHERE email=?', 's', [$email])) {
            $error = 'Email sudah terdaftar.';
        } else {
            db_exec("INSERT INTO users (nama,email,password,role) VALUES (?,?,?,'pembeli')",
                    'sss', [$nama, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            $_SESSION['flash'] = 'Registrasi berhasil, silakan login.';
            redirect('/login.php');
        }
    }
}
include 'inc/header.php';
?>
<h2>Register</h2>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
<form method="post">
  <input name="nama" placeholder="Nama" required value="<?= e($_POST['nama'] ?? '') ?>"><br>
  <input name="email" type="email" placeholder="Email" required value="<?= e($_POST['email'] ?? '') ?>"><br>
  <input name="password" type="password" placeholder="Password (min 6)" required><br>
  <button>Daftar</button>
</form>
<?php include 'inc/footer.php'; ?>
