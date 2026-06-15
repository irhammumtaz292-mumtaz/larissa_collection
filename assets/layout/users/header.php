<?php

// Koneksi ke database dan backend
require_once '../../config/db/db.php';
require_once '../../config/controller/controller.php';

$userAkunAlert = $_SESSION['user_akun_flash'] ?? null;
unset($_SESSION['user_akun_flash']);

$userAkunData = [
    'id_akun' => $_SESSION['id_akun'] ?? '',
    'nama' => $_SESSION['nama'] ?? '',
    'username' => $_SESSION['username'] ?? '',
    'email' => $_SESSION['email'] ?? '',
    'no_hp' => $_SESSION['no_hp'] ?? '',
    'alamat' => $_SESSION['alamat'] ?? '',
];

$idAkunLogin = intval($_SESSION['id_akun'] ?? 0);
$isCustomerLogin = $idAkunLogin > 0 && ($_SESSION['role'] ?? null) === 'Customer';
$landingNavPrefix = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php' ? '' : 'index.php';

if ($isCustomerLogin && isset($_POST['simpan_kelola_akun_user'])) {
    $nama = trim(strip_tags($_POST['nama'] ?? ''));
    $username = trim(strip_tags($_POST['username'] ?? ''));
    $email = trim(strip_tags($_POST['email'] ?? ''));
    $noHp = trim(strip_tags($_POST['no_hp'] ?? ''));
    $alamat = trim(strip_tags($_POST['alamat'] ?? ''));
    $password = $_POST['password'] ?? '';
    $konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';
    $passwordDiisi = $password !== '' || $konfirmasiPassword !== '';

    $flash = [
        'type' => 'danger',
        'message' => 'Data akun gagal diperbarui.',
    ];

    if ($nama === '' || $username === '' || $email === '' || $noHp === '' || $alamat === '') {
        $flash['message'] = 'Semua data akun wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $flash['message'] = 'Format email tidak valid.';
    } elseif ($passwordDiisi && strlen($password) < 5) {
        $flash['message'] = 'Password baru minimal 5 karakter.';
    } elseif ($passwordDiisi && $password !== $konfirmasiPassword) {
        $flash['message'] = 'Konfirmasi password tidak sama.';
    } else {
        $duplikat = false;
        $stmtCek = mysqli_prepare($db, "SELECT id_akun FROM akun WHERE (username = ? OR email = ?) AND id_akun <> ? LIMIT 1");

        if ($stmtCek) {
            mysqli_stmt_bind_param($stmtCek, 'ssi', $username, $email, $idAkunLogin);
            mysqli_stmt_execute($stmtCek);
            mysqli_stmt_store_result($stmtCek);
            $duplikat = mysqli_stmt_num_rows($stmtCek) > 0;
            mysqli_stmt_close($stmtCek);
        }

        if ($duplikat) {
            $flash['message'] = 'Username atau email sudah digunakan akun lain.';
        } else {
            mysqli_begin_transaction($db);

            $stmtCustomer = mysqli_prepare(
                $db,
                "UPDATE customer c
                    JOIN akun a ON a.id_customer = c.id_customer
                    SET c.nama = ?, c.no_hp = ?, c.alamat = ?
                    WHERE a.id_akun = ? AND a.role = 'Customer'"
            );

            if ($stmtCustomer) {
                mysqli_stmt_bind_param($stmtCustomer, 'sssi', $nama, $noHp, $alamat, $idAkunLogin);
                $updateCustomer = mysqli_stmt_execute($stmtCustomer);
                mysqli_stmt_close($stmtCustomer);
            } else {
                $updateCustomer = false;
            }

            if ($passwordDiisi) {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmtAkun = mysqli_prepare($db, "UPDATE akun SET username = ?, email = ?, password = ? WHERE id_akun = ? AND role = 'Customer'");

                if ($stmtAkun) {
                    mysqli_stmt_bind_param($stmtAkun, 'sssi', $username, $email, $passwordHash, $idAkunLogin);
                }
            } else {
                $stmtAkun = mysqli_prepare($db, "UPDATE akun SET username = ?, email = ? WHERE id_akun = ? AND role = 'Customer'");

                if ($stmtAkun) {
                    mysqli_stmt_bind_param($stmtAkun, 'ssi', $username, $email, $idAkunLogin);
                }
            }

            if ($stmtAkun) {
                $updateAkun = mysqli_stmt_execute($stmtAkun);
                mysqli_stmt_close($stmtAkun);
            } else {
                $updateAkun = false;
            }

            if ($updateCustomer && $updateAkun) {
                mysqli_commit($db);

                $_SESSION['nama'] = $nama;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['no_hp'] = $noHp;
                $_SESSION['alamat'] = $alamat;

                $flash = [
                    'type' => 'success',
                    'message' => 'Data akun berhasil diperbarui.',
                ];
            } else {
                mysqli_rollback($db);
            }
        }
    }

    $_SESSION['user_akun_flash'] = $flash;
    header('Location: ' . ($_SERVER['REQUEST_URI'] ?? './'));
    exit;
}

