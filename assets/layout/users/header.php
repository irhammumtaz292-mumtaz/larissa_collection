<?php

    // Koneksi ke database dan backend
    require_once '../../config/db/db.php';
    require_once '../../config/controller/controller.php';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Landing page konveksi dengan katalog produk dan informasi layanan">
    <meta name="author" content="Larisa Collection">
    <title><?= htmlspecialchars($title);?></title>
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
                        <li class="nav-item"><a class="nav-link" href="#services">Layanan</a></li>
                        <li class="nav-item"><a class="nav-link" href="#portfolio">Katalog</a></li>
                        <li class="nav-item"><a class="nav-link" href="#about">Tentang</a></li>
                    </ul>
                    <div class="d-flex align-items-center ms-lg-3">
                        <div class="dropdown">
                            <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="../../assets/img/users/g1W.png" alt="Profile" width="40" height="40" class="rounded-circle border border-light me-2" style="object-fit: cover;">
                                <span class="fw-semibold"><?= htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="riwayat_pesanan.php?id_akun=<?= $_SESSION['id_akun'] ?? '' ?>"><i class="bi bi-receipt me-2"></i>Riwayat Pesanan</a></li>
                                <li><a class="dropdown-item" href="kelola_akun.php?id_akun=<?= $_SESSION['id_akun'] ?? '' ?>"><i class="bi bi-person-gear me-2"></i>Kelola Data Akun</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="../../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>