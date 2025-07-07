<?php
session_start();
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $checkQuery = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $_SESSION['error'] = "Username atau email sudah terdaftar!";
        header("Location: register.php");
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashedPassword')";
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal mendaftar. Coba lagi!";
        header("Location: register.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register</title>
  <link rel="icon" href="img/logo.png" type="image/png">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      background-image: linear-gradient(rgba(7, 0, 109, 0.7), rgba(68, 0, 179, 0.7)), url('img/bg.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      height: 100vh;
      display: flex;
      justify-content: center;
      padding-top: -20px; 
    }

    .container {
      width: 100%;
      max-width: 500px;
      padding: 20px;
    }

    .logo {
      text-align: center;
      margin-bottom: 20px;
    }

    .logo img {
      width: 120px;
      height: auto;
    }

     .card {
  backdrop-filter: blur(5px);           /* blur latar belakang agar lebih kontras */
  -webkit-backdrop-filter: blur(8px);   /* dukungan Safari */
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(255, 255, 255, 0.2);
  padding: 20px;
  width: 100%;
  max-width: 500px; /* dari 400px ke 500px misalnya */
  padding: 30px;
  color: white;
}

    .card h2 {
      color: white;
      text-align: center;
      margin-bottom: 20px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      background: rgba(255, 255, 255, 0.06);
      color: white !important;
      width: 100%;
      padding: 10px;
      margin-bottom: 12px;
      border-radius: 6px;
      border: 1px solid rgba(255, 255, 255);
    }

    input::placeholder {
      color: rgba(255, 255, 255);
    }

    .btn-register {
      width: 100%;
      padding: 10px;
      background-color: #0b9b1e;
      color: white;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      margin-bottom: 12px;
    }

    .bottom-text {
      font-size: 0.9em;
      text-align: center;
      margin-top: 10px;
    }

    .bottom-text a {
      color: rgb(0, 255, 34);
      text-decoration: none;
    }

    .message {
      text-align: center;
      margin-bottom: 10px;
      font-weight: bold;
    }

    .error {
      color: red;
    }

    .success {
      color: lightgreen;
    }

    @media (max-width: 480px) {
      .logo img {
        width: 100px;
      }

      .card {
        padding: 16px;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="logo">
      <img src="img/logo.png" alt="YummyPol Logo">
    </div>

    <div class="card">
      <h2>Register Akun</h2>

      <?php
      if (isset($_SESSION['error'])) {
          echo "<p class='message error'>" . $_SESSION['error'] . "</p>";
          unset($_SESSION['error']);
      }
      if (isset($_SESSION['success'])) {
          echo "<p class='message success'>" . $_SESSION['success'] . "</p>";
          unset($_SESSION['success']);
      }
      ?>

      <form method="post" action="register.php">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn-register">Daftar</button>
      </form>

      <div class="bottom-text">
        Sudah punya akun? <a href="login.php">Login di sini</a>
      </div>
    </div>
  </div>

</body>
</html>
