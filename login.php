<?php

session_start();

require_once 'config/db/db.php';
require_once 'config/controller/controller.php';

$resetAlert = null;

if (isset($_POST['lupa_sandi'])) {
    $resetAlert = lupa_sandi_akun($_POST);
}

if (isset($_POST['login'])) {

    $email    = mysqli_real_escape_string($db, trim($_POST['email']));
    $password = $_POST['password'];

    // Query dengan JOIN ke customer table untuk mendapatkan nama, no_hp, alamat
    $result = mysqli_query(
        $db,
        "SELECT a.*, c.nama, c.no_hp, c.alamat 
         FROM akun a 
         JOIN customer c ON a.id_customer = c.id_customer 
         WHERE a.email = '$email'"
    );

    if (mysqli_num_rows($result) == 1) {

        $hasil = mysqli_fetch_assoc($result);

        if ($hasil['is_verified'] == 0) {

            echo "<script>
                alert('Akun belum diverifikasi!');
                window.location.href='login.php';
            </script>";
            exit;
        }

        if (password_verify($password, $hasil['password'])) {

            $_SESSION['login']    = true;
            $_SESSION['id_akun']  = $hasil['id_akun'];
            $_SESSION['nama']     = $hasil['nama'];
            $_SESSION['username'] = $hasil['username'];
            $_SESSION['email']    = $hasil['email'];
            $_SESSION['no_hp']    = $hasil['no_hp'];
            $_SESSION['alamat']   = $hasil['alamat'];
            $_SESSION['role']     = $hasil['role'];

            if ($hasil['role'] == 'Admin') {
                header("Location: app/admin/.");
                exit;
            } else {
                header("Location: app/users/.");
                exit;
            }

        } else {
            $error = true;
        }

    } else {
        $error = true;
    }

}

?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/login.css" rel="stylesheet">

</head>
<body>

<main class="container min-vh-100 d-flex justify-content-center align-items-center">

    <section class="w-100" style="max-width:380px;">

        <article
            class="card login-card bg-dark border-0 rounded-5"
            style="--bs-bg-opacity:.75;">

            <header class="card-body text-center pt-4 pb-0">

                <img
                    src="assets/img/logo/g1W.png"
                    alt="Logo"
                    width="80"
                    class="mb-3">

                <h1 class="h3 fw-bold mb-2">
                    Selamat Datang
                </h1>

                <p class="text-body-secondary mb-0">
                    Silakan login untuk melanjutkan
                </p>

            </header>

            <section class="card-body px-4 pb-4">

                <form action="" method="POST">

                    <?php if (isset($error)) : ?>
                        <div class="alert alert-danger rounded-4 py-2 mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Email atau password salah!
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($resetAlert)) : ?>
                        <div class="alert alert-<?= htmlspecialchars($resetAlert['type']) ?> rounded-4 py-2 mb-3">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <?= htmlspecialchars($resetAlert['message']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-floating mb-3">
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            placeholder="Email"
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            required>

                        <label for="email">
                            <i class="bi bi-envelope me-2"></i>Email
                        </label>
                    </div>

                    <div class="input-group mb-4">

                        <div class="form-floating flex-grow-1">
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Password"
                                required>

                            <label for="password">
                                <i class="bi bi-lock me-2"></i>Password
                            </label>
                        </div>

                        <button
                            class="btn btn-outline-warning"
                            type="button"
                            id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>

                    </div>

                    <div class="d-grid">
                        <button
                            type="submit"
                            name="login"
                            class="btn btn-warning py-2 fw-semibold btn-login rounded-4">
                            Login
                        </button>

                        <button
                            type="button"
                            class="btn btn-link link-warning text-decoration-none fw-semibold mt-2"
                            data-bs-toggle="modal"
                            data-bs-target="#modalLupaSandi">
                            Lupa sandi?
                        </button>
                    </div>

                    <div class="text-center mt-4">
                        <span class="text-body-secondary">
                            Belum punya akun?
                        </span>

                        <a
                            href="register.php"
                            class="link-warning text-decoration-none fw-semibold ms-1">
                            Daftar sekarang
                        </a>
                    </div>

                </form>

            </section>

            <footer class="text-center pb-3">
                <small class="text-body-secondary">
                    &copy;2026 Larissa Collection
                </small>
            </footer>

        </article>

    </section>

</main>

<div class="modal fade" id="modalLupaSandi" tabindex="-1" aria-labelledby="modalLupaSandiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-warning rounded-4">
            <form action="" method="POST">
                <div class="modal-header border-warning">
                    <h2 class="modal-title h5 fw-bold" id="modalLupaSandiLabel">
                        Lupa Sandi
                    </h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="form-floating">
                        <input
                            type="email"
                            class="form-control"
                            id="reset_email"
                            name="reset_email"
                            placeholder="Email"
                            value="<?= isset($_POST['reset_email']) ? htmlspecialchars($_POST['reset_email']) : ''; ?>"
                            required>

                        <label for="reset_email">
                            <i class="bi bi-envelope me-2"></i>Email akun
                        </label>
                    </div>
                </div>

                <div class="modal-footer border-warning">
                    <button type="button" class="btn btn-outline-light rounded-4" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" name="lupa_sandi" class="btn btn-warning fw-semibold rounded-4">
                        Kirim Link Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/login.js"></script>

<script src="assets/js/bootstrap.bundle.min.js"></script>

</body>
</html>
