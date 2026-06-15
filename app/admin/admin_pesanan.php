<?php

    session_start();

    $laman = 'Pesanan';
    $fileLaman ='admin_pesanan.php';

    include '../../assets/layout/admin/header.php';

    // ===== HANDLER POST UNTUK VALIDASI PESANAN =====

    // 0) Admin memberikan harga pesanan
    if (isset($_POST['update_harga_pesanan'])) {
        $id_pesanan = intval($_POST['id_pesanan'] ?? 0);
        $total_harga = max(0, intval($_POST['total_harga'] ?? 0));
        $catatan_harga = mysqli_real_escape_string($GLOBALS['db'], htmlspecialchars(strip_tags($_POST['catatan_harga'] ?? '')));
        $status_harga = htmlspecialchars(strip_tags($_POST['status_harga'] ?? 'Harga Diberikan'));
        $status_valid = ['Menunggu Harga', 'Harga Diberikan', 'Disetujui', 'Ditolak'];

        if ($id_pesanan > 0 && $total_harga > 0 && in_array($status_harga, $status_valid, true)) {
            $query_update = "UPDATE pesanan
                SET total_harga = $total_harga,
                    harga = $total_harga,
                    catatan_harga = " . ($catatan_harga !== '' ? "'$catatan_harga'" : "NULL") . ",
                    status_harga = '$status_harga'
                WHERE id_pesanan = $id_pesanan";
            mysqli_query($GLOBALS['db'], $query_update);

            $popup = true;
            $statusPopup = 'Berhasil';
            $warnaPopup = 'success';
            $iconPopup = 'check2-circle';
            $popupEksekusi = 'diberi harga';
        } else {
            $popup = true;
            $statusPopup = 'Gagal';
            $warnaPopup = 'danger';
            $iconPopup = 'x-circle';
            $popupEksekusi = 'diberi harga';
        }
    }

    // 1) Update Status Pengerjaan
    if (isset($_POST['update_status_pengerjaan'])) {
        $id_pesanan = intval($_POST['id_pesanan'] ?? 0);
        $status_pengerjaan = htmlspecialchars(strip_tags($_POST['status_pengerjaan'] ?? ''));
        $status_pengerjaan_valid = ['Menunggu Pembayaran', 'Menunggu Diproses', 'Sedang Diproses', 'Selesai', 'Dibatalkan'];

        if ($id_pesanan > 0 && in_array($status_pengerjaan, $status_pengerjaan_valid, true)) {
            $boleh_update_status = true;

            if ($status_pengerjaan === 'Selesai') {
                $data_pembayaran = select("SELECT status_pembayaran FROM transaksi WHERE id_pesanan = $id_pesanan ORDER BY id_transaksi DESC LIMIT 1");
                $status_pembayaran = $data_pembayaran[0]['status_pembayaran'] ?? '';
                $boleh_update_status = $status_pembayaran === 'Lunas';
            }

            if (!$boleh_update_status) {
                $popup = true;
                $statusPopup = 'Gagal';
                $warnaPopup = 'danger';
                $iconPopup = 'x-circle';
                $popupEksekusi = 'diselesaikan karena pembayaran belum lunas';
            } else {
                $query_update = "UPDATE pesanan
                    SET status_pengerjaan = '$status_pengerjaan',
                        tanggal_selesai = CASE
                            WHEN '$status_pengerjaan' = 'Selesai' THEN NOW()
                            ELSE NULL
                        END
                    WHERE id_pesanan = $id_pesanan";
                mysqli_query($GLOBALS['db'], $query_update);

                $popup = true;
                $statusPopup = 'Berhasil';
                $warnaPopup = 'success';
                $iconPopup = 'check2-circle';
                $popupEksekusi = 'diperbarui';
            }
        } else {
            $popup = true;
            $statusPopup = 'Gagal';
            $warnaPopup = 'danger';
            $iconPopup = 'x-circle';
            $popupEksekusi = 'diperbarui';
        }
    }

    // 2) Validasi pembayaran customer
    if (isset($_POST['validasi_pembayaran'])) {
        $id_transaksi = intval($_POST['id_transaksi'] ?? 0);

        if ($id_transaksi > 0) {
            $data_transaksi = select("
                SELECT
                    t.id_pesanan,
                    t.status_pembayaran,
                    t.jumlah_bayar,
                    p.total_harga,
                    p.harga
                FROM transaksi t
                JOIN pesanan p ON p.id_pesanan = t.id_pesanan
                WHERE t.id_transaksi = $id_transaksi
                LIMIT 1
            ");

            $transaksi_validasi = $data_transaksi[0] ?? null;
            $total_harga_validasi = intval($transaksi_validasi['total_harga'] ?? 0);
            if ($total_harga_validasi <= 0) {
                $total_harga_validasi = intval($transaksi_validasi['harga'] ?? 0);
            }

            $jumlah_bayar_validasi = intval($transaksi_validasi['jumlah_bayar'] ?? 0);
            $minimal_dp_validasi = intdiv($total_harga_validasi + 1, 2);
            $status_bayar_baru = '';

            if ($transaksi_validasi && ($transaksi_validasi['status_pembayaran'] ?? '') === 'Pending') {
                if ($total_harga_validasi > 0 && $jumlah_bayar_validasi >= $total_harga_validasi) {
                    $status_bayar_baru = 'Lunas';
                } elseif ($total_harga_validasi > 0 && $jumlah_bayar_validasi >= $minimal_dp_validasi) {
                    $status_bayar_baru = 'DP';
                }
            }

            if ($status_bayar_baru !== '') {
                $id_pesanan_validasi = intval($transaksi_validasi['id_pesanan']);
                $set_harga_dp = $status_bayar_baru === 'DP' ? ", harga_dp = $jumlah_bayar_validasi" : "";

                mysqli_query($GLOBALS['db'], "UPDATE transaksi SET status_pembayaran = '$status_bayar_baru' WHERE id_transaksi = $id_transaksi");
                mysqli_query($GLOBALS['db'], "UPDATE pesanan
                    SET status_harga = CASE
                            WHEN status_harga = 'Harga Diberikan' THEN 'Disetujui'
                            ELSE status_harga
                        END,
                        status_pengerjaan = CASE
                            WHEN status_pengerjaan = 'Menunggu Pembayaran' THEN 'Menunggu Diproses'
                            ELSE status_pengerjaan
                        END
                        $set_harga_dp
                    WHERE id_pesanan = $id_pesanan_validasi");

                $popup = true;
                $statusPopup = 'Berhasil';
                $warnaPopup = 'success';
                $iconPopup = 'check2-circle';
                $popupEksekusi = 'pembayaran divalidasi sebagai ' . $status_bayar_baru;
            } else {
                $popup = true;
                $statusPopup = 'Gagal';
                $warnaPopup = 'danger';
                $iconPopup = 'x-circle';
                $popupEksekusi = 'memvalidasi pembayaran';
            }
        }
    }

    // 3) Tolak Bukti Pembayaran
    if (isset($_POST['tolak_bukti'])) {
        $id_transaksi = intval($_POST['id_transaksi'] ?? 0);
        
        if ($id_transaksi > 0) {
            $query_update = "UPDATE transaksi SET status_pembayaran = 'Pending' WHERE id_transaksi = $id_transaksi";
            mysqli_query($GLOBALS['db'], $query_update);
            
            $popup = true;
            $statusPopup = 'Berhasil';
            $warnaPopup = 'success';
            $iconPopup = 'check2-circle';
            $popupEksekusi = 'ditolak';
        }
    }

    // 4) Hapus Pesanan (Reject Pembelian)
    if (isset($_POST['hapus_pesanan'])) {
        $id_pesanan = intval($_POST['id_pesanan'] ?? 0);
        
        if ($id_pesanan > 0) {
            $result = hapus_pesanan_dan_file($id_pesanan) > 0;
            
            $popup = true;
            $statusPopup = $result ? 'Berhasil' : 'Gagal';
            $warnaPopup = $result ? 'success' : 'danger';
            $popupEksekusi = 'dihapus';
        }
    }

    // Query untuk mendapatkan daftar pesanan dengan data terkait
    $query_pesanan = "
        SELECT 
            p.id_pesanan,
            p.id_customer,
            p.jumlah_beli,
            p.harga,
            p.harga_dp,
            p.total_harga,
            p.status_harga,
            p.status_pengerjaan,
            p.tanggal_pesan,
            p.tanggal_selesai,
            p.catatan_harga,
            p.id_produk,
            p.id_bahan,
            p.id_desain,
            p.id_desain_custom,
            p.ukuran,
            pr.nama_produk,
            c.nama as customer_nama,
            c.no_hp as customer_hp,
            c.alamat as customer_alamat,
            b.jenis_bahan,
            w.nama_warna,
            d.nama_desain,
            d.gambar_desain,
            d.deskripsi as desain_deskripsi,
            dc.files as desain_custom_files,
            dc.catatan as desain_custom_catatan,
            t.id_transaksi,
            t.metode_pembayaran,
            t.status_pembayaran,
            t.jumlah_bayar,
            t.bukti_pembayaran,
            t.tanggal_pembayaran
        FROM pesanan p
        JOIN produk pr ON p.id_produk = pr.id_produk
        JOIN customer c ON p.id_customer = c.id_customer
        JOIN bahan b ON p.id_bahan = b.id_bahan
        JOIN warna w ON b.id_warna = w.id_warna
        LEFT JOIN desain d ON p.id_desain = d.id_desain
        LEFT JOIN desain_custom dc ON p.id_desain_custom = dc.id_desain_custom
        LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan
        WHERE p.status_pengerjaan <> 'Selesai'
            OR COALESCE(t.status_pembayaran, '') <> 'Lunas'
        ORDER BY p.id_pesanan DESC
    ";

    $pesanan_list = select($query_pesanan);

?>

    <!-- Dashboard Main -->
    <main class="overflow-auto" style="flex:1;">

        <!-- Popup -->
        <?php require_once '../popup.php';?>

        <!-- Desktop View -->
        <section aria-label="Pesanan View" class="d-none d-md-block">
            <div class="row justify-content-center">
                <div class="col-sm-6 col-md-8 col-lg-12">
                    <div class="card admin-order-table-card">
                        <!-- Card Header -->
                        <div class="card-header admin-order-table-header">
                            <div class="card-wrap admin-order-toolbar d-flex justify-content-between align-items-center">
                                <h3 class="card-title">Pesanan</h3>
                                <form class="form" action="" method="post">
                                    <div class="input-group admin-order-search">
                                        <input type="search" class="form-control" id="searchNama" placeholder="Cari nama..." aria-label="Search">
                                        <button class="btn btn-outline-info" type="button" onclick="filterTable()"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                            </div>

                            <div class="admin-order-filters">
                                <select class="form-select form-select-sm d-inline-block" style="width: auto;" id="filterStatus" onchange="filterTable()">
                                    <option value="">Semua Status Harga</option>
                                    <option value="Menunggu Harga">Menunggu Harga</option>
                                    <option value="Harga Diberikan">Harga Diberikan</option>
                                    <option value="Disetujui">Disetujui</option>
                                    <option value="Ditolak">Ditolak</option>
                                </select>
                                <button class="btn btn-sm btn-secondary" onclick="resetFilters()"><i class="bi bi-arrow-clockwise me-1"></i>Reset</button>
                            </div>
                        </div>
                        <!-- /.card-header -->

                        <!-- Card Body -->
                        <div class="card-body admin-order-table-body overflow-auto" style="max-height: 500px;">
                            <div class="table-responsive">
                                <table id="pesananTable" class="table table-sm table-bordered border-dark table-hover admin-order-table align-middle">
                                    <thead class="text-center">
                                        <tr>
                                            <th class="bg-success">#</th>
                                            <th class="bg-success">Pelanggan</th>
                                            <th class="bg-success">Produk</th>
                                            <th class="bg-success">Jumlah</th>
                                            <th class="bg-success">Tanggal Pesan</th>
                                            <th class="bg-success">Total Harga</th>
                                            <th class="bg-success">Status</th>
                                            <th class="bg-success">Pengerjaan</th>
                                            <th class="bg-success">Bukti Bayar</th>
                                            <th class="bg-success">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($pesanan_list)): ?>
                                            <tr>
                                                <td colspan="10" class="text-center py-4 text-muted">Belum ada pesanan</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; foreach ($pesanan_list as $pesanan): ?>
                                                <tr data-nama="<?= strtolower(htmlspecialchars($pesanan['customer_nama'])) ?>" data-status="<?= htmlspecialchars($pesanan['status_harga'] ?? 'Menunggu Harga') ?>">
                                                    <td scope="row" class="text-center admin-order-number"><?= $no++; ?></td>
                                                    <td class="admin-order-customer">
                                                        <div class="admin-order-customer-name"><?= htmlspecialchars($pesanan['customer_nama']) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($pesanan['customer_hp']) ?></small>
                                                    </td>
                                                    <td class="admin-order-product"><?= htmlspecialchars($pesanan['nama_produk']) ?></td>
                                                    <td class="text-center admin-order-qty"><?= intval($pesanan['jumlah_beli']) ?> pcs</td>
                                                    <td class="admin-order-date"><?= format_tanggal_pesanan($pesanan['tanggal_pesan'] ?? null) ?></td>
                                                    <td class="fw-bold admin-order-price">
                                                        <?php if (!empty($pesanan['total_harga'])): ?>
                                                            Rp <?= number_format(intval($pesanan['total_harga'])) ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">Menunggu Harga</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center admin-order-status">
                                                        <?php
                                                            $statusHarga = $pesanan['status_harga'] ?? 'Menunggu Harga';
                                                            $labelStatusHarga = $statusHarga;
                                                            if ($statusHarga === 'Disetujui') {
                                                                $statusPembayaranHarga = $pesanan['status_pembayaran'] ?? '';
                                                                if (in_array($statusPembayaranHarga, ['DP', 'Lunas'], true)) {
                                                                    $labelStatusHarga = 'Disetujui - ' . $statusPembayaranHarga;
                                                                } elseif ($statusPembayaranHarga === 'Pending') {
                                                                    $labelStatusHarga = 'Disetujui - Menunggu Validasi';
                                                                } else {
                                                                    $labelStatusHarga = 'Disetujui - Belum Bayar';
                                                                }
                                                            }
                                                            $badgeHarga = match($statusHarga) {
                                                                'Harga Diberikan' => 'bg-info text-dark',
                                                                'Disetujui' => 'bg-success',
                                                                'Ditolak' => 'bg-danger',
                                                                default => 'bg-secondary'
                                                            };
                                                        ?>
                                                        <span class="badge
                                                            <?= $badgeHarga ?>
                                                        ">
                                                            <?= htmlspecialchars($labelStatusHarga) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center admin-order-status">
                                                        <?php
                                                            $statusPengerjaan = $pesanan['status_pengerjaan'] ?? 'Menunggu Pembayaran';
                                                            $badgePengerjaan = match($statusPengerjaan) {
                                                                'Menunggu Diproses' => 'bg-info text-dark',
                                                                'Sedang Diproses' => 'bg-warning text-dark',
                                                                'Selesai' => 'bg-success',
                                                                'Dibatalkan' => 'bg-danger',
                                                                default => 'bg-secondary'
                                                            };
                                                        ?>
                                                        <span class="badge <?= $badgePengerjaan ?>">
                                                            <?= htmlspecialchars($statusPengerjaan) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center admin-order-proof">
                                                        <?php
                                                            $buktiPembayaran = $pesanan['bukti_pembayaran'] ?? '';
                                                            $adaBuktiFile = $buktiPembayaran !== '' && file_exists('../../assets/img/bukti_transaksi/' . $buktiPembayaran);
                                                            $adaTransaksi = !empty($pesanan['id_transaksi']);
                                                        ?>
                                                        <?php if ($adaBuktiFile || $adaTransaksi): ?>
                                                            <button class="btn btn-sm btn-outline-info admin-order-proof-btn"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalBukti"
                                                                    onclick="setBuktiModal(<?= htmlspecialchars(json_encode($adaBuktiFile ? $buktiPembayaran : ''), ENT_QUOTES) ?>, '<?= str_pad($pesanan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?>', <?= intval($pesanan['id_transaksi'] ?? 0) ?>, <?= htmlspecialchars(json_encode($pesanan['status_pembayaran'] ?? ''), ENT_QUOTES) ?>, <?= intval($pesanan['jumlah_bayar'] ?? 0) ?>, <?= intval($pesanan['total_harga'] ?? $pesanan['harga'] ?? 0) ?>)">
                                                                <i class="bi <?= $adaBuktiFile ? 'bi-image' : 'bi-credit-card-2-front' ?>"></i>
                                                                <?= $adaBuktiFile ? 'Lihat' : 'Validasi' ?>
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="badge bg-light text-dark">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center admin-order-actions">
                                                        <div class="admin-order-action-group">
                                                        <button class="btn btn-sm btn-outline-warning"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalHarga"
                                                                onclick="setHargaModal(<?= intval($pesanan['id_pesanan']) ?>, <?= intval($pesanan['total_harga'] ?? $pesanan['harga'] ?? 0) ?>, '<?= htmlspecialchars($pesanan['status_harga'] ?? 'Menunggu Harga', ENT_QUOTES) ?>', <?= htmlspecialchars(json_encode($pesanan['catatan_harga'] ?? '')) ?>)">
                                                            <i class="bi bi-cash-coin me-1"></i>Beri Harga
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalDetail"
                                                                onclick="setDetailModal(<?= htmlspecialchars(json_encode($pesanan)) ?>)">
                                                            <i class="bi bi-eye me-1"></i>Lihat
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-success"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalValidasi"
                                                                onclick="setStatusPengerjaanModal(<?= intval($pesanan['id_pesanan']) ?>, '<?= htmlspecialchars($pesanan['status_pengerjaan'] ?? 'Menunggu Pembayaran', ENT_QUOTES) ?>', '<?= htmlspecialchars($pesanan['status_pembayaran'] ?? '', ENT_QUOTES) ?>')">
                                                            <i class="bi bi-gear me-1"></i>Pengerjaan
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalHapus"
                                                                onclick="setHapusModal(<?= intval($pesanan['id_pesanan']) ?>, '<?= str_pad($pesanan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?>')">
                                                            <i class="bi bi-trash me-1"></i>Hapus
                                                        </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
        </section>

        <!-- Mobile View -->
        <section aria-label="Pesanan View Mobile" class="d-block d-md-none">
            <!-- Action Card -->
            <div class="card mb-3 shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header py-2">
                    <div class="card-wrap">
                        <h3 class="card-title mb-2">Pesanan</h3>
                        <div class="input-group mb-2">
                            <input type="search" class="form-control" id="searchNama" placeholder="Cari nama..." aria-label="Search">
                            <button class="btn btn-outline-info" type="button" onclick="filterTable()"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-2">
                    <select class="form-select form-select-sm mb-2" id="filterStatus" onchange="filterTable()">
                        <option value="">Semua Status Harga</option>
                        <option value="Menunggu Harga">Menunggu Harga</option>
                        <option value="Harga Diberikan">Harga Diberikan</option>
                        <option value="Disetujui">Disetujui</option>
                        <option value="Ditolak">Ditolak</option>
                    </select>
                    <button class="btn btn-sm btn-secondary w-100 rounded-pill" onclick="resetFilters()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset Filter
                    </button>
                </div>
            </div>

            <!-- List Data -->
            <div class="row g-2">
                <?php if (!empty($pesanan_list)): ?>
                    <?php foreach ($pesanan_list as $pesanan): ?>
                        <div class="col-6" data-nama="<?= strtolower(htmlspecialchars($pesanan['customer_nama'])) ?>" data-status="<?= htmlspecialchars($pesanan['status_harga'] ?? 'Menunggu Harga') ?>">
                            <div class="card mb-3 shadow-sm border-0 rounded-4 overflow-hidden">
                                <!-- Header -->
                                <div class="card-header bg-info d-flex justify-content-between align-items-center py-2">
                                    <div class="me-2 overflow-hidden">
                                        <h6 class="mb-0 fw-semibold text-truncate">#<?= str_pad($pesanan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?></h6>
                                        <small class="text-muted text-truncate d-block"><?= htmlspecialchars($pesanan['customer_nama']) ?></small>
                                    </div>
                                    <?php
                                        $statusHargaMobile = $pesanan['status_harga'] ?? 'Menunggu Harga';
                                        $labelStatusHargaMobile = $statusHargaMobile;
                                        if ($statusHargaMobile === 'Disetujui') {
                                            $statusPembayaranHargaMobile = $pesanan['status_pembayaran'] ?? '';
                                            if (in_array($statusPembayaranHargaMobile, ['DP', 'Lunas'], true)) {
                                                $labelStatusHargaMobile = 'Disetujui - ' . $statusPembayaranHargaMobile;
                                            } elseif ($statusPembayaranHargaMobile === 'Pending') {
                                                $labelStatusHargaMobile = 'Disetujui - Menunggu Validasi';
                                            } else {
                                                $labelStatusHargaMobile = 'Disetujui - Belum Bayar';
                                            }
                                        }
                                        $badgeHargaMobile = match($statusHargaMobile) {
                                            'Harga Diberikan' => 'info text-dark',
                                            'Disetujui' => 'success',
                                            'Ditolak' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>
                                    <span class="badge rounded-pill px-2 py-1 bg-<?= $badgeHargaMobile ?>">
                                        <?= htmlspecialchars($labelStatusHargaMobile) ?>
                                    </span>
                                </div>

                                <!-- Body -->
                                <div class="card-body py-2">
                                    <div class="row g-2 small">
                                        <div class="col-12">
                                            <span class="text-muted fw-medium">Produk</span>
                                            <div><?= htmlspecialchars($pesanan['nama_produk']) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted fw-medium">Jumlah</span>
                                            <div><?= intval($pesanan['jumlah_beli']) ?> pcs</div>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted fw-medium">Tanggal Pesan</span>
                                            <div><?= format_tanggal_pesanan($pesanan['tanggal_pesan'] ?? null) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted fw-medium">Total</span>
                                            <div class="fw-bold">
                                                <?php $total_harga_mobile = intval($pesanan['total_harga'] ?? $pesanan['harga'] ?? 0); ?>
                                                <?= $total_harga_mobile > 0 ? 'Rp ' . number_format($total_harga_mobile, 0, '.', '.') : 'Menunggu Harga' ?>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <span class="text-muted fw-medium">HP</span>
                                            <div><?= htmlspecialchars($pesanan['customer_hp']) ?></div>
                                        </div>
                                        <div class="col-12">
                                            <span class="text-muted fw-medium">Pengerjaan</span>
                                            <?php
                                                $statusPengerjaanMobile = $pesanan['status_pengerjaan'] ?? 'Menunggu Pembayaran';
                                                $badgePengerjaanMobile = match($statusPengerjaanMobile) {
                                                    'Menunggu Diproses' => 'info text-dark',
                                                    'Sedang Diproses' => 'warning text-dark',
                                                    'Selesai' => 'success',
                                                    'Dibatalkan' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>
                                            <div>
                                                <span class="badge bg-<?= $badgePengerjaanMobile ?>"><?= htmlspecialchars($statusPengerjaanMobile) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="card-footer border-0 pt-0 pb-2">
                                    <div class="d-flex gap-2 flex-column">
                                        <button class="btn btn-sm btn-outline-warning rounded-pill"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalHarga"
                                            onclick="setHargaModal(<?= intval($pesanan['id_pesanan']) ?>, <?= intval($pesanan['total_harga'] ?? $pesanan['harga'] ?? 0) ?>, '<?= htmlspecialchars($pesanan['status_harga'] ?? 'Menunggu Harga', ENT_QUOTES) ?>', <?= htmlspecialchars(json_encode($pesanan['catatan_harga'] ?? '')) ?>)">
                                            <i class="bi bi-cash-coin me-1"></i>Beri Harga
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary rounded-pill"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalDetail"
                                            onclick="setDetailModal(<?= htmlspecialchars(json_encode($pesanan)) ?>)">
                                            <i class="bi bi-eye me-1"></i>Detail
                                        </button>
                                        <?php
                                            $buktiPembayaranMobile = $pesanan['bukti_pembayaran'] ?? '';
                                            $adaBuktiFileMobile = $buktiPembayaranMobile !== '' && file_exists('../../assets/img/bukti_transaksi/' . $buktiPembayaranMobile);
                                            $adaTransaksiMobile = !empty($pesanan['id_transaksi']);
                                        ?>
                                        <?php if ($adaBuktiFileMobile || $adaTransaksiMobile): ?>
                                            <button class="btn btn-sm btn-outline-info rounded-pill" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalBukti"
                                                    onclick="setBuktiModal(<?= htmlspecialchars(json_encode($adaBuktiFileMobile ? $buktiPembayaranMobile : ''), ENT_QUOTES) ?>, '<?= str_pad($pesanan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?>', <?= intval($pesanan['id_transaksi'] ?? 0) ?>, <?= htmlspecialchars(json_encode($pesanan['status_pembayaran'] ?? ''), ENT_QUOTES) ?>, <?= intval($pesanan['jumlah_bayar'] ?? 0) ?>, <?= intval($pesanan['total_harga'] ?? $pesanan['harga'] ?? 0) ?>)">
                                                <i class="bi <?= $adaBuktiFileMobile ? 'bi-image' : 'bi-credit-card-2-front' ?> me-1"></i><?= $adaBuktiFileMobile ? 'Bukti' : 'Validasi Bayar' ?>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-success rounded-pill"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalValidasi"
                                                onclick="setStatusPengerjaanModal(<?= intval($pesanan['id_pesanan']) ?>, '<?= htmlspecialchars($pesanan['status_pengerjaan'] ?? 'Menunggu Pembayaran', ENT_QUOTES) ?>', '<?= htmlspecialchars($pesanan['status_pembayaran'] ?? '', ENT_QUOTES) ?>')">
                                            <i class="bi bi-gear me-1"></i>Pengerjaan
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalHapus"
                                                onclick="setHapusModal(<?= intval($pesanan['id_pesanan']) ?>, '<?= str_pad($pesanan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?>')">
                                            <i class="bi bi-trash me-1"></i>Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-4 p-4 text-center">
                            <i class="bi bi-inbox" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 10px;"></i>
                            <p class="text-muted">Belum ada pesanan</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <!-- ===== MODAL LIHAT BUKTI PEMBAYARAN ===== -->
    <div class="modal fade" id="modalBukti" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">Bukti Pembayaran - <span id="buktiNoPesanan">#000000</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-2">
                    <img id="buktiImage" src="" alt="Bukti Pembayaran" class="img-fluid rounded-3 d-none" style="max-width: 100%; max-height: 400px; object-fit: contain;">
                    <div id="buktiTanpaFile" class="alert alert-light border mb-2 d-none">
                        <i class="bi bi-credit-card-2-front me-2"></i>Pembayaran ini tidak memakai file bukti.
                    </div>
                    <div id="buktiInfo" class="small text-start mt-3"></div>
                    <form method="POST" id="formValidasiPembayaran" class="mt-3 d-none">
                        <input type="hidden" name="id_transaksi" id="validasiIdTransaksi">
                        <button type="submit" name="validasi_pembayaran" class="btn btn-success w-100">
                            <i class="bi bi-check2-circle me-1"></i><span id="validasiLabel">Validasi Pembayaran</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODAL DETAIL PESANAN ===== -->
    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Detail Pesanan - <span id="detailNoPesanan">#000000</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detailContent"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODAL BERI HARGA PESANAN ===== -->
    <div class="modal fade" id="modalHarga" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-cash-coin me-2"></i>Beri Harga Pesanan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_pesanan" id="hargaIdPesanan">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Total Harga</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="total_harga" id="hargaTotalInput" class="form-control" min="1" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status Harga</label>
                            <select name="status_harga" id="hargaStatusInput" class="form-select" required>
                                <option value="Menunggu Harga">Menunggu Harga</option>
                                <option value="Harga Diberikan">Harga Diberikan</option>
                                <option value="Disetujui">Disetujui</option>
                                <option value="Ditolak">Ditolak</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan untuk Customer</label>
                            <textarea name="catatan_harga" id="hargaCatatanInput" class="form-control" rows="4" placeholder="Contoh: harga sudah termasuk bahan, sablon, dan jahit."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_harga_pesanan" class="btn btn-warning text-dark fw-semibold">
                            <i class="bi bi-floppy me-1"></i>Simpan Harga
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== MODAL STATUS PENGERJAAN ===== -->
    <div class="modal fade" id="modalValidasi" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-gear me-2"></i>Status Pengerjaan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_pesanan" id="pengerjaanIdPesanan">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status Pengerjaan</label>
                            <select name="status_pengerjaan" class="form-select rounded-3" id="statusPengerjaan">
                                <option value="Menunggu Pembayaran">Menunggu Pembayaran</option>
                                <option value="Menunggu Diproses">Menunggu Diproses</option>
                                <option value="Sedang Diproses">Sedang Diproses</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Dibatalkan">Dibatalkan</option>
                            </select>
                        </div>

                        <div class="alert alert-warning d-none" id="peringatanBelumLunas">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Status <strong>Selesai</strong> hanya bisa dipilih jika pembayaran sudah <strong>Lunas</strong>.
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Catatan:</strong>
                            <ul class="mb-0 small mt-2">
                                <li><strong>Menunggu Pembayaran:</strong> Customer belum melakukan pembayaran.</li>
                                <li><strong>Menunggu Diproses:</strong> Pembayaran sudah masuk, pesanan menunggu dikerjakan.</li>
                                <li><strong>Sedang Diproses:</strong> Pesanan sedang dibuat.</li>
                                <li><strong>Selesai:</strong> Pesanan sudah selesai dan pembayaran wajib sudah Lunas.</li>
                                <li><strong>Dibatalkan:</strong> Pesanan dibatalkan.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_status_pengerjaan" class="btn btn-success">
                            <i class="bi bi-check me-1"></i>Simpan Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- ===== MODAL HAPUS PESANAN ===== -->
    <div class="modal fade" id="modalHapus" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-exclamation-triangle me-2"></i>Hapus Pesanan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_pesanan" id="hapusIdPesanan">
                        
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            <strong>Perhatian!</strong>
                            <p class="mb-0 mt-2">Anda akan menghapus pesanan <strong id="hapusNoPesanan">#000000</strong></p>
                            <p class="mb-0 mt-1">Aksi ini tidak dapat dibatalkan!</p>
                        </div>

                        <p class="text-muted mb-0">
                            Gunakan fitur ini untuk menghapus pesanan yang dianggap pembelian fiktif atau tidak valid.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="hapus_pesanan" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>Ya, Hapus Pesanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== JAVASCRIPT ===== -->
    <script>
        // Mengisi modal bukti pembayaran dengan file, status, dan aksi validasi.
        function setBuktiModal(fileName, noPesanan, idTransaksi, statusPembayaran, jumlahBayar, totalHarga) {
            document.getElementById('buktiNoPesanan').textContent = noPesanan;
            const image = document.getElementById('buktiImage');
            const tanpaFile = document.getElementById('buktiTanpaFile');
            const info = document.getElementById('buktiInfo');
            const formValidasi = document.getElementById('formValidasiPembayaran');
            const validasiId = document.getElementById('validasiIdTransaksi');
            const validasiLabel = document.getElementById('validasiLabel');
            const jumlah = parseInt(jumlahBayar || 0, 10);
            const total = parseInt(totalHarga || 0, 10);
            const targetStatus = total > 0 && jumlah >= total ? 'Lunas' : 'DP';
            const formatRupiah = value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);

            if (fileName) {
                image.src = '../../assets/img/bukti_transaksi/' + fileName;
                image.classList.remove('d-none');
                tanpaFile.classList.add('d-none');
            } else {
                image.removeAttribute('src');
                image.classList.add('d-none');
                tanpaFile.classList.remove('d-none');
            }

            info.innerHTML = `
                <div class="border rounded-3 p-2 bg-light">
                    <div class="d-flex justify-content-between gap-2 mb-1">
                        <span class="text-muted">Status sekarang</span>
                        <strong>${escapeHtml(statusPembayaran || 'Belum Bayar')}</strong>
                    </div>
                    <div class="d-flex justify-content-between gap-2 mb-1">
                        <span class="text-muted">Jumlah bayar</span>
                        <strong>${formatRupiah(jumlah)}</strong>
                    </div>
                    <div class="d-flex justify-content-between gap-2">
                        <span class="text-muted">Target validasi</span>
                        <strong>${escapeHtml(targetStatus)}</strong>
                    </div>
                </div>
            `;

            validasiId.value = idTransaksi || '';
            validasiLabel.textContent = 'Validasi sebagai ' + targetStatus;
            formValidasi.classList.toggle('d-none', !(idTransaksi > 0 && statusPembayaran === 'Pending'));
        }

        // Mengisi modal pengaturan harga pesanan.
        function setHargaModal(idPesanan, totalHarga, statusHarga, catatanHarga) {
            document.getElementById('hargaIdPesanan').value = idPesanan;
            document.getElementById('hargaTotalInput').value = totalHarga > 0 ? totalHarga : '';
            document.getElementById('hargaStatusInput').value = (!statusHarga || statusHarga === 'Menunggu Harga') ? 'Harga Diberikan' : statusHarga;
            document.getElementById('hargaCatatanInput').value = catatanHarga || '';
        }

        // Mengamankan teks sebelum dimasukkan ke HTML.
        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        // Membuat label status harga berdasarkan status harga dan pembayaran.
        function formatStatusHarga(statusHarga, statusPembayaran) {
            if (statusHarga === 'Menunggu Harga') {
                return 'Pending';
            }

            if (statusHarga === 'Disetujui') {
                if (['DP', 'Lunas'].includes(statusPembayaran)) {
                    return `Disetujui - ${statusPembayaran}`;
                }

                return statusPembayaran === 'Pending'
                    ? 'Disetujui - Menunggu Validasi'
                    : 'Disetujui - Belum Bayar';
            }

            return statusHarga || 'Menunggu Harga';
        }

        // Membuat tampilan detail desain pesanan untuk modal detail.
        function renderDesignDetail(pesanan) {
            if (pesanan.desain_custom_files) {
                let files = {};
                try {
                    files = typeof pesanan.desain_custom_files === 'string'
                        ? JSON.parse(pesanan.desain_custom_files || '{}')
                        : (pesanan.desain_custom_files || {});
                } catch (error) {
                    files = {};
                }

                const images = [
                    ['depan', 'Tampak Depan'],
                    ['belakang', 'Tampak Belakang'],
                    ['kanan', 'Tampak Kanan'],
                    ['kiri', 'Tampak Kiri']
                ]
                    .filter(([key]) => files[key])
                    .map(([key, label]) => `
                        <div class="col-md-6">
                            <a href="../../assets/img/desain_custom/${escapeHtml(files[key])}" target="_blank" class="text-decoration-none text-dark">
                                <div class="border rounded-3 overflow-hidden bg-light h-100">
                                    <img src="../../assets/img/desain_custom/${escapeHtml(files[key])}" alt="${escapeHtml(label)}" class="w-100" style="height: 160px; object-fit: cover;">
                                    <div class="p-2 fw-semibold small">${escapeHtml(label)}</div>
                                </div>
                            </a>
                        </div>
                    `)
                    .join('');

                const logos = Array.isArray(files.logo) ? files.logo : [];
                const logoHtml = logos.length
                    ? `
                        <div class="mt-3">
                            <p class="fw-semibold mb-2">Logo</p>
                            <div class="d-flex flex-wrap gap-2">
                                ${logos.map(logo => `
                                    <a href="../../assets/img/desain_custom/${escapeHtml(logo)}" target="_blank">
                                        <img src="../../assets/img/desain_custom/${escapeHtml(logo)}" alt="Logo" class="border rounded-3 bg-light" style="width: 88px; height: 88px; object-fit: contain;">
                                    </a>
                                `).join('')}
                            </div>
                        </div>
                    `
                    : '';

                const catatan = pesanan.desain_custom_catatan
                    ? `<div class="alert alert-light border mt-3 mb-0">${escapeHtml(pesanan.desain_custom_catatan).replaceAll('\n', '<br>')}</div>`
                    : '';

                return `
                    <hr>
                    <div class="mt-3">
                        <h6 class="fw-bold mb-3"><i class="bi bi-images me-2"></i>Desain Custom Upload</h6>
                        ${images ? `<div class="row g-3">${images}</div>` : '<p class="text-muted">Tidak ada gambar desain custom.</p>'}
                        ${logoHtml}
                        ${catatan}
                    </div>
                `;
            }

            if (pesanan.gambar_desain) {
                return `
                    <hr>
                    <div class="mt-3">
                        <h6 class="fw-bold mb-3"><i class="bi bi-palette me-2"></i>Desain Dipilih</h6>
                        <div class="border rounded-3 overflow-hidden bg-light">
                            <a href="../../assets/img/desain/${escapeHtml(pesanan.gambar_desain)}" target="_blank">
                                <img src="../../assets/img/desain/${escapeHtml(pesanan.gambar_desain)}" alt="${escapeHtml(pesanan.nama_desain || 'Desain')}" class="w-100" style="max-height: 320px; object-fit: contain;">
                            </a>
                            <div class="p-3">
                                <h6 class="fw-bold mb-1">${escapeHtml(pesanan.nama_desain || 'Desain Katalog')}</h6>
                                ${pesanan.desain_deskripsi ? `<p class="text-muted small mb-0">${escapeHtml(pesanan.desain_deskripsi)}</p>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }

            return `
                <hr>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>Data gambar desain tidak tersedia.
                </div>
            `;
        }

        // Memformat tanggal database menjadi format tampilan Indonesia.
        function formatTanggalPesanan(value) {
            if (!value) {
                return '-';
            }

            const parts = String(value).split(/[- :]/);
            if (parts.length < 3) {
                return '-';
            }

            const jam = parts[3] && parts[4] ? ` ${parts[3]}:${parts[4]}` : '';
            return `${parts[2]}/${parts[1]}/${parts[0]}${jam}`;
        }

        // Membuat tampilan rincian ukuran dari data JSON ukuran pesanan.
        function renderUkuranDetail(ukuranValue) {
            let ukuran = {};

            try {
                ukuran = typeof ukuranValue === 'string'
                    ? JSON.parse(ukuranValue || '{}')
                    : (ukuranValue || {});
            } catch (error) {
                ukuran = {};
            }

            const ukuranHtml = Object.entries(ukuran)
                .filter(([, qty]) => parseInt(qty || 0, 10) > 0)
                .map(([size, qty]) => `
                    <div class="col-auto">
                        <div class="border rounded-3 bg-light px-3 py-2 text-center">
                            <div class="text-muted small">Ukuran ${escapeHtml(size)}</div>
                            <div class="fw-bold">${parseInt(qty, 10)} pcs</div>
                        </div>
                    </div>
                `)
                .join('');

            return ukuranHtml || `
                <div class="col-12">
                    <p class="fw-bold mb-0">-</p>
                </div>
            `;
        }

        // Mengisi modal detail pesanan dengan data pesanan lengkap.
        function setDetailModal(pesanan) {
            document.getElementById('detailNoPesanan').textContent = '#' + String(pesanan.id_pesanan).padStart(6, '0');
            
            const alamatArray = pesanan.customer_alamat.split('\n').filter(a => a.trim());
            const totalHarga = parseInt(pesanan.total_harga || pesanan.harga || 0, 10);
            const alamat = alamatArray.join(' • ');
            
            let content = `
                <div class="row mb-3">
                    <div class="col-6">
                        <p class="text-muted small mb-1">Nama Pelanggan</p>
                        <p class="fw-bold">${pesanan.customer_nama}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">No HP</p>
                        <p class="fw-bold">${pesanan.customer_hp}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Tanggal Pesan</p>
                        <p class="fw-bold">${formatTanggalPesanan(pesanan.tanggal_pesan)}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Tanggal Selesai</p>
                        <p class="fw-bold">${formatTanggalPesanan(pesanan.tanggal_selesai)}</p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <p class="text-muted small mb-1">Alamat</p>
                    <p class="fw-bold">${alamat}</p>
                </div>
                
                <hr>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <p class="text-muted small mb-1">Produk</p>
                        <p class="fw-bold">${pesanan.nama_produk}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Jumlah</p>
                        <p class="fw-bold">${pesanan.jumlah_beli} pcs</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Bahan</p>
                        <p class="fw-bold">${escapeHtml(pesanan.jenis_bahan || '-')}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Warna</p>
                        <p class="fw-bold">${escapeHtml(pesanan.nama_warna || '-')}</p>
                    </div>
                </div>

                <div class="mb-3">
                    <p class="text-muted small mb-2">Ukuran Yang Dipesan</p>
                    <div class="row g-2">
                        ${renderUkuranDetail(pesanan.ukuran)}
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-6">
                        <p class="text-muted small mb-1">Harga Admin</p>
                        <p class="fw-bold fs-5">${totalHarga > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID').format(totalHarga) : 'Menunggu Harga'}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Status Harga</p>
                        <p class="fw-bold">
                            <span class="badge ${pesanan.status_harga === 'Harga Diberikan' ? 'bg-info text-dark' : pesanan.status_harga === 'Disetujui' ? 'bg-success' : pesanan.status_harga === 'Ditolak' ? 'bg-danger' : 'bg-secondary'}">
                                ${escapeHtml(formatStatusHarga(pesanan.status_harga, pesanan.status_pembayaran))}
                            </span>
                        </p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Status Pengerjaan</p>
                        <p class="fw-bold">${escapeHtml(pesanan.status_pengerjaan || 'Menunggu Pembayaran')}</p>
                    </div>
                </div>
                ${pesanan.catatan_harga ? `<div class="mt-3"><p class="text-muted small mb-1">Catatan Admin</p><p class="fw-bold">${pesanan.catatan_harga}</p></div>` : ''}
                ${renderDesignDetail(pesanan)}
            `;
            
            document.getElementById('detailContent').innerHTML = content;
        }

        // Mengisi modal status pengerjaan dan membatasi opsi selesai jika belum lunas.
        function setStatusPengerjaanModal(idPesanan, statusPengerjaan, statusPembayaran) {
            document.getElementById('pengerjaanIdPesanan').value = idPesanan;
            const select = document.getElementById('statusPengerjaan');
            const selesaiOption = select.querySelector('option[value="Selesai"]');
            const peringatan = document.getElementById('peringatanBelumLunas');
            const belumLunas = statusPembayaran !== 'Lunas';

            selesaiOption.disabled = belumLunas;
            peringatan.classList.toggle('d-none', !belumLunas);
            select.value = belumLunas && statusPengerjaan === 'Selesai'
                ? 'Sedang Diproses'
                : (statusPengerjaan || 'Menunggu Pembayaran');
        }

        // Mengisi modal konfirmasi hapus pesanan.
        function setHapusModal(idPesanan, noPesanan) {
            document.getElementById('hapusIdPesanan').value = idPesanan;
            document.getElementById('hapusNoPesanan').textContent = noPesanan;
        }

        // Mengosongkan filter tabel pesanan.
        function resetFilters() {
            document.getElementById('searchNama').value = '';
            document.getElementById('filterStatus').value = '';
            filterTable();
        }

        // Memfilter baris tabel pesanan berdasarkan nama dan status.
        function filterTable() {
            const searchNama = document.getElementById('searchNama').value.toLowerCase();
            const filterStatus = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('#pesananTable tbody tr');
            
            rows.forEach(row => {
                const nama = row.dataset.nama.toLowerCase();
                const status = row.dataset.status;
                
                const namaMatch = nama.includes(searchNama);
                const statusMatch = !filterStatus || status === filterStatus;
                
                row.style.display = namaMatch && statusMatch ? '' : 'none';
            });
        }

        // Event listeners untuk filter
        document.getElementById('searchNama').addEventListener('keyup', filterTable);
        document.getElementById('filterStatus').addEventListener('change', filterTable);
    </script>

<?php

    include '../../assets/layout/admin/footer.php';

?>
