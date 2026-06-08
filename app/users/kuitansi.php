<?php

  // Mulai session untuk mengakses data user yang login
  session_start();

  if (!isset($_SESSION['id_akun'])) {
    header('Location: ../../login.php');
    exit;
  }

  // Metadata halaman
  $title = 'Kuitansi Pesanan';
  $halmut = './';
  $halkuitansi = 'kuitansi.php';

  // Ambil id_pesanan dari URL
  $id_pesanan = intval($_GET['id_pesanan'] ?? 0);

  if (empty($id_pesanan)) {
    header('Location: index.php');
    exit;
  }

  require_once '../../config/db/db.php';
  require_once '../../config/controller/controller.php';

  $id_akun = intval($_SESSION['id_akun']);
  $pesanan_milik_user = select("
    SELECT p.id_pesanan
    FROM pesanan p
    JOIN akun a ON p.id_customer = a.id_customer
    WHERE p.id_pesanan = $id_pesanan
      AND a.id_akun = $id_akun
    LIMIT 1
  ");

  if (empty($pesanan_milik_user)) {
    header('Location: riwayat_pesanan.php');
    exit;
  }

  // Sertakan dependencies
  include '../../assets/layout/users/header.php';
  
  if (isset($_POST['update_bukti_pembayaran'])) {
    $id_transaksi = intval($_POST['id_transaksi'] ?? 0);
    
    if ($id_transaksi > 0 && isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
      $bukti_file = upload_foto('bukti_pembayaran', 'bukti_transaksi');
      
      if ($bukti_file) {
        $bukti_file_escaped = mysqli_real_escape_string($db, $bukti_file);
        $query_update = "UPDATE transaksi SET bukti_pembayaran = '$bukti_file_escaped'
          WHERE id_transaksi = $id_transaksi AND id_pesanan = $id_pesanan";
        
        if (mysqli_query($db, $query_update) && mysqli_affected_rows($db) > 0) {
          $popup = true;
          $statusPopup = 'success';
          $pesan = 'Bukti pembayaran berhasil diperbarui!';
        } else {
          $popup = true;
          $statusPopup = 'danger';
          $pesan = 'Gagal mengupdate bukti pembayaran.';
        }
      } else {
        $popup = true;
        $statusPopup = 'danger';
        $pesan = 'Gagal mengupload file.';
      }
    } else {
      $popup = true;
      $statusPopup = 'danger';
      $pesan = 'Silahkan pilih file bukti pembayaran.';
    }
    
    // Reload halaman untuk refresh data
    if ($statusPopup === 'success') {
      echo '<div class="alert alert-' . $statusPopup . ' alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: auto; max-width: 400px;">
              <i class="bi bi-check-circle me-2"></i>' . $pesan . '
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
      header('Refresh: 2; url=kuitansi.php?id_pesanan=' . $id_pesanan);
    }
  }

  // Query untuk mendapatkan data pesanan lengkap
  // JOIN dengan customer, produk, bahan, desain (untuk design existing)
  $query_pesanan = "
    SELECT 
      p.id_pesanan,
      p.id_customer,
      p.id_produk,
      p.id_bahan,
      p.id_desain,
      p.id_desain_custom,
      p.jumlah_beli,
      p.ukuran,
      p.harga,
      p.harga_dp,
      c.nama as customer_nama,
      c.no_hp as customer_hp,
      c.alamat as customer_alamat,
      pr.nama_produk,
      b.jenis_bahan,
      b.harga_bahan,
      d.nama_desain,
      d.harga_desain
    FROM pesanan p
    JOIN customer c ON p.id_customer = c.id_customer
    JOIN produk pr ON p.id_produk = pr.id_produk
    JOIN bahan b ON p.id_bahan = b.id_bahan
    LEFT JOIN desain d ON p.id_desain = d.id_desain
    WHERE p.id_pesanan = $id_pesanan
    LIMIT 1
  ";

  $pesanan_data = select($query_pesanan);

  if (empty($pesanan_data)) {
    echo '<div class="alert alert-danger" role="alert">Pesanan tidak ditemukan!</div>';
    header('Location: index.php');
    exit;
  }

  $pesanan = $pesanan_data[0];

  // Query untuk mendapatkan data transaksi
  $query_transaksi = "
    SELECT 
      id_transaksi,
      metode_pembayaran,
      status_pembayaran,
      jumlah_bayar,
      bukti_pembayaran,
      tanggal_pembayaran
    FROM transaksi
    WHERE id_pesanan = $id_pesanan
    LIMIT 1
  ";

  $transaksi_data = select($query_transaksi);
  $transaksi = !empty($transaksi_data) ? $transaksi_data[0] : null;

  // Query untuk mendapatkan data design custom jika ada
  $desain_custom = null;
  if (!empty($pesanan['id_desain_custom'])) {
    $query_dc = "SELECT * FROM desain_custom WHERE id_desain_custom = " . intval($pesanan['id_desain_custom']) . " LIMIT 1";
    $dc_data = select($query_dc);
    $desain_custom = !empty($dc_data) ? $dc_data[0] : null;
  }

  // Parse ukuran dari JSON
  $ukuran = json_decode($pesanan['ukuran'], true) ?? [];

?>

<main class="">
  <section aria-label="Kuitansi" class="py-4">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">

          <!-- KUITANSI CARD -->
          <div class="card border-0 shadow-lg rounded-5" id="kuitansi-container">
            <div class="card-body p-5">

              <!-- HEADER -->
              <div class="text-center mb-5 pb-4 border-bottom">
                <h2 class="fw-bold mb-2">KUITANSI PESANAN</h2>
                <p class="text-muted mb-0">Terima kasih telah memesan kepada kami</p>
              </div>

              <!-- INFO PESANAN -->
              <div class="row mb-5">
                <div class="col-md-6">
                  <div class="mb-3">
                    <p class="text-muted small mb-1">No. Pesanan</p>
                    <p class="fw-bold fs-5">#<?= str_pad($pesanan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?></p>
                  </div>
                  <div class="mb-3">
                    <p class="text-muted small mb-1">Tanggal Pesanan</p>
                    <p class="fw-bold"><?= isset($transaksi['tanggal_pembayaran']) ? date('d F Y', strtotime($transaksi['tanggal_pembayaran'])) : date('d F Y') ?></p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <p class="text-muted small mb-1">Status Pembayaran</p>
                    <p class="fw-bold">
                      <?php if ($transaksi): ?>
                        <span class="badge 
                          <?php 
                            if ($transaksi['status_pembayaran'] === 'Lunas') {
                              echo 'bg-success';
                            } elseif ($transaksi['status_pembayaran'] === 'DP') {
                              echo 'bg-warning';
                            } else {
                              echo 'bg-secondary';
                            }
                          ?>
                        ">
                          <?= htmlspecialchars($transaksi['status_pembayaran']) ?>
                        </span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Menunggu Konfirmasi</span>
                      <?php endif; ?>
                    </p>
                  </div>
                  <div class="mb-3">
                    <p class="text-muted small mb-1">Metode Pembayaran</p>
                    <p class="fw-bold">
                      <?php 
                        if ($transaksi) {
                          $metode = strtoupper(str_replace('_', ' ', $transaksi['metode_pembayaran']));
                          echo htmlspecialchars($metode);
                        } else {
                          echo '-';
                        }
                      ?>
                    </p>
                  </div>
                </div>
              </div>

              <!-- DATA PEMESAN -->
              <div class="mb-5 p-4 rounded-4 bg-light">
                <h6 class="fw-bold mb-3">
                  <i class="bi bi-person-circle me-2"></i>Data Pemesan
                </h6>
                <div class="row">
                  <div class="col-md-6">
                    <p class="text-muted small mb-1">Nama Lengkap</p>
                    <p class="fw-bold mb-3"><?= htmlspecialchars($pesanan['customer_nama']) ?></p>
                  </div>
                  <div class="col-md-6">
                    <p class="text-muted small mb-1">No HP</p>
                    <p class="fw-bold mb-3"><?= htmlspecialchars($pesanan['customer_hp']) ?></p>
                  </div>
                </div>
                <p class="text-muted small mb-1">Alamat Pengiriman</p>
                <p class="fw-bold"><?= htmlspecialchars($pesanan['customer_alamat']) ?></p>
              </div>

              <!-- DETAIL PESANAN -->
              <div class="mb-5">
                <h6 class="fw-bold mb-3">
                  <i class="bi bi-bag-check me-2"></i>Detail Pesanan
                </h6>

                <div class="table-responsive">
                  <table class="table table-borderless">
                    <tr>
                      <td class="text-muted small">Produk</td>
                      <td class="fw-bold"><?= htmlspecialchars($pesanan['nama_produk']) ?></td>
                    </tr>
                    <tr>
                      <td class="text-muted small">Bahan</td>
                      <td class="fw-bold"><?= htmlspecialchars($pesanan['jenis_bahan']) ?></td>
                    </tr>
                    <tr>
                      <td class="text-muted small">Jumlah Beli</td>
                      <td class="fw-bold"><?= intval($pesanan['jumlah_beli']) ?> pcs</td>
                    </tr>
                  </table>
                </div>
              </div>

              <!-- RINCIAN UKURAN -->
              <?php if (!empty($ukuran)): ?>
              <div class="mb-5 p-4 rounded-4 bg-light">
                <h6 class="fw-bold mb-3">
                  <i class="bi bi-rulers me-2"></i>Rincian Ukuran
                </h6>
                <div class="row g-2">
                  <?php foreach ($ukuran as $size => $qty): ?>
                    <?php if ($qty > 0): ?>
                      <div class="col-auto">
                        <div class="text-center p-3 bg-white rounded-3 border">
                          <p class="text-muted small mb-1">Ukuran <?= htmlspecialchars($size) ?></p>
                          <p class="fw-bold fs-5"><?= intval($qty) ?> pcs</p>
                        </div>
                      </div>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endif; ?>

              <!-- DESIGN -->
              <div class="mb-5">
                <h6 class="fw-bold mb-3">
                  <i class="bi bi-palette me-2"></i>Desain
                </h6>

                <?php if ($desain_custom): ?>
                  <!-- Custom Design -->
                  <div class="alert alert-info alert-dismissible fade show">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Desain Custom</strong> - Sudah diunggah oleh Anda
                  </div>

                  <div class="row g-3 mb-3">
                    <?php 
                      // Parse files JSON
                      $files = json_decode($desain_custom['files'] ?? '{}', true);
                      
                      // Parse catatan - split by \n and extract per-side notes
                      $catatan_all = $desain_custom['catatan'] ?? '';
                      $catatan_array = [];
                      if (!empty($catatan_all)) {
                        $lines = explode("\n", $catatan_all);
                        foreach ($lines as $line) {
                          if (strpos($line, ': ') !== false) {
                            list($key, $value) = explode(': ', $line, 2);
                            $catatan_array[strtolower(trim($key))] = trim($value);
                          }
                        }
                      }
                      
                      $custom_images = [
                        ['label' => 'Tampak Depan', 'image' => $files['depan'] ?? null, 'catatan' => $catatan_array['depan'] ?? ''],
                        ['label' => 'Tampak Belakang', 'image' => $files['belakang'] ?? null, 'catatan' => $catatan_array['belakang'] ?? ''],
                        ['label' => 'Tampak Kanan', 'image' => $files['kanan'] ?? null, 'catatan' => $catatan_array['kanan'] ?? ''],
                        ['label' => 'Tampak Kiri', 'image' => $files['kiri'] ?? null, 'catatan' => $catatan_array['kiri'] ?? ''],
                      ];
                    ?>

                    <?php foreach ($custom_images as $img): ?>
                      <?php if (!empty($img['image'])): ?>
                        <div class="col-md-6">
                          <div class="card border-0 shadow-sm h-100">
                            <div style="height: 150px; overflow: hidden; background: #f0f0f0;">
                              <img src="../../assets/img/desain/<?= htmlspecialchars($img['image']) ?>" 
                                   alt="<?= htmlspecialchars($img['label']) ?>" 
                                   class="w-100 h-100 object-fit-cover">
                            </div>
                            <div class="card-body">
                              <h6 class="card-title"><?= htmlspecialchars($img['label']) ?></h6>
                              <?php if (!empty($img['catatan'])): ?>
                                <p class="text-muted small"><?= htmlspecialchars($img['catatan']) ?></p>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </div>

                  <?php 
                    // Display logos if available
                    $logos = $files['logo'] ?? [];
                    if (!empty($logos)): 
                  ?>
                    <div class="card border-0 shadow-sm p-3">
                      <h6 class="fw-bold mb-2">
                        <i class="bi bi-image me-1"></i>Logo
                      </h6>
                      <div class="row g-2">
                        <?php foreach ($logos as $logo): ?>
                          <div class="col-4">
                            <div style="height: 100px; overflow: hidden; background: #f0f0f0; border-radius: 8px;">
                              <img src="../../assets/img/desain/<?= htmlspecialchars($logo) ?>" 
                                   alt="Logo" 
                                   class="w-100 h-100 object-fit-contain">
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                      <?php if (!empty($catatan_array['logo'])): ?>
                        <p class="text-muted small mt-2 mb-0"><?= htmlspecialchars($catatan_array['logo']) ?></p>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>

                <?php elseif (!empty($pesanan['id_desain']) && !empty($pesanan['nama_desain'])): ?>
                  <!-- Design dari Katalog -->
                  <div class="alert alert-info alert-dismissible fade show">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Desain Katalog</strong> - <?= htmlspecialchars($pesanan['nama_desain']) ?>
                  </div>

                  <div class="card border-0 shadow-sm">
                    <div style="height: 250px; overflow: hidden; background: #f0f0f0;">
                      <!-- Query untuk mendapatkan gambar desain -->
                      <?php 
                        $design_image_query = "SELECT gambar_desain FROM desain WHERE id_desain = " . intval($pesanan['id_desain']) . " LIMIT 1";
                        $design_image_data = select($design_image_query);
                        $design_image = !empty($design_image_data) ? $design_image_data[0]['gambar_desain'] : '';
                      ?>
                      <?php if (!empty($design_image)): ?>
                        <img src="../../assets/img/desain/<?= htmlspecialchars($design_image) ?>" 
                             alt="<?= htmlspecialchars($pesanan['nama_desain']) ?>" 
                             class="w-100 h-100 object-fit-cover">
                      <?php else: ?>
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                          <i class="bi bi-image" style="font-size: 48px;"></i>
                        </div>
                      <?php endif; ?>
                    </div>
                    <div class="card-body">
                      <h6 class="card-title"><?= htmlspecialchars($pesanan['nama_desain']) ?></h6>
                      <p class="text-muted small">Harga Desain: Rp <?= number_format($pesanan['harga_desain'] ?? 0) ?></p>
                    </div>
                  </div>

                <?php else: ?>
                  <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Data desain tidak tersedia
                  </div>
                <?php endif; ?>
              </div>

              <!-- RINCIAN PEMBAYARAN -->
              <div class="mb-5 p-4 rounded-4 bg-light">
                <h6 class="fw-bold mb-3">
                  <i class="bi bi-credit-card-2-front me-2"></i>Rincian Pembayaran
                </h6>

                <?php
                  // Hitung total dengan memperhitungkan harga desain
                  $harga_bahan_total = intval($pesanan['harga_bahan']) * intval($pesanan['jumlah_beli']);
                  $harga_desain_total = (intval($pesanan['harga_desain'] ?? 0)) * intval($pesanan['jumlah_beli']);
                  $total_harga_keseluruhan = hitung_total_harga_pesanan(
                    $pesanan['harga_bahan'],
                    $pesanan['harga_desain'] ?? 0,
                    $pesanan['jumlah_beli']
                  );
                  $bayar = $transaksi ? intval($transaksi['jumlah_bayar']) : 0;
                  $sisa_pembayaran = max(0, $total_harga_keseluruhan - $bayar);
                ?>

                <div class="table-responsive">
                  <table class="table table-borderless">
                    <tr>
                      <td class="text-muted">Harga Bahan</td>
                      <td class="text-muted text-end small">Rp <?= number_format(intval($pesanan['harga_bahan'])) ?> × <?= intval($pesanan['jumlah_beli']) ?> pcs</td>
                    </tr>
                    <tr>
                      <td></td>
                      <td class="fw-bold text-end">Rp <?= number_format($harga_bahan_total) ?></td>
                    </tr>
                    <?php if (!empty($pesanan['harga_desain'])): ?>
                    <tr>
                      <td class="text-muted">Harga Desain</td>
                      <td class="text-muted text-end small">Rp <?= number_format(intval($pesanan['harga_desain'])) ?> × <?= intval($pesanan['jumlah_beli']) ?> pcs</td>
                    </tr>
                    <tr>
                      <td></td>
                      <td class="fw-bold text-end">Rp <?= number_format($harga_desain_total) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="border-top pt-3">
                      <td class="text-muted fw-bold">Total Harga</td>
                      <td class="fw-bold text-end fs-5 text-success">Rp <?= number_format($total_harga_keseluruhan) ?></td>
                    </tr>
                    <tr>
                      <td class="text-muted">Dibayarkan</td>
                      <td class="fw-bold text-end">
                        <?php if ($bayar > 0): ?>
                          <span class="text-success">Rp <?= number_format($bayar) ?></span>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="text-muted">Sisa Pembayaran</td>
                      <td class="fw-bold text-end">
                        Rp <?= number_format($sisa_pembayaran) ?>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>

              <!-- INFO TRANSAKSI -->
              <?php if ($transaksi): ?>
              <div class="mb-5 p-4 rounded-4 bg-light">
                <h6 class="fw-bold mb-3">
                  <i class="bi bi-receipt me-2"></i>Informasi Transaksi
                </h6>

                <div class="table-responsive">
                  <table class="table table-borderless">
                    <tr>
                      <td class="text-muted small">No. Transaksi</td>
                      <td class="fw-bold">#<?= str_pad($transaksi['id_transaksi'], 6, '0', STR_PAD_LEFT) ?></td>
                    </tr>
                    <tr>
                      <td class="text-muted small">Metode Pembayaran</td>
                      <td class="fw-bold"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $transaksi['metode_pembayaran']))) ?></td>
                    </tr>
                    <tr>
                      <td class="text-muted small">Total Harus Dibayar</td>
                      <td class="fw-bold text-success">Rp <?= number_format($total_harga_keseluruhan) ?></td>
                    </tr>
                    <tr>
                      <td class="text-muted small">Jumlah Terbayar</td>
                      <td class="fw-bold">
                        <?php if ($bayar > 0): ?>
                          Rp <?= number_format($bayar) ?>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="text-muted small">Tanggal Pembayaran</td>
                      <td class="fw-bold"><?= date('d F Y', strtotime($transaksi['tanggal_pembayaran'])) ?></td>
                    </tr>
                    <tr>
                      <td class="text-muted small">Status</td>
                      <td class="fw-bold">
                        <span class="badge 
                          <?php 
                            if ($transaksi['status_pembayaran'] === 'Lunas') {
                              echo 'bg-success';
                            } elseif ($transaksi['status_pembayaran'] === 'DP') {
                              echo 'bg-warning';
                            } else {
                              echo 'bg-secondary';
                            }
                          ?>
                        ">
                          <?= htmlspecialchars($transaksi['status_pembayaran']) ?>
                        </span>
                      </td>
                    </tr>
                  </table>
                </div>

                <!-- TUJUAN PEMBAYARAN - HANYA JIKA STATUS DP -->
                <?php if ($transaksi && $transaksi['status_pembayaran'] === 'DP'): ?>

                <!-- TUJUAN PEMBAYARAN - QRIS -->
                <?php if ($transaksi['metode_pembayaran'] === 'qris'): ?>
                <div class="alert alert-info border-2 mt-3">
                  <h6 class="fw-bold mb-3">
                    <i class="bi bi-qr-code me-2"></i>Tujuan Pembayaran: QRIS
                  </h6>
                  <div class="text-center">
                    <div class="bg-white p-4 rounded-3 border border-secondary" style="max-width: 250px; margin: 0 auto;">
                      <div style="width: 200px; height: 200px; margin: 0 auto; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                        <div class="text-center text-muted">
                          <i class="bi bi-qr-code" style="font-size: 80px;"></i>
                          <p class="small mt-2">QR Code QRIS</p>
                        </div>
                      </div>
                    </div>
                    <p class="text-muted small mt-3">Scan QR Code di atas menggunakan aplikasi e-wallet Anda</p>
                  </div>
                </div>
                <?php endif; ?>

                <!-- TUJUAN PEMBAYARAN - VIRTUAL ACCOUNT -->
                <?php if ($transaksi['metode_pembayaran'] === 'virtual_account'): ?>
                <div class="alert alert-info border-2 mt-3">
                  <h6 class="fw-bold mb-3">
                    <i class="bi bi-bank me-2"></i>Tujuan Pembayaran: Virtual Account
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-sm table-borderless">
                      <tbody>
                        <tr>
                          <td><strong>Bank</strong></td>
                          <td class="text-end"><strong>BRI</strong></td>
                        </tr>
                        <tr>
                          <td><strong>Nomor Rekening</strong></td>
                          <td class="text-end">
                            <code style="font-size: 14px; background: #f0f0f0; padding: 6px 10px; border-radius: 4px;">
                              9876543210
                            </code>
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Atas Nama</strong></td>
                          <td class="text-end">PT. KONVEKSI APP</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <p class="text-muted small">Transfer ke nomor rekening di atas sesuai dengan nominal yang tertera</p>
                </div>
                <?php endif; ?>

                <!-- TUJUAN PEMBAYARAN - TRANSFER BANK -->
                <?php if ($transaksi['metode_pembayaran'] === 'transfer'): ?>
                <div class="alert alert-info border-2 mt-3">
                  <h6 class="fw-bold mb-3">
                    <i class="bi bi-arrow-left-right me-2"></i>Tujuan Pembayaran: Transfer Bank
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-sm table-borderless">
                      <tbody>
                        <tr>
                          <td><strong>Bank</strong></td>
                          <td class="text-end"><strong>BCA</strong></td>
                        </tr>
                        <tr>
                          <td><strong>Nomor Rekening</strong></td>
                          <td class="text-end">
                            <code style="font-size: 14px; background: #f0f0f0; padding: 6px 10px; border-radius: 4px;">
                              9876543210
                            </code>
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Atas Nama</strong></td>
                          <td class="text-end">PT. KONVEKSI APP</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <p class="text-muted small">Transfer ke nomor rekening di atas sesuai dengan nominal yang tertera</p>
                </div>
                <?php endif; ?>

                <!-- TUJUAN PEMBAYARAN - PEMBAYARAN LANGSUNG -->
                <?php if ($transaksi['metode_pembayaran'] === 'cash'): ?>
                <div class="alert alert-info border-2 mt-3">
                  <h6 class="fw-bold mb-3">
                    <i class="bi bi-cash-coin me-2"></i>Tujuan Pembayaran: Pembayaran Langsung
                  </h6>
                  <p class="mb-0">
                    Pembayaran akan dilakukan secara langsung saat barang diterima atau sesuai dengan kesepakatan antara Anda dan admin.
                  </p>
                </div>
                <?php endif; ?>

                <?php endif; ?>
                <?php if ($transaksi['status_pembayaran'] === 'Pending'): ?>
                <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                  <i class="bi bi-info-circle me-2"></i>
                  <strong>Menunggu Konfirmasi</strong> - Admin akan mengecek pembayaran Anda dan memperbarui status. Mohon tunggu.
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- UPLOAD BUKTI PEMBAYARAN - HANYA JIKA STATUS DP -->
                <?php if ($transaksi && $transaksi['status_pembayaran'] === 'DP'): ?>
                <div class="mt-4 pt-4 border-top">
                  <div class="card border-warning rounded-3 shadow-sm">
                    <div class="card-body">
                      <h6 class="card-title fw-bold mb-3">
                        <i class="bi bi-cloud-upload me-2 text-warning"></i>Upload Bukti Pembayaran
                      </h6>
                      <p class="text-muted small mb-3">
                        Jika belum mengunggah bukti pembayaran atau ingin memperbarui bukti pembayaran Anda, silakan unggah file di bawah ini.
                      </p>
                      
                      <form method="POST" enctype="multipart/form-data" id="formUploadBukti">
                        <div class="mb-3">
                          <label for="buktiPembayaran" class="form-label">
                            Pilih File Bukti Pembayaran <span class="text-danger">*</span>
                          </label>
                          <input type="hidden" name="id_transaksi" value="<?= intval($transaksi['id_transaksi']) ?>">
                          <input type="file" 
                                 id="buktiPembayaran" 
                                 name="bukti_pembayaran" 
                                 accept="image/*" 
                                 class="form-control rounded-3"
                                 required>
                          <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle me-1"></i>Format: JPG, PNG, JPEG, GIF (Max 2MB)
                          </small>
                        </div>
                        
                        <div id="previewBukti" class="mb-3" style="display: none;">
                          <p class="text-muted small mb-2">Pratinjau:</p>
                          <img id="imageBuktiPreview" 
                               src="" 
                               alt="Preview" 
                               class="img-thumbnail rounded-3"
                               style="max-width: 200px; max-height: 200px;">
                        </div>
                        
                        <button type="submit" name="update_bukti_pembayaran" class="btn btn-warning rounded-3 w-100 fw-semibold">
                          <i class="bi bi-check-circle me-2"></i>Upload Bukti Pembayaran
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
                
                <script>
                  document.getElementById('buktiPembayaran').addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                      const reader = new FileReader();
                      reader.onload = function(event) {
                        document.getElementById('imageBuktiPreview').src = event.target.result;
                        document.getElementById('previewBukti').style.display = 'block';
                      };
                      reader.readAsDataURL(file);
                    } else {
                      document.getElementById('previewBukti').style.display = 'none';
                    }
                  });
                </script>
                <?php endif; ?>

                <?php if (!empty($transaksi['bukti_pembayaran']) && file_exists('../../assets/img/bukti_transaksi/' . $transaksi['bukti_pembayaran'])): ?>
                <div class="mt-3 pt-3 border-top">
                  <p class="text-muted small mb-2">Bukti Pembayaran</p>
                  <img src="../../assets/img/bukti_transaksi/<?= htmlspecialchars($transaksi['bukti_pembayaran']) ?>" 
                       alt="Bukti Pembayaran" 
                       class="img-fluid rounded-3" 
                       style="max-width: 300px;">
                </div>
                <?php endif; ?>
              </div>
              <?php endif; ?>

              <!-- BUTTON -->
              <div class="d-flex gap-2 justify-content-center">
                <button class="btn btn-warning rounded-pill px-4 py-2 fw-semibold text-dark" onclick="printKuitansi()">
                  <i class="bi bi-printer me-2"></i>Cetak Kuitansi
                </button>
                <a href="index.php" class="btn btn-secondary rounded-pill px-4 py-2 fw-semibold">
                  <i class="bi bi-arrow-left me-2"></i>Kembali ke Pesanan
                </a>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </section>
