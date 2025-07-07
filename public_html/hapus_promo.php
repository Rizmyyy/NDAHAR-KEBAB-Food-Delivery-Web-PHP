<?php
// 1. Mulai sesi untuk bisa mengakses $_SESSION
session_start();

// 2. Hapus hanya kunci 'promo' dari array session
// Ini akan menghapus promo yang sedang aktif tanpa mengganggu isi keranjang
unset($_SESSION['promo']);

// 3. Kembalikan pengguna ke halaman keranjang
// Mereka akan melihat harga kembali normal (tanpa diskon)
header('Location: keranjang.php');
exit();
?>