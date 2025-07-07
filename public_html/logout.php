<?php
// File: logout.php

// Pastikan sesi dimulai sebelum mencoba mengakses atau menghancurkannya
session_start();

// Hapus semua variabel sesi.
// Ini mengosongkan array $_SESSION, menghapus semua data yang tersimpan di sesi.
$_SESSION = array();

// Jika menggunakan cookie sesi (default di PHP), hapus juga cookie sesi dari browser user.
// Ini penting untuk keamanan dan kebersihan sesi.
// session_name() mengembalikan nama sesi saat ini (biasanya 'PHPSESSID').
// time() - 42000 mengatur waktu kadaluarsa cookie di masa lalu, yang membuatnya dihapus.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Terakhir, hancurkan sesi di server.
// Ini akan menghapus file sesi yang sesuai di direktori penyimpanan sesi server.
session_destroy();

// Optional: Hapus juga data dari sessionStorage di browser jika Anda menyimpannya di sana.
// Ini adalah kode JavaScript yang akan dieksekusi di browser setelah PHP selesai.
// Baris ini penting jika Anda memang menggunakan sessionStorage di frontend untuk menyimpan user_id.
echo '<script>';
echo 'sessionStorage.removeItem("user_id");'; // Pastikan nama key sesuai dengan yang Anda gunakan
echo '</script>';

// Lakukan redirect ke halaman login atau halaman utama
// Ini adalah redirect sisi server yang lebih bersih dan efisien daripada redirect JavaScript
// Redirect ini harus dilakukan setelah semua output PHP (termasuk script di atas) selesai.
header('Location: index.php'); // Ganti 'index.php' dengan halaman tujuan setelah logout
exit; // Penting: Hentikan eksekusi script setelah header Location dikirimkan
?>