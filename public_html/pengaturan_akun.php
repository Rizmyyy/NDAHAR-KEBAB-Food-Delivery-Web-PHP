<?php
session_start();
include 'koneksi.php'; // Pastikan file koneksi.php ada dan terhubung dengan benar

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = ''; // Untuk pesan feedback ke user
$error = '';   // Untuk pesan error

// Ambil data user saat ini dari database
// Penting: Gunakan prepared statement untuk keamanan!
$stmt_select = $conn->prepare("SELECT id, username, email, password FROM users WHERE id = ?");
if ($stmt_select === false) {
    die("Error menyiapkan statement select: " . $conn->error);
}
$stmt_select->bind_param("i", $user_id);
$stmt_select->execute();
$result_select = $stmt_select->get_result();
$user = $result_select->fetch_assoc();
$stmt_select->close();

// Jika user tidak ditemukan (misal data sesi tidak valid)
if (!$user) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// --- PROSES UPDATE DATA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aksi untuk update username/email
    if (isset($_POST['update_profile'])) {
        $new_username = trim($_POST['username']);
        $new_email = trim($_POST['email']);

        // Validasi input
        if (empty($new_username) || empty($new_email)) {
            $error = "Username dan email tidak boleh kosong.";
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid.";
        } else {
            // Cek apakah username atau email sudah ada di user lain (kecuali user ini sendiri)
            $stmt_check_dupes = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            if ($stmt_check_dupes === false) {
                $error = "Error menyiapkan statement duplikasi: " . $conn->error;
            } else {
                $stmt_check_dupes->bind_param("ssi", $new_username, $new_email, $user_id);
                $stmt_check_dupes->execute();
                $result_check_dupes = $stmt_check_dupes->get_result();

                if ($result_check_dupes->num_rows > 0) {
                    $error = "Username atau email sudah digunakan oleh akun lain.";
                } else {
                    // Update data di database
                    $stmt_update_profile = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                    if ($stmt_update_profile === false) {
                        $error = "Error menyiapkan statement update profil: " . $conn->error;
                    } else {
                        $stmt_update_profile->bind_param("ssi", $new_username, $new_email, $user_id);
                        if ($stmt_update_profile->execute()) {
                            $message = "Profil berhasil diperbarui!";
                            // Update data user di variabel $user setelah berhasil diupdate
                            $user['username'] = $new_username;
                            $user['email'] = $new_email;
                        } else {
                            $error = "Gagal memperbarui profil: " . $stmt_update_profile->error;
                        }
                        $stmt_update_profile->close();
                    }
                }
                $stmt_check_dupes->close();
            }
        }
    }

    // Aksi untuk update password
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validasi input password
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "Semua field password harus diisi.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Password baru dan konfirmasi password tidak cocok.";
        } elseif (strlen($new_password) < 6) { // Contoh minimal 6 karakter
            $error = "Password baru minimal 6 karakter.";
        } else {
            // Verifikasi password saat ini
            if (password_verify($current_password, $user['password'])) {
                // Hash password baru
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password di database
                $stmt_update_password = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt_update_password === false) {
                    $error = "Error menyiapkan statement update password: " . $conn->error;
                } else {
                    $stmt_update_password->bind_param("si", $hashed_password, $user_id);
                    if ($stmt_update_password->execute()) {
                        $message = "Password berhasil diperbarui!";
                        // Update password di variabel $user agar konsisten (opsional, karena tidak ditampilkan)
                        $user['password'] = $hashed_password;
                    } else {
                        $error = "Gagal memperbarui password: " . $stmt_update_password->error;
                    }
                    $stmt_update_password->close();
                }
            } else {
                $error = "Password saat ini salah.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun</title>
    <link rel="icon" href="img/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-image: linear-gradient(rgba(7, 0, 109, 0.7), rgba(68, 0, 179, 0.7)), url('img/bg.png');
        background-size: cover; /* Pastikan background menutupi seluruh area */
        background-position: center;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }
    .container-custom {
        max-width: 800px; /* Lebar maksimum kontainer, bisa disesuaikan */
        width: 90%; /* Kontainer akan mengambil 90% lebar viewport pada layar lebih kecil */
        background: #fff;
        padding: 40px; /* Padding di dalam kontainer */
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .card-title {
        font-size: 28px; /* Sedikit lebih besar dari 24px */
        color: #333;
        margin-bottom: 30px;
        text-align: center;
        position: relative;
        padding-bottom: 15px; /* Tambahan padding agar garis bawah tidak terlalu mepet */
    }
    .card-title::after {
        content: '';
        width: 60px; /* Sedikit lebih lebar */
        height: 4px; /* Sedikit lebih tebal */
        background: #667eea;
        position: absolute;
        bottom: 0px; /* Sesuaikan posisi garis */
        left: 50%;
        transform: translateX(-50%);
        border-radius: 5px;
    }
    h4 { /* Gaya untuk judul 'Informasi Profil' dan 'Ganti Password' */
        font-size: 22px;
        color: #444;
        margin-bottom: 25px; /* Jarak bawah */
        border-bottom: 1px solid #eee; /* Garis pemisah */
        padding-bottom: 10px; /* Padding di bawah judul */
    }
    .form-group {
        margin-bottom: 25px; /* Jarak antar grup form */
    }
    .form-group label {
        font-weight: 600;
        color: #555;
        margin-bottom: 8px; /* Jarak antara label dan input */
        display: block; /* Pastikan label mengambil baris penuh */
        font-size: 16px; /* Ukuran font label */
    }
    .form-control {
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 12px 18px; /* Padding input lebih besar */
        font-size: 16px; /* Ukuran font input */
        width: 100%; /* Pastikan input mengambil lebar penuh form-group */
        box-sizing: border-box; /* Penting: padding tidak menambah lebar total */
    }
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .btn-primary {
        background: #667eea;
        border-color: #667eea;
        border-radius: 8px;
        padding: 12px 30px; /* Padding tombol lebih besar */
        font-weight: 600;
        transition: background 0.3s ease, transform 0.2s ease;
        font-size: 16px; /* Ukuran font tombol */
        width: auto; /* Agar tombol tidak melebar penuh */
    }
    .btn-primary:hover {
        background: #5a6fd8;
        border-color: #5a6fd8;
        transform: translateY(-2px); /* Efek hover kecil */
    }
    .alert {
        border-radius: 8px;
        margin-bottom: 20px;
        padding: 15px 20px; /* Padding alert lebih besar */
        font-size: 16px; /* Ukuran font alert */
    }
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-color: #c3e6cb;
    }
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }
    .text-center {
        margin-top: 30px; /* Jarak atas lebih besar */
    }
    .text-center a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
        font-size: 16px; /* Ukuran font link */
    }
    .text-center a:hover {
        text-decoration: underline;
    }

    /* Media Queries untuk Responsif */
    @media (max-width: 768px) {
        .container-custom {
            padding: 25px; /* Kurangi padding pada layar kecil */
        }
        .card-title {
            font-size: 24px;
        }
        h4 {
            font-size: 20px;
        }
        .form-group label, .form-control, .btn-primary, .alert, .text-center a {
            font-size: 15px; /* Sedikit lebih kecil untuk mobilitas */
        }
    }
</style>
</head>
<body>
    <div class="container-custom">
        <h2 class="card-title">Pengaturan Akun Anda</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="pengaturan_akun.php" method="POST" class="mb-5">
            <h4>Informasi Profil</h4>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Update Profil</button>
        </form>

        <form action="pengaturan_akun.php" method="POST">
            <h4>Ganti Password</h4>
            <div class="form-group">
                <label for="current_password">Password Saat Ini</label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">Password Baru</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" name="update_password" class="btn btn-primary">Ganti Password</button>
        </form>

        <div class="text-center">
            <a href="akun.php"><i class="fas fa-chevron-left"></i> Kembali ke Akun Saya</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>