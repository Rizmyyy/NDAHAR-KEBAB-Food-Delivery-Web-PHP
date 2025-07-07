<?php require 'cek_admin.php'; // Panggil file keamanan di baris pertama ?>
<?php require 'koneksi.php'; // Hubungkan ke database ?>

<?php
// Cek dan buat tabel yang diperlukan jika belum ada
$tables_sql = [
    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255) NOT NULL,
        customer_email VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20),
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS promos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        discount_percentage INT NOT NULL DEFAULT 0,
        min_purchase DECIMAL(10,2) DEFAULT 0,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

// Eksekusi pembuatan tabel
foreach($tables_sql as $sql) {
    mysqli_query($conn, $sql);
}

// Insert sample data jika tabel kosong
$check_orders = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
if(mysqli_fetch_assoc($check_orders)['count'] == 0) {
    $sample_orders = [
        "INSERT INTO orders (customer_name, customer_email, customer_phone, total_amount, status) VALUES 
        ('John Doe', 'john@email.com', '081234567890', 150000, 'completed'),
        ('Jane Smith', 'jane@email.com', '081234567891', 225000, 'pending'),
        ('Bob Wilson', 'bob@email.com', '081234567892', 180000, 'processing'),
        ('Alice Brown', 'alice@email.com', '081234567893', 320000, 'completed')"
    ];
    
    foreach($sample_orders as $sql) {
        mysqli_query($conn, $sql);
    }
}

// Insert sample promos jika tabel kosong
$check_promos = mysqli_query($conn, "SELECT COUNT(*) as count FROM promos");
if(mysqli_fetch_assoc($check_promos)['count'] == 0) {
    $sample_promos = [
        "INSERT INTO promos (name, description, discount_percentage, min_purchase, start_date, end_date) VALUES 
        ('Diskon Akhir Tahun', 'Diskon spesial untuk pembelian akhir tahun', 25, 100000, '2024-12-01', '2024-12-31'),
        ('Promo Ramadan', 'Diskon spesial bulan ramadan', 15, 50000, '2024-03-01', '2024-04-30')"
    ];
    
    foreach($sample_promos as $sql) {
        mysqli_query($conn, $sql);
    }
}

// Ambil data untuk dashboard
$total_products_query = "SELECT COUNT(*) as total FROM products";
$total_products_result = mysqli_query($conn, $total_products_query);
$total_products = $total_products_result ? mysqli_fetch_assoc($total_products_result)['total'] : 0;

$total_orders_query = "SELECT COUNT(*) as total FROM orders";
$total_orders_result = mysqli_query($conn, $total_orders_query);
$total_orders = $total_orders_result ? mysqli_fetch_assoc($total_orders_result)['total'] : 0;

$total_revenue_query = "SELECT SUM(total_amount) as revenue FROM orders WHERE status = 'completed'";
$total_revenue_result = mysqli_query($conn, $total_revenue_query);
$total_revenue = $total_revenue_result ? (mysqli_fetch_assoc($total_revenue_result)['revenue'] ?? 0) : 0;

// --- Data untuk grafik (7 HARI TERAKHIR) - Versi Efisien ---
$daily_sales_labels = [];
$daily_sales_data = [];
$sales_map = [];

// Siapkan 7 hari terakhir sebagai default dengan penjualan 0
for ($i = 6; $i >= 0; $i--) {
    $date_key = date('Y-m-d', strtotime("-$i days"));
    $label = date('d M', strtotime($date_key));
    $daily_sales_labels[] = $label;
    $sales_map[$date_key] = 0; // Default penjualan adalah 0
}

// Hanya 1x query untuk mengambil data penjualan 7 hari terakhir
$sql_chart = "SELECT DATE(created_at) as sales_date, SUM(total_amount) as daily_sales 
              FROM orders 
              WHERE created_at >= CURDATE() - INTERVAL 7 DAY
              GROUP BY DATE(created_at)";

$result_chart = mysqli_query($conn, $sql_chart);

if ($result_chart) {
    while ($row = mysqli_fetch_assoc($result_chart)) {
        // Timpa nilai 0 dengan data penjualan dari database jika ada
        $sales_map[$row['sales_date']] = (float)$row['daily_sales'];
    }
}

// Ubah map menjadi array data final untuk grafik
$daily_sales_data = array_values($sales_map);

