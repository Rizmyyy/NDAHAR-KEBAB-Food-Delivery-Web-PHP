<?php
session_start();
require 'koneksi.php';

// Ambil ID pengguna yang sedang login
$user_id = $_SESSION['user_id'] ?? 0;

// Ambil ID pesanan spesifik dari URL, jika ada
$order_id_param = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

$display_order = null;  // Untuk menyimpan data pesanan yang akan ditampilkan
$order_history = [];    // Untuk menyimpan daftar semua riwayat pesanan
$message = '';

if ($order_id_param > 0) {
    // --- MODE 1: TAMPILKAN DETAIL SATU PESANAN ---
    // Ambil data pesanan utama dari tabel 'orders'
    // Kita tambahkan 'AND user_id = ?' untuk keamanan, agar pengguna tidak bisa melihat pesanan orang lain
    $sql_order = "SELECT *, DATE_FORMAT(created_at, '%d %b %Y %H:%i') as formatted_date FROM orders WHERE id = ? AND user_id = ?";
    $stmt_order = mysqli_prepare($conn, $sql_order);
    mysqli_stmt_bind_param($stmt_order, "ii", $order_id_param, $user_id);
    mysqli_stmt_execute($stmt_order);
    $result_order = mysqli_stmt_get_result($stmt_order);
    $display_order = mysqli_fetch_assoc($result_order);

    if ($display_order) {
        $message = "Pesanan Anda berhasil dibuat dan sedang menunggu verifikasi.";
        
        // Ambil item-item pesanan dari tabel 'order_items'
        $sql_items = "SELECT oi.*, p.name as title, p.image_path as image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
        $stmt_items = mysqli_prepare($conn, $sql_items);
        mysqli_stmt_bind_param($stmt_items, "i", $order_id_param);
        mysqli_stmt_execute($stmt_items);
        $result_items = mysqli_stmt_get_result($stmt_items);
        
        $items_from_db = [];
        while ($item = mysqli_fetch_assoc($result_items)) {
            $item['currentPrice'] = $item['price']; // Menyesuaikan nama kunci untuk HTML
            $items_from_db[] = $item;
        }
        $display_order['items'] = $items_from_db; // Gabungkan data item
    } else {
        $message = "Pesanan tidak ditemukan atau bukan milik Anda.";
    }

} else {
    // --- MODE 2: TAMPILKAN SEMUA RIWAYAT PESANAN ---
    if ($user_id > 0) {
        $message = "Riwayat Pesanan Anda:";
        // Ambil semua pesanan milik user ini dari database
        $sql_history = "SELECT o.id as order_db_id, o.*, DATE_FORMAT(o.created_at, '%d %b %Y') as formatted_date FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC";
        $stmt_history = mysqli_prepare($conn, $sql_history);
        mysqli_stmt_bind_param($stmt_history, "i", $user_id);
        mysqli_stmt_execute($stmt_history);
        $result_history = mysqli_stmt_get_result($stmt_history);
        
        while($row = mysqli_fetch_assoc($result_history)){
            $order_history[] = $row;
        }
    } else {
        $message = "Silakan login untuk melihat riwayat pesanan Anda.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan</title>
    <link rel="icon" href="img/logo.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
       /* Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-image: linear-gradient(rgba(7, 0, 109, 0.7), rgba(68, 0, 179, 0.7)), url('img/bg.png');
    min-height: 100vh;
    color: #333;
}

/* Header */
.header {
    background: #fff;
    box-shadow: 0 2px 20px rgba(0,0,0,0.1);
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    align-items: center;
    justify-content: left;
}

.header h1 {
    color: #1e40af;
    font-size: 1.8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Container */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 20px;
}

/* Message Card */
.message-card {
    background: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border-left: 4px solid #3b82f6;
}

.message-card .message-icon {
    display: inline-block;
    width: 20px;
    height: 20px;
    margin-right: 10px;
    color: #3b82f6;
}

/* Order Detail */
.order-detail-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    margin-bottom: 2rem;
}

.order-header {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 2rem;
    text-align: center;
}

.order-header h2 {
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.order-header .order-id {
    font-size: 1rem;
    opacity: 0.9;
}

.order-summary {
    padding: 2rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.summary-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.summary-label {
    font-size: 0.9rem;
    color: #64748b;
    font-weight: 500;
}

.summary-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
}

.total-amount {
    font-size: 1.5rem !important;
    color: #059669 !important;
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-pending {
    background: #fef3c7;
    color: #d97706;
    border: 1px solid #fcd34d;
}

.status-confirmed {
    background: #dbeafe;
    color: #2563eb;
    border: 1px solid #93c5fd;
}

.status-preparing {
    background: #fed7aa;
    color: #ea580c;
    border: 1px solid #fdba74;
}

.status-ready {
    background: #dcfce7;
    color: #16a34a;
    border: 1px solid #86efac;
}

.status-delivered {
    background: #d1fae5;
    color: #059669;
    border: 1px solid #6ee7b7;
}

.status-cancelled {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fca5a5;
}

/* Promo Code */
.promo-highlight {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 1rem 2rem;
    border-radius: 12px;
    margin: 1rem 0;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.promo-highlight i {
    font-size: 1.5rem;
}

/* Order Items */
.order-items {
    padding: 2rem;
}

.order-items h3 {
    font-size: 1.4rem;
    margin-bottom: 1.5rem;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.item-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.item-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    gap: 1rem;
    align-items: center;
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
}

.item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.item-image {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}

.item-detail {
    flex: 1;
}

.item-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.item-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #64748b;
}

.item-price {
    font-weight: 600;
    color: #059669;
}

/* History List */
.history-grid {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
}

.order-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
}

.order-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.order-card-header {
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.order-number {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1e40af;
    margin-bottom: 0.5rem;
}

.order-card-body {
    padding: 1.5rem;
}

.order-info-grid {
    display: grid;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-label {
    color: #64748b;
    font-size: 0.9rem;
}

.info-value {
    font-weight: 600;
    color: #1e293b;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2563eb, #1e40af);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.btn-secondary {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

.btn-secondary:hover {
    background: #e2e8f0;
    transform: translateY(-2px);
}

.btn-outline {
    background: transparent;
    color: #3b82f6;
    border: 2px solid #3b82f6;
}

.btn-outline:hover {
    background: #3b82f6;
    color: white;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.empty-state i {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: #64748b;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #94a3b8;
    margin-bottom: 2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }

    .header-content {
        padding: 0 1rem;
    }

    .header h1 {
        font-size: 1.5rem;
    }

    .summary-grid {
        grid-template-columns: 1fr;
    }

    .item-card {
        flex-direction: column;
        text-align: center;
    }

    .history-grid {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        flex-direction: column;
    }

    .order-header {
        padding: 1.5rem;
    }

    .order-summary,
    .order-items {
        padding: 1.5rem;
    }
}

/* Loading Spinner */
.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Fade In */
.fade-in {
    animation: fadeIn 0.6s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

    </style>
</head>
<body>
    <div class="header">
    <div class="header-content" style="display: flex; align-items: center; gap: 15px;">
        <a href="index.php" style="text-decoration: none; color: white; font-size: 18px; background: #2ecc71; padding: 8px 12px; border-radius: 6px;">
            ← Home
        </a>
        <h1 style="margin: 0;">Riwayat Pesanan</h1>
    </div>
</div>


    <div class="container">
        <?php if ($message): ?>
            <div class="message-card fade-in">
                <i class="fas fa-info-circle message-icon"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($display_order): // Jika kita sedang menampilkan DETAIL satu pesanan ?>
            <div class="order-detail-card fade-in">
                <div class="order-header">
                    <h2>Detail Pesanan</h2>
                    <div class="order-id">#<?php echo htmlspecialchars($display_order['id']); ?></div>
                </div>

                <div class="order-summary">
                    <div class="summary-grid">
                        <div class="summary-item">
                            <span class="summary-label">Total Pembayaran</span>
                            <span class="summary-value total-amount">Rp <?php echo number_format($display_order['total_amount'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Tanggal Pesan</span>
                            <span class="summary-value"><?php echo $display_order['formatted_date']; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Status Pesanan</span>
                            <span class="status-badge status-<?php echo strtolower($display_order['status']); ?>">
                                <i class="fas fa-circle"></i>
                                <?php echo ucfirst($display_order['status']); ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($display_order['promo_code_used'])): ?>
                        <div class="promo-highlight">
                            <i class="fas fa-tags"></i>
                            <div>
                                <strong>Promo Digunakan:</strong>
                                <?php echo htmlspecialchars($display_order['promo_code_used']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="order-items">
                    <h3><i class="fas fa-shopping-bag"></i>Item Pesanan</h3>
                    <ul class="item-list">
                        <?php if (!empty($display_order['items'])): ?>
                            <?php foreach ($display_order['items'] as $item): ?>
                                <li class="item-card">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                         class="item-image">
                                    <div class="item-detail">
                                        <div class="item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <div class="item-info">
                                            <div>Jumlah: <span class="item-price"><?php echo htmlspecialchars($item['quantity']); ?></span></div>
                                            <div>Harga: <span class="item-price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span></div>
                                            <div>Subtotal: <span class="item-price">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></span></div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="item-card">
                                <div class="item-detail">Detail item tidak ditemukan.</div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="action-buttons">
                <a href="status_pesanan.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Lihat Semua Riwayat
                </a>
            </div>

        <?php elseif (!empty($order_history)): // Jika kita menampilkan DAFTAR RIWAYAT ?>
            <div class="history-grid">
                <?php foreach ($order_history as $order): ?>
                    <div class="order-card fade-in">
                        <div class="order-card-header">
                            <div class="order-number">
                                #ORD-<?php echo date('Ymd', strtotime($order['created_at'])) . $order['order_db_id']; ?>
                            </div>
                        </div>
                        <div class="order-card-body">
                            <div class="order-info-grid">
                                <div class="info-row">
                                    <span class="info-label">Tanggal</span>
                                    <span class="info-value"><?php echo $order['formatted_date']; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Total</span>
                                    <span class="info-value" style="color: #059669;">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Status</span>
                                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                        <i class="fas fa-circle"></i>
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <a href="status_pesanan.php?order_id=<?php echo $order['order_db_id']; ?>" class="btn btn-primary" style="width: 100%; justify-content: center;">
                                <i class="fas fa-eye"></i>
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: // Jika tidak ada order spesifik dan tidak ada riwayat ?>
            <div class="empty-state fade-in">
                <i class="fas fa-receipt"></i>
                <h3>Belum Ada Pesanan</h3>
                <p>Anda belum memiliki riwayat pesanan. Yuk, pesan makanan favorit Anda!</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-utensils"></i>
                    Mulai Pesan
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>