<?php
session_start();
include "koneksi.php"; // Anda menggunakan $conn dari file ini

// Pastikan request datang dari form, bukan diakses langsung
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// Ambil data dari form
$username = $_POST['username'];
$password = $_POST['password'];

// --- PERUBAHAN 1: KEAMANAN DATABASE DENGAN PREPARED STATEMENTS ---
// Kode Anda sebelumnya rentan terhadap SQL Injection. Ini cara yang aman.
// Kita juga secara spesifik memilih kolom yang dibutuhkan, termasuk 'role'.
$query = "SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1";


// Menyiapkan statement
$stmt = mysqli_prepare($conn, $query);

// Mengikat parameter ke statement
mysqli_stmt_bind_param($stmt, "ss", $username, $username);

// Menjalankan statement
mysqli_stmt_execute($stmt);

// Mengambil hasil
$result = mysqli_stmt_get_result($stmt);

// Cek apakah pengguna ditemukan
if ($result && mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    // Verifikasi password yang diinput dengan hash di database
    if (password_verify($password, $user['password'])) {
        // Jika password cocok...

        // --- PERUBAHAN 2: MENAMBAH KEAMANAN SESSION ---
        // Regenerasi ID Session untuk mencegah serangan Session Fixation
        session_regenerate_id(true);

 // Simpan semua data yang diperlukan ke dalam session
$_SESSION['user'] = $user['username'];
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_nama'] = $user['username']; // atau sesuai kolom yang ada
$_SESSION['user_email'] = $user['email'];    // <-- TAMBAHAN
$_SESSION['user_role'] = $user['role'];
$_SESSION['is_logged_in'] = true;
        // --- PERUBAHAN 3: PENGALIHAN (REDIRECT) BERDASARKAN ROLE ---
        if ($user['role'] === 'admin') {
            // Jika rolenya 'admin', arahkan ke halaman admin
            header("Location: admin_produk.php");
            exit();
        } else {
            // Jika bukan admin (misal 'customer'), arahkan ke halaman utama
            header("Location: index.php");
            exit();
        }
    }
}

// Jika proses di atas gagal (user tidak ada ATAU password salah), jalankan blok ini
$_SESSION['error'] = "Username atau password salah!";
header("Location: login.php");
exit();

?>