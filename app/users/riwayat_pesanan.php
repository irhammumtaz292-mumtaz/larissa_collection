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
      p.id_produk,
      p.id_bahan,
      pr.nama_produk,
      b.jenis_bahan,
      b.harga_bahan,
      w.nama_warna,
      d.harga_desain,
      t.id_transaksi,
      t.status_pembayaran,
      t.tanggal_pembayaran
    FROM pesanan p
    JOIN produk pr ON p.id_produk = pr.id_produk
    JOIN bahan b ON p.id_bahan = b.id_bahan
    JOIN warna w ON b.id_warna = w.id_warna
    LEFT JOIN desain d ON p.id_desain = d.id_desain
    LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan
    WHERE p.id_customer = $id_customer
    ORDER BY p.id_pesanan DESC
  ";

  $pesanan_list = select($query_pesanan_list);

?>

<main>
  <section aria-label="Riwayat Pesanan" class="py-5">
    <div class="container">
      <div class="row mb-5">
        <div class="col-12">
          <div class="text-center mb-4">
            <h2 class="fw-bold mb-2">Riwayat Pesanan Anda</h2>
            <p class="text-muted">Kelola dan lihat detail semua pesanan Anda</p>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <?php if (empty($pesanan_list)): ?>
            <!-- KOSONG -->
            <div class="card border-0 shadow-lg rounded-5">
              <div class="card-body p-5 text-center">
                <div class="mb-3">
                  <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                </div>
                <h4 class="fw-bold mb-2">Belum Ada Pesanan</h4>
                <p class="text-muted mb-4">Anda belum membuat pesanan apapun. Mulai berbelanja sekarang!</p>
                <a href="index.php#katalog" class="btn btn-warning rounded-pill px-4 py-2 fw-semibold text-dark">
                  <i class="bi bi-bag-plus me-2"></i>Mulai Pesan
                </a>
              </div>
            </div>
          <?php else: ?>
            <!-- DAFTAR PESANAN -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th class="fw-bold">No. Pesanan</th>
                    <th class="fw-bold">Produk</th>
                    <th class="fw-bold">Bahan & Warna</th>
                    <th class="fw-bold">Jumlah</th>
                    <th class="fw-bold">Total Harga</th>
                    <th class="fw-bold">Status</th>
                    <th class="fw-bold">Tanggal</th>
                    <th class="fw-bold">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pesanan_list as $pesanan): ?>
                  <tr>
                    <td class="fw-bold">#<?= str_pad($pesanan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($pesanan['nama_produk']) ?></td>
                    <td>
                      <span class="badge bg-light text-dark"><?= htmlspecialchars($pesanan['jenis_bahan']) ?></span>
                      <span class="badge bg-info text-white"><?= htmlspecialchars($pesanan['nama_warna']) ?></span>
                    </td>
                    <td><?= $pesanan['jumlah_beli'] ?> pcs</td>
                    <td>Rp <?= number_format(hitung_total_harga_pesanan(
                      $pesanan['harga_bahan'],
                      $pesanan['harga_desain'] ?? 0,
                      $pesanan['jumlah_beli']
                    )) ?></td>
                    <td>
                      <?php
                        $status = $pesanan['status_pembayaran'] ?? 'Menunggu';
                        $badgeColor = match($status) {
                          'Lunas' => 'success',
                          'DP' => 'warning',
                          'Pending' => 'secondary',
                          default => 'light'
                        };
                        $textColor = $status === 'DP' ? 'text-dark' : '';
                      ?>
                      <span class="badge bg-<?= $badgeColor ?> <?= $textColor ?>"><?= $status ?></span>
                    </td>
                    <td><?= isset($pesanan['tanggal_pembayaran']) ? date('d/m/Y', strtotime($pesanan['tanggal_pembayaran'])) : 'Belum bayar' ?></td>
                    <td>
                      <a href="kuitansi.php?id_pesanan=<?= $pesanan['id_pesanan'] ?>" class="btn btn-sm btn-info text-white" title="Lihat Kuitansi">
                        <i class="bi bi-receipt"></i> Kuitansi
                      </a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
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
