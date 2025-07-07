<?php
// Pastikan Anda memiliki file koneksi database (misalnya, 'koneksi.php' atau 'db_connect.php')
// Sesuaikan path ini dengan lokasi file koneksi Anda
require_once 'koneksi.php'; // Ganti dengan nama file koneksi database Anda

// Pastikan hanya request GET yang valid dengan ID yang dikirim
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // Ambil ID promo dari URL dan lakukan sanitasi
    $id_promo = mysqli_real_escape_string($conn, $_GET['id']);

    // Query untuk menghapus promo berdasarkan ID
    $sql_delete = "DELETE FROM promos WHERE id = '$id_promo'";

    if (mysqli_query($conn, $sql_delete)) {
        // Jika penghapusan berhasil, arahkan kembali ke halaman promos.php
        // Anda bisa menambahkan parameter 'status' jika ingin menampilkan pesan di promos.php
        header("Location: admin_produk.php?status=deleted_success");
        exit(); // Penting untuk menghentikan eksekusi script setelah redirect
    } else {
        // Jika terjadi kesalahan saat penghapusan, arahkan kembali dengan pesan error
        header("Location: admin_produk.php?status=deleted_error&message=" . urlencode(mysqli_error($conn)));
        exit();
    }
} else {
    // Jika ID tidak ditemukan, arahkan kembali ke halaman promos.php dengan pesan error
    header("Location: admin_produk.php?status=deleted_error&message=" . urlencode("ID promo tidak ditemukan."));
    exit();
}

// Tutup koneksi database
mysqli_close($conn);
?>