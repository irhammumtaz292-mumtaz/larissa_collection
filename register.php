<?php

    require_once ("config/db/db.php");
    require_once ("config/controller/controller.php");

    // jika tombol tambah di tekan jalankan script berikut
    if (isset($_POST['daftar'])) {
        if($result = daftar_akun_baru($_POST) > 0) {
            echo "
                <script>
                    alert('Email telah dikirim silahkan cek!');
                    window.location.href = 'login.php';
                </script>
                ";
        } else {
            echo "
                <script>
                    alert('Email gagal dikirim!');
                    window.location.href = 'register.php';
                </script>
                ";
        }

    }

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>

    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">

                <div class="card shadow-sm">

                    <!-- Header -->
                    <div class="card-header text-center fw-bold">
                        Register Akun
                    </div>

                    <!-- Body (Scrollable) -->
                    <div class="card-body overflow-auto" style="max-height: 400px;">
                        
                        <form action="" method="post" enctype="multipart/form-data">

                            <div class="form-floating mb-2">
                                <input type="text" name="nama" id="floatingInput" class="form-control" minlength="5" placeholder="Nama" required>
                                <label for="floatingInput">Nama</label>
                            </div>

                            <div class="form-floating mb-2">
                                <input type="text" name="username" id="floatingInput" class="form-control" minlength="5" placeholder="Username" required>
                                <label for="floatingInput">Username</label>
                            </div>

                            <div class="form-floating mb-2">
                                <input type="password" name="password" id="floatingInput" class="form-control" minlength="5" placeholder="Password" required>
                                <label for="floatingInput">Password</label>
                            </div>

                            <div class="form-floating mb-2">
                                <input type="email" name="email" id="floatingInput" class="form-control" minlength="5" placeholder="Email" required>
                                <label for="floatingInput">Email</label>
                            </div>
                            
                            <div class="form-floating mb-2">
                                <input type="number" name="no_hp" id="floatingInput" class="form-control" minlength="12" maxlength="18" placeholder="Nomor Handphone" required>
                                <label for="floatingInput">Nomor Handphone</label>
                            </div>
                            
                            <div class="form mb-2">
                                <textarea name="alamat" class="form-control" minlength="5" placeholder="Alamat" rows="3" required></textarea>
                            </div>

                    </div>

                    <!-- Footer -->
                    <div class="card-footer text-center">
                        <button type="submit" name="daftar" class="btn btn-primary w-100 mb-2">
                            Daftar
                        </button>
                        <small>
                            Sudah punya akun? <a href="login.php">Login</a>
                        </small>
                    </div>

                    </form>

                </div>

            </div>
        </div>
    </main>

</body>
</html>