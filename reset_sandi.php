<?php

require_once 'config/db/db.php';
require_once 'config/controller/controller.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$resetAlert = null;
$resetSuccess = false;
$tokenValid = false;

if ($token === '') {
    $resetAlert = [
        'type' => 'danger',
        'message' => 'Token reset sandi tidak valid.',
    ];
} else {
    $stmt = mysqli_prepare($db, "SELECT token_expired FROM akun WHERE token = ? AND is_verified = 1 LIMIT 1");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $token);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $tokenExpired);
        $tokenValid = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if (!$tokenValid) {
            $resetAlert = [
                'type' => 'danger',
                'message' => 'Token reset sandi tidak valid.',
            ];
        } elseif (empty($tokenExpired) || strtotime($tokenExpired) < time()) {
            $tokenValid = false;
            $resetAlert = [
                'type' => 'danger',
                'message' => 'Token reset sandi sudah kedaluwarsa.',
            ];
        }
    } else {
        $resetAlert = [
            'type' => 'danger',
            'message' => 'Token reset sandi gagal diperiksa.',
        ];
    }
}

if ($tokenValid && isset($_POST['reset_sandi'])) {
    $resetAlert = reset_sandi_akun($_POST, $token);
    $resetSuccess = ($resetAlert['type'] ?? '') === 'success';

    if ($resetSuccess) {
        $tokenValid = false;
    }
}

?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Sandi</title>

    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/login.css" rel="stylesheet">
</head>
<body>

<main class="container min-vh-100 d-flex justify-content-center align-items-center">

    <section class="w-100" style="max-width:390px;">

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
                    Reset Sandi
                </h1>

                <p class="text-body-secondary mb-0">
                    Buat password baru untuk akun Anda
                </p>
            </header>

            <section class="card-body px-4 pb-4">
                <?php if (!empty($resetAlert)) : ?>
                    <div class="alert alert-<?= htmlspecialchars($resetAlert['type']) ?> rounded-4 py-2 mb-3">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <?= htmlspecialchars($resetAlert['message']) ?>
                    </div>
                <?php endif; ?>

                <?php if ($tokenValid) : ?>
                    <form id="resetPasswordForm" action="" method="POST">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                        <div class="input-group mb-3">
                            <div class="form-floating flex-grow-1">
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    minlength="5"
                                    placeholder="Password Baru"
                                    required>

                                <label for="password">
                                    <i class="bi bi-lock me-2"></i>Password Baru
                                </label>
                            </div>

                            <button
                                class="btn btn-outline-warning"
                                type="button"
                                id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>

                        <div class="input-group mb-3">
                            <div class="form-floating flex-grow-1">
                                <input
                                    type="password"
                                    class="form-control"
                                    id="konfirmasi_password"
                                    name="konfirmasi_password"
                                    minlength="5"
                                    placeholder="Konfirmasi Password"
                                    required>

                                <label for="konfirmasi_password">
                                    <i class="bi bi-lock-fill me-2"></i>Konfirmasi Password
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

                        <button
                            type="submit"
                            name="reset_sandi"
                            class="btn btn-warning w-100 py-2 fw-semibold btn-login rounded-4">
                            Simpan Password Baru
                        </button>
                    </form>
                <?php else : ?>
                    <a
                        href="login.php"
                        class="btn btn-warning w-100 py-2 fw-semibold btn-login rounded-4">
                        Kembali ke Login
                    </a>
                <?php endif; ?>
            </section>

            <footer class="text-center pb-3">
                <small class="text-body-secondary">
                    &copy;2026 Larissa Collection
                </small>
            </footer>

        </article>

    </section>

</main>

<script src="assets/js/reset_sandi.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>

</body>
</html>
