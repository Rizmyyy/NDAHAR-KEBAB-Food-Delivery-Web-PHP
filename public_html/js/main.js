(function ($) {
    "use strict";


    
    // Initiate the wowjs
    new WOW().init();

// Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 45) {
            $('.navbar').addClass('sticky-top shadow-sm');
        } else {
            $('.navbar').removeClass('sticky-top shadow-sm');
        }
    });
    
    
    // Dropdown on mouse hover
    const $dropdown = $(".dropdown");
    const $dropdownToggle = $(".dropdown-toggle");
    const $dropdownMenu = $(".dropdown-menu");
    const showClass = "show";
    
    $(window).on("load resize", function() {
        if (this.matchMedia("(min-width: 992px)").matches) {
            $dropdown.hover(
            function() {
                const $this = $(this);
                $this.addClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "true");
                $this.find($dropdownMenu).addClass(showClass);
            },
            function() {
                const $this = $(this);
                $this.removeClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "false");
                $this.find($dropdownMenu).removeClass(showClass);
            }
            );
        } else {
            $dropdown.off("mouseenter mouseleave");
        }
    });
    
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


    // Facts counter
    $('[data-toggle="counter-up"]').counterUp({
        delay: 10,
        time: 2000
    });




    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1000,
        center: true,
        margin: 24,
        dots: true,
        loop: true,
        nav : false,
        responsive: {
            0:{
                items:1
            },
            768:{
                items:2
            },
            992:{
                items:3
            }
        }
    });

    
    
})(jQuery);

let slideIndex = 0;
const slides = document.querySelectorAll('.promo-slide');
const dots = document.querySelectorAll('.slider-dot');

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    slides[index].classList.add('active');
    dots[index].classList.add('active');
}

function nextSlide() {
    slideIndex = (slideIndex + 1) % slides.length;
    showSlide(slideIndex);
}

function prevSlide() {
    slideIndex = (slideIndex - 1 + slides.length) % slides.length;
    showSlide(slideIndex);
}

function currentSlide(index) {
    slideIndex = index - 1;
    showSlide(slideIndex);
}

setInterval(nextSlide, 5000); // Auto slide every 5 seconds

function performSearch() {
            const searchInput = document.getElementById('searchInput');
            const searchTerm = searchInput.value.trim();
            
            if (searchTerm) {
                // Simulasi pencarian - bisa diganti dengan fungsi pencarian yang sesuai
                console.log('Mencari:', searchTerm);
                alert(`Mencari: "${searchTerm}"`);
                
                // Contoh redirect ke halaman menu dengan parameter pencarian
                // window.location.href = `menu.html?search=${encodeURIComponent(searchTerm)}`;
            } else {
                alert('Masukkan kata kunci pencarian!');
            }
        }
        
        // Enter key support
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
        
        // Search suggestions (optional)
        const searchSuggestions = [
            'Kebab Ayam',
            'Kebab Daging',
            'Kebab Vegetarian',
            'Kebab Spesial',
            'Paket Combo',
            'Minuman',
            'Dessert'
        ];
        
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const value = e.target.value.toLowerCase();
            
            // Simple autocomplete logic
            if (value.length > 2) {
                const matches = searchSuggestions.filter(item => 
                    item.toLowerCase().includes(value)
                );
                
                // You can implement dropdown suggestions here
                console.log('Suggestions:', matches);
            }
        });

function performSearch(inputId = 'searchInput') {
  const input = document.getElementById(inputId);
  if (input) {
    const query = input.value.trim();
    if (query) {
      window.location.href = 'cari.php?q=' + encodeURIComponent(query);
    }
  }
}

        let map, marker;
function initMap() {
    map = L.map('map').setView([-6.200000, 106.816666], 13); // Koordinat awal Jakarta
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    map.on('click', function (e) {
        if (marker) map.removeLayer(marker);
        marker = L.marker(e.latlng).addTo(map);
        marker.bindPopup("Lokasi dipilih: " + e.latlng.toString()).openPopup();
    });
}

function confirmLocation() {
    if (marker) {
        alert("Lokasi Anda: " + marker.getLatLng().toString());
        // Simpan lokasi ke localStorage, form, atau kirim ke backend
        var modal = bootstrap.Modal.getInstance(document.getElementById('locationModal'));
        modal.hide();
    } else {
        alert("Silakan pilih lokasi terlebih dahulu.");
    }
}

document.getElementById('locationModal').addEventListener('shown.bs.modal', function () {
    setTimeout(() => {
        if (!map) initMap();
        else map.invalidateSize();
    }, 200);
});

let currentItem = {}; // Untuk menyimpan data produk yang sedang dilihat di modal
let currentQuantity = 1; // Untuk menyimpan jumlah yang dipilih di modal

/**
 * Fungsi untuk membuka modal. Sekarang menerima ID Angka sebagai parameter pertama.
 * @param {number} productId - ID angka dari produk (misal: 1, 2, 3)
 * @param {string} productSlug - Slug produk (misal: 'sapi')
 * @param {string} title - Nama produk
 * ... dan parameter lainnya
 */
