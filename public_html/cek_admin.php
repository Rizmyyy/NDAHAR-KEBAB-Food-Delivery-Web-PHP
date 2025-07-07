<?php
// File ini akan dipanggil di setiap halaman admin
session_start();

// Cek apakah pengguna sudah login DAN rolenya adalah 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Jika tidak, tendang ke halaman login atau halaman utama
    header('Location: login.php');
    exit();
}
?>