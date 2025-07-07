<?php
require 'cek_admin.php';
require 'koneksi.php';

// === TAMBAH PRODUK ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = (float)$_POST['price'];
    $original_price = isset($_POST['original_price']) && !empty($_POST['original_price']) ? (float)$_POST['original_price'] : 0;

    // Upload Gambar
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $file_name = uniqid() . '-' . basename($file['name']);
        $target_dir = "img/produk/"; // ✅ perbaikan utama
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // buat folder jika belum ada
        }
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];

        if (!in_array($imageFileType, $allowed_types)) {
            header('Location: admin_produk.php?page=products&status=gagal_format');
            exit();
        }

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $image_path = $target_file;
        } else {
            header('Location: admin_produk.php?page=products&status=gagal_upload');
            exit();
        }
    } else {
        header('Location: admin_produk.php?page=products&status=gagal_gambar');
        exit();
    }

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $icon_path = 'img/icon_default.png';
    $delivery_time = '20 min';
    $rating = 4.5;
    $reviews = '(0+)';

    $sql = "INSERT INTO products (name, description, price, original_price, image_path, icon_path, delivery_time, rating, reviews, slug) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssddssssss", $name, $description, $price, $original_price, $image_path, $icon_path, $delivery_time, $rating, $reviews, $slug);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: admin_produk.php?page=products&status=sukses_tambah');
    } else {
        header('Location: admin_produk.php?page=products&status=gagal_db');
    }
    exit();


// === UPDATE PRODUK ===
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

    $id = (int)$_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = (float)$_POST['price'];
    $old_image = $_POST['old_image'];
    $image_path = $old_image;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $file_name = uniqid() . '-' . basename($file['name']);
        $target_dir = "img/produk/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
                $image_path = $target_file;
            }
        }
    }

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    $sql = "UPDATE products SET name = ?, description = ?, price = ?, image_path = ?, slug = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssdssi", $name, $description, $price, $image_path, $slug, $id);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: admin_produk.php?page=products&status=sukses_update');
    } else {
        header('Location: admin_produk.php?page=products&status=gagal_update');
    }
    exit();

} else {
    header('Location: admin_produk.php');
    exit();
}
?>
