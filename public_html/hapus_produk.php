<?php
// 1. Panggil file keamanan dan koneksi
require 'cek_admin.php';
require 'koneksi.php';

// 2. Cek apakah ada ID yang dikirim melalui URL
if (isset($_GET['id'])) {
    // Ambil dan bersihkan ID
    $id = (int)$_GET['id'];

    // --- LANGKAH PENTING: HAPUS FILE GAMBAR DARI SERVER ---
    
    // 3. Ambil path/lokasi gambar dari database terlebih dahulu
    $sql_select_image = "SELECT image_path FROM products WHERE id = ?";
    $stmt_select = mysqli_prepare($conn, $sql_select_image);
    mysqli_stmt_bind_param($stmt_select, "i", $id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);

    if ($product = mysqli_fetch_assoc($result)) {
        $image_path_to_delete = $product['image_path'];
        
        // 4. Cek apakah file tersebut ada, lalu hapus menggunakan unlink()
        if (file_exists($image_path_to_delete)) {
            unlink($image_path_to_delete);
        }
    }
    mysqli_stmt_close($stmt_select);


    // --- LANGKAH UTAMA: HAPUS DATA DARI DATABASE ---

    // 5. Siapkan query DELETE dengan Prepared Statement
    $sql_delete = "DELETE FROM products WHERE id = ?";
    $stmt_delete = mysqli_prepare($conn, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $id);

    // 6. Eksekusi query dan arahkan kembali ke halaman admin
    if (mysqli_stmt_execute($stmt_delete)) {
        // Jika berhasil, redirect dengan pesan sukses
        header('Location: admin_produk.php?page=products&status=sukses_hapus');
    } else {
        // Jika gagal, redirect dengan pesan gagal
        header('Location: admin_produk.php?page=products&status=gagal_hapus');
    }
    exit();

} else {
    // Jika tidak ada ID di URL, langsung tendang kembali ke halaman admin
    header('Location: admin_produk.php?page=products');
    exit();
}
?>