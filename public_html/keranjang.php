<?php
session_start();
require 'koneksi.php';

// ==========================================================
// PERSIAPAN DATA UNTUK DITAMPILKAN DI HALAMAN (PINDAHKAN KE ATAS)
// ==========================================================
$keranjang = $_SESSION['keranjang'] ?? [];
$promo_aktif = $_SESSION['promo'] ?? null;

// 1. Hitung total harga asli (subtotal)
$total_harga_asli = 0;
foreach ($keranjang as $item) {
    $total_harga_asli += (float)($item['harga'] ?? 0) * (int)($item['jumlah'] ?? 0);
}

// 2. Cek dan terapkan promo
$info_diskon = 0;
$total_setelah_diskon = $total_harga_asli;
if ($promo_aktif) {
    $persen_diskon = (int)($promo_aktif['discount'] ?? 0);
    // Cek apakah minimal pembelian terpenuhi
    $min_purchase = $promo_aktif['min_purchase'] ?? 0;
    if ($total_harga_asli >= $min_purchase) {
        $info_diskon = $total_harga_asli * ($persen_diskon / 100);
        $total_setelah_diskon -= $info_diskon;
    } else {
        // Jika subtotal tidak memenuhi syarat, hapus promo
        unset($_SESSION['promo']);
        $promo_aktif = null; 
    }
}

// 3. Tambahkan ongkos kirim (contoh statis)
$ongkos_kirim = 15000;
$total_pembayaran_final = $total_setelah_diskon + $ongkos_kirim;

// Jika form checkout disubmit dari halaman ini
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_checkout'])) {
    // Cek login
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php?pesan=harus_login');
        exit();
    }
    // Cek keranjang kosong
    if (empty($_SESSION['keranjang'])) {
        header('Location: keranjang.php?status=kosong');
        exit();
    }

    // Siapkan data order untuk diproses oleh bayar.php
    $_SESSION['order_data'] = [
        'order_id' => 'ORD-' . date('YmdHis') . rand(100,999),
        'total'    => $total_pembayaran_final, // Pastikan key 'total' ada
        'items'    => $keranjang,
        'promo'    => $promo_aktif
    ];
    
    // PERBAIKAN: Arahkan langsung ke bayar.php, bukan proses_checkout.php
    header('Location: bayar.php');
    exit();
}

