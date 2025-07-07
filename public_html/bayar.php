<?php
session_start();
require 'koneksi.php'; // Sebaiknya selalu panggil koneksi

// ===================================================================
// === AWAL BLOK REVISI ===
// ===================================================================

// 1. Ambil data pesanan dari session
$order_data = $_SESSION['order_data'] ?? null;

// 2. REVISI PENGECEKAN: Cek apakah ada data pesanan, bukan order_id.
if (!$order_data || empty($order_data['items'])) {
    // Jika tidak ada data pesanan atau itemnya kosong, kembalikan ke keranjang.
    header('Location: keranjang.php?status=data_hilang');
    exit();
}

// 2b. FIX UNDEFINED VARIABLE: pastikan $order_id di-set
$order_id_display = 'TEMP-' . date('YmdHis');
$order_id = $_SESSION['order_data']['order_id'] ?? $order_id_display;

// 3. Ambil metode pembayaran jika sudah dipilih dari form
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : null;

// --- Data metode pembayaran (tidak berubah, hanya sumber $order_id yang diganti) ---
$payment_methods = [
    'bca' => [
        'name' => 'Bank BCA', 'account' => '1234567890', 'account_name' => 'TOKO MAKANAN ONLINE', 'icon' => '🏦', 'color' => '#0066cc',
        'instructions' => [ 'Login ke BCA Mobile/Internet Banking', 'Pilih Transfer ke Rekening BCA', 'Masukkan nomor rekening: 1234567890', 'Masukkan nominal sesuai total pembayaran', 'Masukkan kode unik: ' . substr($order_id, -3), 'Konfirmasi transfer dan simpan bukti' ]
    ],
    
    'mandiri' => [
        'name' => 'Bank Mandiri', 'account' => '0987654321', 'account_name' => 'TOKO MAKANAN ONLINE', 'icon' => '🏦', 'color' => '#003d82',
        'instructions' => [ 'Login ke Mandiri Online/Mobile Banking', 'Pilih Transfer Online', 'Masukkan nomor rekening: 0987654321', 'Masukkan nominal sesuai total pembayaran', 'Masukkan kode unik: ' . substr($order_id, -3), 'Konfirmasi transfer dan simpan bukti' ]
    ],
    'bni' => [
        'name' => 'Bank BNI', 'account' => '1122334455', 'account_name' => 'TOKO MAKANAN ONLINE', 'icon' => '🏦', 'color' => '#ed6c02',
        'instructions' => [ 'Login ke BNI Mobile Banking', 'Pilih Transfer', 'Masukkan nomor rekening: 1122334455', 'Masukkan nominal sesuai total pembayaran', 'Masukkan kode unik: ' . substr($order_id, -3), 'Konfirmasi transfer dan simpan bukti' ]
    ],
    'gopay' => [
        'name' => 'GoPay', 'account' => '081234567890', 'account_name' => 'TOKO MAKANAN ONLINE', 'icon' => '📱', 'color' => '#00AA5B',
        'instructions' => [ 'Buka aplikasi Gojek', 'Pilih menu GoPay', 'Pilih Transfer ke No HP: 081234567890', 'Masukkan nominal sesuai total pembayaran', 'Tambahkan catatan: ' . $order_id, 'Konfirmasi transfer dan simpan bukti' ]
    ],
    'ovo' => [
        'name' => 'OVO', 'account' => '081234567890', 'account_name' => 'TOKO MAKANAN ONLINE', 'icon' => '📱', 'color' => '#4C3494',
        'instructions' => [ 'Buka aplikasi OVO', 'Pilih Transfer', 'Pilih ke No HP: 081234567890', 'Masukkan nominal sesuai total pembayaran', 'Tambahkan catatan: ' . $order_id, 'Konfirmasi transfer dan simpan bukti' ]
    ],
    'dana' => [
        'name' => 'DANA', 'account' => '081234567890', 'account_name' => 'TOKO MAKANAN ONLINE', 'icon' => '📱', 'color' => '#118EEA',
        'instructions' => [ 'Buka aplikasi DANA', 'Pilih Kirim', 'Pilih ke No HP: 081234567890', 'Masukkan nominal sesuai total pembayaran', 'Tambahkan catatan: ' . $order_id, 'Konfirmasi transfer dan simpan bukti' ]
    ],
];

