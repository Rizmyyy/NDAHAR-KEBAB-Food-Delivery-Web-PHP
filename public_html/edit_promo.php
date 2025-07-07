<?php
session_start();
require 'koneksi.php';

header('Content-Type: application/json');

// Ambil kode promo dari JavaScript
$promo_code = isset($_POST['promo_code']) ? strtoupper(trim($_POST['promo_code'])) : '';

if (empty($promo_code)) {
    echo json_encode(['success' => false, 'message' => 'Kode promo tidak boleh kosong.']);
    exit();
}

// Cari kode promo di database
$sql = "SELECT * FROM promos WHERE name = ? AND is_active = TRUE AND CURDATE() BETWEEN start_date AND end_date LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $promo_code);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$promo = mysqli_fetch_assoc($result);

if ($promo) {
    // --- BAGIAN BARU: HITUNG ULANG HARGA ---
    $keranjang = $_SESSION['keranjang'] ?? [];
    
    // Cek dulu apakah minimal pembelian terpenuhi
    $total_harga_asli = 0;
    foreach ($keranjang as $item) {
        $total_harga_asli += (float)($item['harga'] ?? 0) * (int)($item['jumlah'] ?? 0);
    }

    if ($total_harga_asli < $promo['min_purchase']) {
        echo json_encode(['success' => false, 'message' => 'Minimal pembelian untuk promo ini adalah Rp ' . number_format($promo['min_purchase'])]);
        exit();
    }

    // Jika semua valid, simpan promo ke session
    $_SESSION['promo'] = [
        'id' => $promo['id'],
        'name' => $promo['name'],
        'discount' => (int)$promo['discount_percentage']
    ];
    
    // Hitung rincian harga baru untuk dikirim kembali ke JavaScript
    $persen_diskon = (int)$promo['discount_percentage'];
    $info_diskon = $total_harga_asli * ($persen_diskon / 100);
    $total_setelah_diskon = $total_harga_asli - $info_diskon;
    $ongkos_kirim = 15000; // Samakan dengan di keranjang.php
    $total_pembayaran_final = $total_setelah_diskon + $ongkos_kirim;

    // Kirim kembali semua data harga yang baru
    echo json_encode([
        'success' => true, 
        'message' => 'Promo berhasil diterapkan!',
        'promoName' => $promo['name'],
        'discountAmount' => '- Rp ' . number_format($info_diskon),
        'finalTotal' => 'Rp ' . number_format($total_pembayaran_final)
    ]);

} else {
    unset($_SESSION['promo']);
    echo json_encode(['success' => false, 'message' => 'Kode promo tidak valid atau kedaluwarsa.']);
}
?>