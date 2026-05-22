<?php

    // Koneksi ke database dan backend
    require_once '../../config/db/db.php';
    require_once '../../config/controller/controller.php';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title;?></title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>

    <!-- HEADER -->
    <header class="sticky-top">
        <nav class="navbar navbar-expand-lg navbar-success bg-success">
            <div class="container">
                
                <!-- BRAND (KIRI SENDIRI) -->
                <a class="navbar-brand fw-bold" href="#">Larisa Collection</a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- BAGIAN KANAN -->
                <div class="collapse navbar-collapse justify-content-end align-items-center" id="navbarNav">

                    <!-- MENU -->
                    <ul class="navbar-nav">
                        <li class="nav-item"><a class="nav-link" href="<?= $halmut ?>">Katalog</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= $halpem ?>">Pesanan</a></li>
                        <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                    </ul>
                    
                    <!-- TOGGLE -->
                    <div class="form-check form-switch text-white me-3">
                        <input class="form-check-input" type="checkbox" id="themeSwitch">
                        <label class="form-check-label" for="themeSwitch">Tema</label>
                    </div>

                </div>

            </div>
        </nav>
    </header>