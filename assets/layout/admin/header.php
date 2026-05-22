<?php

    // Koneksi ke database dan backend
    require_once '../../config/db/db.php';
    require_once '../../config/controller/controller.php';

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
                    <li class="nav-item"><a href="./" class="nav-link <?= ($laman == 'Dashboard') ? 'active' : '' ?> text-white"><i class="bi bi-house-door me-2"></i>Dashboard</a></li>
                    <li class="nav-item"><a href="admin_user.php" class="nav-link <?= ($laman == 'Pengguna') ? 'active' : '' ?> text-white"><i class="bi bi-people me-2"></i>Pengguna</a></li>
                    <li class="nav-item"><a href="admin_pesanan.php" class="nav-link <?= ($laman == 'Pesanan') ? 'active' : '' ?> text-white"><i class="bi bi-bag me-2"></i>Pesanan</a></li>
                    <li class="nav-item"><a href="admin_produk.php" class="nav-link <?= ($laman == 'Produk') ? 'active' : '' ?> text-white"><i class="bi bi-basket me-2"></i>Produk</a></li>
                    <li class="nav-item"><a href="admin_warna.php" class="nav-link <?= ($laman == 'Warna') ? 'active' : '' ?> text-white"><i class="bi bi-palette me-2"></i>Warna</a></li>
                    <li class="nav-item"><a href="#" class="nav-link text-white"><i class="bi bi-file-earmark-text me-2"></i>Laporan</a></li>
                    <!-- <li class="nav-item"><a href="#" class="nav-link text-white"><i class="bi bi-gear me-2"></i>Settings</a></li> -->
                    </ul>
                </nav>

                <hr>

                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="https://placehold.co/32x32" alt="User" class="rounded-circle me-2">
                    <strong><?= $_SESSION['username'] ?></strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li><a class="dropdown-item" href="../../logout.php">Sign out</a></li>
                    </ul>
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