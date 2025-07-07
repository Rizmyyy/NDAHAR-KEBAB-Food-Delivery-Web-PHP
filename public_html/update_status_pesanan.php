<?php
require 'cek_admin.php'; // Keamanan: Pastikan hanya admin yang bisa akses
require 'koneksi.php';   // Koneksi ke database ($conn)

// Set header sebagai JSON
header('Content-Type: application/json');

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
    exit();
}

// Ambil data dari JavaScript
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$new_status = isset($_POST['status']) ? $_POST['status'] : '';

// Validasi status untuk keamanan
$allowed_statuses = ['processing', 'completed', 'cancelled'];
if ($order_id > 0 && in_array($new_status, $allowed_statuses)) {

    // Gunakan Prepared Statement untuk UPDATE (Sangat Aman)
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    // Binding parameter (s = string, i = integer)
    mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);

    // Eksekusi query
    if (mysqli_stmt_execute($stmt)) {
        // Jika berhasil, kirim jawaban sukses
        echo json_encode(['success' => true]);
    } else {
        // Jika gagal, kirim jawaban gagal
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status di database.']);
    }
    mysqli_stmt_close($stmt);

} else {
    // Jika data yang dikirim tidak valid
    echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
}

mysqli_close($conn);
?>