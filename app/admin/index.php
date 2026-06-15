<?php

    session_start();

    // membatasi halaman sebelum login
    if (!isset($_SESSION["login"])) {
        echo "<script>
                alert('AKSES DI TOLAK!');
                document.location.href = '../../.';
            </script>";
        exit;
    }

    // membatasi halaman sesuai user login
    if ($_SESSION["role"] != 'Admin') {
        echo "<script>
            alert('AKSES DI TOLAK!');
            document.location.href = '../../.';
            </script>";
        exit;
    }

    $laman = 'Dashboard';
    include '../../assets/layout/admin/header.php';

    $total_pengguna = select("SELECT COUNT(*) AS total FROM akun")[0]['total'] ?? 0;
    $total_produk = select("SELECT COUNT(*) AS total FROM produk")[0]['total'] ?? 0;
    $total_pesanan = select("SELECT COUNT(*) AS total FROM pesanan")[0]['total'] ?? 0;
    $pesanan_selesai = select("SELECT COUNT(*) AS total FROM pesanan WHERE status_pengerjaan = 'Selesai'")[0]['total'] ?? 0;
    $pesanan_menunggu = select("SELECT COUNT(*) AS total FROM pesanan WHERE status_pengerjaan <> 'Selesai'")[0]['total'] ?? 0;
    $pesanan_terbaru = select("
        SELECT
            p.id_pesanan,
            p.jumlah_beli,
            p.total_harga,
            p.status_harga,
            p.status_pengerjaan,
            p.tanggal_pesan,
            c.nama AS customer_nama,
            pr.nama_produk
        FROM pesanan p
        JOIN customer c ON p.id_customer = c.id_customer
        JOIN produk pr ON p.id_produk = pr.id_produk
        ORDER BY p.id_pesanan DESC
        LIMIT 5
    ");

?>

    <!-- Dashboard Main -->
    <main class="overflow-auto" style="flex:1;">
        <section class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 row-cols-xxl-5 g-3 mb-4" aria-label="Ringkasan Dashboard">
            <article class="col">
            <div class="card text-bg-primary admin-summary-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-subtitle mb-2">Pengguna</h6>
                    <h4 class="card-title"><?= number_format((int) $total_pengguna) ?></h4>
                </div>
                <i class="bi bi-people fs-2 text-warning"></i>
                </div>
            </div>
            </article>
            <article class="col">
            <div class="card text-bg-success admin-summary-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-subtitle mb-2">Produk</h6>
                    <h4 class="card-title"><?= number_format((int) $total_produk) ?></h4>
                </div>
                <i class="bi bi-archive fs-2 text-warning"></i>
                </div>
            </div>
            </article>
            <article class="col">
            <div class="card text-bg-warning admin-summary-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-subtitle mb-2">Pesanan</h6>
                    <h4 class="card-title"><?= number_format((int) $total_pesanan) ?></h4>
                </div>
                <i class="bi bi-bag fs-2 text-warning"></i>
                </div>
            </div>
            </article>
            <article class="col">
            <div class="card text-bg-danger admin-summary-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-subtitle mb-2">Belum Selesai</h6>
                    <h4 class="card-title"><?= number_format((int) $pesanan_menunggu) ?></h4>
                </div>
                <i class="bi bi-hourglass-split fs-2 text-warning"></i>
                </div>
            </div>
            </article>
            <article class="col">
            <div class="card text-bg-success admin-summary-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-subtitle mb-2">Selesai Dibuat</h6>
                    <h4 class="card-title"><?= number_format((int) $pesanan_selesai) ?></h4>
                </div>
                <i class="bi bi-check2-circle fs-2 text-warning"></i>
                </div>
            </div>
            </article>
        </section>

        <section class="mb-4" aria-label="Aksi Cepat">
            <h5>Aksi Cepat</h5>
            <div class="d-flex flex-wrap gap-2">
            <a href="admin_user.php" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Kelola Pengguna</a>
            <a href="admin_produk.php" class="btn btn-secondary"><i class="bi bi-archive me-1"></i>Kelola Produk</a>
            <a href="admin_pesanan.php" class="btn btn-primary"><i class="bi bi-bag-check me-1"></i>Lihat Pesanan</a>
            </div>
        </section>

        <section aria-label="Pesanan Terbaru">
            <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title">Pesanan Terbaru</h5>
                <a href="admin_pesanan.php" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-right me-1"></i>Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                    <tr>
                        <th scope="col">No. Pesanan</th>
                        <th scope="col">Pelanggan</th>
                        <th scope="col">Produk</th>
                        <th scope="col">Jumlah</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tanggal</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($pesanan_terbaru)) : ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada pesanan.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($pesanan_terbaru as $pesanan) : ?>
                            <tr>
                                <td class="fw-bold">#<?= str_pad($pesanan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($pesanan['customer_nama']) ?></td>
                                <td><?= htmlspecialchars($pesanan['nama_produk']) ?></td>
                                <td><?= intval($pesanan['jumlah_beli']) ?> pcs</td>
                                <td><span class="badge bg-success"><?= htmlspecialchars($pesanan['status_pengerjaan']) ?></span></td>
                                <td><?= format_tanggal_pesanan($pesanan['tanggal_pesan'] ?? null) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
            </div>
        </section>
    </main>

<?php

    include '../../assets/layout/admin/footer.php';

?>