// Ambil halaman yang aktif
$page = $_GET['page'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Admin</title>
    <link rel="icon" href="img/logo.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --light-bg: #f8f9fc;
            --sidebar-bg: #ffffff;
            --text-muted: #5a5c69;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
             background-image: linear-gradient(rgba(7, 0, 109, 0.7), rgba(68, 0, 179, 0.7)), url('img/bg.png');
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 280px;
            background: var(--sidebar-bg);
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 25px 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
        }

        .sidebar-header h4 {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .sidebar-header small {
            opacity: 0.8;
        }

        .sidebar-menu {
            padding: 0;
            list-style: none;
            margin-top: 20px;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: #f8f9fc;
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            transform: translateX(5px);
        }

        .sidebar-menu i {
            width: 20px;
            margin-right: 15px;
            font-size: 16px;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .topbar {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .content-area {
            padding: 30px;
        }

        /* Dashboard Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .stat-card.success::before {
            background: var(--success-color);
        }

        .stat-card.warning::before {
            background: var(--warning-color);
        }

        .stat-card.info::before {
            background: var(--info-color);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }

        .stat-icon.primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .stat-icon.success {
            background: var(--success-color);
        }

        .stat-icon.warning {
            background: var(--warning-color);
        }

        .stat-icon.info {
            background: var(--info-color);
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-change {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 20px;
            background: #e8f5e8;
            color: var(--success-color);
        }

        /* Chart Container - FIXED */
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 40px;
            position: relative;
            min-height: 400px;
            max-height: 450px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            height: auto;
        }

        .chart-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        /* Canvas container to ensure proper sizing */
        .chart-canvas-container {
            position: relative;
            height: 320px;
            width: 100%;
            margin-bottom: 10px;
        }

        #salesChart {
            max-height: 320px !important;
        }

        /* Table Styles */
        .modern-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-top: 20px;
        }

        .table {
            margin: 0;
        }

        .table thead th {
            background: #f8f9fc;
            border: none;
            padding: 15px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .table tbody td {
            padding: 15px;
            border-top: 1px solid #eee;
            vertical-align: middle;
        }

        /* Form Styles */
        .modern-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .form-control {
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn {
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }

            .chart-container {
                min-height: 350px;
                max-height: 400px;
            }

            .chart-canvas-container {
                height: 280px;
            }

            #salesChart {
                max-height: 280px !important;
            }
        }

        /* Page Content Styles */
        .page-content {
            display: none;
        }

        .page-content.active {
            display: block;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .chat-admin-container {
    display: flex;
    height: 600px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.chat-sidebar {
    width: 320px;
    background: #f8f9fa;
    border-right: 1px solid #dee2e6;
    display: flex;
    flex-direction: column;
}

.chat-sidebar-header {
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-sidebar-header h5 {
    margin: 0;
    font-size: 16px;
}

.chat-refresh-btn {
    cursor: pointer;
    padding: 5px;
    border-radius: 3px;
    transition: background 0.3s;
}

.chat-refresh-btn:hover {
    background: rgba(255,255,255,0.2);
}

.chat-users-list {
    flex: 1;
    overflow-y: auto;
    padding: 10px 0;
}

.loading-users {
    text-align: center;
    padding: 20px;
    color: #6c757d;
}

.chat-user-item {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background 0.3s;
    position: relative;
}

.chat-user-item:hover {
    background: #e9ecef;
}

.chat-user-item.active {
    background: #667eea;
    color: white;
}

.chat-user-item.active .text-muted {
    color: rgba(255,255,255,0.8) !important;
}

.chat-user-info-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-user-avatar-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 14px;
}

.chat-user-item.active .chat-user-avatar-small {
    background: rgba(255,255,255,0.2);
}

.chat-user-details-small h6 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
}

.chat-user-details-small small {
    font-size: 12px;
    opacity: 0.8;
}

.unread-badge {
    position: absolute;
    top: 10px;
    right: 15px;
    background: #dc3545;
    color: white;
    border-radius: 10px;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: bold;
}

.chat-user-item.active .unread-badge {
    background: rgba(255,255,255,0.9);
    color: #667eea;
}

.chat-main-area {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.chat-welcome {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    color: #6c757d;
    padding: 40px;
}

.chat-welcome-icon {
    font-size: 64px;
    color: #667eea;
    margin-bottom: 20px;
}

.chat-welcome h4 {
    margin-bottom: 10px;
    color: #495057;
}


.chat-area {
    display: flex;
    flex-direction: column; /* Agar header, messages, dan input tersusun vertikal */
    flex-grow: 1; /* Biarkan chat-area mengisi ruang yang tersedia */
    overflow: hidden; /* Penting untuk mencegah scrollbar ganda atau menutupi */
}

.chat-header {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.chat-user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 16px;
}

.chat-user-details h6 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: linear-gradient(to bottom, #f8f9fa, #ffffff);
}

.message-item {
    margin-bottom: 15px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.message-item.admin {
    justify-content: flex-end;
}

.message-item.customer {
    justify-content: flex-start;
}

.message-container {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    max-width: 70%;
}

.message-item.admin .message-container {
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

.message-item.admin .message-avatar {
    background: #667eea;
}

.message-item.customer .message-avatar {
    background: #28a745;
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

.message-item.admin .message-bubble {
    background: #667eea;
    color: white;
    border-bottom-right-radius: 5px;
}

.message-item.customer .message-bubble {
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

.message-item.customer .message-time {
    text-align: left;
}

.chat-input-area {
    padding: 20px;
    background: white;
    border-top: 1px solid #dee2e6;
}

.chat-input-area .input-group {
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.chat-input-area .form-control {
    border: none;
    padding: 12px 20px;
    font-size: 14px;
}

.chat-input-area .form-control:focus {
    box-shadow: none;
}

.chat-input-area .btn {
    border: none;
    padding: 12px 20px;
    background: #667eea;
}

.chat-input-area .btn:hover {
    background: #5a6fd8;
}

/* Responsive */
@media (max-width: 768px) {
    .chat-admin-container {
        flex-direction: column;
        height: auto;
    }
    
    .chat-sidebar {
        width: 100%;
        max-height: 200px;
    }
    
    .chat-main-area {
        min-height: 400px;
    }
}

/* Animation */
.message-item {
    animation: fadeInUp 0.3s ease;
}

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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-store"></i> Admin Panel</h4>
            <small>Kelola Toko Online</small>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" onclick="showPage('dashboard')" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a></li>
            <li><a href="#" onclick="showPage('products')" class="<?php echo $page == 'products' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Kelola Produk
            </a></li>
            <li><a href="#" onclick="showPage('orders')" class="<?php echo $page == 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Kelola Pesanan
            </a></li>
            <li><a href="#" onclick="showPage('promos')" class="<?php echo $page == 'promos' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i> Kelola Promo
            </a></li>
            <li><a href="#" onclick="showPage('chat')" class="<?php echo $page == 'chat' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i> Customer Service
            </a></li>
            <li><a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="page-title" id="pageTitle">Dashboard</div>
            <div class="user-info">
                <span>Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong></span>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['user'], 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <?php
            if (isset($_GET['status'])) {
                $status = $_GET['status'];
                $pesan = '';
                $tipe_alert = 'success'; // Tipe default adalah sukses

                switch ($status) {
                    case 'sukses_tambah':
                        $pesan = 'Produk baru berhasil ditambahkan!';
                        break;
                    case 'sukses_update':
                        $pesan = 'Data produk berhasil diperbarui!';
                        break;
                    case 'sukses_hapus':
                        $pesan = 'Produk berhasil dihapus!';
                        break;
                    case 'gagal_upload':
                        $pesan = 'Terjadi kesalahan saat mengupload gambar.';
                        $tipe_alert = 'danger';
                        break;
                    case 'gagal_db':
                        $pesan = 'Terjadi kesalahan pada database. Gagal memproses data.';
                        $tipe_alert = 'danger';
                        break;
                    // Anda bisa tambahkan case lain jika perlu
                }

                if ($pesan) {
                    echo "<div class='alert alert-{$tipe_alert} alert-dismissible fade show' role='alert'>
                            {$pesan}
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                          </div>";
                }
            }
            ?>
            <!-- Dashboard Page -->
            <div id="dashboard" class="page-content active">
                <!-- Stats Cards -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon primary">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                        <div class="stat-label">Total Produk</div>
                        <div class="stat-value"><?php echo $total_products; ?></div>
                        <div class="stat-change">+12% dari bulan lalu</div>
                    </div>

                    <div class="stat-card success">
                        <div class="stat-header">
                            <div class="stat-icon success">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <div class="stat-label">Total Pesanan</div>
                        <div class="stat-value"><?php echo $total_orders; ?></div>
                        <div class="stat-change">+8% dari bulan lalu</div>
                    </div>

                    <div class="stat-card warning">
                        <div class="stat-header">
                            <div class="stat-icon warning">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="stat-label">Total Pendapatan</div>
                        <div class="stat-value">Rp <?php echo number_format($total_revenue); ?></div>
                        <div class="stat-change">+15% dari bulan lalu</div>
                    </div>

                    <div class="stat-card info">
                        <div class="stat-header">
                            <div class="stat-icon info">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-label">Pelanggan Aktif</div>
                        <div class="stat-value">156</div>
                        <div class="stat-change">+5% dari bulan lalu</div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="chart-container">
                    <div class="chart-header">
                        <div class="chart-title" id="chartTitleText">Tren Penjualan (7 Hari Terakhir)</div>
                    </div>
                    <div class="chart-canvas-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="modern-table">
                    <div style="padding: 20px 25px; border-bottom: 1px solid #eee;">
                        <h5 class="mb-0">Pesanan Terbaru</h5>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent_orders_query = "SELECT o.*, DATE_FORMAT(o.created_at, '%d %b %Y') as formatted_date 
                                                  FROM orders o 
                                                  ORDER BY o.created_at DESC 
                                                  LIMIT 5";
                            $recent_orders_result = mysqli_query($conn, $recent_orders_query);
                            
                            if($recent_orders_result && mysqli_num_rows($recent_orders_result) > 0) {
                                while($order = mysqli_fetch_assoc($recent_orders_result)) {
                                    $status_class = '';
                                    $status_text = '';
                                    switch($order['status']) {
                                        case 'completed':
                                            $status_class = 'status-completed';
                                            $status_text = 'Selesai';
                                            break;
                                        case 'processing':
                                            $status_class = 'status-pending';
                                            $status_text = 'Diproses';
                                            break;
                                        case 'pending':
                                            $status_class = 'status-pending';
                                            $status_text = 'Pending';
                                            break;
                                        case 'cancelled':
                                            $status_class = 'status-cancelled';
                                            $status_text = 'Dibatal';
                                            break;
                                    }
                            ?>
                            <tr>
                                <td>#ORD<?php echo str_pad($order['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td>Rp <?php echo number_format($order['total_amount']); ?></td>
                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                                <td><?php echo $order['formatted_date']; ?></td>
                            </tr>
                            <?php 
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada pesanan</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Products Page -->
            <div id="products" class="page-content">
                
                <!-- Add Product Form -->
                <div class="modern-form mb-4">
    <h5 class="mb-3">Tambah Produk Baru</h5>
    <form action="proses_produk.php" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nama Produk</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Harga Jual</label>
                <input type="number" name="price" class="form-control" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Harga Asli (coret)</label>
                <input type="number" name="original_price" class="form-control">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Gambar Produk</label>
            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)" required>
            <!-- Tambahkan tempat preview -->
            <div class="image-preview-container mt-2"></div>
        </div>

        <button type="submit" name="tambah" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Produk
        </button>
    </form>
</div>

                <!-- Products Table -->
                <div class="modern-table">
                    <div style="padding: 20px 25px; border-bottom: 1px solid #eee;">
                        <h5 class="mb-0">Daftar Produk</h5>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Gambar</th>
                                <th>Nama Produk</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT id, name, price, image_path FROM products";
                            $result = mysqli_query($conn, $sql);
                            while ($product = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><img src="<?php echo htmlspecialchars($product['image_path']); ?>" class="product-image"></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>Rp <?php echo number_format($product['price']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_produk.php?id=<?php echo $product['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="hapus_produk.php?id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Orders Page -->
            <<div id="orders" class="page-content">
  
  <div class="modern-table">
    <div style="padding: 20px 25px; border-bottom: 1px solid #eee;">
      <h5 class="mb-0">Daftar Pesanan</h5>
    </div>
    <table class="table">
      <thead>
        <tr>
          <th>ID Pesanan</th>
          <th>Pelanggan</th>
          <th>Produk</th>
          <th>Total</th>
          <th>Status</th>
          <th>Bukti Bayar</th> <!-- Kolom baru -->
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $orders_query = "SELECT o.*, DATE_FORMAT(o.created_at, '%d %b %Y') as formatted_date 
                         FROM orders o 
                         ORDER BY o.created_at DESC";
        $orders_result = mysqli_query($conn, $orders_query);
        
        if($orders_result && mysqli_num_rows($orders_result) > 0) {
            while($order = mysqli_fetch_assoc($orders_result)) {
                $status_class = '';
                $status_text = '';
                switch($order['status']) {
                    case 'completed':
                        $status_class = 'status-completed';
                        $status_text = 'Selesai';
                        break;
                    case 'processing':
                        $status_class = 'status-pending';
                        $status_text = 'Diproses';
                        break;
                    case 'pending':
                        $status_class = 'status-pending';
                        $status_text = 'Pending';
                        break;
                    case 'cancelled':
                        $status_class = 'status-cancelled';
                        $status_text = 'Dibatalkan';
                        break;
                }

                // Ambil detail produk
                $order_items_query = "SELECT oi.*, p.name as product_name 
                                      FROM order_items oi 
                                      JOIN products p ON oi.product_id = p.id 
                                      WHERE oi.order_id = ?";
                $stmt_items = mysqli_prepare($conn, $order_items_query);
                mysqli_stmt_bind_param($stmt_items, "i", $order['id']);
                mysqli_stmt_execute($stmt_items);
                $order_items_result = mysqli_stmt_get_result($stmt_items);
                $products_list = [];

                if($order_items_result) {
                    while($item = mysqli_fetch_assoc($order_items_result)) {
                        $products_list[] = $item['product_name'] . ' x ' . $item['quantity'];
                    }
                }

                $products_text = !empty($products_list) ? implode(', ', $products_list) : 'Detail produk tidak tersedia';

                // Cek bukti bayar
                $bukti_path = !empty($order['bukti_bayar']) ? 'uploads/' . $order['bukti_bayar'] : '';
        ?>
        <tr>
          <td>#ORD<?php echo str_pad($order['id'], 3, '0', STR_PAD_LEFT); ?></td>
          <td>
            <?php echo htmlspecialchars($order['customer_name']); ?><br>
            <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
          </td>
          <td><?php echo htmlspecialchars(substr($products_text, 0, 50)) . (strlen($products_text) > 50 ? '...' : ''); ?></td>
          <td>Rp <?php echo number_format($order['total_amount']); ?></td>
          <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
          
          <!-- ✅ Kolom Bukti Bayar -->
          <td>
<?php if (!empty($order['bukti_bayar'])): ?>
  <a href="uploads/<?php echo htmlspecialchars($order['bukti_bayar']); ?>" target="_blank">
    <img src="uploads/<?php echo htmlspecialchars($order['bukti_bayar']); ?>" width="50">
  </a>
<?php else: ?>
  <span class="text-muted">Belum diunggah</span>
<?php endif; ?>
</td>

          <td><?php echo $order['formatted_date']; ?></td>
          <td>
            <div class="action-buttons">
              <?php if($order['status'] == 'pending') { ?>
              <button class="btn btn-success btn-sm" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'processing')">
                <i class="fas fa-check"></i> Konfirmasi
              </button>
              <?php } ?>
              <?php if($order['status'] != 'cancelled' && $order['status'] != 'completed') { ?>
              <button class="btn btn-danger btn-sm" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'cancelled')">
                <i class="fas fa-times"></i> Batal
              </button>
              <?php } ?>
              <?php if($order['status'] == 'processing') { ?>
              <button class="btn btn-info btn-sm" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'completed')">
                <i class="fas fa-check-circle"></i> Selesai
              </button>
              <?php } ?>
            </div>
          </td>
        </tr>
        <?php 
            }
        } else {
        ?>
        <tr>
          <td colspan="8" class="text-center text-muted">Belum ada pesanan</td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

            <!-- Promos Page -->
            <div id="promos" class="page-content">
    <div class="section-title">Kelola Promo</div>
    
    <div class="modern-form mb-4">
        <h5 class="mb-3">Tambah Promo Baru</h5>
        <form action="proses_promo.php" method="POST">
            <input type="hidden" name="action" value="tambah_promo">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama/Kode Promo (Harus Unik)</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: DISKONBARU" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Diskon (%)</label>
                    <input type="number" name="discount_percentage" class="form-control" min="1" max="100" placeholder="Contoh: 15" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Min. Pembelian (Rp)</label>
                    <input type="number" name="min_purchase" class="form-control" placeholder="Contoh: 50000" value="0">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Berakhir</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi Promo</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" name="tambah_promo" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Promo
            </button>
        </form>
    </div>

    <div class="modern-table">
        <div style="padding: 20px 25px; border-bottom: 1px solid #eee;">
            <h5 class="mb-0">Daftar Promo</h5>
        </div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Kode Promo</th>
                    <th>Diskon</th>
                    <th>Min. Pembelian</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $promo_query = "SELECT * FROM promos ORDER BY end_date DESC";
                $promo_result = mysqli_query($conn, $promo_query);
                if (mysqli_num_rows($promo_result) > 0) {
                    while($promo = mysqli_fetch_assoc($promo_result)) {
                        $is_active_now = (strtotime($promo['start_date']) <= time() && strtotime($promo['end_date']) >= time() && $promo['is_active']);
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($promo['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($promo['discount_percentage']); ?>%</td>
                    <td>Rp <?php echo number_format($promo['min_purchase']); ?></td>
                    <td><?php echo date('d M Y', strtotime($promo['start_date'])) . ' - ' . date('d M Y', strtotime($promo['end_date'])); ?></td>
                    <td>
                        <?php if($is_active_now): ?>
                            <span class="status-badge status-completed">Aktif</span>
                        <?php else: ?>
                            <span class="status-badge status-cancelled">Tidak Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="edit_promo_admin.php?id=<?php echo $promo['id']; ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="hapus_promo_admin.php?id=<?php echo $promo['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin ingin menghapus promo ini?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center text-muted'>Belum ada promo yang dibuat.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chat Admin Section - Insert this after the promos div -->
<div id="chat" class="page-content">
    
    <div class="chat-admin-container">
        <div class="chat-sidebar">
            <div class="chat-sidebar-header">
                <h5><i class="fas fa-comments"></i> Customer Chats</h5>
                <span class="chat-refresh-btn" onclick="loadChatUsers()">
                    <i class="fas fa-sync-alt"></i>
                </span>
            </div>

            <!-- Tombol kirim pesan baru ke customer -->
            <button class="btn btn-sm btn-outline-primary w-100 m-2" onclick="openSendNewMessageModal()">
                <i class="fas fa-user-plus"></i> Kirim ke Customer Baru
            </button>

            <div class="chat-users-list" id="chatUsersList">
                <div class="loading-users">
                    <i class="fas fa-spinner fa-spin"></i> Loading chats...
                </div>
            </div>
        </div>
        
        <div class="chat-main-area">
            <div class="chat-welcome" id="chatWelcome">
                <div class="chat-welcome-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h4>Customer Support Dashboard</h4>
                <p>Pilih customer dari sidebar untuk memulai atau melanjutkan percakapan</p>
            </div>
            
            <div class="chat-area" id="chatArea" style="display: flex;">
                <div class="chat-header">
                    <div class="chat-user-info">
                        <div class="chat-user-avatar" id="chatUserAvatar">C</div>
                        <div class="chat-user-details">
                            <h6 id="chatUserName">Customer Name</h6>
                            <small class="text-muted">Customer</small>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshChat()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <!-- Messages will be loaded here -->
                </div>
                
                <div class="chat-input-area">
                    <div class="input-group">
                        <input type="text" class="form-control" id="messageInput" 
                               placeholder="Ketik balasan Anda..." autocomplete="off">
                        <button class="btn btn-primary" type="button" id="sendMessageBtn" onclick="sendMessage()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk mengirim pesan ke customer baru -->
<div class="modal fade" id="sendNewMessageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Kirim Pesan ke Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <select id="newCustomerSelect" class="form-select mb-3">
          <option value="">-- Pilih Customer --</option>
        </select>
        <textarea id="newCustomerMessage" class="form-control" placeholder="Tulis pesan..."></textarea>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" onclick="sendNewCustomerMessage()">Kirim</button>
      </div>
    </div>
  </div>
</div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Page Navigation
        function showPage(pageId) {
            // Hide all pages
            document.querySelectorAll('.page-content').forEach(page => {
                page.classList.remove('active');
            });
            
            // Show selected page
            document.getElementById(pageId).classList.add('active');
            
            // Update active menu
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Update page title
            const titles = {
                'dashboard': 'Dashboard',
                'products': 'Kelola Produk', 
                'orders': 'Kelola Pesanan',
                'promos': 'Kelola Promo',
                'chat': 'Customer Service'
            };
            document.getElementById('pageTitle').textContent = titles[pageId];
        }

// Initialize Chart (Versi Final Anti-Timpa)
window.onload = function() {
    // Ambil data dari PHP dan cetak ke console untuk bukti final
    const labelsForChart = <?php echo json_encode($daily_sales_labels); ?>;
    const dataForChart = <?php echo json_encode($daily_sales_data); ?>;

    console.log("--- BUKTI FINAL DATA GRAFIK ---");
    console.log("Labels yang diterima JS:", labelsForChart);
    console.log("Data yang diterima JS:", dataForChart);
    
    // Cek apakah ada elemen canvas
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: labelsForChart,
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: dataForChart,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
        console.log("Grafik 7 hari berhasil digambar!");
    } else {
        console.error("Elemen canvas #salesChart tidak ditemukan!");
    }
};
        // Mobile Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (window.innerWidth <= 768) {
                sidebar.style.transform = sidebar.style.transform === 'translateX(0px)' ? 'translateX(-100%)' : 'translateX(0px)';
            }
        }

        // Responsive handling
        window.addEventListener('resize', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (window.innerWidth > 768) {
                sidebar.style.transform = 'translateX(0px)';
                mainContent.style.marginLeft = '280px';
            } else {
                sidebar.style.transform = 'translateX(-100%)';
                mainContent.style.marginLeft = '0';
            }
        });

        // Add mobile menu button to topbar
        document.addEventListener('DOMContentLoaded', function() {
            const topbar = document.querySelector('.topbar');
            const pageTitle = document.querySelector('.page-title');
            
            if (window.innerWidth <= 768) {
                const menuButton = document.createElement('button');
                menuButton.innerHTML = '<i class="fas fa-bars"></i>';
                menuButton.className = 'btn btn-outline-primary btn-sm';
                menuButton.onclick = toggleSidebar;
                
                const titleContainer = document.createElement('div');
                titleContainer.className = 'd-flex align-items-center gap-3';
                titleContainer.appendChild(menuButton);
                titleContainer.appendChild(pageTitle.cloneNode(true));
                
                topbar.replaceChild(titleContainer, pageTitle);
            }
        });

        // Form validation and notifications
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        // Enhanced form handling
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                    submitBtn.disabled = true;
                }
            });
        });

        // Image preview for product form
        document.querySelector('input[name="image"]')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.getElementById('imagePreview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.id = 'imagePreview';
                        preview.className = 'mt-2';
                        const container = document.querySelector('.image-preview-container'); // ganti dengan class wrapper yang pasti ada
                        if (container) {
                            container.appendChild(preview);
                        } else {
                            console.error("Container preview tidak ditemukan.");
                            return;
                        }
                    }
                    preview.innerHTML = `
                        <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        <p class="text-muted small mt-1">Preview gambar</p>
                    `;
                };

                reader.readAsDataURL(file);
            }
        });

        // Auto-refresh data every 30 seconds for dashboard
        setInterval(function() {
            if (document.getElementById('dashboard').classList.contains('active')) {
                // Refresh dashboard data
                // This would typically involve AJAX calls to update the stats
                console.log('Refreshing dashboard data...');
            }
        }, 30000);

        // Search functionality
        function initSearch() {
            const searchInputs = document.querySelectorAll('.search-input');
            searchInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const table = this.closest('.modern-table').querySelector('tbody');
                    const rows = table.querySelectorAll('tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            });
        }


         // Initialize search on page load
        document.addEventListener('DOMContentLoaded', initSearch);

        // Confirmation dialogs
        document.querySelectorAll('[data-confirm]').forEach(element => {
            element.addEventListener('click', function(e) {
                if (!confirm(this.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            });
        });

       // Status update functionality (Versi BARU dengan AJAX)
function updateOrderStatus(orderId, newStatus) {
    // Tampilkan konfirmasi kepada admin
    if (!confirm(`Apakah Anda yakin ingin mengubah status pesanan #${orderId} menjadi "${newStatus}"?`)) {
        return; // Jika admin klik 'Cancel', hentikan fungsi
    }

    // Siapkan data untuk dikirim ke server
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', newStatus);

    // Kirim data menggunakan Fetch (AJAX) ke file PHP baru
    fetch('update_status_pesanan.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // Harapkan jawaban dalam format JSON
    .then(data => {
        if (data.success) {
            // Jika server menjawab sukses...
            showNotification(`Status pesanan #${orderId} berhasil diubah!`, 'success');
            // Tunggu sebentar lalu reload halaman untuk melihat perubahan
            setTimeout(() => {
                window.location.reload(); 
            }, 1500); // 1.5 detik
        } else {
            // Jika server menjawab gagal...
            showNotification('Gagal mengubah status: ' + (data.message || 'Error tidak diketahui.'), 'danger');
        }
    })
    .catch(error => {
        // Jika terjadi error koneksi
        console.error('Error:', error);
        showNotification('Terjadi kesalahan koneksi.', 'danger');
    });
}

        // Export functionality
        function exportData(type) {
            showNotification('Data sedang diekspor...', 'info');
            // Implementation for exporting data
        }

        // Drag and drop for file uploads
        document.querySelectorAll('input[type="file"]').forEach(input => {
            const parent = input.parentElement;
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                parent.addEventListener(eventName, preventDefaults, false);
            });
            
            ['dragenter', 'dragover'].forEach(eventName => {
                parent.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                parent.addEventListener(eventName, unhighlight, false);
            });
            
            parent.addEventListener('drop', handleDrop, false);
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            function highlight(e) {
                parent.classList.add('border-primary');
            }
            
            function unhighlight(e) {
                parent.classList.remove('border-primary');
            }
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                input.files = files;
                input.dispatchEvent(new Event('change'));
            }
        });

        let currentChatUser = null;
let chatRefreshInterval = null;

function loadChatUsers() {
    fetch('admin_chat_handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_chat_users'
    })
    .then(res => res.json())
    .then(users => {
        const usersList = document.getElementById('chatUsersList');

        if (users.length === 0) {
            usersList.innerHTML = '<div class="loading-users"><i class="fas fa-inbox"></i><br>Belum ada chat dari customer</div>';
            return;
        }

        usersList.innerHTML = '';
        users.forEach((user, index) => {
            const lastMessage = user.last_message ?
                (user.last_message.length > 30 ? user.last_message.substring(0, 30) + '...' : user.last_message)
                : 'Belum ada pesan';

            const timeAgo = user.last_message_time ? formatTimeAgo(user.last_message_time) : '';
            const unreadBadge = user.unread_count > 0 ? `<span class="unread-badge">${user.unread_count}</span>` : '';

            const userItem = document.createElement('div');
            userItem.className = 'chat-user-item';
            userItem.onclick = (e) => selectChatUser(user.id, user.username || 'User', e.currentTarget);

            userItem.innerHTML = `
                <div class="chat-user-info-item">
                    <div class="chat-user-avatar-small">${(user.username || 'US').substring(0, 2).toUpperCase()}</div>
                    <div class="chat-user-details-small">
                        <h6>${user.username || 'User'}</h6>
                        <small class="text-muted">${lastMessage}</small>
                        ${timeAgo ? `<small class="text-muted d-block">${timeAgo}</small>` : ''}
                    </div>
                </div>
                ${unreadBadge}
            `;

            usersList.appendChild(userItem);
        });

        if (users.length === 1) {
            const onlyUser = users[0];
            const userEl = usersList.querySelector('.chat-user-item');
            selectChatUser(onlyUser.id, onlyUser.username || 'User', userEl);
        }
    })
    .catch(err => {
        console.error('Error loading chat users:', err);
        document.getElementById('chatUsersList').innerHTML = '<div class="loading-users text-danger"><i class="fas fa-exclamation-triangle"></i><br>Error loading chats</div>';
    });
}

function selectChatUser(userId, username, clickedElement) {
    currentChatUser = { id: userId, username: username };

    document.getElementById('chatWelcome').style.display = 'none';
    document.getElementById('chatArea').style.display = 'flex';
    document.getElementById('chatUserName').textContent = username;
    document.getElementById('chatUserAvatar').textContent = username.substring(0, 2).toUpperCase();

    document.querySelectorAll('.chat-user-item').forEach(item => {
        item.classList.remove('active');
    });
    clickedElement.classList.add('active');

    loadMessages(userId);

    if (chatRefreshInterval) clearInterval(chatRefreshInterval);
    chatRefreshInterval = setInterval(() => loadMessages(userId), 3000);
}

function loadMessages(userId) {
    fetch('admin_chat_handler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=load_messages&user_id=${userId}`
    })
    .then(res => res.json())
    .then(messages => {
        const messagesContainer = document.getElementById('chatMessages');
        messagesContainer.innerHTML = '';

        if (!Array.isArray(messages)) {
            messagesContainer.innerHTML = '<div class="text-danger text-center">Gagal memuat pesan</div>';
            console.error('Invalid response:', messages);
            return;
        }

        if (messages.length === 0) {
            messagesContainer.innerHTML = '<div class="text-center text-muted py-4">Belum ada percakapan</div>';
            return;
        }

        messages.forEach(message => {
            const isAdmin = message.sender_id == 1;
            const username = isAdmin ? 'Admin' : (message.username || 'User'); // ✅ fix kalau null
            const avatar = username.substring(0, 2).toUpperCase();
            const timeFormatted = formatMessageTime(message.timestamp);

            const messageDiv = document.createElement('div');
            messageDiv.className = `message-item ${isAdmin ? 'admin' : 'customer'}`;
            messageDiv.innerHTML = `
                <div class="message-container">
                    <div class="message-avatar">${avatar}</div>
                    <div class="message-content">
                        <div class="message-bubble">${escapeHtml(message.message)}</div>
                        <div class="message-time">${timeFormatted}</div>
                    </div>
                </div>
            `;
            messagesContainer.appendChild(messageDiv);
        });

        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    })
    .catch(err => {
        console.error('Error loading messages:', err);
        document.getElementById('chatMessages').innerHTML = '<div class="text-danger text-center">Gagal memuat pesan</div>';
    });
}


