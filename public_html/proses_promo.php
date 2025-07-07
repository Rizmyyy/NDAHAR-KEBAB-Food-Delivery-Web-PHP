<?php
require 'cek_admin.php';
require 'koneksi.php';

// --- LOGIKA UNTUK MENAMBAH PROMO BARU ---
if (isset($_POST['action']) && $_POST['action'] == 'tambah_promo') {
    // Ambil data dari form
    $name = strtoupper(mysqli_real_escape_string($conn, $_POST['name']));
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $disc_percent = (int)$_POST['discount_percentage'];
    $min_purchase = (float)$_POST['min_purchase'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Query INSERT menggunakan Prepared Statement
    $sql = "INSERT INTO promos (name, description, discount_percentage, min_purchase, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssidis", $name, $desc, $disc_percent, $min_purchase, $start_date, $end_date);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: admin_produk.php?page=promos&status=sukses_tambah');
    } else {
        header('Location: admin_produk.php?page=promos&status=gagal_db');
    }
    exit();
}

// --- LOGIKA UNTUK MENG-UPDATE PROMO ---
elseif (isset($_POST['update_promo'])) {
    // Ambil data dari form edit
    $id = (int)$_POST['id'];
    $name = strtoupper(mysqli_real_escape_string($conn, $_POST['name']));
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $disc_percent = (int)$_POST['discount_percentage'];
    $min_purchase = (float)$_POST['min_purchase'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $is_active = isset($_POST['is_active']) ? 1 : 0; // Cek checkbox

    // Query UPDATE menggunakan Prepared Statement
    $sql = "UPDATE promos SET name=?, description=?, discount_percentage=?, min_purchase=?, start_date=?, end_date=?, is_active=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssidisii", $name, $desc, $disc_percent, $min_purchase, $start_date, $end_date, $is_active, $id);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: admin_produk.php?page=promos&status=sukses_update');
    } else {
        header('Location: admin_produk.php?page=promos&status=gagal_db');
    }
    exit();
}

// Jika diakses langsung, redirect
else {
    header('Location: admin_produk.php');
    exit();
}
?>