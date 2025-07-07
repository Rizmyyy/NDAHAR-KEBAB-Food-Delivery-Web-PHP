<?php
// File: admin_chat_handler.php (untuk handle AJAX requests)
session_start();
include 'koneksi.php';

// Set timezone untuk Indonesia
date_default_timezone_set('Asia/Jakarta');

// Fallback admin session jika belum login (untuk keperluan pengujian)
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['admin_id'] = 1; // fallback sementara
}
$admin_id = $_SESSION['admin_id'];

// Handle AJAX POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // Kirim pesan dari admin ke user
    if ($_POST['action'] === 'send_message') {
        $receiver_id = (int)$_POST['receiver_id'];
        $message = mysqli_real_escape_string($conn, $_POST['message']);
        $timestamp = date('Y-m-d H:i:s');

        $query = "INSERT INTO messages (sender_id, receiver_id, message, timestamp, is_read) 
                  VALUES ($admin_id, $receiver_id, '$message', '$timestamp', 0)";
        if (mysqli_query($conn, $query)) {
            echo json_encode(['status' => 'success', 'timestamp' => $timestamp]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send message']);
        }
        exit;
    }

    // Load pesan antara admin dan user
    if ($_POST['action'] === 'load_messages') {
        $user_id = (int)$_POST['user_id'];

        // Tandai pesan dari user sebagai sudah dibaca
        mysqli_query($conn, "UPDATE messages SET is_read = 1 
                             WHERE sender_id = $user_id AND receiver_id = $admin_id");

        $messages_query = mysqli_query($conn, "
            SELECT m.*, u.username, u.role 
            FROM messages m 
            LEFT JOIN users u ON m.sender_id = u.id 
            WHERE (m.sender_id = $user_id AND m.receiver_id = $admin_id) 
               OR (m.sender_id = $admin_id AND m.receiver_id = $user_id) 
            ORDER BY m.timestamp ASC
        ");

        $messages = [];
        while ($message = mysqli_fetch_assoc($messages_query)) {
            $messages[] = $message;
        }

        echo json_encode($messages);
        exit;
    }

    // Ambil daftar user yang punya riwayat chat dengan admin
    if ($_POST['action'] === 'get_chat_users') {
        $users_query = mysqli_query($conn, "
            SELECT DISTINCT u.id, u.username, u.role,
                   (SELECT message FROM messages m2 
                    WHERE (m2.sender_id = u.id AND m2.receiver_id = $admin_id) 
                       OR (m2.sender_id = $admin_id AND m2.receiver_id = u.id)
                    ORDER BY m2.timestamp DESC LIMIT 1) as last_message,
                   (SELECT timestamp FROM messages m3 
                    WHERE (m3.sender_id = u.id AND m3.receiver_id = $admin_id) 
                       OR (m3.sender_id = $admin_id AND m3.receiver_id = u.id)
                    ORDER BY m3.timestamp DESC LIMIT 1) as last_message_time,
                   (SELECT COUNT(*) FROM messages m4 
                    WHERE m4.sender_id = u.id AND m4.receiver_id = $admin_id 
                    AND m4.is_read = 0) as unread_count
            FROM users u 
            WHERE u.id IN (
                SELECT DISTINCT sender_id FROM messages WHERE receiver_id = $admin_id
                UNION
                SELECT DISTINCT receiver_id FROM messages WHERE sender_id = $admin_id
            ) AND u.role = 'customer'
            ORDER BY last_message_time DESC
        ");

        $users = [];
        while ($user = mysqli_fetch_assoc($users_query)) {
            $users[] = $user;
        }

        echo json_encode($users);
        exit;
    }
}
?>
