<?php

    // Koneksi ke database dan backend
    require_once '../../config/db/db.php';
    require_once '../../config/controller/controller.php';

    if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? null) !== 'Admin') {
        header('Location: ../../login.php');
        exit;
    }

    $adminAkunAlert = $_SESSION['admin_akun_flash'] ?? null;
    unset($_SESSION['admin_akun_flash']);

    $adminAkunData = [
        'id_akun' => $_SESSION['id_akun'] ?? '',
        'nama' => $_SESSION['nama'] ?? '',
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'no_hp' => $_SESSION['no_hp'] ?? '',
        'alamat' => $_SESSION['alamat'] ?? '',
    ];

    $idAkunLogin = intval($_SESSION['id_akun'] ?? 0);

    if ($idAkunLogin > 0 && isset($_POST['simpan_kelola_akun_admin'])) {
        $nama = trim(strip_tags($_POST['nama'] ?? ''));
        $username = trim(strip_tags($_POST['username'] ?? ''));
        $email = trim(strip_tags($_POST['email'] ?? ''));
        $noHp = trim(strip_tags($_POST['no_hp'] ?? ''));
        $alamat = trim(strip_tags($_POST['alamat'] ?? ''));
        $password = $_POST['password'] ?? '';
        $konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

        $flash = [
            'type' => 'danger',
            'message' => 'Data akun gagal diperbarui.',
        ];

        if ($nama === '' || $username === '' || $email === '' || $noHp === '' || $alamat === '') {
            $flash['message'] = 'Semua data akun wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $flash['message'] = 'Format email tidak valid.';
        } elseif ($password !== '' && strlen($password) < 5) {
            $flash['message'] = 'Password baru minimal 5 karakter.';
        } elseif ($password !== '' && $password !== $konfirmasiPassword) {
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
                    WHERE a.id_akun = ? AND a.role = 'Admin'"
                );

                if ($stmtCustomer) {
                    mysqli_stmt_bind_param($stmtCustomer, 'sssi', $nama, $noHp, $alamat, $idAkunLogin);
                    $updateCustomer = mysqli_stmt_execute($stmtCustomer);
                    mysqli_stmt_close($stmtCustomer);
                } else {
                    $updateCustomer = false;
                }

                if ($password !== '') {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmtAkun = mysqli_prepare($db, "UPDATE akun SET username = ?, email = ?, password = ? WHERE id_akun = ? AND role = 'Admin'");

                    if ($stmtAkun) {
                        mysqli_stmt_bind_param($stmtAkun, 'sssi', $username, $email, $passwordHash, $idAkunLogin);
                    }
                } else {
                    $stmtAkun = mysqli_prepare($db, "UPDATE akun SET username = ?, email = ? WHERE id_akun = ? AND role = 'Admin'");

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

        $_SESSION['admin_akun_flash'] = $flash;
        header('Location: ' . ($_SERVER['REQUEST_URI'] ?? './'));
        exit;
    }

    if ($idAkunLogin > 0) {
        $stmtAkunLogin = mysqli_prepare(
            $db,
            "SELECT a.id_akun, c.nama, a.username, a.email, c.no_hp, c.alamat
            FROM akun a
            JOIN customer c ON a.id_customer = c.id_customer
            WHERE a.id_akun = ? AND a.role = 'Admin'
            LIMIT 1"
        );

        if ($stmtAkunLogin) {
            mysqli_stmt_bind_param($stmtAkunLogin, 'i', $idAkunLogin);
            mysqli_stmt_execute($stmtAkunLogin);
            mysqli_stmt_bind_result($stmtAkunLogin, $dbIdAkun, $dbNama, $dbUsername, $dbEmail, $dbNoHp, $dbAlamat);

            if (mysqli_stmt_fetch($stmtAkunLogin)) {
                $adminAkunData = [
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

    $notifikasiPesananCount = 0;
    $notifikasiPesanan = select("SELECT COUNT(*) AS total FROM pesanan WHERE status_pengerjaan <> 'Selesai'");
    if (!empty($notifikasiPesanan[0]['total'])) {
        $notifikasiPesananCount = (int) $notifikasiPesanan[0]['total'];
    }

?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($laman ?? 'Admin Dashboard') ?> - Admin Larisa Collection</title>
        <link rel="icon" type="image/x-icon" href="../../assets/favicon.ico">
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet" type="text/css">
        <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="../../assets/css/bootstrap-icons.min.css" rel="stylesheet">
        <link href="../../assets/css/admin.css" rel="stylesheet">
    </head>

    <body class="admin-shell">

        <div class="d-flex vh-100 overflow-hidden">

            <!-- Sidebar -->
            <div id="sidebar" class="collapse collapse-horizontal show">

                <aside class="admin-sidebar d-flex flex-column flex-shrink-0 text-white h-100 p-3 overflow-auto">

                <a href="./" class="admin-sidebar-brand d-flex align-items-center mb-4 text-decoration-none">
                    <span class="admin-sidebar-brand-icon d-inline-flex align-items-center justify-content-center me-3">
                        <i class="bi bi-stars"></i>
                    </span>
                    <span>
                        <span class="admin-sidebar-brand-title d-block">Larisa Admin</span>
                        <small class="admin-sidebar-brand-subtitle d-block">Kelola toko dengan rapi</small>
                    </span>
                </a>

                <hr class="admin-sidebar-divider">

                <nav aria-label="Sidebar Navigation">
                    <ul class="nav nav-pills flex-column mb-auto">

                        <li class="nav-item">
                            <a href="./"
                            class="nav-link <?= ($laman == 'Dashboard') ? 'active' : '' ?> text-white">
                                <i class="bi bi-house-door me-2"></i>Dashboard
                            </a>
                        </li>

                        <!-- DROPDOWN KATALOG -->
                        <li class="nav-item">
                            <button class="btn nav-link w-100 text-white text-start d-flex justify-content-between align-items-center px-3 py-2 rounded-0 border-0 bg-transparent
                            <?= ($laman == 'Katalog' || $laman == 'Produk' || $laman == 'Warna' || $laman == 'Bahan' || $laman == 'Desain') ? 'active' : '' ?>"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#produkCollapse"
                            aria-expanded="false"
                            aria-controls="produkCollapse">
                                <span><i class="bi bi-archive me-2"></i>Produk</span>
                                <i class="bi bi-chevron-down small"></i>
                            </button>

                            <div class="collapse <?= ($laman == 'Katalog' || $laman == 'Produk' || $laman == 'Warna' || $laman == 'Bahan' || $laman == 'Desain') ? 'show' : '' ?>" id="produkCollapse">
                                <ul class="nav flex-column mb-0">
                                    <li class="nav-item">
                                        <a href="admin_produk.php"
                                        class="nav-link text-white ps-4 <?= ($laman == 'Produk') ? 'active' : '' ?>">
                                            Data Produk
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="admin_desain.php"
                                        class="nav-link text-white ps-4 <?= ($laman == 'Desain') ? 'active' : '' ?>">
                                            Data Desain
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="admin_bahan.php"
                                        class="nav-link text-white ps-4 <?= ($laman == 'Bahan') ? 'active' : '' ?>">
                                            Data Bahan
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="admin_warna.php"
                                        class="nav-link text-white ps-4 <?= ($laman == 'Warna') ? 'active' : '' ?>">
                                            Data Warna
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a href="admin_user.php"
                            class="nav-link <?= ($laman == 'Pengguna') ? 'active' : '' ?> text-white">
                                <i class="bi bi-people me-2"></i>Pengguna
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="admin_pesanan.php"
                            class="nav-link <?= ($laman == 'Pesanan') ? 'active' : '' ?> text-white">
                                <i class="bi bi-bag me-2"></i>Pesanan
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="admin_laporan.php"
                            class="nav-link <?= ($laman == 'Laporan') ? 'active' : '' ?> text-white">
                                <i class="bi bi-file-earmark-text me-2"></i>Laporan
                            </a>
                        </li>

                    </ul>
                </nav>

                <hr class="admin-sidebar-divider mt-4">

                <div class="admin-user-panel mt-auto">
                    <button class="admin-user-button btn w-100 text-start d-flex align-items-center justify-content-between" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#userMenuCollapse"
                        aria-expanded="false"
                        aria-controls="userMenuCollapse">
                        <span class="d-flex align-items-center">
                            <span class="admin-user-avatar d-inline-flex align-items-center justify-content-center rounded-circle me-3">
                                <i class="bi bi-person"></i>
                            </span>
                            <span>
                                <strong class="d-block"><?= htmlspecialchars($adminAkunData['username'] ?? 'Admin') ?></strong>
                                <small class="admin-user-role">Administrator</small>
                            </span>
                        </span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="collapse mt-2" id="userMenuCollapse">
                        <ul class="list-unstyled mb-0">
                            <li>
                                <button type="button" class="admin-user-action btn w-100 text-start mb-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalKelolaAkunAdmin">
                                    <i class="bi bi-person-gear me-2"></i>Kelola Data Akun
                                </button>
                            </li>
                            <li><a class="admin-user-action btn w-100 text-start" href="../../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
                        </ul>
                    </div>
                </div>

                </aside>

            </div>

            <!-- Main Content -->
            <div class="admin-main flex-grow-1 d-flex flex-column p-2 p-md-3">

            <!-- Navbar -->
            <header>
                <nav class="admin-topbar navbar navbar-expand-lg rounded-4 mb-3 mb-md-4 px-2 px-md-3 position-sticky top-0 z-3" aria-label="Top Navigation">
                    <div class="container-fluid p-0">

                        <!-- Toggle Button -->
                        <button class="btn me-2"
                            data-bs-toggle="collapse"
                            data-bs-target="#sidebar">
                            <i class="bi bi-list"></i>
                        </button>

                        <span class="navbar-brand mb-0 fw-semibold" href="#"><?= $laman; ?></span>
                       
                         <div class="ms-auto d-flex align-items-center gap-3">
                            <a href="admin_pesanan.php#pesananTable" class="position-relative" aria-label="Lihat pesanan yang belum selesai">
                                <i class="bi bi-bell fs-5"></i>
                                <?php if ($notifikasiPesananCount > 0) : ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?= $notifikasiPesananCount ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <!-- <a href="#" class="text-dark"><i class="bi bi-envelope fs-5"></i></a>
                            <a href="#" class="text-dark"><i class="bi bi-gear fs-5"></i></a> -->
                        </div>
                    </div>
                </nav>
            </header>

            <?php if (!empty($adminAkunAlert)) : ?>
                <div class="alert alert-<?= htmlspecialchars($adminAkunAlert['type']) ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle me-2"></i><?= htmlspecialchars($adminAkunAlert['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="modal fade" id="modalKelolaAkunAdmin" tabindex="-1" aria-labelledby="modalKelolaAkunAdminLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <form action="" method="post">
                            <input type="hidden" name="simpan_kelola_akun_admin" value="1">

                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="modalKelolaAkunAdminLabel">
                                    <i class="bi bi-person-gear me-2"></i>Kelola Data Akun
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body overflow-auto" style="max-height: calc(100vh - 210px);">
                                <div class="form-floating mb-3">
                                    <input type="text" name="nama" id="adminNama" class="form-control" minlength="3" placeholder="Nama" value="<?= htmlspecialchars($adminAkunData['nama'] ?? '') ?>" required>
                                    <label for="adminNama">Nama</label>
                                </div>

                                <div class="form-floating mb-3">
                                    <input type="text" name="username" id="adminUsername" class="form-control" minlength="3" placeholder="Username" value="<?= htmlspecialchars($adminAkunData['username'] ?? '') ?>" required>
                                    <label for="adminUsername">Username</label>
                                </div>

                                <div class="form-floating mb-3">
                                    <input type="email" name="email" id="adminEmail" class="form-control" placeholder="Email" value="<?= htmlspecialchars($adminAkunData['email'] ?? '') ?>" required>
                                    <label for="adminEmail">Email</label>
                                </div>

                                <div class="form-floating mb-3">
                                    <input type="text" name="no_hp" id="adminNoHp" class="form-control" minlength="8" placeholder="Nomor Handphone" value="<?= htmlspecialchars($adminAkunData['no_hp'] ?? '') ?>" required>
                                    <label for="adminNoHp">Nomor Handphone</label>
                                </div>

                                <div class="form-floating mb-3">
                                    <textarea name="alamat" id="adminAlamat" class="form-control" minlength="5" placeholder="Alamat" style="height: 100px;" required><?= htmlspecialchars($adminAkunData['alamat'] ?? '') ?></textarea>
                                    <label for="adminAlamat">Alamat</label>
                                </div>

                                <hr>

                                <div class="form-floating mb-3">
                                    <input type="password" name="password" id="adminPassword" class="form-control" minlength="5" placeholder="Password Baru">
                                    <label for="adminPassword">Password Baru</label>
                                </div>

                                <div class="form-floating mb-1">
                                    <input type="password" name="konfirmasi_password" id="adminKonfirmasiPassword" class="form-control" minlength="5" placeholder="Konfirmasi Password">
                                    <label for="adminKonfirmasiPassword">Konfirmasi Password</label>
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
