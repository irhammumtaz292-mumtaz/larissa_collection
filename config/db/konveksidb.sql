-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 15 Jun 2026 pada 02.39
-- Versi server: 12.3.2-MariaDB-log
-- Versi PHP: 8.5.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `konveksidb`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `akun`
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
-- Dumping data untuk tabel `akun`
--

INSERT INTO `akun` (`id_akun`, `id_customer`, `username`, `password`, `email`, `role`, `is_verified`, `token`, `token_expired`) VALUES
(1, 5, 'Chiron', '$2y$12$MytfuwRRwS9mJ4pLwGoDJ.CxAObMtep9IPEGPORTH2SdaLxyzFk42', 'duck@duck.go', 'Admin', 1, NULL, NULL),
(12, 17, 'Raven', '$2y$12$fh/4s7y7y5J7WosUV2XeYulqztZouvufM0Ay6KUO/Elz0Xt8BRE/i', 'tumbalrvn1@gmail.com', 'Customer', 1, NULL, '2026-05-22 12:57:20'),
(14, 19, 'aryaa', '$2y$12$Gfmg4rMpiyGUDz4Mzxv2NeJOT67NoJjwOq7RdkhzL0u9n6HbyVe3i', 'aryahernawan9@gmail.com', 'Admin', 1, NULL, NULL),
(15, 20, 'bielzyy', '$2y$12$bfUJmg1aiRSEyUx7amDfDupRvI3G8.vldX2C41.JWcKpprxEnR2ye', 'bielzy@gmail.com', 'Customer', 1, NULL, NULL),
(16, 23, 'maula', '$2y$12$s0rGFALsWC2MXl/DQP0bq.PYxaQZp6WVJKZEVOD5PoiLfle4Cs3l2', 'larissanoreply@gmail.com', 'Admin', 1, 'e1b4de68fbef4716fa94ca4e6978a8fdcd5dc3d3b190b03df4f4cdf3c839b420', '2026-06-15 03:32:58'),
(17, 24, 'Renn', '$2y$12$GujDQX9OCamFz1ya5lxjhuy0qfnk//sWu2s./gWRlkiPcay0JzK7i', 'nasyid123@gmail.com', 'Customer', 0, '0811cc77f8ab3b88fd75ab4c88edf6b10173f3601d913b32ccfad4dbcdc0189b', '2026-06-14 12:27:05'),
(18, 25, 'irham', '$2y$12$1CMCiaE5zpFXFQh7KxO4Z.hcDNydNdg55XPardiFBciDiGSRhEttW', 'immumtaz747@gmail.com', 'Customer', 0, 'a76969c6a0efa7b40323ac046b847acc999d98aa88d47aafe3bbc4dfc095a5f9', '2026-06-14 12:30:11'),
(19, 26, 'mumtazz', '$2y$12$RSxL5kgMLz6k0AgxSHxHguSjYC5amyVUZxPRN1eH4aPVUvoxCayxG', 'irhammumtaz292@gmail.com', 'Customer', 0, 'f4577013cdcdce76f23582d48ec9d665cc2142435965a4a99e9254ad6ca5140c', '2026-06-14 15:52:28'),
(20, 27, 'rasasd', '$2y$12$Wo0mJRztm19YyFPLnzRJVeeXxHOEG6ca2ppRWDdaVHtlz7rAzyqfC', 'tumbalrvn0@gmail.com', 'Customer', 1, NULL, '2026-06-15 02:52:38'),
(21, 28, 'Kirito', '$2y$12$IrQiQekknt3.5ztSF0ruqODI9zJnRIO9qDhdhI5Uem9rUgtg/O5Hm', 'nabil071106@gmail.com', 'Customer', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `bahan`
--

CREATE TABLE `bahan` (
  `id_bahan` int(11) NOT NULL,
  `jenis_bahan` varchar(50) NOT NULL,
  `id_warna` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `bahan`
--

INSERT INTO `bahan` (`id_bahan`, `jenis_bahan`, `id_warna`) VALUES
(1, 'Polyester', 2),
(2, 'Wolls', 1),
(8, 'Katun', 2),
(9, 'Wolls', 5),
(11, 'Katun', 5),
(12, 'Polyester', 5),
(14, 'Polyester', 1),
(15, 'Wolls', 3),
(16, 'Wolls', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer`
--

CREATE TABLE `customer` (
  `id_customer` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `alamat` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customer`
--

INSERT INTO `customer` (`id_customer`, `nama`, `no_hp`, `alamat`) VALUES
(5, 'Johns', '0812345678', 'Subang'),
(17, 'Mandala', '0812345678', 'Subang'),
(19, 'Revan nurhadi', '083166536486', 'Kasomalang\r\n'),
(20, 'nabill', '082102939281', 'subang'),
(21, 'asdasdsad', '213213213', 'Alsakjdlkajdas'),
(22, '', '', ''),
(23, 'maula', '088765678987', 'karawang'),
(24, 'Nasyid', '081234567891', 'Spanyol'),
(25, 'Irham Suka Cewe', '081234567891', 'Spanyol'),
(26, 'mumtaz', '0898765433', 'karawang'),
(27, 'carls', '0812345678', 'Jakarta\r\n'),
(28, 'Kirito', '082120675286', 'jln taman sari II RT 06 RW 02, kecamatan pasir kareumbi, kabupaten subang, provinsi jawa barat.');

-- --------------------------------------------------------

--
-- Struktur dari tabel `desain`
--

CREATE TABLE `desain` (
  `id_desain` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `nama_desain` varchar(50) NOT NULL,
  `gambar_desain` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `desain`
--

INSERT INTO `desain` (`id_desain`, `id_produk`, `nama_desain`, `gambar_desain`, `deskripsi`) VALUES
(1, 4, 'seragam paud', '6a2cdaca1d6f9.jpg', 'paud'),
(3, 3, 'pakaian blus', '6a2cdb1625190.jpg', 'blus'),
(4, 5, 'Garis garis', '6a2d7858221c5.jpg', 'kemeja flanel'),
(5, 7, 'baggy', '6a2cdcad1e681.jpg', 'baggy'),
(7, 2, 'PDH', '6a2d786bedbea.jpg', 'PDH'),
(8, 1, 'Polos', '6a2cdd8cc187e.jpg', 'kaos putih'),
(9, 8, 'tactical', '6a2cddb8c1a84.jpg', 'tactic'),
(10, 10, 'polo', '6a2cdde063fc5.jpg', 'polo'),
(11, 1, 'polos', '6a2d75013b737.jpg', 'kaos hitam'),
(12, 9, 'jas almamater', '6a2d75326c3c5.jpg', 'UGM'),
(13, 9, 'jas almamater', '6a2d7626f2775.jpg', 'ITB'),
(14, 9, 'jas almamater', '6a2d76510ea10.jpg', 'UI');

-- --------------------------------------------------------

--
-- Struktur dari tabel `desain_custom`
--

CREATE TABLE `desain_custom` (
  `id_desain_custom` int(11) NOT NULL,
  `id_customer` int(11) NOT NULL,
  `files` longtext DEFAULT NULL,
  `catatan` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON: notes/descriptions' CHECK (json_valid(`catatan`)),
  `harga_custom` int(11) DEFAULT NULL,
  `status_desain` enum('Menunggu','Diproses','Selesai','Ditolak') DEFAULT 'Menunggu',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `desain_custom`
--

INSERT INTO `desain_custom` (`id_desain_custom`, `id_customer`, `files`, `catatan`, `harga_custom`, `status_desain`, `created_at`) VALUES
(1, 17, '{\"depan\":\"6a1ff2e63a07a.png\",\"belakang\":\"6a1ff2e63ba4e.png\",\"kanan\":\"6a1ff2e63c544.png\",\"kiri\":\"6a1ff2e63e880.png\",\"logo\":[\"6a1ff2e63fee0.png\"]}', NULL, NULL, 'Menunggu', '2026-06-03 09:24:54'),
(2, 20, '{\"depan\":\"6a223cc8ceb27.jpeg\",\"belakang\":\"6a223cc8d1f97.png\",\"kanan\":\"6a223cc8d2829.png\",\"kiri\":\"6a223cc8d3d61.png\",\"logo\":[\"6a223cc8e9049.png\"]}', NULL, NULL, 'Menunggu', '2026-06-05 03:04:40'),
(3, 20, '{\"depan\":\"6a2643012da5f.png\",\"belakang\":\"6a2643012e9c4.jpeg\",\"kanan\":\"6a2643012f548.png\",\"kiri\":\"6a26430130ff9.png\",\"logo\":[\"6a2643013d571.png\"]}', NULL, NULL, 'Menunggu', '2026-06-08 04:20:17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_customer` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `id_bahan` int(11) NOT NULL,
  `id_desain` int(11) DEFAULT NULL,
  `id_desain_custom` int(11) DEFAULT NULL,
  `jumlah_beli` int(11) NOT NULL,
  `ukuran` varchar(50) NOT NULL,
  `harga` int(11) NOT NULL,
  `harga_dp` int(11) DEFAULT NULL,
  `total_harga` int(11) DEFAULT NULL,
  `catatan_harga` text DEFAULT NULL,
  `status_harga` enum('Menunggu Harga','Harga Diberikan','Disetujui','Ditolak') DEFAULT 'Menunggu Harga',
  `status_pengerjaan` enum('Menunggu Pembayaran','Menunggu Diproses','Sedang Diproses','Selesai','Dibatalkan') DEFAULT 'Menunggu Pembayaran',
  `tanggal_pesan` datetime NOT NULL DEFAULT current_timestamp(),
  `tanggal_selesai` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_customer`, `id_produk`, `id_bahan`, `id_desain`, `id_desain_custom`, `jumlah_beli`, `ukuran`, `harga`, `harga_dp`, `total_harga`, `catatan_harga`, `status_harga`, `status_pengerjaan`, `tanggal_pesan`, `tanggal_selesai`) VALUES
(21, 20, 1, 1, NULL, 2, 20, '{\"S\":0,\"M\":20,\"L\":0,\"XL\":0,\"XXL\":0,\"XXXL\":0}', 800000, 450000, 800000, NULL, 'Disetujui', 'Selesai', '2026-06-11 00:00:00', '2026-06-12 01:35:25'),
(27, 20, 9, 12, 12, NULL, 35, '{\"S\":0,\"M\":5,\"L\":28,\"XL\":2,\"XXL\":0,\"XXXL\":0}', 20000, 10000, 20000, NULL, 'Disetujui', 'Selesai', '2026-06-14 14:35:34', '2026-06-14 14:42:02'),
(31, 20, 3, 1, 3, NULL, 5, '{\"S\":0,\"M\":0,\"L\":5,\"XL\":0,\"XXL\":0,\"XXXL\":0}', 0, 0, NULL, NULL, 'Menunggu Harga', 'Menunggu Pembayaran', '2026-06-15 08:41:39', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar_produk` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `deskripsi`, `gambar_produk`) VALUES
(1, 'Kaos', 'kaos polos ', '6a2b02d38142e.jpg'),
(2, 'PDH', 'Kemeja PDH', '6a2cd93276f37.jpg'),
(3, 'blus', 'pakaian wanita', '6a2cd7a19ef8f.jpg'),
(4, 'seragam', 'seragam paud', '6a2cd752cea52.jpg'),
(5, 'flanel', 'kemeja flanel', '6a2cd775ab78b.jpg'),
(7, 'celana jeans', 'jeans', '6a2b02ea9e7e6.jpg'),
(8, 'tactical', 'kemeja tactical', '6a2cd7c4428e8.jpg'),
(9, 'jas ', 'jas almamater', '6a2cd7df4ddda.jpg'),
(10, 'polo', 'kaos polo', '6a2cd7f7c4b65.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
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

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_pesanan`, `metode_pembayaran`, `status_pembayaran`, `jumlah_bayar`, `bukti_pembayaran`, `tanggal_pembayaran`) VALUES
(22, 21, 'qris', 'Lunas', 800000, '6a2affbd1c61f.jpg', '2026-06-12'),
(25, 27, 'virtual_account', 'Lunas', 20000, '6a2e5b2c64392.jpg', '2026-06-14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `warna`
--

CREATE TABLE `warna` (
  `id_warna` int(11) NOT NULL,
  `nama_warna` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `warna`
--

INSERT INTO `warna` (`id_warna`, `nama_warna`) VALUES
(1, 'navy'),
(2, 'aqua'),
(3, 'maroon'),
(5, 'grey');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `akun`
--
ALTER TABLE `akun`
  ADD PRIMARY KEY (`id_akun`),
  ADD UNIQUE KEY `username` (`username`,`email`),
  ADD KEY `id_customer` (`id_customer`);

--
-- Indeks untuk tabel `bahan`
--
ALTER TABLE `bahan`
  ADD PRIMARY KEY (`id_bahan`),
  ADD KEY `id_warna` (`id_warna`),
  ADD KEY `idx_warna` (`id_warna`);

--
-- Indeks untuk tabel `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id_customer`);

--
-- Indeks untuk tabel `desain`
--
ALTER TABLE `desain`
  ADD PRIMARY KEY (`id_desain`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `idx_produk` (`id_produk`);

--
-- Indeks untuk tabel `desain_custom`
--
ALTER TABLE `desain_custom`
  ADD PRIMARY KEY (`id_desain_custom`),
  ADD KEY `idx_customer` (`id_customer`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `id_customer` (`id_customer`,`id_bahan`,`id_desain`),
  ADD KEY `id_desain` (`id_desain`),
  ADD KEY `id_bahan` (`id_bahan`),
  ADD KEY `id_desain_custom` (`id_desain_custom`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `idx_pesanan` (`id_pesanan`);

--
-- Indeks untuk tabel `warna`
--
ALTER TABLE `warna`
  ADD PRIMARY KEY (`id_warna`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `akun`
--
ALTER TABLE `akun`
  MODIFY `id_akun` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `bahan`
--
ALTER TABLE `bahan`
  MODIFY `id_bahan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `customer`
--
ALTER TABLE `customer`
  MODIFY `id_customer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `desain`
--
ALTER TABLE `desain`
  MODIFY `id_desain` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `desain_custom`
--
ALTER TABLE `desain_custom`
  MODIFY `id_desain_custom` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `warna`
--
ALTER TABLE `warna`
  MODIFY `id_warna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `akun`
--
ALTER TABLE `akun`
  ADD CONSTRAINT `akun_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `customer` (`id_customer`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `bahan`
--
ALTER TABLE `bahan`
  ADD CONSTRAINT `bahan_ibfk_1` FOREIGN KEY (`id_warna`) REFERENCES `warna` (`id_warna`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `desain`
--
ALTER TABLE `desain`
  ADD CONSTRAINT `desain_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `desain_custom`
--
ALTER TABLE `desain_custom`
  ADD CONSTRAINT `desain_custom_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `customer` (`id_customer`);

--
-- Ketidakleluasaan untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `customer` (`id_customer`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pesanan_ibfk_2` FOREIGN KEY (`id_desain`) REFERENCES `desain` (`id_desain`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pesanan_ibfk_3` FOREIGN KEY (`id_bahan`) REFERENCES `bahan` (`id_bahan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pesanan_ibfk_4` FOREIGN KEY (`id_desain_custom`) REFERENCES `desain_custom` (`id_desain_custom`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pesanan_ibfk_5` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
