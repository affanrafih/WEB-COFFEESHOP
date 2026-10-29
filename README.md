# Kopi Kita - Aplikasi Pemesanan Kopi (PHP + MySQL)

## Cara menjalankan
1. Install XAMPP/Laragon, start Apache + MySQL.
2. Copy folder `kopi` ke `htdocs` (XAMPP) atau `www` (Laragon).
3. Import `sql/database.sql` lewat phpMyAdmin.
4. Cek `config.php` (user/password MySQL, konstanta BASE).
5. Buka `http://localhost/kopi/setup_admin.php` SEKALI -> admin dibuat, lalu HAPUS file itu.
6. Admin: admin@kopi.test / admin123 -> `http://localhost/kopi/login.php`

## Skenario tes
1. Login admin -> tambah 2-3 kopi (nama, gambar, harga).
2. Logout, buka halaman utama tanpa login -> harga NORMAL, pesan (isi nama).
3. Register akun pembeli, login -> harga tampil DISKON 10%, pesan.
4. Login admin -> menu User -> pembeli terdaftar, coba hapus.
