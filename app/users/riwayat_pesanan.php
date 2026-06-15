<?php

  // Mulai session
  session_start();

  // Check apakah user sudah login
  if (!isset($_SESSION['id_akun'])) {
    header('Location: ../../login.php');
    exit;
  }

  // Metadata halaman
  $title = 'Riwayat Pesanan';
  $halmut = './';

  // Sertakan header
  include '../../assets/layout/users/header.php';

  // Ambil id_customer dari session
  // Query untuk mendapatkan id_customer dari id_akun yang login
  $id_akun = intval($_SESSION['id_akun']);
  $akun_data = select("SELECT id_customer FROM akun WHERE id_akun = $id_akun LIMIT 1");
  
  if (empty($akun_data)) {
    echo '<div class="alert alert-danger" role="alert">Terjadi kesalahan saat mengambil data akun!</div>';
    header('Location: index.php');
    exit;
  }

  $id_customer = intval($akun_data[0]['id_customer']);

  // Query untuk mendapatkan daftar pesanan user dengan informasi bahan dan warna
  $query_pesanan_list = "
    SELECT 
      p.id_pesanan,
      p.jumlah_beli,
      p.harga,
      p.total_harga,
      p.status_harga,
      p.catatan_harga,
      p.tanggal_pesan,
      p.tanggal_selesai,
      p.id_produk,
      p.id_bahan,
      pr.nama_produk,
      b.jenis_bahan,
      w.nama_warna,
      t.id_transaksi,
      t.status_pembayaran,
      t.tanggal_pembayaran
    FROM pesanan p
    JOIN produk pr ON p.id_produk = pr.id_produk
    JOIN bahan b ON p.id_bahan = b.id_bahan
    JOIN warna w ON b.id_warna = w.id_warna
    LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan
    WHERE p.id_customer = $id_customer
    ORDER BY p.id_pesanan DESC
  ";

  $pesanan_list = select($query_pesanan_list);
  $total_pesanan = count($pesanan_list);
  $pesanan_selesai = 0;
  $pesanan_proses = 0;
  $pesanan_belum_bayar = 0;

  foreach ($pesanan_list as $item_pesanan) {
    if (($item_pesanan['tanggal_selesai'] ?? null)) {
      $pesanan_selesai++;
    }

    $status_bayar_item = $item_pesanan['status_pembayaran'] ?? '';
    if (in_array($status_bayar_item, ['Pending', 'Belum Bayar', ''], true)) {
      $pesanan_belum_bayar++;
    }

    if (($item_pesanan['tanggal_selesai'] ?? null) === null) {
      $pesanan_proses++;
    }
  }

?>

