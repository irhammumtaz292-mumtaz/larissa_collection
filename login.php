<?php

  session_start();

  include 'config/db/db.php';

  // check apakah tombol login ditekan
  if (isset($_POST['login'])) {

    // ambil input username dan password
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);

    // check username
    $result = mysqli_query($db, "SELECT * FROM akun WHERE username = '$username'");

    // jika ada usernya
    if (mysqli_num_rows($result) == 1) {

      // check passwordnya
      $hasil = mysqli_fetch_assoc($result);

      if ($hasil['is_verified'] == 0) {

          echo "<script>
            alert('Akun belum diverifikasi!');
            window.location.href = 'login.php';
          </script>";
          exit;
      }

      if (password_verify($password, $hasil['password'])) {
          // set session
          $_SESSION['login']         = true;
          $_SESSION['id_akun']       = $hasil['id_akun'];
          $_SESSION['nama']          = $hasil['nama'];
          $_SESSION['username']      = $hasil['username'];
          $_SESSION['email']         = $hasil['email'];
          $_SESSION['no_hp']          = $hasil['no_hp'];
          $_SESSION['alamat']          = $hasil['alamat'];
          $_SESSION['role']         = $hasil['role'];

          // jika login benar arahkan ke file sesuai role
          if ($hasil['role'] == 'Admin') {
            header("Location: app/admin/.");
          exit;
          } else {
            header("Location: app/users/.");
          exit;
          }
      }else {
          // jika username/password salah
          $error = true;
      }
    } 
  } 

?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="auto">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
      body {
        user-select: none;
      }
      img {
        -webkit-user-drag: none;
      }
    </style>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap-icons.min.css" rel="stylesheet">
  </head>
  <body>

    <main class="container d-flex justify-content-center align-items-center min-vh-100">

      <div class="card animate__animated animate__flipInY animate__fast" style="width: 20rem;">

        <form action="" method="post">

          <!-- HEADER -->
          <div class="card-header bg-success text-center">
            <img class="animate__animated animate__zoomIn animate__slow"
                style="width: 100px; height: 100px;"
                src="assets/img/g1W.png"
                alt="">
            <h3 class="mt-2">Login</h3>
          </div>

          <!-- BODY -->
          <div class="card-body">

            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="floatingInput" name="username" placeholder="Username" required>
              <label for="floatingInput">Username</label>
            </div>

            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="floatingPassword" name="password" placeholder="Password" required>
              <label for="floatingPassword">Password</label>
            </div>

            <button class="btn btn-success w-100 py-2" type="submit" name="login">
              Login
            </button>

          </div>

          <!-- FOOTER -->
          <div class="card-footer text-body-secondary">

            <div class="d-flex justify-content-between">
              <small>Belum punya akun?</small>
              <small>&copy;Larissa Collection</small>
            </div>

            <a href="register.php" class="text-info mt-1">
              Register
            </a>

          </div>

        </form>

      </div>

    </main>
    
    <footer>
      <script src="assets/js/bootstrap.bundle.min.js"></script>
    </footer>

  </body>
</html>