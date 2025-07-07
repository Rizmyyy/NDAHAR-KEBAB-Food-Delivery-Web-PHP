<?php
// Pastikan Anda memiliki file koneksi database
// Sesuaikan path ini dengan lokasi file koneksi database Anda
require_once 'koneksi.php';

$promo = null; // Inisialisasi variabel promo untuk menampung data yang akan diedit
$error_message = ''; // Untuk menyimpan pesan error jika ada
$success_message = ''; // Untuk menyimpan pesan sukses jika ada

// --- Bagian 1: Mengambil data promo yang akan diedit ---
// Pastikan ID promo ada di URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_promo = mysqli_real_escape_string($conn, $_GET['id']);

    $sql_select = "SELECT * FROM promos WHERE id = '$id_promo'";
    $result_select = mysqli_query($conn, $sql_select);

    if (mysqli_num_rows($result_select) > 0) {
        $promo = mysqli_fetch_assoc($result_select);
    } else {
        // Jika promo tidak ditemukan, arahkan kembali ke daftar promo
        header("Location: admin_produk.php?status=edit_error&message=" . urlencode("Promo tidak ditemukan."));
        exit();
    }
} else {
    // Jika ID tidak disediakan, arahkan kembali ke daftar promo
    header("Location: admin_produk.php?status=edit_error&message=" . urlencode("ID promo tidak disediakan untuk pengeditan."));
    exit();
}

// --- Bagian 2: Memproses update data saat formulir disubmit ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pastikan ID promo juga dikirim melalui hidden input
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id_promo_post = mysqli_real_escape_string($conn, $_POST['id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $discount_percentage = mysqli_real_escape_string($conn, $_POST['discount_percentage']);
        $min_purchase = mysqli_real_escape_string($conn, $_POST['min_purchase']);
        $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
        $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        // Kolom is_active (jika ada dan ingin diedit di form)
        $is_active = isset($_POST['is_active']) ? 1 : 0; // Checkbox, 1 jika dicentang, 0 jika tidak

        // Query untuk update promo
        $sql_update = "UPDATE promos SET 
                        name = '$name',
                        discount_percentage = '$discount_percentage',
                        min_purchase = '$min_purchase',
                        start_date = '$start_date',
                        end_date = '$end_date',
                        description = '$description',
                        is_active = '$is_active' 
                        WHERE id = '$id_promo_post'";

        if (mysqli_query($conn, $sql_update)) {
            $success_message = "Promo berhasil diperbarui!";
            // Perbarui objek promo agar form menampilkan data terbaru setelah update
            $promo['name'] = $name;
            $promo['discount_percentage'] = $discount_percentage;
            $promo['min_purchase'] = $min_purchase;
            $promo['start_date'] = $start_date;
            $promo['end_date'] = $end_date;
            $promo['description'] = $description;
            $promo['is_active'] = $is_active;

            // Redirect setelah sukses (opsional, bisa juga tampilkan pesan sukses di halaman yang sama)
            // header("Location: promos.php?status=edited_success");
            // exit();
        } else {
            $error_message = "Error saat memperbarui promo: " . mysqli_error($conn);
        }
    } else {
        $error_message = "ID promo tidak valid untuk pembaruan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Promo - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: bold;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Edit Promo</h2>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($promo): // Tampilkan form hanya jika data promo berhasil diambil ?>
            <form action="edit_promo_admin.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($promo['id']); ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="promoName" class="form-label">Nama/Kode Promo (Harus Unik)</label>
                        <input type="text" name="name" id="promoName" class="form-control" placeholder="Contoh: DISKONBARU" value="<?php echo htmlspecialchars($promo['name']); ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="discountPercentage" class="form-label">Diskon (%)</label>
                        <input type="number" name="discount_percentage" id="discountPercentage" class="form-control" min="1" max="100" placeholder="Contoh: 15" value="<?php echo htmlspecialchars($promo['discount_percentage']); ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="minPurchase" class="form-label">Min. Pembelian (Rp)</label>
                        <input type="number" name="min_purchase" id="minPurchase" class="form-control" placeholder="Contoh: 50000" value="<?php echo htmlspecialchars($promo['min_purchase']); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="startDate" class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="startDate" class="form-control" value="<?php echo htmlspecialchars($promo['start_date']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="endDate" class="form-label">Tanggal Berakhir</label>
                        <input type="date" name="end_date" id="endDate" class="form-control" value="<?php echo htmlspecialchars($promo['end_date']); ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="promoDescription" class="form-label">Deskripsi Promo</label>
                    <textarea name="description" id="promoDescription" class="form-control" rows="2"><?php echo htmlspecialchars($promo['description']); ?></textarea>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?php echo $promo['is_active'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="isActive">
                        Aktifkan Promo
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="admin_produk.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </form>
        <?php else: ?>
            <p class="text-danger">Promo tidak ditemukan atau terjadi kesalahan saat memuat data.</p>
            <a href="admin_produk.php" class="btn btn-primary">Kembali ke Daftar Promo</a>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Tutup koneksi database di akhir skrip
mysqli_close($conn);
?>