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
<html lang="id" data-bs-theme="dark">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register</title>

        <link href="assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/css/bootstrap-icons.min.css" rel="stylesheet">
        <link href="assets/css/register.css" rel="stylesheet">

    </head>
    <body>

        <main class="container min-vh-100 d-flex justify-content-center align-items-center">

            <div class="w-100 px-3" style="max-width:390px;">

                <div
                    class="card register-card bg-dark border-0 rounded-5"
                    style="--bs-bg-opacity:.75;">

                    <div class="card-header text-center py-4">
                        <h4 class="fw-bold mb-0">
                            Register Akun
                        </h4>
                    </div>

                    <form
                        id="registerForm"
                        action=""
                        method="post"
                        enctype="multipart/form-data">

                        <div class="card-body form-scroll px-4" style="max-height:50vh;">

                            <div class="form-floating mb-3">
                                <input
                                    type="text"
                                    name="nama"
                                    id="nama"
                                    class="form-control"
                                    minlength="5"
                                    placeholder="Nama"
                                    required>
                                <label for="nama">Nama</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input
                                    type="text"
                                    name="username"
                                    id="username"
                                    class="form-control"
                                    minlength="5"
                                    placeholder="Username"
                                    required>
                                <label for="username">Username</label>
                            </div>

                            <!-- Password -->
                            <div class="input-group mb-3">

                                <div class="form-floating flex-grow-1">
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="form-control"
                                        minlength="5"
                                        placeholder="Password"
                                        required>

                                    <label for="password">
                                        Password
                                    </label>
                                </div>

                                <button
                                    class="btn btn-outline-warning"
                                    type="button"
                                    id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>

                            </div>

                            <!-- Konfirmasi Password -->
                            <div class="input-group mb-3">

                                <div class="form-floating flex-grow-1">
                                    <input
                                        type="password"
                                        name="konfirmasi_password"
                                        id="konfirmasi_password"
                                        class="form-control"
                                        minlength="5"
                                        placeholder="Konfirmasi Password"
                                        required>

                                    <label for="konfirmasi_password">
                                        Konfirmasi Password
                                    </label>
                                </div>

                                <button
                                    class="btn btn-outline-warning"
                                    type="button"
                                    id="toggleConfirmPassword">
                                    <i class="bi bi-eye"></i>
                                </button>

                            </div>

                            <div
                                id="passwordError"
                                class="invalid-feedback d-none mb-3">
                            </div>

                            <div class="form-floating mb-3">
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    class="form-control"
                                    minlength="5"
                                    placeholder="Email"
                                    required>

                                <label for="email">Email</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input
                                    type="number"
                                    name="no_hp"
                                    id="no_hp"
                                    class="form-control"
                                    placeholder="Nomor Handphone"
                                    required>

                                <label for="no_hp">
                                    Nomor Handphone
                                </label>
                            </div>

                            <div class="mb-3">
                                <textarea
                                    name="alamat"
                                    class="form-control"
                                    minlength="5"
                                    placeholder="Alamat"
                                    rows="3"
                                    required></textarea>
                            </div>

                        </div>

                        <div class="card-footer text-center py-3 px-4">

                            <button
                                type="submit"
                                name="daftar"
                                class="btn btn-warning w-100 py-2 fw-semibold btn-register rounded-4">
                                Daftar
                            </button>

                            <p class="mt-3 mb-0 small text-body-secondary">
                                Sudah punya akun?
                                <a
                                    href="login.php"
                                    class="link-warning text-decoration-none fw-semibold">
                                    Login
                                </a>
                            </p>

                        </div>

                    </form>

                </div>

            </div>

        </main>

        <script src="assets/js/register.js"></script>

        <script src="assets/js/bootstrap.bundle.min.js"></script>

    </body>
</html>