function openModal(productId, productSlug, title, icon, image, currentPrice, originalPrice, rating, reviews, description) {
    const numericPrice = parseFloat(currentPrice) || 0;
    // Simpan semua data, termasuk ID Angka dan Slug dengan benar
    currentItem = { 
        id: productId, 
        slug: productSlug,
        title: title, 
        icon: icon, 
        image: image, 
        currentPrice: numericPrice, // <-- Harga sekarang diambil dari parameter yang benar
        originalPrice: originalPrice, 
        rating: rating, 
        reviews: reviews, 
        description: description 
    };
    currentQuantity = 1;

    // Mengisi konten modal (logika ini tidak berubah)
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalIcon').src = icon;
    document.getElementById('modalImage').src = image;
    document.getElementById('modalImage').alt = title;
    document.getElementById('modalRating').textContent = rating;
    document.getElementById('modalReviews').textContent = `(${reviews})`;
    document.getElementById('modalDescription').textContent = description;

    updateQuantityDisplay();
    updatePriceDisplay();

    // Menampilkan modal
    const modalOverlay = document.querySelector('.modal-overlay');
    modalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Fungsi-fungsi pembantu modal (tidak berubah)
function closeModal() {
    const modalOverlay = document.querySelector('.modal-overlay');
    modalOverlay.classList.remove('active');
    document.body.style.overflow = 'auto';
}

function updatePriceDisplay() {
    const itemPrice = currentItem.currentPrice * currentQuantity;
    // Tampilan harga per item sekarang akan benar
    document.getElementById('itemPrice').textContent = `Rp ${currentItem.currentPrice.toLocaleString('id-ID')}`;
    // Tampilan total harga sekarang akan benar
    document.getElementById('totalPrice').textContent = `Rp ${itemPrice.toLocaleString('id-ID')}`;
}

function performSearch() {
    const keyword = document.getElementById('searchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.menu-card');

    cards.forEach(card => {
        const nama = card.getAttribute('data-nama');
        if (nama.includes(keyword)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Tambahkan juga listener jika user tekan Enter
document.getElementById('searchInput').addEventListener('keyup', function(event) {
    if (event.key === 'Enter') {
        performSearch();
    }
});

function updateQuantityDisplay() {
    document.getElementById('quantity').textContent = currentQuantity;
    document.getElementById('decreaseBtn').disabled = currentQuantity <= 1;
}

function increaseQuantity() {
    if (currentQuantity < 99) {
        currentQuantity++;
        updateQuantityDisplay();
        updatePriceDisplay();
    }
}

function decreaseQuantity() {
    if (currentQuantity > 1) {
        currentQuantity--;
        updateQuantityDisplay();
        updatePriceDisplay();
    }
}

// Fungsi addToCart BARU yang mengirim ID ANGKA
function addToCart() {
    const formData = new FormData();
    formData.append('id', currentItem.id); 
    formData.append('nama', currentItem.title);
    formData.append('harga', currentItem.currentPrice);
    formData.append('jumlah', currentQuantity);
    formData.append('image', currentItem.image);

    fetch('tambah_keranjang.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCountDOM(data.cartCount);
            showSuccessMessage(`${currentItem.title} berhasil ditambahkan!`);
            closeModal();
        } else {
            alert('Gagal menambahkan produk: ' + (data.message || 'Terjadi error'));
        }
    })
    .catch(error => {
        console.error('Error saat addToCart:', error);
        alert('Terjadi kesalahan koneksi.');
    });
}

// Fungsi BARU untuk update ikon keranjang
function updateCartCountDOM(count) {
    const cartCountElement = document.getElementById('cartCount');
    if (cartCountElement) {
        cartCountElement.textContent = count;
    }
}

// Fungsi BARU untuk inisialisasi keranjang
function initializeCart() {
    fetch('get_cart_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCountDOM(data.cartCount);
            }
        })
        .catch(error => {
            console.error('Error saat initializeCart:', error);
        });
}


function showSuccessMessage(message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: #27ae60;
        color: white;
        padding: 15px 25px;
        border-radius: 25px;
        box-shadow: 0 8px 20px rgba(39, 174, 96, 0.3);
        z-index: 9999;
        font-weight: 600;
        opacity: 0;
        transition: all 0.3s ease;`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(-50%) translateY(10px)';
    }, 100);

    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(-50%) translateY(-20px)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}


function initializeHeartToggle() {
    document.querySelectorAll('.heart-icon').forEach(heart => {
        heart.addEventListener('click', function(e) {
            e.stopPropagation();
            const heartSpan = this.querySelector('span');
            if (heartSpan.textContent === '🤍') {
                heartSpan.textContent = '❤️';
                this.style.background = '#ff6b6b';
            } else {
                heartSpan.textContent = '🤍';
                this.style.background = 'white';
            }
        });
    });
}

function initializeCardClick() {
    document.querySelectorAll('.menu-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.closest('.heart-icon') && !e.target.closest('.order-button')) {
                this.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    this.style.transform = 'translateY(-10px)';
                }, 100);
            }
        });
    });
}

function initializeModalOverlay() {
    const modalOverlay = document.querySelector('.modal-overlay');
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    loadCart(); // ⬅️ WAJIB dipanggil dulu
    updateCartCount();
    initializeHeartToggle();
    initializeCardClick();
    initializeModalOverlay();
    initializeCart();

    let slideIndex = 0;
const slides = document.querySelectorAll('.promo-slide');
const dots = document.querySelectorAll('.slider-dot');

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    slides[index].classList.add('active');
    dots[index].classList.add('active');
}

function nextSlide() {
    slideIndex = (slideIndex + 1) % slides.length;
    showSlide(slideIndex);
}

function prevSlide() {
    slideIndex = (slideIndex - 1 + slides.length) % slides.length;
    showSlide(slideIndex);
}

function currentSlide(index) {
    slideIndex = index - 1;
    showSlide(slideIndex);
}

setInterval(nextSlide, 5000); // Auto slide every 5 seconds
});