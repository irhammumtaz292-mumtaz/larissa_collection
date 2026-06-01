-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 01, 2026 at 04:05 PM
-- Server version: 11.4.10-MariaDB-log
-- PHP Version: 8.5.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `konveksidb`
--

-- --------------------------------------------------------

--
-- Table structure for table `akun`
--

CREATE TABLE `akun` (
  `id_akun` int(11) NOT NULL,
  `id_customer` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `role` enum('Admin','Customer') NOT NULL DEFAULT 'Customer',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `token` varchar(255) DEFAULT NULL,
  `token_expired` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `akun`
--

INSERT INTO `akun` (`id_akun`, `id_customer`, `username`, `password`, `email`, `role`, `is_verified`, `token`, `token_expired`) VALUES
(1, 5, 'Chiron', '$2y$12$MytfuwRRwS9mJ4pLwGoDJ.CxAObMtep9IPEGPORTH2SdaLxyzFk42', 'duck@duck.go', 'Admin', 1, NULL, NULL),
(12, 17, 'Raveb', '$2y$12$fh/4s7y7y5J7WosUV2XeYulqztZouvufM0Ay6KUO/Elz0Xt8BRE/i', 'tumbalrvn1@gmail.com', 'Customer', 1, NULL, '2026-05-22 12:57:20'),
(14, 19, 'Revan', '$2y$12$Gfmg4rMpiyGUDz4Mzxv2NeJOT67NoJjwOq7RdkhzL0u9n6HbyVe3i', 'aryahernawan9@gmail.com', 'Admin', 1, NULL, NULL),
(15, 20, 'bielzyy', '$2y$12$bfUJmg1aiRSEyUx7amDfDupRvI3G8.vldX2C41.JWcKpprxEnR2ye', 'bielzy@gmail.com', 'Customer', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bahan`
--

CREATE TABLE `bahan` (
  `id_bahan` int(11) NOT NULL,
  `jenis_bahan` varchar(50) NOT NULL,
  `id_warna` int(11) NOT NULL,
  `stok` int(11) NOT NULL,
  `harga_bahan` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bahan`
--

INSERT INTO `bahan` (`id_bahan`, `jenis_bahan`, `id_warna`, `stok`, `harga_bahan`) VALUES
(1, 'Polyester', 2, 6, 40000),
(2, 'Wolls', 1, 3, 70000),
(4, 'Katun', 1, 5, 80000);

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `id_customer` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `alamat` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`id_customer`, `nama`, `no_hp`, `alamat`) VALUES
(5, 'Johns', '0812345678', 'Subang'),
(17, 'Mandala', '0812345678', 'Subang'),
(19, 'Revan nurhadi', '083166536486', 'Kasomalang\r\n'),
(20, 'nabill', '082102939281', 'subang'),
(21, 'asdasdsad', '213213213', 'Alsakjdlkajdas');

-- --------------------------------------------------------

--
-- Table structure for table `desain`
--

CREATE TABLE `desain` (
  `id_desain` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `nama_desain` varchar(50) NOT NULL,
  `gambar_desain` varchar(255) NOT NULL,
  `harga_desain` int(11) NOT NULL,
  `deskripsi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `desain`
--

INSERT INTO `desain` (`id_desain`, `id_produk`, `nama_desain`, `gambar_desain`, `harga_desain`, `deskripsi`) VALUES
(1, 1, 'Keren', '6a145b26126cb.png', 12000, 'Keren Coy'),
(2, 2, 'Bagus', '6a145eab62fd4.png', 70000, 'Keren'),
(3, 3, 'Keren', '6a150c6195a71.jpeg', 70000, 'Seksi');

-- --------------------------------------------------------

--
-- Table structure for table `desain_custom`
--

CREATE TABLE `desain_custom` (
  `id_desain_custom` int(11) NOT NULL,
  `id_customer` int(11) NOT NULL,
  `gambar_depan` varchar(255) NOT NULL,
  `catatan_depan` text DEFAULT NULL,
  `gambar_belakang` varchar(255) NOT NULL,
  `catatan_belakang` text DEFAULT NULL,
  `gambar_kanan` varchar(255) NOT NULL,
  `catatan_kanan` text DEFAULT NULL,
  `gambar_kiri` varchar(255) NOT NULL,
  `catatan_kiri` text DEFAULT NULL,
  `gambar_logo` text NOT NULL,
  `catatan_logo` text DEFAULT NULL,
  `status_desain` enum('Menunggu','Diproses','Selesai','Ditolak') DEFAULT 'Menunggu',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_customer` int(11) NOT NULL,
  `id_bahan` int(11) NOT NULL,
  `id_desain` int(11) DEFAULT NULL,
  `id_desain_custom` int(11) DEFAULT NULL,
  `jumlah_beli` int(11) NOT NULL,
  `ukuran` varchar(50) NOT NULL,
  `harga` int(11) NOT NULL,
  `harga_dp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_customer`, `id_bahan`, `id_desain`, `id_desain_custom`, `jumlah_beli`, `ukuran`, `harga`, `harga_dp`) VALUES
(1, 21, 4, 2, NULL, 37, '{\"S\":12,\"M\":25,\"L\":0,\"XL\":0,\"XXL\":0,\"XXXL\":0}', 2960000, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar_produk` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `deskripsi`, `gambar_produk`) VALUES
(1, 'Kaos', 'Nyaman', '6a1449d992079.png'),
(2, 'Jaket', 'Aman', '6a145e92e1661.png'),
(3, 'Rok', 'Mantap', '6a150bae7bb3c.png'),
(4, 'Sarung', 'Cihuy', '6a150bd2694b0.png'),
(5, 'Sepatu', 'katanya sih sepatu', '6a18ff1bef8dd.png'),
(6, 'Apa', 'lkj', '6a1d7f5e54c33.png'),
(7, 'ldsad', 'sldjsad', '6a1d7f710fa87.png');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `status_pembayaran` enum('Pending','DP','Lunas') NOT NULL DEFAULT 'Pending',
  `jumlah_bayar` int(11) NOT NULL,
  `bukti_pembayaran` varchar(255) NOT NULL,
  `tanggal_pembayaran` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warna`
--

CREATE TABLE `warna` (
  `id_warna` int(11) NOT NULL,
  `nama_warna` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warna`
--

INSERT INTO `warna` (`id_warna`, `nama_warna`) VALUES
(1, 'navy'),
(2, 'aqua'),
(3, 'maroon');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `akun`
--
ALTER TABLE `akun`
  ADD PRIMARY KEY (`id_akun`),
  ADD UNIQUE KEY `username` (`username`,`email`),
  ADD KEY `id_customer` (`id_customer`);

--
-- Indexes for table `bahan`
--
ALTER TABLE `bahan`
  ADD PRIMARY KEY (`id_bahan`),
  ADD KEY `id_warna` (`id_warna`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id_customer`);

--
-- Indexes for table `desain`
--
ALTER TABLE `desain`
  ADD PRIMARY KEY (`id_desain`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `desain_custom`
--
ALTER TABLE `desain_custom`
  ADD PRIMARY KEY (`id_desain_custom`),
  ADD KEY `id_customer` (`id_customer`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `fk_detail_pesanan` (`id_pesanan`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `id_customer` (`id_customer`,`id_bahan`,`id_desain`),
  ADD KEY `id_desain` (`id_desain`),
  ADD KEY `id_bahan` (`id_bahan`),
  ADD KEY `id_desain_custom` (`id_desain_custom`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_pesanan` (`id_pesanan`);

--
-- Indexes for table `warna`
--
ALTER TABLE `warna`
  ADD PRIMARY KEY (`id_warna`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `akun`
--
ALTER TABLE `akun`
  MODIFY `id_akun` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `bahan`
--
ALTER TABLE `bahan`
  MODIFY `id_bahan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `id_customer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `desain`
--
ALTER TABLE `desain`
  MODIFY `id_desain` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `desain_custom`
--
ALTER TABLE `desain_custom`
  MODIFY `id_desain_custom` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `warna`
--
ALTER TABLE `warna`
  MODIFY `id_warna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `akun`
--
ALTER TABLE `akun`
  ADD CONSTRAINT `akun_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `customer` (`id_customer`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `bahan`
--
ALTER TABLE `bahan`
  ADD CONSTRAINT `bahan_ibfk_1` FOREIGN KEY (`id_warna`) REFERENCES `warna` (`id_warna`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `desain_custom`
--
ALTER TABLE `desain_custom`
  ADD CONSTRAINT `desain_custom_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `customer` (`id_customer`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `customer` (`id_customer`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pesanan_ibfk_2` FOREIGN KEY (`id_desain`) REFERENCES `desain` (`id_desain`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pesanan_ibfk_3` FOREIGN KEY (`id_bahan`) REFERENCES `bahan` (`id_bahan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pesanan_ibfk_4` FOREIGN KEY (`id_desain_custom`) REFERENCES `desain_custom` (`id_desain_custom`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
