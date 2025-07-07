<?php
session_start();
require 'koneksi.php';

header('Content-Type: application/json');

$promo_code = isset($_POST['promo_code']) ? strtoupper(trim($_POST['promo_code'])) : '';

if (empty($promo_code)) {
    echo json_encode(['success' => false, 'message' => 'Kode promo tidak boleh kosong.']);
    exit();
}

// Cari promo di database
$sql = "SELECT * FROM promos WHERE name = ? AND is_active = TRUE AND CURDATE() BETWEEN start_date AND end_date LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $promo_code);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$promo = mysqli_fetch_assoc($result);

if ($promo) {
    // Promo valid, simpan ke session
    $_SESSION['promo'] = [
        'id' => $promo['id'],
        'name' => $promo['name'],
        'discount' => (int)$promo['discount_percentage']
    ];

    // --- BAGIAN BARU: HITUNG ULANG HARGA UNTUK DIKIRIM KEMBALI ---
    $keranjang = $_SESSION['keranjang'] ?? [];
    $total_harga_asli = 0;
    foreach ($keranjang as $item) {
        $total_harga_asli += (float)($item['harga'] ?? 0) * (int)($item['jumlah'] ?? 0);
    }
    
    $persen_diskon = (int)$promo['discount_percentage'];
    $info_diskon = $total_harga_asli * ($persen_diskon / 100);
    $total_setelah_diskon = $total_harga_asli - $info_diskon;
    $ongkos_kirim = 15000; // Samakan dengan di keranjang.php
    $total_pembayaran_final = $total_setelah_diskon + $ongkos_kirim;

    // Kirim kembali semua data harga yang baru
    echo json_encode([
        'success' => true, 
        'message' => 'Promo berhasil digunakan!',
        'promo_name' => $promo['name'],
        'discount_amount_formatted' => 'Rp ' . number_format($info_diskon),
        'final_total_formatted' => 'Rp ' . number_format($total_pembayaran_final)
    ]);

} else {
    // Jika promo tidak valid
    unset($_SESSION['promo']);
    echo json_encode(['success' => false, 'message' => 'Kode promo tidak valid atau kedaluwarsa.']);
}
?>
