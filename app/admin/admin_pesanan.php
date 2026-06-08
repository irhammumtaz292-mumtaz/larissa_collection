<?php

    session_start();

    $laman = 'Pesanan';
    $fileLaman ='admin_pesanan.php';

    include '../../assets/layout/admin/header.php';

    // ===== HANDLER POST UNTUK VALIDASI PESANAN =====
    
    // 1) Update Status Pembayaran
    if (isset($_POST['update_status_pembayaran'])) {
        $id_transaksi = intval($_POST['id_transaksi'] ?? 0);
        $status_baru = htmlspecialchars(strip_tags($_POST['status_pembayaran'] ?? ''));
        
        if ($id_transaksi > 0 && in_array($status_baru, ['Pending', 'DP', 'Lunas'])) {
            $query_update = "UPDATE transaksi SET status_pembayaran = '$status_baru' WHERE id_transaksi = $id_transaksi";
            mysqli_query($GLOBALS['db'], $query_update);
            
            $popup = true;
            $statusPopup = 'Berhasil';
            $warnaPopup = 'success';
            $popupEksekusi = 'diperbarui';
        } else {
            $popup = true;
            $statusPopup = 'Gagal';
            $warnaPopup = 'danger';
            $popupEksekusi = 'diperbarui';
        }
    }

    // 2) Update Jumlah Bayar (Manual Input Admin)
    if (isset($_POST['update_jumlah_bayar_pembayaran'])) {
        $id_transaksi = intval($_POST['id_transaksi'] ?? 0);
        $jumlah_bayar = intval($_POST['jumlah_bayar'] ?? 0);
        
        if ($id_transaksi > 0 && $jumlah_bayar >= 0) {
            $query_update = "UPDATE transaksi SET jumlah_bayar = $jumlah_bayar WHERE id_transaksi = $id_transaksi";
            mysqli_query($GLOBALS['db'], $query_update);
            
            $popup = true;
            $statusPopup = 'Berhasil';
            $warnaPopup = 'success';
            $popupEksekusi = 'diperbarui';
        } else {
            $popup = true;
            $statusPopup = 'Gagal';
            $warnaPopup = 'danger';
            $popupEksekusi = 'diperbarui';
        }
    }

    // 2) Update Jumlah Bayar (Manual Input Admin)
    if (isset($_POST['update_jumlah_bayar_pembayaran'])) {
        $id_transaksi = intval($_POST['id_transaksi'] ?? 0);
        $jumlah_bayar = intval($_POST['jumlah_bayar'] ?? 0);
        
        if ($id_transaksi > 0 && $jumlah_bayar >= 0) {
            $query_update = "UPDATE transaksi SET jumlah_bayar = $jumlah_bayar WHERE id_transaksi = $id_transaksi";
            mysqli_query($GLOBALS['db'], $query_update);
            
            $popup = true;
            $statusPopup = 'Berhasil';
            $warnaPopup = 'success';
            $popupEksekusi = 'diperbarui';
        } else {
            $popup = true;
            $statusPopup = 'Gagal';
            $warnaPopup = 'danger';
            $popupEksekusi = 'diperbarui';
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
            $popupEksekusi = 'ditolak';
        }
    }

    // 4) Hapus Pesanan (Reject Pembelian)
    if (isset($_POST['hapus_pesanan'])) {
        $id_pesanan = intval($_POST['id_pesanan'] ?? 0);
        
        if ($id_pesanan > 0) {
            // Hapus transaksi terkait terlebih dahulu
            $query_hapus_transaksi = "DELETE FROM transaksi WHERE id_pesanan = $id_pesanan";
            mysqli_query($GLOBALS['db'], $query_hapus_transaksi);
            
            // Hapus pesanan
            $query_hapus_pesanan = "DELETE FROM pesanan WHERE id_pesanan = $id_pesanan";
            $result = mysqli_query($GLOBALS['db'], $query_hapus_pesanan);
            
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
            p.id_produk,
            p.id_bahan,
            p.id_desain,
            pr.nama_produk,
            c.nama as customer_nama,
            c.no_hp as customer_hp,
            c.alamat as customer_alamat,
            b.harga_bahan,
            d.harga_desain,
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
        LEFT JOIN desain d ON p.id_desain = d.id_desain
        LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan
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
                    <div class="card">
                        <!-- Card Header -->
                        <div class="card-header">
                            <div class="card-wrap d-flex justify-content-between align-items-center">
                                <h3 class="card-title">Pesanan</h3>
                                <form class="form" action="" method="post">
                                    <div class="input-group">
                                        <input type="search" class="form-control me-3" id="searchNama" placeholder="Cari nama..." aria-label="Search">
                                        <button class="btn btn-outline-info me-1" type="button" onclick="filterTable()"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                            </div>

                            <div class="mb-2">
                                <select class="form-select form-select-sm d-inline-block me-2" style="width: auto;" id="filterStatus" onchange="filterTable()">
                                    <option value="">Semua Status</option>
                                    <option value="Pending">Pending</option>
                                    <option value="DP">DP</option>
                                    <option value="Lunas">Lunas</option>
                                </select>
                                <button class="btn btn-sm btn-secondary me-1" onclick="resetFilters()"><i class="bi bi-arrow-clockwise me-1"></i>Reset</button>
                            </div>
                        </div>
                        <!-- /.card-header -->

                        <!-- Card Body -->
                        <div class="card-body overflow-auto" style="max-height: 500px;">
                            <div class="table-responsive">
                                <table id="pesananTable" class="table table-sm table-bordered border-dark table-hover">
                                    <thead class="text-center">
                                        <tr>
                                            <th class="bg-success">#</th>
                                            <th class="bg-success">Pelanggan</th>
                                            <th class="bg-success">Produk</th>
                                            <th class="bg-success">Jumlah</th>
                                            <th class="bg-success">Total Harga</th>
                                            <th class="bg-success">Status</th>
                                            <th class="bg-success">Bukti Bayar</th>
                                            <th class="bg-success">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($pesanan_list)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted">Belum ada pesanan</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; foreach ($pesanan_list as $pesanan): ?>
                                                <tr style="height: 10px;" data-nama="<?= strtolower(htmlspecialchars($pesanan['customer_nama'])) ?>" data-status="<?= htmlspecialchars($pesanan['status_pembayaran'] ?? 'Pending') ?>">
                                                    <td scope="row" class="text-center"><?= $no++; ?></td>
                                                    <td>
                                                        <div><?= htmlspecialchars($pesanan['customer_nama']) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($pesanan['customer_hp']) ?></small>
                                                    </td>
                                                    <td><?= htmlspecialchars($pesanan['nama_produk']) ?></td>
                                                    <td class="text-center"><?= intval($pesanan['jumlah_beli']) ?> pcs</td>
                                                    <td class="fw-bold">
                                                        <?php
                                                          $total_harga_table = hitung_total_harga_pesanan(
                                                              $pesanan['harga_bahan'],
                                                              $pesanan['harga_desain'] ?? 0,
                                                              $pesanan['jumlah_beli']
                                                          );
                                                        ?>
                                                        Rp <?= number_format($total_harga_table) ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge 
                                                            <?php 
                                                                if ($pesanan['status_pembayaran'] === 'Lunas') {
                                                                    echo 'bg-success';
                                                                } elseif ($pesanan['status_pembayaran'] === 'DP') {
                                                                    echo 'bg-warning text-dark';
                                                                } else {
                                                                    echo 'bg-secondary';
                                                                }
                                                            ?>
                                                        ">
                                                            <?= htmlspecialchars($pesanan['status_pembayaran'] ?? 'Pending') ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (!empty($pesanan['bukti_pembayaran']) && file_exists('../../assets/img/bukti_transaksi/' . $pesanan['bukti_pembayaran'])): ?>
                                                            <button class="btn btn-sm btn-outline-info" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#modalBukti"
                                                                    onclick="setBuktiModal('<?= htmlspecialchars($pesanan['bukti_pembayaran']) ?>', '<?= str_pad($pesanan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?>')">
                                                                <i class="bi bi-image"></i> Lihat
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="badge bg-light text-dark">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-outline-primary mb-1" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalDetail"
                                                                onclick="setDetailModal(<?= htmlspecialchars(json_encode($pesanan)) ?>)">
                                                            <i class="bi bi-eye me-1"></i>Lihat
                                                        </button>
                                                        <?php if (!empty($pesanan['id_transaksi'])): ?>
                                                            <?php
                                                              $total_harga = hitung_total_harga_pesanan(
                                                                  $pesanan['harga_bahan'],
                                                                  $pesanan['harga_desain'] ?? 0,
                                                                  $pesanan['jumlah_beli']
                                                              );
                                                            ?>
                                                            <button class="btn btn-sm btn-outline-success mb-1" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#modalValidasi"
                                                                    onclick="setValidasiModal(<?= intval($pesanan['id_transaksi']) ?>, '<?= htmlspecialchars($pesanan['status_pembayaran'] ?? 'Pending') ?>', <?= $total_harga ?>, <?= intval($pesanan['jumlah_bayar'] ?? 0) ?>)">
                                                                <i class="bi bi-check-circle me-1"></i>Validasi
                                                            </button>
                                                        <?php endif; ?>
                                                        <button class="btn btn-sm btn-outline-danger mb-1" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalHapus"
                                                                onclick="setHapusModal(<?= intval($pesanan['id_pesanan']) ?>, '<?= str_pad($pesanan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?>')">
                                                            <i class="bi bi-trash me-1"></i>Hapus
                                                        </button>
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
                        <option value="">Semua Status</option>
                        <option value="Pending">Pending</option>
                        <option value="DP">DP</option>
                        <option value="Lunas">Lunas</option>
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
                        <div class="col-6" data-nama="<?= strtolower(htmlspecialchars($pesanan['customer_nama'])) ?>" data-status="<?= htmlspecialchars($pesanan['status_pembayaran'] ?? 'Pending') ?>">
                            <div class="card mb-3 shadow-sm border-0 rounded-4 overflow-hidden">
                                <!-- Header -->
                                <div class="card-header bg-info d-flex justify-content-between align-items-center py-2">
                                    <div class="me-2 overflow-hidden">
                                        <h6 class="mb-0 fw-semibold text-truncate">#<?= str_pad($pesanan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?></h6>
                                        <small class="text-muted text-truncate d-block"><?= htmlspecialchars($pesanan['customer_nama']) ?></small>
                                    </div>
                                    <span class="badge rounded-pill px-2 py-1 bg-<?= ($pesanan['status_pembayaran'] === 'Lunas') ? 'success' : ($pesanan['status_pembayaran'] === 'DP' ? 'warning' : 'secondary'); ?>">
                                        <?= htmlspecialchars($pesanan['status_pembayaran'] ?? 'Pending') ?>
                                    </span>
                                </div>

                                <!-- Body -->
                                <div class="card-body py-2" style="background-color: #DCDCDC;">
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
                                            <span class="text-muted fw-medium">Total</span>
                                            <div class="fw-bold">
                                                <?php
                                                  $total_harga_mobile = hitung_total_harga_pesanan(
                                                      $pesanan['harga_bahan'],
                                                      $pesanan['harga_desain'] ?? 0,
                                                      $pesanan['jumlah_beli']
                                                  );
                                                ?>
                                                Rp <?= number_format($total_harga_mobile, 0, '.', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <span class="text-muted fw-medium">HP</span>
                                            <div><?= htmlspecialchars($pesanan['customer_hp']) ?></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="card-footer border-0 pt-0 pb-2" style="background-color: #DCDCDC;">
                                    <div class="d-flex gap-2 flex-column">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalDetail"
                                            onclick="setDetailModal(<?= htmlspecialchars(json_encode($pesanan)) ?>)">
                                            <i class="bi bi-eye me-1"></i>Detail
                                        </button>
                                        <?php if (!empty($pesanan['bukti_pembayaran']) && file_exists('../../assets/img/bukti_transaksi/' . $pesanan['bukti_pembayaran'])): ?>
                                            <button class="btn btn-sm btn-outline-info rounded-pill" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalBukti"
                                                    onclick="setBuktiModal('<?= htmlspecialchars($pesanan['bukti_pembayaran']) ?>', '<?= str_pad($pesanan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?>')">
                                                <i class="bi bi-image me-1"></i>Bukti
                                            </button>
                                        <?php endif; ?>
                                        <?php if (!empty($pesanan['id_transaksi'])): ?>
                                            <?php
                                              $total_harga = hitung_total_harga_pesanan(
                                                  $pesanan['harga_bahan'],
                                                  $pesanan['harga_desain'] ?? 0,
                                                  $pesanan['jumlah_beli']
                                              );
                                            ?>
                                            <button class="btn btn-sm btn-outline-success rounded-pill" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalValidasi"
                                                    onclick="setValidasiModal(<?= intval($pesanan['id_transaksi']) ?>, '<?= htmlspecialchars($pesanan['status_pembayaran'] ?? 'Pending') ?>', <?= $total_harga ?>, <?= intval($pesanan['jumlah_bayar'] ?? 0) ?>)">
                                                <i class="bi bi-check-circle me-1"></i>Validasi
                                            </button>
                                        <?php endif; ?>
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
                    <img id="buktiImage" src="" alt="Bukti Pembayaran" class="img-fluid rounded-3" style="max-width: 100%; max-height: 400px; object-fit: contain;">
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

    <!-- ===== MODAL VALIDASI PEMBAYARAN ===== -->
    <div class="modal fade" id="modalValidasi" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-check-circle me-2"></i>Validasi Pembayaran
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formValidasi">
                    <div class="modal-body">
                        <input type="hidden" name="id_transaksi" id="validasiTransaksiId">
                        
                        <!-- RINGKASAN PEMBAYARAN -->
                        <div class="alert alert-light border border-2 mb-4">
                            <h6 class="fw-bold mb-3 text-info">
                                <i class="bi bi-credit-card me-2"></i>Ringkasan Pembayaran
                            </h6>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="text-muted small">Total Harus Dibayar</div>
                                    <div class="fw-bold fs-6" id="totalHargaDisplay">Rp 0</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Sudah Dibayar</div>
                                    <div class="fw-bold fs-6" id="sudahDibayarDisplay">Rp 0</div>
                                </div>
                            </div>
                        </div>

                        <!-- INPUT PEMBAYARAN MANUAL -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-cash-coin me-2"></i>Masukkan Nominal Pembayaran
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text rounded-3">Rp</span>
                                <input 
                                    type="number" 
                                    name="jumlah_bayar" 
                                    id="jumlahBayarInput" 
                                    class="form-control rounded-3" 
                                    placeholder="0" 
                                    min="0" 
                                    required
                                    onchange="updateStatusOtomatis()"
                                    oninput="updateStatusOtomatis()">
                            </div>
                            <div class="form-text mt-2">
                                <div id="statusOtomatis" class="d-none">
                                    Status akan berubah menjadi: <strong id="statusOtomatisLabel"></strong>
                                </div>
                            </div>
                        </div>

                        <!-- SISA PEMBAYARAN -->
                        <div class="alert alert-info">
                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    <i class="bi bi-info-circle me-2"></i>
                                </div>
                                <div class="col">
                                    <div class="small text-muted">Sisa Pembayaran</div>
                                    <div class="fw-bold fs-6" id="sisaPembayaranDisplay">Rp 0</div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- STATUS PEMBAYARAN -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status Pembayaran</label>
                            <select name="status_pembayaran" class="form-select rounded-3" id="statusPembayaran">
                                <option value="Pending">⏳ Pending - Menunggu Pembayaran</option>
                                <option value="DP">⚠️ DP - Down Payment (Cicilan)</option>
                                <option value="Lunas">✅ Lunas - Pembayaran Lengkap</option>
                            </select>
                            <div class="form-text mt-2" id="statusInfo"></div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Catatan:</strong>
                            <ul class="mb-0 small mt-2">
                                <li><strong>Pending:</strong> Pembayaran belum diterima atau bukti ditolak</li>
                                <li><strong>DP:</strong> Pelanggan sudah membayar sebagian (Down Payment)</li>
                                <li><strong>Lunas:</strong> Pelanggan sudah membayar penuh</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_jumlah_bayar_pembayaran" class="btn btn-info me-2">
                            <i class="bi bi-cash me-1"></i>Update Pembayaran
                        </button>
                        <button type="submit" name="update_status_pembayaran" class="btn btn-success">
                            <i class="bi bi-check me-1"></i>Perbarui Status
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
        function setBuktiModal(fileName, noPesanan) {
            document.getElementById('buktiNoPesanan').textContent = noPesanan;
            document.getElementById('buktiImage').src = '../../assets/img/bukti_transaksi/' + fileName;
        }

        function setDetailModal(pesanan) {
            document.getElementById('detailNoPesanan').textContent = '#' + String(pesanan.id_pesanan).padStart(6, '0');
            
            const alamatArray = pesanan.customer_alamat.split('\n').filter(a => a.trim());
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
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-6">
                        <p class="text-muted small mb-1">Total Harga</p>
                        <p class="fw-bold fs-5">Rp ${new Intl.NumberFormat('id-ID').format(pesanan.harga)}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Status Pembayaran</p>
                        <p class="fw-bold">
                            <span class="badge ${pesanan.status_pembayaran === 'Lunas' ? 'bg-success' : pesanan.status_pembayaran === 'DP' ? 'bg-warning text-dark' : 'bg-secondary'}">
                                ${pesanan.status_pembayaran || 'Pending'}
                            </span>
                        </p>
                    </div>
                </div>
            `;
            
            document.getElementById('detailContent').innerHTML = content;
        }

        function setValidasiModal(idTransaksi, statusCurrent, totalHarga, jumlahBayarCurrent) {
            document.getElementById('validasiTransaksiId').value = idTransaksi;
            document.getElementById('statusPembayaran').value = statusCurrent;
            document.getElementById('jumlahBayarInput').value = jumlahBayarCurrent;
            
            // Tampilkan ringkasan pembayaran
            document.getElementById('totalHargaDisplay').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalHarga);
            document.getElementById('sudahDibayarDisplay').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(jumlahBayarCurrent);
            
            // Hitung sisa pembayaran
            updateSisaPembayaran(totalHarga);
            
            const statusInfo = document.getElementById('statusInfo');
            const statusTexts = {
                'Pending': 'Status saat ini: <strong>Menunggu Pembayaran</strong>',
                'DP': 'Status saat ini: <strong>Down Payment Diterima</strong>',
                'Lunas': 'Status saat ini: <strong>Pembayaran Lengkap</strong>'
            };
            
            statusInfo.innerHTML = statusTexts[statusCurrent] || 'Status tidak diketahui';
            
            // Simpan totalHarga untuk referensi later
            window.currentTotalHarga = totalHarga;
        }

        function updateSisaPembayaran(totalHarga) {
            const jumlahBayarInput = document.getElementById('jumlahBayarInput');
            const jumlahBayar = parseInt(jumlahBayarInput.value) || 0;
            const sisa = Math.max(0, totalHarga - jumlahBayar);
            document.getElementById('sisaPembayaranDisplay').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(sisa);
        }

        function updateStatusOtomatis() {
            if (!window.currentTotalHarga) return;
            
            const jumlahBayar = parseInt(document.getElementById('jumlahBayarInput').value) || 0;
            const totalHarga = window.currentTotalHarga;
            const statusOtomatisDiv = document.getElementById('statusOtomatis');
            const statusOtomatisLabel = document.getElementById('statusOtomatisLabel');
            
            updateSisaPembayaran(totalHarga);
            
            if (jumlahBayar === 0) {
                statusOtomatisDiv.classList.add('d-none');
            } else if (jumlahBayar >= totalHarga) {
                statusOtomatisDiv.classList.remove('d-none');
                statusOtomatisLabel.textContent = '✅ Lunas';
                statusOtomatisLabel.className = 'badge bg-success';
            } else if (jumlahBayar > 0) {
                statusOtomatisDiv.classList.remove('d-none');
                statusOtomatisLabel.textContent = '⚠️ DP';
                statusOtomatisLabel.className = 'badge bg-warning text-dark';
            }
        }

        function setHapusModal(idPesanan, noPesanan) {
            document.getElementById('hapusIdPesanan').value = idPesanan;
            document.getElementById('hapusNoPesanan').textContent = noPesanan;
        }

        function resetFilters() {
            document.getElementById('searchNama').value = '';
            document.getElementById('filterStatus').value = '';
            filterTable();
        }

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
