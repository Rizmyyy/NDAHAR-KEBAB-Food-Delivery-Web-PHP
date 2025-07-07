<?php
// Tampilkan semua error untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'koneksi.php'; // Pastikan koneksi ke DB ($conn)

// 1. Validasi Keamanan & Data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: keranjang.php');
    exit();
}
if (!isset($_SESSION['user_id']) || !isset($_SESSION['order_data'])) {
    header('Location: login.php');
    exit();
}

$order_data = $_SESSION['order_data'];
$user_id = (int)$_SESSION['user_id'];
// Pastikan variabel session login ini ada
$customer_name = $_SESSION['user_nama'] ?? ($_SESSION['user'] ?? 'Guest'); 
$customer_email = $_SESSION['user_email'] ?? 'guest@example.com';


// ====================================================================
// === AWAL BLOK PERHITUNGAN ULANG HARGA (REVISI) ===
// ====================================================================

// 2. Hitung ulang total harga ASLI dari session (Lebih Aman)
$total_harga_asli = 0;
if (!empty($order_data['items'])) {
    foreach ($order_data['items'] as $item) {
        $total_harga_asli += (float)($item['harga'] ?? 0) * (int)($item['jumlah'] ?? 0);
    }
}

// Terapkan diskon promo jika ada di session
$total_final_untuk_db = $total_harga_asli;
$promo_aktif = $_SESSION['promo'] ?? null;

if ($promo_aktif && isset($promo_aktif['discount'])) {
    $persen_diskon = (int)$promo_aktif['discount'];
    $potongan_harga = $total_harga_asli * ($persen_diskon / 100);
    $total_final_untuk_db -= $potongan_harga;
}

// ====================================================================
// === AKHIR BLOK REVISI ===
// ====================================================================


// --- 3. MULAI TRANSAKSI DATABASE ---
mysqli_autocommit($conn, FALSE);
$error_flag = false;

// 4. INSERT ke tabel 'orders' menggunakan total harga yang sudah final
$sql_order = "INSERT INTO orders (user_id, customer_name, customer_email, total_amount, promo_code_used, status) VALUES (?, ?, ?, ?, ?, 'pending')";
$stmt_order = mysqli_prepare($conn, $sql_order);

// FIX: inisialisasi variabel promo_code
$promo_code_to_save = $promo_aktif['kode'] ?? null;

// FIX: Sesuaikan jumlah parameter dengan query (5 parameter)
mysqli_stmt_bind_param($stmt_order, "issds", $user_id, $customer_name, $customer_email, $total_final_untuk_db, $promo_code_to_save);

if (mysqli_stmt_execute($stmt_order)) {
    $new_order_id = mysqli_insert_id($conn);
} else {
    $error_flag = true;
}

// 5. INSERT setiap item ke tabel 'order_items'
if (!$error_flag && !empty($order_data['items'])) {
    $sql_items = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $stmt_items = mysqli_prepare($conn, $sql_items);

    foreach ($order_data['items'] as $product_id => $item) {
        $quantity = isset($item['jumlah']) ? (int)$item['jumlah'] : 0;
        $price = isset($item['harga']) ? (float)$item['harga'] : 0;
        
        mysqli_stmt_bind_param($stmt_items, "iiid", $new_order_id, $product_id, $quantity, $price);
        
        if (!mysqli_stmt_execute($stmt_items)) {
            $error_flag = true;
            break; 
        }
    }
}

// 6. Finalisasi Transaksi
if ($error_flag) {
    mysqli_rollback($conn);
    header('Location: keranjang.php?status=gagal'); 
    exit();
} else {
    mysqli_commit($conn);

    // Kosongkan keranjang & data order sementara setelah berhasil
    unset($_SESSION['keranjang']);
    unset($_SESSION['order_data']);
    unset($_SESSION['promo']); // Hapus juga session promo setelah dipakai
    
    // Arahkan ke halaman status dengan ID pesanan yang ASLI dari database
    header('Location: status_pesanan.php?order_id=' . $new_order_id);
    exit();
}
?>