function sendMessage() {
    if (!currentChatUser) return;

    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();

    if (!message) return;

    const sendBtn = document.getElementById('sendMessageBtn');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('admin_chat_handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=send_message&receiver_id=${currentChatUser.id}&message=${encodeURIComponent(message)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            messageInput.value = '';
            loadMessages(currentChatUser.id);
            loadChatUsers();
        }
    })
    .catch(err => {
        console.error('Error sending message:', err);
    })
    .finally(() => {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
    });
}

function refreshChat() {
    if (currentChatUser) {
        loadMessages(currentChatUser.id);
    }
    loadChatUsers();
}

function formatTimeAgo(timestamp) {
    const now = new Date();
    const messageTime = new Date(timestamp);
    const diffMs = now - messageTime;
    const diffMins = Math.floor(diffMs / 60000);

    if (diffMins < 1) return 'Baru saja';
    if (diffMins < 60) return `${diffMins}m`;
    if (diffMins < 1440) return `${Math.floor(diffMins / 60)}h`;
    return messageTime.toLocaleDateString('id-ID');
}

function formatMessageTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);

    if (diffMins < 1) return 'Baru saja';
    if (diffMins < 60) return `${diffMins} menit lalu`;
    if (diffMins < 1440) {
        const diffHours = Math.floor(diffMins / 60);
        return `${diffHours} jam lalu`;
    }

    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('chat')) {
        loadChatUsers();
    }

    document.getElementById('messageInput')?.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendMessage();
        }
    });
});


    </script>
</body>
</html>