if ($isCustomerLogin) {
    $stmtAkunLogin = mysqli_prepare(
        $db,
        "SELECT a.id_akun, c.nama, a.username, a.email, c.no_hp, c.alamat
            FROM akun a
            JOIN customer c ON a.id_customer = c.id_customer
            WHERE a.id_akun = ? AND a.role = 'Customer'
            LIMIT 1"
    );

    if ($stmtAkunLogin) {
        mysqli_stmt_bind_param($stmtAkunLogin, 'i', $idAkunLogin);
        mysqli_stmt_execute($stmtAkunLogin);
        mysqli_stmt_bind_result($stmtAkunLogin, $dbIdAkun, $dbNama, $dbUsername, $dbEmail, $dbNoHp, $dbAlamat);

        if (mysqli_stmt_fetch($stmtAkunLogin)) {
            $userAkunData = [
                'id_akun' => $dbIdAkun,
                'nama' => $dbNama,
                'username' => $dbUsername,
                'email' => $dbEmail,
                'no_hp' => $dbNoHp,
                'alamat' => $dbAlamat,
            ];
        }

        mysqli_stmt_close($stmtAkunLogin);
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Landing page konveksi dengan katalog produk dan informasi layanan">
    <meta name="author" content="Larisa Collection">
    <title><?= htmlspecialchars($title); ?></title>
    <link rel="icon" type="image/x-icon" href="../../assets/favicon.ico" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
    <link href="../../assets/css/pesanan.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- HEADER -->
    <header class="sticky-top">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark" id="mainNav">
            <div class="container">
                <a class="navbar-brand" href="#page-top">Larisa Collection</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="fas fa-bars ms-1"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                        <li class="nav-item"><a class="nav-link" href="<?= $landingNavPrefix ?>#services">Layanan</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= $landingNavPrefix ?>#katalog">Katalog</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= $landingNavPrefix ?>#about">Tentang</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= $landingNavPrefix ?>#contact">Kontak</a></li>
                    </ul>
                    <div class="d-flex align-items-center ms-lg-3">
                        <div class="dropdown">
                            <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="../../assets/img/users/g1W.png" alt="Profile" width="40" height="40" class="rounded-circle border border-light me-2" style="object-fit: cover;">
                                <span class="fw-semibold"><?= htmlspecialchars($userAkunData['username'] ?? 'User'); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <?php if ($isCustomerLogin) : ?>
                                    <li><a class="dropdown-item" href="riwayat_pesanan.php?id_akun=<?= htmlspecialchars($userAkunData['id_akun'] ?? '') ?>"><i class="bi bi-receipt me-2"></i>Riwayat Pesanan</a></li>
                                    <li>
                                        <button type="button" class="dropdown-item"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalKelolaAkunUser">
                                            <i class="bi bi-person-gear me-2"></i>Kelola Data Akun
                                        </button>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item text-danger" href="../../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                                <?php else : ?>
                                    <li><a class="dropdown-item" href="../../login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a></li>
                                    <li><a class="dropdown-item" href="../../register.php"><i class="bi bi-person-plus me-2"></i>Daftar</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <?php if (!empty($userAkunAlert)) : ?>
        <div class="container mt-3">
            <div class="alert alert-<?= htmlspecialchars($userAkunAlert['type']) ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i><?= htmlspecialchars($userAkunAlert['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($isCustomerLogin) : ?>
        <div class="modal fade" id="modalKelolaAkunUser" tabindex="-1" aria-labelledby="modalKelolaAkunUserLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <form action="" method="post">
                        <input type="hidden" name="simpan_kelola_akun_user" value="1">

                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="modalKelolaAkunUserLabel">
                                <i class="bi bi-person-gear me-2"></i>Kelola Data Akun
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body overflow-auto" style="max-height: calc(100vh - 210px);">
                            <div class="form-floating mb-3">
                                <input type="text" name="nama" id="userNama" class="form-control" minlength="3" placeholder="Nama" value="<?= htmlspecialchars($userAkunData['nama'] ?? '') ?>" required>
                                <label for="userNama">Nama</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text" name="username" id="userUsername" class="form-control" minlength="3" placeholder="Username" value="<?= htmlspecialchars($userAkunData['username'] ?? '') ?>" required>
                                <label for="userUsername">Username</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="email" name="email" id="userEmail" class="form-control" placeholder="Email" value="<?= htmlspecialchars($userAkunData['email'] ?? '') ?>" required>
                                <label for="userEmail">Email</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text" name="no_hp" id="userNoHp" class="form-control" minlength="8" placeholder="Nomor Handphone" value="<?= htmlspecialchars($userAkunData['no_hp'] ?? '') ?>" required>
                                <label for="userNoHp">Nomor Handphone</label>
                            </div>

                            <div class="form-floating mb-3">
                                <textarea name="alamat" id="userAlamat" class="form-control" minlength="5" placeholder="Alamat" style="height: 100px;" required><?= htmlspecialchars($userAkunData['alamat'] ?? '') ?></textarea>
                                <label for="userAlamat">Alamat</label>
                            </div>

                            <hr>

                            <div class="form-floating mb-3">
                                <input type="password" name="password" id="userPassword" class="form-control" minlength="5" placeholder="Password Baru">
                                <label for="userPassword">Password Baru</label>
                            </div>

                            <div class="form-floating mb-1">
                                <input type="password" name="konfirmasi_password" id="userKonfirmasiPassword" class="form-control" minlength="5" placeholder="Konfirmasi Password">
                                <label for="userKonfirmasiPassword">Konfirmasi Password</label>
                            </div>

                            <small class="text-muted">Kosongkan password jika tidak ingin mengganti password.</small>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-floppy me-1"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
