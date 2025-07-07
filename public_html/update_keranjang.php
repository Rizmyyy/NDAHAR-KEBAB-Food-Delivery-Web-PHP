<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['keranjang'])) {
    $id_produk = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $jumlah_ubah = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;

    if ($id_produk > 0 && isset($_SESSION['keranjang'][$id_produk])) {
        // Jika jumlah diubah jadi banyak (misal -1000), anggap itu hapus
        if ($jumlah_ubah <= -1000) {
            unset($_SESSION['keranjang'][$id_produk]);
        } else {
            $_SESSION['keranjang'][$id_produk]['jumlah'] += $jumlah_ubah;
            // Jangan biarkan jumlah kurang dari 1
            if ($_SESSION['keranjang'][$id_produk]['jumlah'] < 1) {
                $_SESSION['keranjang'][$id_produk]['jumlah'] = 1;
            }
        }
    }

    // Kirim respon sukses agar halaman bisa reload
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
}

header('Content-Type: application/json');
echo json_encode(['success' => false]);
?>