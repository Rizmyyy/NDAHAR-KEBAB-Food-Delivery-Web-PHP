<?php
session_start();
include 'koneksi.php'; // Pastikan file koneksi.php ada dan berfungsi

// Set timezone untuk Indonesia
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user_id'])) {
    // Jika tidak ada user_id di sesi, tidak ada chat yang perlu dimuat
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$admin_id = 1; // Asumsi ID admin selalu 1. Pastikan ini konsisten.

// Periksa apakah $conn sudah terdefinisi dan merupakan objek mysqli
if (!isset($conn) || $conn->connect_error) {
    // Berikan pesan error di HTML jika koneksi gagal
    echo '<div class="welcome-message text-danger">
              <div class="welcome-icon"><i class="fas fa-exclamation-triangle"></i></div>
              <h3>Error Koneksi Database!</h3>
              <p>Mohon coba lagi nanti atau hubungi support.</p>
          </div>';
    exit;
}

// Get user info
// Gunakan Prepared Statement
$stmt_user = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_info_result = $stmt_user->get_result();
$user_info = $user_info_result->fetch_assoc();
$stmt_user->close();

if (!$user_info) {
    // Jika user_id tidak valid atau tidak ditemukan
    echo '<div class="welcome-message text-danger">
              <div class="welcome-icon"><i class="fas fa-user-times"></i></div>
              <h3>User Tidak Ditemukan!</h3>
              <p>Mohon login ulang.</p>
          </div>';
    exit;
}


// Load messages
// Gunakan Prepared Statement
$stmt_messages = $conn->prepare("
    SELECT m.*, 
           IFNULL(u.username, CONCAT('User#', m.sender_id)) AS username,
           IFNULL(u.role, 'admin') AS role
    FROM messages m 
    LEFT JOIN users u ON m.sender_id = u.id 
    WHERE (m.sender_id = ? AND m.receiver_id = ?) 
       OR (m.sender_id = ? AND m.receiver_id = ?) 
    ORDER BY m.timestamp ASC
");
$stmt_messages->bind_param("iiii", $user_id, $admin_id, $admin_id, $user_id);
$stmt_messages->execute();
$messages_result = $stmt_messages->get_result();

function formatRelativeTime($timestamp) {
    $now = new DateTime();
    $messageTime = new DateTime($timestamp);
    $diff = $now->diff($messageTime);
    
    if ($diff->days > 0) {
        return $messageTime->format('d/m/Y H:i');
    } elseif ($diff->h > 0) {
        return $diff->h . ' jam lalu';
    } elseif ($diff->i > 0) {
        return $diff->i . ' menit lalu';
    } else {
        return 'Baru saja';
    }
}

if ($messages_result->num_rows == 0) {
    echo '<div class="welcome-message">
            <div class="welcome-icon">
                <i class="fas fa-headset"></i>
            </div>
            <h3>Selamat datang di Customer Support!</h3>
            <p>Tim kami siap membantu Anda 24/7. Silakan sampaikan pertanyaan atau keluhan Anda.</p>
          </div>';
} else {
    while ($message = $messages_result->fetch_assoc()) {
        $ismine = ($message['sender_id'] == $user_id);
        // Sender name seharusnya sudah ada di $message['username'] dari LEFT JOIN
        // Jika sender adalah admin (sender_id == $admin_id), gunakan 'Admin'
        $senderName = ($message['sender_id'] == $admin_id) ? 'Admin' : htmlspecialchars($message['username']);
        $avatar = ($message['sender_id'] == $admin_id) ? 'A' : strtoupper(substr($message['username'], 0, 2));
        
        // Format timestamp ke ISO untuk JavaScript
        $messageTime = new DateTime($message['timestamp']);
        $isoTimestamp = $messageTime->format('c'); // ISO 8601 format
        $relativeTime = formatRelativeTime($message['timestamp']);
        
        echo '<div class="message ' . ($ismine ? 'mine' : 'theirs') . '">
                <div class="message-container">
                    <div class="message-avatar">' . $avatar . '</div>
                    <div class="message-content">
                        <div class="message-bubble">' . htmlspecialchars($message['message']) . '</div>
                        <div class="message-time" data-timestamp="' . $isoTimestamp . '">' . $relativeTime . '</div>
                    </div>
                </div>
              </div>';
    }
}
$stmt_messages->close();
?>