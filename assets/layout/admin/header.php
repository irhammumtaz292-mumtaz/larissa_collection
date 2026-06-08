<?php

    // Koneksi ke database dan backend
    require_once '../../config/db/db.php';
    require_once '../../config/controller/controller.php';

    if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? null) !== 'Admin') {
        header('Location: ../../login.php');
        exit;
    }

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard</title>
        <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="../../assets/css/bootstrap-icons.min.css" rel="stylesheet">
    </head>

    <body>

        <div class="d-flex vh-100 overflow-hidden">

            <!-- Sidebar -->
            <div id="sidebar" class="collapse collapse-horizontal show">

                <aside class="d-flex flex-column flex-shrink-0 bg-dark text-white h-100 p-3 overflow-auto"
                       style="width: 220px; min-width:220px;">

                <a href="#" class="d-flex align-items-center mb-3 text-white text-decoration-none">
                    <i class="bi bi-speedometer2 fs-4 me-2"></i>
                    <span class="fs-4">Admin</span>
                </a>

                <hr>

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

                            <div class="collapse" id="produkCollapse">
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
                            <a href="#"
                            class="nav-link text-white">
                                <i class="bi bi-file-earmark-text me-2"></i>Laporan
                            </a>
                        </li>

                    </ul>
                </nav>

                <hr>

                <div>
                    <button class="btn btn-dark w-100 text-start d-flex align-items-center justify-content-between" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#userMenuCollapse"
                        aria-expanded="false"
                        aria-controls="userMenuCollapse">
                        <span class="d-flex align-items-center">
                            <img src="https://placehold.co/32x32" alt="User" class="rounded-circle me-2">
                            <strong><?= $_SESSION['username'] ?></strong>
                        </span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="collapse mt-2" id="userMenuCollapse">
                        <ul class="list-unstyled mb-0">
                            <li><a class="btn btn-outline-light w-100 text-start mb-1" href="#">Profile</a></li>
                            <li><a class="btn btn-outline-light w-100 text-start" href="../../logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </div>

                </aside>

            </div>

            <!-- Main Content -->
            <div class="flex-grow-1 d-flex flex-column p-2 p-md-3" style="min-width:0;">

            <!-- Navbar -->
            <header>
                <nav class="navbar navbar-expand-lg navbar-light bg-light rounded mb-3 mb-md-4 shadow-sm px-2 px-md-3 position-sticky top-0 z-3" aria-label="Top Navigation">
                    <div class="container-fluid p-0">

                        <!-- Toggle Button -->
                        <button class="btn btn-outline-secondary me-2"
                            data-bs-toggle="collapse"
                            data-bs-target="#sidebar">
                            <i class="bi bi-list"></i>
                        </button>

                        <span class="navbar-brand mb-0 fw-semibold" href="#"><?= $laman; ?></span>
                       
                         <div class="ms-auto d-flex align-items-center gap-3">
                            <a href="#" class="position-relative text-dark">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
                            </a>
                            <!-- <a href="#" class="text-dark"><i class="bi bi-envelope fs-5"></i></a>
                            <a href="#" class="text-dark"><i class="bi bi-gear fs-5"></i></a> -->
                        </div>
                    </div>
                </nav>
            </header>