// --- LOGIKA UTAMA BAYAR.PHP ---

// Jika form pemilihan metode pembayaran telah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $selected_payment_key = $_POST['payment_method'];

    // Validasi $selected_payment_key
    if (!isset($payment_methods[$selected_payment_key])) {
        // Metode pembayaran tidak valid, redirect kembali
        header('Location: keranjang.php');
        exit();
    }
    // Set payment_method yang sudah dipilih agar bisa diproses lebih lanjut
    $payment_method = $selected_payment_key; 
} 

// Jika ini adalah tampilan pertama (belum ada payment_method di POST)
// atau jika form detail pembayaran sudah disubmit
// Kita perlu dua tahap:
// Tahap 1: Pilih metode pembayaran
// Tahap 2: Isi detail pembayaran (jika metode sudah dipilih)

// Jika payment_method belum dipilih atau form detail pembayaran belum disubmit
if (!isset($payment_method) || empty($payment_method)) {
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Metode Pembayaran - <?php echo $order_id; ?></title>
    <link rel="icon" href="img/logo.png" type="image/png">
    <style>
        body { 
            font-family: sans-serif; 
            padding: 20px; 
            background-image: linear-gradient(rgba(7, 0, 109, 0.7), rgba(68, 0, 179, 0.7)), url('img/bg.png');
         }
        .container { max-width: 600px; margin: 50px auto; background: rgba(255, 255, 255, 0.64); padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; margin-bottom: 30px; }
        .payment-options label {
            display: flex;
            background: rgba(255, 255, 255, 0.81);
            align-items: center;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-options label:hover {
            background-color:rgb(155, 155, 155);
            border-color: #007bff;
        }
        .payment-options input[type="radio"] {
            margin-right: 15px;
            width: 20px;
            height: 20px;
        }
        .payment-options span {
            font-size: 1.1em;
            font-weight: bold;
        }
        .total-info {
            background:rgb(130, 255, 184);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.2em;
            font-weight: bold;
            color:rgb(0, 145, 31);
        }
        .submit-btn {
            width: 100%;
            padding: 15px;
            background-color:rgb(6, 196, 50);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .submit-btn:hover {
            background-color: #218838;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color:rgb(7, 0, 102);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pilih Metode Pembayaran</h1>
        <div class="total-info">
            Total Pesanan: Rp <?php echo number_format($order_data['total'], 0, ',', '.'); ?>
        </div>
        <form method="POST" action="bayar.php">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
            <div class="payment-options">
                <?php foreach ($payment_methods as $key => $method): ?>
                <label>
                    <input type="radio" name="payment_method" value="<?php echo $key; ?>" required>
                    <span><?php echo $method['icon'] . ' ' . $method['name']; ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="submit-btn">Lanjut Pembayaran</button>
        </form>
        <a href="keranjang.php" class="back-link">← Kembali ke Keranjang</a>
    </div>
</body>
</html>
<?php
    exit(); // Penting untuk menghentikan eksekusi setelah menampilkan form pilihan pembayaran
}

// Jika payment_method sudah dipilih, lanjutkan untuk menampilkan detail pembayaran
$selected_payment = $payment_methods[$payment_method];

// Hitung total dengan fee (jika ada)
$total_with_fee = $order_data['total'];
if (in_array($payment_method, ['indomaret', 'alfamart'])) {
    $total_with_fee += 2500; // Fee Rp 2.500
}

// Inisialisasi variabel untuk input pengguna
// Default ke nilai kosong atau nilai yang sudah tersimpan
$user_account_input = $order_data['payment_details']['account'] ?? '';
$user_account_name_input = $order_data['payment_details']['account_name'] ?? '';
$user_payment_code_input = $order_data['payment_details']['payment_code'] ?? '';


// Proses submission dari form detail pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment_details'])) {
    $user_account_input = $_POST['user_account'] ?? '';
    $user_account_name_input = $_POST['user_account_name'] ?? '';
    $user_payment_code_input = $_POST['user_payment_code'] ?? ''; // Untuk Indomaret/Alfamart, jika ada input lagi

    // Simpan detail pembayaran yang diisi pengguna ke dalam $_SESSION['order_data']
    $_SESSION['order_data']['payment_details'] = [
        'method' => $payment_method, // Metode pembayaran yang dipilih
        'account' => $user_account_input,
        'account_name' => $user_account_name_input,
        'payment_code' => $user_payment_code_input // Bisa kosong jika bukan Indomaret/Alfamart
    ];

    // Opsional: Anda bisa menambahkan redirect lagi di sini
    // Misalnya, ke halaman konfirmasi final atau status_pesanan.php
    // Saat ini, kita akan biarkan halaman me-reload dengan data yang sudah tersimpan di sesi
    // dan menampilkan notifikasi sukses
    $payment_details_saved_message = "Detail pembayaran berhasil disimpan!";
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran - <?php echo $order_id; ?></title>
    <link rel="icon" href="img/logo.png" type="image/png">
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
            max-width: 700px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.64);
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .header::before {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 20px;
            background: white;
            border-radius: 20px 20px 0 0;
        }
        
        .success-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }
        
        .header-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
.header-subtitle {
    font-size: 16px;
    color: white;
    background: rgb(4, 122, 0);  /* warna latar belakang transparan */
    padding: 6px 12px;               /* ruang di dalam background */
    border-radius: 6px;              /* sudut membulat */
    display: inline-block;           /* supaya background hanya seluas teks */
    font-weight: 500;
}


        
        .content {
            padding: 30px;
        }
        
        .timer-alert {
            background:  rgb(255, 187, 0);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            color: rgb(0, 0, 0);;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(255,107,107,0.3);
        }
        
        .timer-countdown {
            font-size: 24px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            margin-bottom: 10px;
        }
        
        .payment-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid <?php echo $selected_payment['color']; ?>;
        }
        
        .payment-method-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .payment-icon {
            width: 50px;
            height: 50px;
            background: <?php echo $selected_payment['color']; ?>;
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .payment-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        
        .payment-info {
            display: grid;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            flex-basis: 30%; /* Beri ruang lebih untuk label */
        }
        
        .info-value-input { /* Nama kelas baru untuk input */
            flex-basis: 65%; /* Beri ruang lebih untuk input */
            font-weight: bold;
            color: #333;
            font-family: 'Courier New', monospace;
            background: #e9ecef;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ced4da;
            width: 100%; /* Pastikan input memenuhi lebar */
            box-sizing: border-box; /* Sertakan padding dan border dalam lebar */
        }
        
        /* Hapus style .info-value karena kita pakai input */
        /* .info-value { ... } */ 

        .copy-btn {
            background: <?php echo $selected_payment['color']; ?>;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color:rgb(28, 168, 0);
            text-align: center;
            background:rgb(255, 255, 255);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .instructions {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .instructions-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .instruction-step {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 3px solid <?php echo $selected_payment['color']; ?>;
        }
        
        .step-number {
            background: <?php echo $selected_payment['color']; ?>;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .step-text {
            color: #333;
            line-height: 1.5;
        }
        
        .upload-section {
            background: #e8f5e8;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border: 2px dashed #27ae60;
            text-align: center;
        }
        
        .upload-title {
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 15px;
        }
        
        .upload-description {
            color: #555;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .file-upload {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        
        .file-upload input[type=file] {
            position: absolute;
            left: -9999px;
        }
        
        .file-upload-btn {
            background: #27ae60;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .file-upload-btn:hover {
            background: #229954;
            transform: translateY(-2px);
        }
        
        .uploaded-file {
            margin-top: 15px;
            padding: 10px;
            background: #d4edda;
            border-radius: 6px;
            color: #155724;
            display: none;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
        
        .btn-confirm {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            box-shadow: 0 8px 25px rgba(39,174,96,0.3);
        }
        
        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(39,174,96,0.4);
        }
        
        .btn-confirm:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .contact-info {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid #2196f3;
        }
        
        .contact-title {
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .contact-text {
            color: #555;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .content {
                padding: 20px;
            }
            
            .timer-countdown {
                font-size: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            /* Input field full width di mobile */
            .info-value-input {
                width: 100%;
                text-align: center;
            }
        }
        
        .copy-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #27ae60;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }
        
        .copy-notification.show {
            transform: translateX(0);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="success-icon">✅</div>
        <div class="header-title">Pembayaran Dikonfirmasi</div>
        <div class="header-subtitle">Pesanan #<?php echo $order_id; ?></div>
    </div>
    
    <div class="content">
        <?php if (isset($payment_details_saved_message)): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?php echo $payment_details_saved_message; ?>
            </div>
        <?php endif; ?>

        <div class="timer-alert">
            <div>⏰ Selesaikan pembayaran dalam</div>
            <div class="timer-countdown" id="countdown">14:59</div>
            <div>Pesanan akan dibatalkan otomatis jika melewati batas waktu</div>
        </div>
        
        <div class="payment-details">
            <div class="payment-method-header">
                <div class="payment-icon"><?php echo $selected_payment['icon']; ?></div>
                <div class="payment-name"><?php echo $selected_payment['name']; ?></div>
            </div>
            
            <form method="POST" action="bayar.php" id="paymentDetailsForm">
                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
                <input type="hidden" name="payment_method" value="<?php echo htmlspecialchars($payment_method); ?>">
                <input type="hidden" name="submit_payment_details" value="1"> <div class="payment-info">
                    <?php if (in_array($payment_method, ['bca', 'mandiri', 'bni', 'gopay', 'ovo', 'dana'])): ?>
                    <div class="info-row">
                        <span class="info-label">Nomor Rekening/HP Anda:</span>
                        <input type="text" name="user_account" class="info-value-input" 
                               value="<?php echo htmlspecialchars($user_account_input); ?>" 
                               placeholder="Contoh: 1234567890 (Nomor Rekening Anda)" required>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Atas Nama Anda:</span>
                        <input type="text" name="user_account_name" class="info-value-input" 
                               value="<?php echo htmlspecialchars($user_account_name_input); ?>" 
                               placeholder="Contoh: Nama Anda" required>
                    </div>
                    <?php else: // indomaret, alfamart ?>
                    <div class="info-row">
                        <span class="info-label">Kode Pembayaran Anda:</span>
                        <input type="text" name="user_payment_code" class="info-value-input" 
                               value="<?php echo htmlspecialchars($user_payment_code_input); ?>" 
                               placeholder="Masukkan kode pembayaran dari Indomaret/Alfamart" required>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Atas Nama Anda:</span>
                        <input type="text" name="user_account_name" class="info-value-input" 
                               value="<?php echo htmlspecialchars($user_account_name_input); ?>" 
                               placeholder="Contoh: Nama Anda" required>
                    </div>
                    <?php endif; ?>
                    
                </div>
            </form>
        </div>
        
        <div class="total-amount">
            💰 Total Pembayaran: Rp <?php echo number_format($total_with_fee, 0, ',', '.'); ?>
        </div>
        
        <div class="instructions">
            <div class="instructions-title">
                📋 Cara Pembayaran (Ke Rekening Toko)
            </div>
            
            <?php if (isset($selected_payment['instructions']) && is_array($selected_payment['instructions'])): ?>
            <?php foreach ($selected_payment['instructions'] as $index => $instruction): ?>
            <div class="instruction-step">
                <div class="step-number"><?php echo $index + 1; ?></div>
                <div class="step-text"><?php echo $instruction; ?></div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p>Instruksi pembayaran tidak tersedia.</p>
            <?php endif; ?>
        </div>
        
    
        
        <div class="action-buttons">
    <a href="keranjang.php" class="btn btn-back">
        ← Kembali ke Keranjang
    </a>
    <form action="proses_checkout.php" method="POST" style="flex: 1;">
        <button type="submit" class="btn btn-confirm" id="confirmBtn">
            ✅ Konfirmasi & Buat Pesanan
        </button>
    </form>
</div>
        
        <div class="contact-info">
            <div class="contact-title">
                📞 Butuh Bantuan?
            </div>
            <div class="contact-text">
                Jika mengalami kesulitan dalam pembayaran, hubungi customer service kami:<br>
                <strong>WhatsApp:</strong> 0812-3456-7890<br>
                <strong>Email:</strong> cs@tokomakanan.com<br>
                <strong>Jam Operasional:</strong> 08:00 - 22:00 WIB
            </div>
        </div>
    </div>
</div>

<div class="copy-notification" id="copyNotification">
    ✅ Berhasil disalin ke clipboard!
</div>

<script>
// Set user_id dari PHP ke JavaScript
const USER_ID = <?php echo isset($_SESSION['user_id']) ? json_encode($_SESSION['user_id']) : 'null'; ?>;

// Timer countdown (14 menit 59 detik karena sudah 1 detik berlalu)
let timeLeft = 14 * 60 + 59;
let timerInterval;

function updateTimer() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    document.getElementById('countdown').textContent = display;
    
    // Ubah warna ketika waktu hampir habis
    const timerAlert = document.querySelector('.timer-alert');
    if (timeLeft <= 300) { // 5 menit terakhir
        timerAlert.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
    }
    
    if (timeLeft <= 0) {
        clearInterval(timerInterval);
        alert('Waktu pembayaran habis! Pesanan dibatalkan.');
        window.location.href = 'keranjang.php';
    }
    
    timeLeft--;
}

// Mulai timer hanya jika elemen countdown ada (artinya, sudah menampilkan detail pembayaran)
if (document.getElementById('countdown')) {
    timerInterval = setInterval(updateTimer, 1000);
    updateTimer();
}


// Copy to clipboard function (tidak lagi relevan untuk input user, tapi biarkan saja)
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showCopyNotification();
    }).catch(function(err) {
        // Fallback untuk browser lama
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            showCopyNotification();
        } catch (err) {
            alert('Gagal menyalin. Silakan copy manual.');
        }
        document.body.removeChild(textArea);
    });
}

function showCopyNotification() {
    const notification = document.getElementById('copyNotification');
    notification.classList.add('show');
    setTimeout(() => {
        notification.classList.remove('show');
    }, 2000);
}

// Show uploaded file name
function showFileName(input) {
    const fileDiv = document.getElementById('uploadedFile');
    const confirmBtn = document.getElementById('confirmBtn');

    if (input.files && input.files[0]) {
        fileDiv.textContent = `✅ File dipilih: ${input.files[0].name}`;
        fileDiv.style.display = 'block';
    } else {
        fileDiv.textContent = '';
        fileDiv.style.display = 'none';
    }

    // Cek ulang semua input
    checkAllInputsAndEnableConfirm();
}


// Prevent back button (tetap sama)
history.pushState(null, null, location.href);
window.onpopstate = function() {
    const confirmLeave = confirm('Yakin ingin meninggalkan halaman? Pembayaran belum dikonfirmasi.');
    if (confirmLeave) {
        history.back();
    } else {
        history.pushState(null, null, location.href);
    }
};


// Auto-focus file input when clicked (tetap sama)
document.querySelector('.file-upload-btn').addEventListener('click', function() {
    document.getElementById('bukti_bayar').click();
});

// Tambahkan fungsi ini karena diakses di confirmPayment
function getUserCartKey() {
    return USER_ID ? `cart_user_${USER_ID}` : 'cart';
}
</script>

</body>
</html>