<?php 
session_start(); 
include 'koneksi.php'; // Pastikan file ini berisi koneksi ke database

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil data user dari database
$user_id = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($query);

// Get user's first name for greeting
$first_name = explode(' ', $user['username'])[0];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya</title>
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
            background-image: linear-gradient(rgba(7, 0, 109, 0.7), rgba(68, 0, 179, 0.7)), url('img/bg.png');
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-image: linear-gradient(rgba(7, 0, 109, 0.7), rgba(68, 0, 179, 0.7)), url('img/bg.png');
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .user-info h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .user-info p {
            color: #666;
            font-size: 16px;
        }

        .badge {
            background: #e67e22;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 8px;
            display: inline-block;
        }
/* Sesuaikan styling tombol jika perlu */
.btn-home {
    background: transparent;
    color: #667eea;
    border: 2px solid #667eea;
    padding: 8px 15px; /* Ukuran lebih kecil untuk header */
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-home:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
}
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .card-title {
            font-size: 22px;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: #667eea;
            font-size: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #666;
            font-weight: 500;
        }

        .info-value {
            color: #333;
            font-weight: 600;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 18px 0;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .menu-item:last-child {
            border-bottom: none;
        }

        .menu-item:hover {
            background: #f8f9ff;
            padding-left: 10px;
            border-radius: 8px;
        }

        .menu-item a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 15px;
            width: 100%;
        }

        .menu-item i {
            width: 20px;
            color: #667eea;
            font-size: 18px;
        }

        .menu-item .arrow {
            margin-left: auto;
            color: #ccc;
            font-size: 14px;
        }

        .logout-item {
            color: #e74c3c !important;
        }

        .logout-item i {
            color: #e74c3c !important;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        .quick-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-outline:hover {
            background: #667eea;
            color: white;
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .header {
                flex-direction: column;
                text-align: center;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <div class="avatar">
                <?= strtoupper(substr($first_name, 0, 2)) ?>
            </div>
            <div class="user-info">
                <h1>Selamat datang, <?= htmlspecialchars($first_name) ?>!</h1>
                <p><?= htmlspecialchars($user['email']) ?></p>
                <span class="badge"><?= htmlspecialchars($user['role']) ?></span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Account Information Card -->
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-user"></i>
                    Informasi Akun
                </div>
                
                <div class="info-item">
                    <span class="info-label">Nama Lengkap</span>
                    <span class="info-value"><?= htmlspecialchars($user['username']) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Status Akun</span>
                    <span class="info-value"><?= htmlspecialchars($user['role']) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Bergabung Sejak</span>
                    <span class="info-value">
                        <?= isset($user['created_at']) ? date('d F Y', strtotime($user['created_at'])) : 'N/A' ?>
                    </span>
                </div>

                <div class="quick-actions">
                    <a href="pengaturan_akun.php" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit Profil
                    </a>
                    <a href="ganti_password.php" class="btn btn-outline">
                        <i class="fas fa-lock"></i>
                        Ganti Password
                    </a>
                </div>
            </div>

            <!-- Menu Navigation Card -->
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-th-list"></i>
                    Menu Akun
                </div>
                
                <div class="menu-item">
                    <a href="pengaturan_akun.php">
                        <i class="fas fa-cog"></i>
                        Pengaturan Akun
                        <i class="fas fa-chevron-right arrow"></i>
                    </a>
                </div>
                
                <div class="menu-item">
                    <a href="status_pesanan.php">
                        <i class="fas fa-history"></i>
                        Riwayat Pesanan
                        <i class="fas fa-chevron-right arrow"></i>
                    </a>
                </div>
                
                <div class="menu-item">
                    <a href="chat.php">
                        <i class="fas fa-headset"></i>
                        Hubungi Admin
                        <i class="fas fa-chevron-right arrow"></i>
                    </a>
                </div>
                
                <div class="menu-item">
                    <a href="logout.php" class="logout-item" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                        <i class="fas fa-chevron-right arrow"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">12</div>
                <div class="stat-label">Total Pesanan</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">5</div>
                <div class="stat-label">Menu Favorit</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">Rp 450K</div>
                <div class="stat-label">Total Belanja</div>
            </div>
        </div>
    </div>

    <script>
        // Add smooth scrolling and interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading animation
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Add click animation for menu items
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Add ripple effect
                    const ripple = document.createElement('span');
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.background = 'rgba(102, 126, 234, 0.3)';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.left = (e.clientX - item.offsetLeft) + 'px';
                    ripple.style.top = (e.clientY - item.offsetTop) + 'px';
                    
                    item.style.position = 'relative';
                    item.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });

        // Add CSS animation for ripple effect
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>