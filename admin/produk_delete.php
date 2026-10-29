<?php
require '../config.php'; wajib_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($row = db_one('SELECT gambar FROM produk WHERE id=?', 'i', [$id])) {
        db_exec('DELETE FROM produk WHERE id=?', 'i', [$id]);
        @unlink(__DIR__ . '/../uploads/' . $row['gambar']);
    }
}
redirect('/admin/index.php');
