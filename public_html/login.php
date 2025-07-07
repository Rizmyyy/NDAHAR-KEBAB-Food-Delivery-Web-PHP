<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="icon" href="img/logo.png" type="image/png">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }

body {
  background-image: linear-gradient(rgba(7, 0, 109, 0.7), rgba(68, 0, 179, 0.7)), url('img/bg.png');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  height: 100vh;
  display: flex;
  justify-content: center;/* geser ke atas */
padding-top: -20px;   
}

    .container {
      width: 100%;
      max-width: 400px;
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
    input[type="text"], input[type="password"] {
      background: rgba(255, 255, 255, 0.06);
      color: white !important;
      width: 100%;
      padding: 10px;
      margin-bottom: 12px;
      border-radius: 6px;
     border: 1px solid rgba(255, 255, 255);
    }

    input::placeholder {
  color: rgba(255, 255, 255); /* warna teks placeholder */
}

    .btn-login {
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

    .divider {
      text-align: center;
      color: #aaa;
      margin: 10px 0;
    }

    .btn-google {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      border-radius: 6px;
      background: white;
      cursor: pointer;
    }

    .bottom-text {
      font-size: 0.9em;
      text-align: center;
      margin-top: 10px;
    }

    .bottom-text a {
      color:rgb(0, 255, 34);
      text-decoration: none;
    }

    .checkbox-container {
      display: flex;
      justify-content: space-between;
      font-size: 0.85em;
      margin-top: 8px;
    }

    .message-success {
      color: green;
      text-align: center;
      margin-bottom: 10px;
    }

    .message-error {
      color: red;
      text-align: center;
      margin-bottom: 10px;
    }

    @media (max-width: 480px) {
      .logo img { width: 100px; }
      .card { padding: 16px; }
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="logo">
      <img src="img/logo.png" alt="YummyPol Logo">
    </div>
    <div class="card">
      <h2>Login</h2>

      <?php
      if (isset($_SESSION['success'])) {
          echo "<div class='message-success'>" . $_SESSION['success'] . "</div>";
          unset($_SESSION['success']);
      }

      if (isset($_SESSION['error'])) {
          echo "<div class='message-error'>" . $_SESSION['error'] . "</div>";
          unset($_SESSION['error']);
      }
      ?>

      <form action="proses_login.php" method="post">
        <input type="text" name="username" placeholder="Username/Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn-login">Login</button>

        <div class="checkbox-container">
          <label><input type="checkbox" name="remember"> <i>ingat saya</i></label>
        </div>
        <div class="bottom-text">
          belum punya akun? <a href="register.php">daftar</a>
        </div>
      </form>
    </div>
  </div>

</body>
</html>
