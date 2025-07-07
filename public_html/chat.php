<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Set timezone untuk Indonesia
date_default_timezone_set('Asia/Jakarta');

$user_id = $_SESSION['user_id'];
$admin_id = 1; // Ganti dengan ID user admin sebenarnya

// Get user info
$user_query = mysqli_query($conn, "SELECT username FROM users WHERE id = $user_id");
$user_info = mysqli_fetch_assoc($user_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg = mysqli_real_escape_string($conn, $_POST['message']);
    $timestamp = date('Y-m-d H:i:s');
    mysqli_query($conn, "INSERT INTO messages (sender_id, receiver_id, message, timestamp) VALUES ($user_id, $admin_id, '$msg', '$timestamp')");
    
    // Return JSON response dengan timestamp
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'timestamp' => $timestamp,
        'local_time' => date('d/m/Y H:i:s')
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat dengan Admin</title>
    <link rel="icon" href="img/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            height: 100vh;
            overflow: hidden;
        }

        .chat-container {
            display: flex;
            height: 100vh;
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        /* Sidebar */
        .sidebar {
            width: 300px;
            background-image: linear-gradient(rgba(7, 0, 109, 0.86), rgba(69, 0, 179, 0.89)), url('img/bg.png');
            color: white;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 20px;
            background-image: linear-gradient(rgba(7, 0, 109, 0.86), rgba(69, 0, 179, 0.89)), url('img/bg.png');
            border-bottom: 1px solid #2c3e50;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-image: linear-gradient(rgb(7, 0, 109), rgba(52, 0, 136, 0.89)), url('img/bg.png');
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }

        .user-info h3 {
            font-size: 16px;
            margin-bottom: 2px;
        }

        .user-info p {
            font-size: 12px;
            color: #bdc3c7;
        }

        .sidebar-content {
            flex: 1;
            padding: 20px;
        }

        .quick-actions {
            margin-bottom: 20px;
        }

        .quick-action {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
            color: #ecf0f1;
            text-decoration: none;
        }

        .quick-action:hover {
            background: rgba(255,255,255,0.1);
        }

        .quick-action.active {
            background: #3498db;
        }

        /* Main Chat Area */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }

        .chat-header {
            padding: 15px 20px;
            background-image: linear-gradient(rgba(7, 0, 109, 0.86), rgba(69, 0, 179, 0.89)), url('img/bg.png');
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e67e22;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .admin-info h3 {
            font-size: 16px;
            margin-bottom: 2px;
        }

        .admin-status {
            font-size: 12px;
            color: #2ecc71;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #2ecc71;
        }

        .chat-body {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: linear-gradient(to bottom, #ecf0f1, #ffffff);
            scrollbar-width: thin;
            scrollbar-color: #bdc3c7 transparent;
        }

        .chat-body::-webkit-scrollbar {
            width: 6px;
        }

        .chat-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-body::-webkit-scrollbar-thumb {
            background: #bdc3c7;
            border-radius: 3px;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            animation: fadeInUp 0.3s ease;
            width: 100%;
        }

        .message.mine {
            justify-content: flex-end;
        }

        .message.theirs {
            justify-content: flex-start;
        }

        .message-container {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            max-width: 70%;
        }

        .message.mine .message-container {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
            flex-shrink: 0;
        }

        .message.mine .message-avatar {
            background: linear-gradient(135deg,rgb(0, 32, 173),rgb(13, 0, 131));
        }

        .message.theirs .message-avatar {
            background: #e67e22;
        }

        .message-content {
            display: flex;
            flex-direction: column;
        }

        .message-bubble {
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            position: relative;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 100%;
        }

        .message.mine .message-bubble {
            background: linear-gradient(135deg,rgb(0, 32, 173),rgb(13, 0, 131));
            color: white;
            border-bottom-right-radius: 5px;
        }

        .message.theirs .message-bubble {
            background: #ffffff;
            color: #333;
            border: 1px solid #e1e8ed;
            border-bottom-left-radius: 5px;
        }

        .message-time {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 4px;
            text-align: right;
        }

        .message.theirs .message-time {
            text-align: left;
        }

        /* Chat Input */
        .chat-input {
            padding: 20px;
            background: white;
            border-top: 1px solid #e1e8ed;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .input-container {
            flex: 1;
            position: relative;
        }

        .message-input {
            width: 100%;
            padding: 12px 50px 12px 20px;
            border: 2px solid #e1e8ed;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
            background: #f8f9fa;
        }

        .message-input:focus {
            border-color: #667eea;
            background: white;
        }

        .emoji-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.3s;
        }

        .emoji-btn:hover {
            opacity: 1;
        }

        .send-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
           background: linear-gradient(135deg,rgb(0, 32, 173),rgb(13, 0, 131));
            border: none;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Typing Indicator */
        .typing-indicator {
            display: none;
            padding: 10px 20px;
            color: #7f8c8d;
            font-style: italic;
            font-size: 14px;
            animation: fadeInUp 0.3s ease;
        }

        .typing-dots {
            display: inline-flex;
            gap: 2px;
        }

        .typing-dot {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #7f8c8d;
            animation: typing 1.4s infinite;
        }

        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        /* Welcome Message */
        .welcome-message {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }

        .welcome-icon {
            font-size: 48px;
            color: #e67e22;
            margin-bottom: 20px;
        }

        .welcome-message h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            
            .chat-container {
                margin: 0;
            }
            
            .message-container {
                max-width: 85%;
            }
            
            .message-bubble {
                font-size: 14px;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes typing {
            0%, 20% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        /* Scrollbar Animation */
        .chat-body {
            scroll-behavior: smooth;
        }

        /* Connection Status */
        .connection-status {
            padding: 8px 16px;
            background: #f39c12;
            color: white;
            text-align: center;
            font-size: 12px;
            display: none;
        }

        .connection-status.offline {
            background: #e74c3c;
            display: block;
        }

        .connection-status.online {
            background: #27ae60;
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to { transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="user-profile">
                    <div class="user-avatar">
                        <?= strtoupper(substr($user_info['username'], 0, 2)) ?>
                    </div>
                    <div class="user-info">
                        <h3><?= htmlspecialchars($user_info['username']) ?></h3>
                        <p>Customer</p>
                    </div>
                </div>
            </div>
            
            <div class="sidebar-content">
                <div class="quick-actions">
                    <a href="#" class="quick-action active">
                        <i class="fas fa-comments"></i>
                        <span>Chat Admin</span>
                    </a>
                    <a href="akun.php" class="quick-action">
                        <i class="fas fa-user"></i>
                        <span>Kembali ke Akun</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-main">
            <div class="connection-status" id="connection-status"></div>
            
            <div class="chat-header">
                <div class="admin-avatar">A</div>
                <div class="admin-info">
                    <h3>Admin NdaharKebab</h3>
                    <div class="admin-status">
                        <div class="status-dot"></div>
                        <span>Online - Siap membantu Anda</span>
                    </div>
                </div>
            </div>

            <div class="chat-body" id="chat-box">
                <div class="welcome-message">
                    <div class="welcome-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Selamat datang di Customer Support!</h3>
                    <p>Tim kami siap membantu Anda 24/7. Silakan sampaikan pertanyaan atau keluhan Anda.</p>
                </div>
            </div>

            <div class="typing-indicator" id="typing-indicator">
                <i class="fas fa-user-tie"></i> Admin sedang mengetik
                <span class="typing-dots">
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                </span>
            </div>

            <div class="chat-input">
                <div class="input-container">
                    <input type="text" 
                           class="message-input" 
                           id="message" 
                           placeholder="Ketik pesan Anda di sini..." 
                           autocomplete="off">
                    <button class="emoji-btn" type="button" title="Emoji">😊</button>
                </div>
                <button class="send-btn" id="send-btn" title="Kirim Pesan">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        let isOnline = navigator.onLine;
        let lastMessageTime = 0;
        const chatBox = document.getElementById('chat-box');
        const messageInput = document.getElementById('message');
        const sendBtn = document.getElementById('send-btn');
        const typingIndicator = document.getElementById('typing-indicator');
        const connectionStatus = document.getElementById('connection-status');

        // Connection status monitoring
        function updateConnectionStatus() {
            if (navigator.onLine) {
                connectionStatus.textContent = 'Terhubung ke server';
                connectionStatus.className = 'connection-status online';
                setTimeout(() => {
                    connectionStatus.style.display = 'none';
                }, 2000);
            } else {
                connectionStatus.textContent = 'Koneksi terputus - Pesan akan dikirim saat online';
                connectionStatus.className = 'connection-status offline';
            }
        }

        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);

        // Function untuk format waktu ke format lokal
        function formatLocalTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);

            if (diffMins < 1) {
                return 'Baru saja';
            } else if (diffMins < 60) {
                return `${diffMins} menit lalu`;
            } else if (diffMins < 1440) { // kurang dari 24 jam
                const diffHours = Math.floor(diffMins / 60);
                return `${diffHours} jam lalu`;
            } else {
                // Format lengkap untuk pesan lama
                return date.toLocaleString('id-ID', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }

        function loadChat() {
            fetch('load_chat.php')
                .then(res => res.text())
                .then(data => {
                    const welcomeMsg = chatBox.querySelector('.welcome-message');
                    if (welcomeMsg && data.trim() !== '') {
                        welcomeMsg.style.display = 'none';
                    }

                    chatBox.innerHTML = data;
                    
                    // Update semua timestamp ke waktu lokal
                    const timeElements = chatBox.querySelectorAll('.message-time');
                    timeElements.forEach(timeEl => {
                        const timestamp = timeEl.getAttribute('data-timestamp');
                        if (timestamp) {
                            timeEl.textContent = formatLocalTime(timestamp);
                        }
                    });

                    chatBox.scrollTop = chatBox.scrollHeight;
                })
                .catch(err => {
                    console.error('Error loading chat:', err);
                });
        }

        function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            // Disable send button temporarily
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch('chat.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'message=' + encodeURIComponent(message)
            })
            .then(res => {
                // Coba parse JSON, jika gagal fallback ke text
                const contentType = res.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return res.json();
                } else {
                    return res.text().then(text => ({ status: 'success', fallback: true }));
                }
            })
            .then(data => {
                if (data.local_time) {
                    console.log('Message sent at:', data.local_time);
                }
                messageInput.value = '';
                loadChat();
                
                // Show typing indicator occasionally
                if (Math.random() > 0.7) {
                    showTypingIndicator();
                }
            })
            .catch(err => {
                console.error('Error sending message:', err);
                messageInput.value = '';
                loadChat();
            })
            .finally(() => {
                // Re-enable send button
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            });
        }

        function showTypingIndicator() {
            typingIndicator.style.display = 'block';
            setTimeout(() => {
                typingIndicator.style.display = 'none';
            }, 2000 + Math.random() * 3000);
        }

        // Update waktu setiap menit
        setInterval(() => {
            const timeElements = chatBox.querySelectorAll('.message-time');
            timeElements.forEach(timeEl => {
                const timestamp = timeEl.getAttribute('data-timestamp');
                if (timestamp) {
                    timeEl.textContent = formatLocalTime(timestamp);
                }
            });
        }, 60000);

        // Event listeners
        sendBtn.addEventListener('click', sendMessage);

        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });

        // Auto-resize input
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });

        // Sound notification (optional)
        function playNotificationSound() {
            // You can add sound notification here
            // const audio = new Audio('notification.mp3');
            // audio.play().catch(e => console.log('Could not play sound'));
        }

        // Auto refresh chat every 3 seconds
        setInterval(loadChat, 3000);
        
        // Initial load
        loadChat();

        // Focus on input when page loads
        window.addEventListener('load', () => {
            messageInput.focus();
        });

        // Prevent form submission on page refresh
        window.addEventListener('beforeunload', function(e) {
            if (messageInput.value.trim()) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html>