// 4. Siapkan data keranjang untuk diserahkan ke JavaScript
$cart_json = json_encode(array_values($keranjang));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link rel="icon" href="img/logo.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            gap: 1rem;
        }

        .back-btn {
            background: #f1f5f9;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #3b82f6;
        }

        .back-btn:hover {
            background: #e2e8f0;
            transform: translateX(-2px);
        }

        .header h1 {
            color: #1e40af;
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-count {
            background: #ef4444;
            color: white;
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            min-width: 20px;
            text-align: center;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 20px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        /* Cart Items */
        .cart-section {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .cart-item {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            display: flex;
            gap: 1rem;
            align-items: center;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .item-image {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid #f1f5f9;
        }

        .item-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .item-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .item-price {
            font-size: 1rem;
            color: #059669;
            font-weight: 600;
        }

        .item-restaurant {
            font-size: 0.9rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .quantity-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 0.25rem;
            gap: 0.5rem;
        }

        .qty-btn {
            width: 36px;
            height: 36px;
            border: none;
            background: #3b82f6;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .qty-btn:hover {
            background: #2563eb;
            transform: scale(1.05);
        }

        .qty-btn:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
            transform: none;
        }

        .quantity-display {
            min-width: 40px;
            text-align: center;
            font-weight: 600;
            font-size: 1.1rem;
            color: #1e293b;
        }

        .item-subtotal {
            font-size: 1.1rem;
            font-weight: 700;
            color: #059669;
            text-align: center;
        }

        .remove-btn {
            background: #fee2e2;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            cursor: pointer;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background: #fecaca;
            transform: scale(1.05);
        }

        /* Sidebar */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        /* Promo Section */
        .promo-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }

        .promo-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .promo-header h3 {
            color: #1e293b;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .promo-input-group {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .promo-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .promo-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .apply-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .apply-btn:hover:not(:disabled) {
            background: #2563eb;
        }

        .apply-btn:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
        }

        .promo-active {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .promo-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remove-promo {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
        }

        /* Summary Card */
        .summary-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }

        .summary-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .summary-header h3 {
            color: #1e293b;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: #64748b;
        }

        .summary-value {
            font-weight: 600;
            color: #1e293b;
        }

        .discount-row {
            color: #059669 !important;
        }

        .discount-row .summary-label,
        .discount-row .summary-value {
            color: #059669 !important;
        }

        .total-row {
            border-top: 2px solid #e2e8f0;
            padding-top: 1rem;
            margin-top: 0.5rem;
            font-size: 1.1rem;
        }

        .total-row .summary-label,
        .total-row .summary-value {
            color: #1e293b;
            font-weight: 700;
            font-size: 1.2rem;
        }

        /* Checkout Button */
        .checkout-btn {
            width: 100%;
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1.5rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .checkout-btn:hover {
            background: linear-gradient(135deg, #047857, #065f46);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.3);
        }

        /* Empty State */
        .empty-cart {
            grid-column: 1 / -1;
            background: #fff;
            border-radius: 16px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .empty-cart i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-cart h2 {
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .empty-cart p {
            color: #94a3b8;
            margin-bottom: 2rem;
        }

        .shop-now-btn {
            background: #3b82f6;
            color: white;
            text-decoration: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .shop-now-btn:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }

        /* Messages */
        .message {
            background: #dbeafe;
            color: #1e40af;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #93c5fd;
        }

        .message.success {
            background: #d1fae5;
            color: #065f46;
            border-color: #6ee7b7;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fca5a5;
        }

        /* Loading States */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                padding: 1rem;
                gap: 1rem;
            }

            .sidebar {
                position: static;
                order: -1;
            }

            .cart-item {
                padding: 1rem;
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .item-info {
                align-items: center;
                text-align: center;
            }

            .quantity-section {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                width: 100%;
            }

            .header-content {
                padding: 0 1rem;
            }

            .header h1 {
                font-size: 1.5rem;
            }
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .slide-in {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>
                <i class="fas fa-shopping-cart"></i>
                Keranjang Belanja
                <span class="cart-count" id="cartCountBadge">0</span>
            </h1>
        </div>
    </div>

    <div class="container">
        <div class="cart-section">
            <div id="cartContainer"></div>
        </div>

        <div class="sidebar" id="checkout-area">
            <!-- Promo Section -->
            <div class="promo-card">
                <div class="promo-header">
                    <i class="fas fa-tags" style="color: #3b82f6;"></i>
                    <h3>Kode Promo</h3>
                </div>
                
                <?php if ($promo_aktif): ?>
                    <div class="promo-active">
                        <div class="promo-info">
                            <i class="fas fa-check-circle"></i>
                            <span><?php echo htmlspecialchars($promo_aktif['name']); ?></span>
                        </div>
                        <a href="hapus_promo.php" class="remove-promo">Hapus</a>
                    </div>
                <?php else: ?>
                    <div class="promo-input-group">
                        <input type="text" id="promo_code_input" class="promo-input" placeholder="Masukkan kode promo">
                        <button onclick="applyPromo()" class="apply-btn">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                <?php endif; ?>
                
                <div id="promo_message"></div>
            </div>

            <!-- Order Summary -->
            <div class="summary-card">
                <div class="summary-header">
                    <i class="fas fa-receipt" style="color: #3b82f6;"></i>
                    <h3>Ringkasan Pesanan</h3>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value">Rp <?php echo number_format($total_harga_asli); ?></span>
                </div>

                <?php if ($promo_aktif): ?>
                <div class="summary-row discount-row">
                    <span class="summary-label">
                        <i class="fas fa-tag"></i>
                        Diskon (<?php echo htmlspecialchars($promo_aktif['name']); ?>)
                    </span>
                    <span class="summary-value">- Rp <?php echo number_format($info_diskon); ?></span>
                </div>
                <?php endif; ?>

                <div class="summary-row">
                    <span class="summary-label">
                        <i class="fas fa-shipping-fast"></i>
                        Ongkos Kirim
                    </span>
                    <span class="summary-value">Rp <?php echo number_format($ongkos_kirim); ?></span>
                </div>

                <div class="summary-row total-row">
                    <span class="summary-label">Total Pembayaran</span>
                    <span class="summary-value">Rp <?php echo number_format($total_pembayaran_final); ?></span>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="submit_checkout" value="1">
                    <button type="submit" class="checkout-btn">
                        <i class="fas fa-credit-card"></i>
                        Lanjutkan ke Pembayaran
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const cartItems = <?php echo $cart_json; ?>;

        function updateCartCount() {
            const totalItems = cartItems.reduce((sum, item) => sum + parseInt(item.jumlah || 0), 0);
            document.getElementById('cartCountBadge').textContent = totalItems;
        }

        function renderCart() {
            const container = document.getElementById('cartContainer');
            const checkoutArea = document.getElementById('checkout-area');
            
            if (cartItems.length === 0) {
                container.innerHTML = `
                    <div class="empty-cart fade-in">
                        <i class="fas fa-shopping-cart"></i>
                        <h2>Keranjang Anda Kosong</h2>
                        <p>Looks like you haven't added anything to your cart yet. Start shopping to fill it up!</p>
                        <a href="index.php" class="shop-now-btn">
                            <i class="fas fa-utensils"></i>
                            Mulai Belanja
                        </a>
                    </div>
                `;
                checkoutArea.style.display = 'none';
                return;
            }

            checkoutArea.style.display = 'block';
            container.innerHTML = '';

            cartItems.forEach((item, index) => {
                const harga = parseFloat(item.harga) || 0;
                const jumlah = parseInt(item.jumlah) || 0;
                const itemSubtotal = harga * jumlah;

                const itemEl = document.createElement('div');
                itemEl.className = 'cart-item fade-in';
                itemEl.style.animationDelay = `${index * 0.1}s`;
                itemEl.innerHTML = `
                    <img src="${item.image}" alt="${item.nama}" class="item-image">
                    <div class="item-info">
                        <div class="item-name">${item.nama}</div>
                        <div class="item-price">Rp ${harga.toLocaleString('id-ID')}</div>
                        <div class="item-restaurant">
                            <i class="fas fa-store"></i>
                            Restaurant Name
                        </div>
                    </div>
                    <div class="quantity-section">
                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="updateCartItem(${item.id}, -1)" ${jumlah <= 1 ? 'disabled' : ''}>
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="quantity-display">${jumlah}</span>
                            <button class="qty-btn" onclick="updateCartItem(${item.id}, 1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="item-subtotal">Rp ${itemSubtotal.toLocaleString('id-ID')}</div>
                        <button class="remove-btn" onclick="updateCartItem(${item.id}, -9999)" title="Hapus item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                container.appendChild(itemEl);
            });

            updateCartCount();
        }

        function applyPromo() {
            const promoInput = document.getElementById('promo_code_input');
            const promoCode = promoInput.value.trim();
            const applyBtn = document.querySelector('.apply-btn');
            const messageDiv = document.getElementById('promo_message');
            
            if (!promoCode) {
                showMessage('Silakan masukkan kode promo.', 'error');
                return;
            }

            // Show loading state
            applyBtn.innerHTML = '<div class="loading"></div>';
            applyBtn.disabled = true;

            const formData = new FormData();
            formData.append('promo_code', promoCode);

            fetch('cek_promo.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    showMessage(data.message, data.success ? 'success' : 'error');
                    if (data.success) {
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        // Reset button
                        applyBtn.innerHTML = '<i class="fas fa-check"></i>';
                        applyBtn.disabled = false;
                    }
                })
                .catch(error => {
                    showMessage('Terjadi kesalahan. Silakan coba lagi.', 'error');
                    applyBtn.innerHTML = '<i class="fas fa-check"></i>';
                    applyBtn.disabled = false;
                });
        }
        
        function updateCartItem(productId, quantityChange) {
            const formData = new FormData();
            formData.append('id', productId);
            formData.append('jumlah', quantityChange);

            // Show loading state for quantity buttons
            const qtyButtons = document.querySelectorAll('.qty-btn');
            qtyButtons.forEach(btn => btn.disabled = true);

            fetch('update_keranjang.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Add slide-out animation before reload
                        if (quantityChange === -9999) {
                            const itemElement = event.target.closest('.cart-item');
                            itemElement.style.animation = 'slideOut 0.3s ease-in forwards';
                            setTimeout(() => window.location.reload(), 300);
                        } else {
                            window.location.reload();
                        }
                    } else {
                        showMessage('Gagal mengupdate keranjang.', 'error');
                        // Re-enable buttons
                        qtyButtons.forEach(btn => btn.disabled = false);
                    }
                })
                .catch(error => {
                    showMessage('Terjadi kesalahan. Silakan coba lagi.', 'error');
                    qtyButtons.forEach(btn => btn.disabled = false);
                });
        }

        function showMessage(message, type = 'info') {
            const messageDiv = document.getElementById('promo_message');
            messageDiv.className = `message ${type}`;
            messageDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i> ${message}`;
            messageDiv.style.display = 'block';
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }

        // Enter key support for promo input
        document.addEventListener('DOMContentLoaded', function() {
            const promoInput = document.getElementById('promo_code_input');
            if (promoInput) {
                promoInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        applyPromo();
                    }
                });
            }
            renderCart();
        });

        // Add slide-out animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOut {
                to {
                    transform: translateX(-100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>