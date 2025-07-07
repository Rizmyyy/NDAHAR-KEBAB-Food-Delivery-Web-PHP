<?php
require 'cek_admin.php'; // Panggil keamanan
require 'koneksi.php';   // Hubungkan ke DB

// 1. Cek apakah ID produk ada di URL
if (!isset($_GET['id'])) {
    header('Location: admin_produk.php?page=products'); // Jika tidak ada ID, kembali
    exit();
}

$id = (int)$_GET['id'];

// 2. Ambil data produk yang akan diedit dari database
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

// Jika produk dengan ID tersebut tidak ditemukan, hentikan
if (!$product) {
    die("Error: Produk tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk  <?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="icon" href="img/logo.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fc; padding: 30px; }
        .form-container { 
            max-width: 800px; 
            margin: auto; 
            background: white; 
            padding: 30px; 
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2 class="mb-4">Edit Produk</h2>
        <hr>
        
        <form action="proses_produk.php" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Harga Jual</label>
                    <input type="number" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="form-label">Gambar Saat Ini</label><br>
                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" class="img-thumbnail" width="150">
                    <input type="hidden" name="old_image" value="<?php echo $product['image_path']; ?>">
                </div>
                <div class="col-md-9">
                    <label class="form-label">Ganti Gambar (Biarkan kosong jika tidak ingin diubah)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>

            <hr class="my-4">

            <button type="submit" name="update" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
            <a href="admin_produk.php?page=products" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>