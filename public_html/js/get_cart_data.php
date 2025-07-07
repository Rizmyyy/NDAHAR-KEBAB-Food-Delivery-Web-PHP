<?php
session_start();

$total_item = 0;
if (isset($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $item) {
        $total_item += $item['jumlah'];
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'cartCount' => $total_item]);
?>