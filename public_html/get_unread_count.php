<?php
require 'cek_admin.php';
require 'koneksi.php';

$unread_query = "SELECT COUNT(*) as count FROM customer_chats WHERE sender_type = 'customer' AND is_read = FALSE";
$result = mysqli_query($conn, $unread_query);
$count = $result ? mysqli_fetch_assoc($result)['count'] : 0;

header('Content-Type: application/json');
echo json_encode(['count' => (int)$count]);
?>