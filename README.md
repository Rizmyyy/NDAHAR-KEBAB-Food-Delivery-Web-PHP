# NDAHAR-KEBAB-Food-Delivery-Web-PHP
Aplikasi web food delivery lengkap berbasis PHP &amp; MySQL. Fitur meliputi penelusuran menu, keranjang, pemesanan, pembayaran, status pesanan, akun pengguna, dan chat. Dilengkapi panel admin untuk manajemen menu, pesanan, &amp; pengguna. Solusi komprehensif untuk platform pemesanan makanan online.

## Deskripsi Proyek
Proyek ini adalah aplikasi web *food delivery* lengkap yang dikembangkan menggunakan PHP sebagai *backend*, serta HTML, CSS, dan JavaScript untuk *frontend*. Sistem ini dirancang untuk memungkinkan pengguna menelusuri menu, mengelola keranjang belanja, melakukan pemesanan, dan melacak status pesanan. Tersedia juga fitur *chat* *real-time* untuk komunikasi, serta panel administrasi untuk mengelola semua aspek aplikasi.

## Fitur Utama

### Untuk Pengguna
* **Pilihan Menu Interaktif**: Jelajahi berbagai kategori dan item makanan.
* **Keranjang Belanja**: Tambah, hapus, dan perbarui jumlah item dalam keranjang.
* **Pemesanan & Pembayaran**: Proses *checkout* yang mudah dan opsi pembayaran.
* **Pelacakan Status Pesanan**: Lihat *update* status pesanan secara *real-time*.
* **Manajemen Akun**: Pendaftaran, *login*, pengelolaan profil, dan riwayat pesanan.
* **Chat Real-time**: Berkomunikasi langsung dengan admin atau *support*.

### Untuk Admin
* **Manajemen Menu**: Tambah, edit, dan hapus produk serta kategori.
* **Manajemen Pesanan**: Lihat, perbarui status, dan kelola semua pesanan.
* **Manajemen Pengguna**: Kelola akun pengguna terdaftar.
* **Manajemen Promo**: Tambah, edit, dan hapus kode promo.
* **Manajemen Chat**: Melihat dan merespons pesan *chat* dari pengguna.

## Teknologi yang Digunakan
* **Backend**: PHP (Native)
* **Database**: MySQL
* **Frontend**: HTML5, CSS3, JavaScript
* **Styling**: Bootstrap
* **Server**: XAMPP (Apache)

## Cara Menjalankan Aplikasi

### Prasyarat
* **XAMPP / MAMP / WAMP** (atau *web server* Apache dengan PHP dan MySQL terinstal)
* **Web Browser** (Google Chrome, Firefox, dll.)

### Langkah-langkah Setup

1.  **Klon Repositori**:
    ```bash
    git clone [https://github.com/NamaPenggunaAnda/Food-Delivery-Web-App-PHP.git](https://github.com/Rizmyyy/NDAHAR-KEBAB-Food-Delivery-Web-PHP.git)
    NDAHAR-KEBAB-Food-Delivery-Web-PHP
    ```

2.  **Konfigurasi Variabel Lingkungan (Opsional namun Direkomendasikan)**:
    Buat file `.env` di root folder proyek Anda. Salin konten dari `.env.example` ke dalam `.env` dan isi dengan nilai-nilai yang sesuai (misalnya, kredensial database).

3.  **Setup Database**:
    * Buka XAMPP Control Panel dan mulai Apache dan MySQL.
    * Buka PHPMyAdmin. Buat database baru.
    * Impor skema database dari `docs/database_schema.sql` ke database yang baru Anda buat.

4.  **Tempatkan Kode di Web Server**:
    Salin seluruh isi folder `public_html/` dari repositori yang Anda *clone* ke dalam folder `htdocs` XAMPP Anda.
    Contoh: `C:\xampp\htdocs\food-delivery-app`

5.  **Konfigurasi Koneksi Database**:
    Buka file `public_html/koneksi.php` dan pastikan kredensial database (nama *host*, *username*, *password*, nama *database*) sudah sesuai dengan konfigurasi XAMPP dan database Anda.

6.  **Akses Aplikasi**:
    Buka *web browser* Anda dan navigasikan ke `http://localhost/food-delivery-app/`.

## Live Demo
Aplikasi ini pernah di-hosting dan dapat diakses melalui domain berikut:
[**ndaharkebab.jurnalanalisis.com**](http://ndaharkebab.jurnalanalisis.com)
*(Catatan: Ketersediaan demo ini tergantung pada masa aktif *hosting*.)*

## Dokumentasi Visual (Screenshot)
Berikut adalah beberapa pratinjau tampilan aplikasi:

### Halaman Utama (Homepage)
![Halaman Utama Website](https://github.com/user-attachments/assets/a0ead60f-53d5-4d16-9213-59da3d7acf09)

### Halaman Menu Makanan
![Tampilan Pilihan Menu](https://github.com/user-attachments/assets/f2b3392e-8719-4906-b643-4d3345f34dee)

### Halaman Keranjang Belanja
![Halaman Keranjang](https://github.com/user-attachments/assets/ace9577e-81d7-4e98-97bb-bdb2e4c9ab26)

### Dashboard Admin
![Halaman Utama Admin Panel](https://github.com/user-attachments/assets/13231a53-3d3e-4857-a923-2f86defb815b)

### Fitur Chat
![Halaman Chat ke Admin](https://github.com/user-attachments/assets/a040af1b-6c72-4bf2-b9b1-6b63ea512f45)

### Tampilan Mobile
![Tampilan Mobile](https://github.com/user-attachments/assets/79426ca1-5cde-4d80-bfd2-6c7bb2f5e383)

## Kontribusi
Kami menyambut kontribusi! Jika Anda ingin berkontribusi pada proyek ini, silakan ikuti langkah-langkah berikut:
1.  *Fork* repositori ini.
2.  Buat *branch* baru untuk fitur Anda (`git checkout -b feature/nama-fitur`).
3.  Lakukan perubahan dan *commit* (`git commit -m 'Menambahkan fitur baru: nama fitur'`).
4.  *Push* ke *branch* Anda (`git push origin feature/nama-fitur`).
5.  Buat *Pull Request* baru.

## Lisensi
Proyek ini dilisensikan di bawah Lisensi MIT. Lihat file `LICENSE` untuk detail lebih lanjut.

## Kontak
Jika ada pertanyaan atau saran, silakan hubungi kami melalui *issue tracker* di GitHub.






