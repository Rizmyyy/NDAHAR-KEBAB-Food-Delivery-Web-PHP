<?php
session_start();

require 'koneksi.php';
$sql_promos = "SELECT name, description, discount_percentage FROM promos 
               WHERE is_active = TRUE AND CURDATE() BETWEEN start_date AND end_date 
               ORDER BY end_date ASC LIMIT 3";
$promos_result = mysqli_query($conn, $sql_promos);
$is_logged_in = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Ndahar Kebab</title>
    <link rel="icon" href="img/logo.png" type="image/png">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <!-- Favicon -->
    <link rel="icon" href="img/logo.png" type="image/png">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <style>
/* CSS untuk Kartu Promo */
.promo-card {
    background: #fff;
    border: 2px dashed var(--primary-color, #FEA116);
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}
.promo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}
.promo-percent {
    position: absolute;
    top: -20px;
    right: -20px;
    background: var(--primary-color, #FEA116);
    color: white;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: flex-end;
    justify-content: flex-start;
    padding: 25px 0 0 15px;
    font-size: 1.2rem;
    font-weight: bold;
    transform: rotate(15deg);
}
.promo-code {
    font-family: 'Courier New', monospace;
    font-size: 1.5rem;
    font-weight: bold;
    background-color: #f8f9fa;
    padding: 5px 15px;
    border-radius: 5px;
    display: inline-block;
    margin: 10px 0;
    color: #333;
}
.promo-desc {
    color: #666;
    min-height: 40px;
}
.copy-btn {
    background-color: #343a40;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 50px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
.copy-btn:hover {
    background-color: #000;
}
.copy-btn.copied {
    background-color: #28a745; /* Warna hijau saat berhasil disalin */
}

@media (max-width: 991.98px) {
  /* Gunakan style container search mobile */
  .search-container {
    display: flex !important;
    width: 100% !important;
    padding: 0 15px !important;
    margin-bottom: 15px !important;
    order: -1 !important;
  }

  .search-box {
    height: 38px !important;
    padding: 8px 40px 8px 16px !important;
    font-size: 14px !important;
  }

  .search-icon {
    right: 20px !important;
    font-size: 18px !important;
  }

  .navbar-collapse {
    flex-direction: column !important;
    align-items: flex-start !important;
  }

  .navbar-nav {
    width: 100% !important;
  }
}

    </style>
   

</head>

<body>
    <?php if (isset($_SESSION['user_id'])): ?>
<script>
    sessionStorage.setItem('user_id', '<?php echo $_SESSION['user_id']; ?>');
</script>
<?php endif; ?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 px-lg-5 py-3 py-lg-0 sticky-navbar">
   <div class="container-xxl position-relative p-0">
    <a href="" class="navbar-brand d-flex align-items-center">
      <img src="img/logo.png" alt="Logo" class="rotating-logo me-2" style="height: 150px; width: auto; vertical-align: middle; transform: translateY(-5px);">
      <h1 class="text-primary m-0 d-flex align-items-center">
        <span class="bounce-text">Ndahar</span><span class="bounce-text-delayed" style="color:rgb(0, 117, 219);">Kebab</span>
      </h1>
    </a>

    <!-- Cart icon always visible on mobile -->
    <div class="d-flex d-lg-none align-items-center ms-auto me-2">
      <div class="cart-icon" onclick="window.location.href='keranjang.php'">🛒
        <div class="cart-count" id="cartCount">0</div>
      </div>
    </div>

    <!-- Toggle button -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
      <span class="fa fa-bars"></span>
    </button>

    <div class="collapse navbar-collapse align-items-center" id="navbarCollapse">
<!-- Versi desktop -->
<div class="search-container d-none d-lg-flex me-auto">
  <input type="text" id="searchInput" class="search-box" placeholder="Mau makan apa?" onkeypress="if(event.key==='Enter'){performSearch()}">
  <i class="fas fa-search search-icon" onclick="performSearch()"></i>
</div>

      <!-- Nav Links -->
      <div class="navbar-nav ms-auto align-items-center">
        <a href="index.php" class="nav-item nav-link <?= $current=='index.php' ? 'active' : '' ?>">Beranda</a>
        <a href="status_pesanan.php" class="nav-item nav-link <?= $current=='status_pesanan.php' ? 'active' : '' ?>">Riwayat</a>
        <a href="akun.php" class="nav-item nav-link <?= $current=='akun.php' ? 'active' : '' ?>">Profil</a>
        <?php if (!$is_logged_in): ?>
          <a href="login.php" class="nav-item nav-link"><i class="fa fa-user me-1"></i>Login</a>
        <?php else: ?>
          <a href="logout.php" class="nav-item nav-link"><i class="fa fa-sign-out-alt me-1"></i></a>
        <?php endif; ?>
        <!-- Cart icon desktop -->
        <div class="cart-icon d-none d-lg-block ms-2" onclick="window.location.href='keranjang.php'">🛒
          <div class="cart-count" id="cartCount">0</div>
        </div>
      </div>
    </div>
</div>
</nav>

<!-- Modal Lokasi -->
<div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="locationModalLabel">Pilih Lokasi Anda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <!-- Peta -->
        <div id="map" style="height: 400px; width: 100%;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" onclick="confirmLocation()">Konfirmasi Lokasi</button>
      </div>
    </div>
  </div>
</div>
<div class="container-xxl py-5 bg-dark hero-header mb-5 promo-slider">
 <!-- Slide 1 -->
<div class="promo-slide active">
    <div class="container my-5 py-5 h-100 d-flex align-items-center">
        <div class="row align-items-center g-5 flex-column flex-lg-row">
            <div class="col-lg-6 text-center text-lg-start">
                <div class="promo-badge animated slideInLeft">🔥 PROMO SPESIAL</div>
                <h1 class="display-3 text-white animated slideInLeft">DISKON 50%<br>Semua Menu Kebab</h1>
                <p class="text-white animated slideInLeft mb-4 pb-2">Nikmati kelezatan kebab premium dengan harga terbaik! Promo terbatas hingga akhir bulan.</p>
                <span class="btn btn-primary py-sm-3 px-sm-5 me-3 animated slideInLeft">
  Kode Promo: KEBABHEMAT50
</span>
            </div>
            <div class="col-lg-6 text-center text-lg-end overflow-hidden">
                <img class="img-fluid" src="img/hero.png" alt="Promo Spesial">
            </div>
        </div>
    </div>
</div>

<!-- Slide 2 -->
<div class="promo-slide">
    <div class="container my-5 py-5 h-100 d-flex align-items-center">
        <div class="row align-items-center g-5 flex-column flex-lg-row">
            <div class="col-lg-6 text-center text-lg-start">
                <div class="promo-badge animated slideInLeft">🔥 PROMO SPESIAL</div>
                <h1 class="display-3 text-white animated slideInLeft">DISKON 30%<br>Min. Order 100K</h1>
                <p class="text-white animated slideInLeft mb-4 pb-2">Ajak teman dan keluarga menikmati promo istimewa kami</p>
                <a href="#" class="btn btn-primary py-sm-3 px-sm-5 me-3 animated slideInLeft">Kode Promo: 100RIBUDAY</a>
            </div>
            <div class="col-lg-6 text-center text-lg-end overflow-hidden">
                <img class="img-fluid" src="img/hero1.png" alt="Gratis Ongkir">
            </div>
        </div>
    </div>
</div>

<!-- Slide 3 -->
<div class="promo-slide">
    <div class="container my-5 py-5 h-100 d-flex align-items-center">
        <div class="row align-items-center g-5 flex-column flex-lg-row">
            <div class="col-lg-6 text-center text-lg-start">
                <div class="promo-badge animated slideInLeft">🎉DISKON HANYA UNTUKMU</div>
                <h1 class="display-3 text-white animated slideInLeft">DISKON 25%<br>untuk orang terpilih</h1>
                <p class="text-white animated slideInLeft mb-4 pb-2">Jadikan keberuntungan anda menjadi motivasi di masa depan</p>
                <a href="#" class="btn btn-primary py-sm-3 px-sm-5 me-3 animated slideInLeft">Kode Promo: FORYOU</a>
            </div>
            <div class="col-lg-6 text-center text-lg-end overflow-hidden">
                <img class="img-fluid" src="img/hero2.png" alt="Buy 2 Get 1">
            </div>
        </div>
    </div>
</div>

    <!-- Navigation -->
    <button class="slider-nav prev" onclick="prevSlide()">‹</button>
    <button class="slider-nav next" onclick="nextSlide()">›</button>
    
    <!-- Indicators -->
    <div class="slider-indicators">
        <div class="slider-dot active" onclick="currentSlide(1)"></div>
        <div class="slider-dot" onclick="currentSlide(2)"></div>
        <div class="slider-dot" onclick="currentSlide(3)"></div>
    </div>
</div>
        <!-- Navbar & Hero End -->

<section class="our-foods" id="our-foods">

        <section class="our-foods" id="our-foods">
  <h1 class="heading">Pilihan <span>Menu</span></h1>

     

    <!-- Modal -->
    <!-- Modal Overlay -->
<div class="modal-overlay" id="modalOverlay">
    <!-- Modal Box -->
    <div class="custom-modal">
        <!-- Modal Header -->
        <div class="modal-header">
            <img id="modalImage" src="" alt="Gambar Produk" class="modal-image">
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>

        <!-- Modal Content -->
        <div class="modal-content">
            <!-- Judul dan Icon -->
            <div class="modal-title">
                <img id="modalIcon" src="" alt="Icon" class="modal-icon">
                <h2 id="modalTitle"></h2>
            </div>

            <!-- Nama restoran -->
            <div class="modal-restaurant">Warung Kebab Istimewa</div>

            <!-- Rating dan Ulasan -->
            <div class="modal-rating">
                <div class="modal-rating-stars">
                    <span class="stars">⭐</span>
                    <span id="modalRating">4.8</span>
                </div>
                <span id="modalReviews">(245+)</span>
            </div>

            <!-- Deskripsi -->
            <p class="modal-description" id="modalDescription">Deskripsi makanan akan tampil di sini.</p>

            <!-- Section Jumlah dan Harga -->
            <div class="quantity-section">
                <div class="quantity-label">Jumlah Pesanan</div>
                <div class="quantity-controls">
                    <div class="quantity-buttons">
                        <button class="quantity-btn" id="decreaseBtn" onclick="decreaseQuantity()">−</button>
                        <span class="quantity-display" id="quantity">1</span>
                        <button class="quantity-btn" onclick="increaseQuantity()">+</button>
                    </div>
                    <div class="quantity-price">
                        <span id="itemPrice">Rp 0</span>
                    </div>
                </div>
            </div>

            <!-- Total Harga -->
            <div class="total-section">
                <span class="total-label">Total Harga:</span>
                <span class="total-price" id="totalPrice">Rp 0</span>
            </div>

            <!-- Tombol Aksi -->
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeModal()">Batal</button>
                <button class="btn-add-to-cart" onclick="addToCart()">Tambah ke Keranjang</button>
            </div>
        </div>
    </div>
</div>

    <div class="menu-container">
    <?php
    // --- KODE BARU DIMULAI DARI SINI ---

    // Mengambil data produk dari database
    $sql = "SELECT * FROM products ORDER BY id ASC";
    $result = mysqli_query($conn, $sql);

    // Cek jika ada produk
    if (mysqli_num_rows($result) > 0) {
        // Looping untuk setiap produk
        while ($product = mysqli_fetch_assoc($result)) {
            // Hitung diskon (logika ini ada di dalam loop)
            $discount_percentage = 0;
            if ($product['original_price'] > 0 && $product['price'] < $product['original_price']) {
                $discount_percentage = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
            }
    ?>
    
    <div class="menu-card" data-nama="<?php echo strtolower(htmlspecialchars($product['name'])); ?>">
        <div class="image-container">
            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="menu-image">
            <div class="delivery-time">
                <span>🕒</span>
                <span><?php echo htmlspecialchars($product['delivery_time']); ?></span>
            </div>
            <div class="heart-icon">
                <span>🤍</span>
            </div>
        </div>
        <div class="content">
            <div class="menu-title">
                <img src="<?php echo htmlspecialchars($product['icon_path']); ?>" alt="icon" class="icon">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
            </div>
            <div class="restaurant-name">Warung Kebab Istimewa</div>
            <div class="rating-section">
                <div class="rating">
                    <span class="stars">⭐</span>
                    <span class="rating-number"><?php echo htmlspecialchars($product['rating']); ?></span>
                </div>
                <span class="review-count"><?php echo htmlspecialchars($product['reviews']); ?></span>
            </div>
            <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>
            <div class="price-section">
                <div class="price">
                    <span class="current-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                    <?php if ($product['original_price'] > 0): ?>
                    <span class="original-price">Rp <?php echo number_format($product['original_price'], 0, ',', '.'); ?></span>
                    <span class="discount">-<?php echo $discount_percentage; ?>%</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="delivery-info">✅ Gratis ongkir untuk pembelian di atas Rp 50.000</div>
            <button class="order-button" onclick="openModal(
    <?php echo $product['id']; ?>,  // <-- TAMBAHKAN INI (ID ANGKA PRODUK)
    '<?php echo htmlspecialchars($product['slug']); ?>', 
    '<?php echo htmlspecialchars($product['name']); ?>', 
    '<?php echo htmlspecialchars($product['icon_path']); ?>', 
    '<?php echo htmlspecialchars($product['image_path']); ?>', 
    <?php echo $product['price']; ?>, 
    <?php echo $product['original_price']; ?>, 
    '<?php echo htmlspecialchars($product['rating']); ?>', 
    '<?php echo htmlspecialchars($product['reviews']); ?>', 
    '<?php echo htmlspecialchars(addslashes($product['description'])); ?>'
)">Order Sekarang</button>
        </div>
    </div>

    <?php
        } // Akhir dari while loop
    } else {
        echo "<p>Belum ada produk yang tersedia.</p>";
    }
    // --- KODE BARU BERAKHIR DI SINI ---
    ?>
</div>
    <!-- Tambahkan box makanan lainnya di sini sesuai kebutuhan -->
    
  </div>
</section>
        <!-- Testimonial Start -->
  


        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script>
function copyPromoCode(button, promoCode) {
    navigator.clipboard.writeText(promoCode).then(function() {
        // Jika berhasil disalin
        button.innerHTML = '<i class="fas fa-check"></i> Tersalin!';
        button.classList.add('copied');

        // Kembalikan ke teks semula setelah 2 detik
        setTimeout(function() {
            button.innerHTML = '<i class="far fa-copy"></i> Salin Kode';
            button.classList.remove('copied');
        }, 2000);
    }, function(err) {
        // Jika gagal
        alert('Gagal menyalin kode: ', err);
    });
}
</script>

</body>

</html>