<main class="order-history-page">
  <section aria-label="Riwayat Pesanan" class="order-history-section py-5">
    <div class="container">
      <div class="row mb-4">
        <div class="col-12">
          <div class="order-history-hero text-center">
            <span class="order-history-eyebrow">Riwayat Pesanan</span>
            <h2 class="fw-bold mb-2">Riwayat Pesanan Anda</h2>
            <p class="text-muted mb-0">Kelola dan lihat detail semua pesanan Anda dengan tampilan yang lebih rapi dan mudah dibaca.</p>
          </div>
        </div>
      </div>

      <?php if (!empty($pesanan_list)): ?>
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="order-history-stat-card">
              <span class="order-history-stat-label">Total Pesanan</span>
              <strong class="order-history-stat-value"><?= number_format($total_pesanan) ?></strong>
            </div>
          </div>
          <div class="col-md-4">
            <div class="order-history-stat-card">
              <span class="order-history-stat-label">Masih Diproses</span>
              <strong class="order-history-stat-value"><?= number_format($pesanan_proses) ?></strong>
            </div>
          </div>
          <div class="col-md-4">
            <div class="order-history-stat-card">
              <span class="order-history-stat-label">Belum Lunas</span>
              <strong class="order-history-stat-value"><?= number_format($pesanan_belum_bayar) ?></strong>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-12">
          <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
          <?php endif; ?>

          <?php if (empty($pesanan_list)): ?>
            <!-- KOSONG -->
            <div class="card border-0 shadow-lg rounded-5 order-history-empty-card">
              <div class="card-body p-5 text-center">
                <div class="mb-3">
                  <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                </div>
                <h4 class="fw-bold mb-2">Belum Ada Pesanan</h4>
                <p class="text-muted mb-4">Anda belum membuat pesanan apapun. Mulai berbelanja sekarang!</p>
                <a href="index.php#katalog" class="btn btn-warning rounded-pill px-4 py-2 fw-semibold text-dark order-history-primary-btn">
                  <i class="bi bi-bag-plus me-2"></i>Mulai Pesan
                </a>
              </div>
            </div>
          <?php else: ?>
            <!-- DAFTAR PESANAN -->
            <div class="order-history-table-card">
              <div class="order-history-table-head d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                <div>
                  <h4 class="mb-1">Daftar Pesanan</h4>
                  <p class="text-muted mb-0">Lihat status harga, pembayaran, dan kuitansi tiap pesanan Anda.</p>
                </div>
                <span class="order-history-table-chip mt-3 mt-lg-0"><?= number_format($total_pesanan) ?> pesanan tercatat</span>
              </div>
              <div class="table-responsive order-history-table-wrap">
              <table class="table table-hover order-history-table">
                <thead>
                  <tr>
                    <th class="fw-bold">No. Pesanan</th>
                    <th class="fw-bold">Produk</th>
                    <th class="fw-bold">Bahan & Warna</th>
                    <th class="fw-bold">Jumlah</th>
                    <th class="fw-bold">Harga Admin</th>
                    <th class="fw-bold">Status Harga</th>
                    <th class="fw-bold">Status Bayar</th>
                    <th class="fw-bold">Tanggal Pesan</th>
                    <th class="fw-bold">Tanggal Selesai</th>
                    <th class="fw-bold">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pesanan_list as $pesanan): ?>
                  <tr>
                    <td class="fw-bold order-history-order-id">#<?= str_pad($pesanan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($pesanan['nama_produk']) ?></td>
                    <td>
                      <span class="badge order-history-soft-badge"><?= htmlspecialchars($pesanan['jenis_bahan']) ?></span>
                      <span class="badge order-history-soft-badge order-history-soft-badge-accent"><?= htmlspecialchars($pesanan['nama_warna']) ?></span>
                    </td>
                    <td><?= $pesanan['jumlah_beli'] ?> pcs</td>
                    <td>
                      <?php if (!empty($pesanan['total_harga'])): ?>
                        <span class="fw-bold order-history-price">Rp <?= number_format(intval($pesanan['total_harga'])) ?></span>
                      <?php else: ?>
                        <span class="text-muted">Menunggu admin</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php
                        $statusHarga = $pesanan['status_harga'] ?? 'Menunggu Harga';
                        $labelStatusHarga = $statusHarga === 'Menunggu Harga' ? 'Pending' : $statusHarga;
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
                          'Harga Diberikan' => 'info',
                          'Disetujui' => 'success',
                          'Ditolak' => 'danger',
                          default => 'secondary'
                        };
                      ?>
                      <span class="badge bg-<?= $badgeHarga ?> order-history-status-badge"><?= htmlspecialchars($labelStatusHarga) ?></span>
                    </td>
                    <td>
                      <?php
                        $status = $pesanan['status_pembayaran'] ?? 'Belum Bayar';
                        $badgeColor = match($status) {
                          'Lunas' => 'success',
                          'DP' => 'warning',
                          'Pending' => 'secondary',
                          default => 'light'
                        };
                        $textColor = $status === 'DP' ? 'text-dark' : '';
                      ?>
                      <span class="badge bg-<?= $badgeColor ?> <?= $textColor ?> order-history-status-badge"><?= $status ?></span>
                    </td>
                    <td><?= format_tanggal_pesanan($pesanan['tanggal_pesan'] ?? null) ?></td>
                    <td><?= format_tanggal_pesanan($pesanan['tanggal_selesai'] ?? null) ?></td>
                    <td>
                      <?php if (!empty($pesanan['total_harga']) && empty($pesanan['id_transaksi'])): ?>
                        <a href="kuitansi.php?id_pesanan=<?= $pesanan['id_pesanan'] ?>" class="btn btn-sm btn-warning text-dark order-history-primary-btn" title="Bayar Pesanan">
                          <i class="bi bi-wallet2"></i> Bayar
                        </a>
                      <?php else: ?>
                        <a href="kuitansi.php?id_pesanan=<?= $pesanan['id_pesanan'] ?>" class="btn btn-sm btn-info text-white order-history-secondary-btn" title="Lihat Kuitansi">
                          <i class="bi bi-receipt"></i> Kuitansi
                        </a>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </section>
</main>

<?php
  include '../../assets/layout/users/footer.php';
?>