</main>

<script>
  function printKuitansi() {
    const element = document.getElementById('kuitansi-container');
    const printWindow = window.open('', '', 'width=800,height=600');
    
    // Ambil styling dari halaman utama
    const style = `
      <style>
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }
        body {
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          padding: 20px;
          background: white;
        }
        .card { border: none; }
        .fw-bold { font-weight: 700; }
        .fw-semibold { font-weight: 600; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-muted { color: #6c757d; }
        .small { font-size: 0.875rem; }
        .badge {
          display: inline-block;
          padding: 0.35em 0.65em;
          border-radius: 0.25rem;
          font-weight: 500;
        }
        .bg-success { background-color: #28a745; color: white; }
        .bg-warning { background-color: #ffc107; color: black; }
        .bg-secondary { background-color: #6c757d; color: white; }
        .bg-light { background-color: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        table { width: 100%; margin-bottom: 20px; }
        th, td { padding: 8px 0; border: none; }
        tr { border-bottom: 1px solid #dee2e6; }
        tr:last-child { border-bottom: none; }
        .border-bottom { border-bottom: 2px solid #dee2e6; padding-bottom: 20px; }
        .mb-3 { margin-bottom: 20px; }
        .mb-5 { margin-bottom: 30px; }
        .pb-4 { padding-bottom: 20px; }
        h2 { font-size: 24px; margin-bottom: 10px; }
        h6 { font-size: 16px; margin-bottom: 15px; }
        p { margin: 0; }
        @media print {
          body { padding: 0; }
          .no-print { display: none; }
        }
      </style>
    `;
    
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Kuitansi Pesanan #<?= str_pad($pesanan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?></title>
        ${style}
      </head>
      <body>
        ${element.innerHTML.replace(/<button[^>]*>.*?<\/button>/gs, '')}
      </body>
      </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    
    setTimeout(() => {
      printWindow.print();
      printWindow.close();
    }, 250);
  }
</script>

<?php
  include '../../assets/layout/users/footer.php';
?>
