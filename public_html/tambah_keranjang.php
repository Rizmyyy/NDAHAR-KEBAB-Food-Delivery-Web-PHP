<?php
// Fungsi untuk menulis laporan ke file debug_log.txt
function log_message($message) {
    // Mencatat pesan beserta tanggal dan waktu
    file_put_contents('debug_log.txt', date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

// =======================================================
// MULAI PROGRAM UTAMA
// =======================================================

log_message("--- Memulai request ke tambah_keranjang.php ---");

session_start();
log_message("Session dimulai.");

// Cek apakah metodenya POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_message("Request GAGAL: Metode bukan POST.");
    // Kirim respon error dan berhenti
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Metode tidak valid.']);
    exit();
}
log_message("Request adalah POST. Lanjut...");

// Log data yang diterima dari form
log_message("Data POST yang diterima: " . json_encode($_POST));
log_message("Data FILES yang diterima: " . json_encode($_FILES));

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
    log_message("Session keranjang dibuat (masih kosong).");
}

// Ambil data dari JavaScript
$id_produk = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$harga_mentah = $_POST['harga'] ?? 0;
$harga_bersih = (float) filter_var($harga_mentah, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

log_message("Mengecek data produk: ID=" . $id_produk . ", Harga Bersih=" . $harga_bersih);

// Hanya proses jika ID produk dan harga valid
if ($id_produk > 0 && $harga_bersih > 0) {
    log_message("Data produk VALID. Memproses penambahan ke keranjang...");
    
    $nama_produk = $_POST['nama'] ?? 'Produk Tidak Dikenal';
    $jumlah = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 1;
    $image = $_POST['image'] ?? 'img/placeholder.jpg';
    
    // Gunakan ID ANGKA sebagai kunci array
    if (isset($_SESSION['keranjang'][$id_produk])) {
        $_SESSION['keranjang'][$id_produk]['jumlah'] += $jumlah;
        log_message("Produk ID " . $id_produk . " sudah ada. Jumlah diperbarui.");
    } else {
        $_SESSION['keranjang'][$id_produk] = [
            'id'     => $id_produk,
            'nama'   => $nama_produk,
            'harga'  => $harga_bersih,
            'jumlah' => $jumlah,
            'image'  => $image
        ];
        log_message("Produk ID " . $id_produk . " baru ditambahkan ke keranjang.");
    }
    log_message("Isi keranjang saat ini: " . json_encode($_SESSION['keranjang']));

} else {
    log_message("Data produk TIDAK VALID. Proses penambahan dilewati.");
}

// Hitung total item di keranjang untuk dikirim balik ke JavaScript
$total_item = 0;
if (isset($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $item) {
        $total_item += $item['jumlah'];
    }
}
log_message("Total item di keranjang sekarang: " . $total_item);

// Kirim respon JSON ke browser
$response = ['success' => true, 'cartCount' => $total_item];
log_message("Mengirim respon JSON ke browser: " . json_encode($response));
log_message("--- Request selesai ---");

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>