<?php

  // Mulai session
  session_start();

  // Check apakah user sudah login
  if (!isset($_SESSION['id_akun'])) {
    header('Location: ../../login.php');
    exit;
  }

  // Check apakah ada draft pesanan di session
  if (!isset($_SESSION['draft_pesanan'])) {
    header('Location: index.php');
    exit;
  }

  // Metadata halaman
  $title = 'Konfirmasi Pesanan';
  $halmut = './';

  // Sertakan dependencies
  include '../../assets/layout/users/header.php';

  // Ambil draft pesanan dari session
  $post_data = $_SESSION['draft_pesanan'];
  $design_type = $_SESSION['draft_design_type'] ?? null;

  // Ambil data produk
  $id_produk = intval($post_data['id_produk'] ?? 1);
  $produk_data = select("SELECT * FROM produk WHERE id_produk = $id_produk LIMIT 1");
  $nama_produk = !empty($produk_data) ? htmlspecialchars($produk_data[0]['nama_produk']) : 'Produk';

  // Ambil data bahan & warna
  $id_bahan = intval($post_data['id_bahan'] ?? 0);
  $bahan_data = select("SELECT b.*, w.nama_warna FROM bahan b JOIN warna w ON b.id_warna = w.id_warna WHERE b.id_bahan = $id_bahan LIMIT 1");
  $jenis_bahan = !empty($bahan_data) ? htmlspecialchars($bahan_data[0]['jenis_bahan']) : '-';
  $nama_warna = !empty($bahan_data) ? htmlspecialchars($bahan_data[0]['nama_warna']) : '-';
  $harga_bahan = !empty($bahan_data) ? intval($bahan_data[0]['harga_bahan']) : 0;

  // Ambil data design dan harga desain
  $design_info = '';
  $harga_desain = 0;
  if ($design_type === 'existing' && isset($post_data['id_desain'])) {
    $id_desain = intval($post_data['id_desain']);
    $desain_data = select("SELECT * FROM desain WHERE id_desain = $id_desain LIMIT 1");
    if (!empty($desain_data)) {
      $design_info = htmlspecialchars($desain_data[0]['nama_desain']);
      $harga_desain = intval($desain_data[0]['harga_desain'] ?? 0);
    }
  } elseif ($design_type === 'upload' && isset($post_data['id_desain_custom'])) {
    $design_info = 'Design Custom (Upload)';
  }

  // Hitung jumlah & harga
  $sizes = [
    'S' => intval($post_data['size_s'] ?? 0),
    'M' => intval($post_data['size_m'] ?? 0),
    'L' => intval($post_data['size_l'] ?? 0),
    'XL' => intval($post_data['size_xl'] ?? 0),
    'XXL' => intval($post_data['size_xxl'] ?? 0),
    'XXXL' => intval($post_data['size_xxxl'] ?? 0),
  ];
  $jumlah_beli = array_sum($sizes);
  $subtotal_bahan = $harga_bahan * $jumlah_beli;
  $subtotal_desain = $harga_desain * $jumlah_beli;
  $total_harga = hitung_total_harga_pesanan($harga_bahan, $harga_desain, $jumlah_beli);
  $nominal_dp_default = max(1, intdiv($total_harga + 1, 2));
  $error_pembayaran = '';

  // Saat user klik "Setuju/Konfirmasi"
  if (isset($_POST['confirm_pesanan'])) {
    $metode_pembayaran = htmlspecialchars(strip_tags($_POST['metode_pembayaran'] ?? ''));
    $jenis_pembayaran = htmlspecialchars(strip_tags($_POST['jenis_pembayaran'] ?? ''));
    $nominal_dp = max(0, intval($_POST['nominal_dp'] ?? 0));
    $metode_pembayaran_valid = ['qris', 'virtual_account', 'transfer', 'cash'];

    if (!in_array($jenis_pembayaran, ['dp', 'lunas'], true)) {
      $error_pembayaran = 'Jenis pembayaran harus dipilih.';
    } elseif (!in_array($metode_pembayaran, $metode_pembayaran_valid, true)) {
      $error_pembayaran = 'Metode pembayaran harus dipilih.';
    } elseif ($jenis_pembayaran === 'dp' && ($nominal_dp <= 0 || $nominal_dp >= $total_harga)) {
      $error_pembayaran = 'Nominal DP harus lebih dari Rp 0 dan lebih kecil dari total harga.';
    } else {
      $jumlah_pembayaran = $jenis_pembayaran === 'lunas' ? $total_harga : $nominal_dp;

      $post_data['metode_pembayaran'] = $metode_pembayaran;
      $post_data['jenis_pembayaran'] = $jenis_pembayaran;
      $post_data['jumlah_bayar'] = $jumlah_pembayaran;
      $post_data['harga_dp'] = $jenis_pembayaran === 'dp' ? $jumlah_pembayaran : 0;
      
      // Panggil fungsi tambah_pesanan dengan data draft
      $id_pesanan = tambah_pesanan($post_data);

      if ($id_pesanan) {
        // Ambil harga akhir dari tabel pesanan
        $pesanan_data = select("SELECT harga FROM pesanan WHERE id_pesanan = $id_pesanan LIMIT 1");
        $total_harga_final = !empty($pesanan_data) ? intval($pesanan_data[0]['harga']) : 0;

        // Upload bukti pembayaran jika ada
        $bukti_file = null;
        if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
          $bukti_file = upload_foto('bukti_pembayaran', 'bukti_transaksi');
        }

        // Tambah transaksi
        $id_transaksi = tambah_transaksi($post_data, $id_pesanan, $total_harga_final, $bukti_file);
        
        if (!$id_transaksi) {
          if ($bukti_file) {
            $bukti_path = __DIR__ . '/../../assets/img/bukti_transaksi/' . $bukti_file;
            if (file_exists($bukti_path)) {
              @unlink($bukti_path);
            }
          }
          mysqli_query($db, "DELETE FROM pesanan WHERE id_pesanan = $id_pesanan");
          $error_pembayaran = 'Transaksi gagal disimpan. Silakan coba kembali.';
        } else {
          // Hapus draft dari session
          unset($_SESSION['draft_pesanan']);
          unset($_SESSION['draft_design_type']);
          unset($_SESSION['design_type']);
          unset($_SESSION['id_desain']);
          unset($_SESSION['id_desain_custom_uploaded']);

          // Redirect ke kuitansi
          $_SESSION['success'] = 'Pesanan berhasil dikonfirmasi! ID Pesanan: ' . $id_pesanan;
          header('Location: kuitansi.php?id_pesanan=' . $id_pesanan);
          exit;
        }
      } else {
        $error_pembayaran = 'Pesanan gagal disimpan. Silakan coba kembali.';
      }
    }
  }

?>

<main>
  <section aria-label="Konfirmasi Pesanan" class="py-5">
    <div class="container">
      <div class="row mb-5">
        <div class="col-12">
          <div class="text-center mb-4">
            <h2 class="fw-bold mb-2">Konfirmasi Pesanan Anda</h2>
            <p class="text-muted">Periksa detail pesanan sebelum melanjutkan</p>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- DETAIL PESANAN -->
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
              
              <!-- Produk -->
              <div class="mb-4">
                <h5 class="fw-bold mb-3">📦 Produk yang Dipesan</h5>
                <div class="alert alert-light border border-2">
                  <strong><?= $nama_produk ?></strong>
                </div>
              </div>

              <!-- Bahan & Warna -->
              <div class="mb-4">
                <h5 class="fw-bold mb-3">🎨 Bahan & Warna</h5>
                <div class="d-flex gap-2">
                  <span class="badge bg-light text-dark" style="font-size: 14px; padding: 8px 12px;">
                    <?= $jenis_bahan ?>
                  </span>
                  <span class="badge bg-info text-white" style="font-size: 14px; padding: 8px 12px;">
                    <?= $nama_warna ?>
                  </span>
                </div>
              </div>

              <!-- Size & Jumlah -->
              <div class="mb-4">
                <h5 class="fw-bold mb-3">👕 Rincian Ukuran</h5>
                <table class="table table-sm table-borderless">
                  <tbody>
                    <?php foreach ($sizes as $size => $qty): ?>
                      <?php if ($qty > 0): ?>
                        <tr>
                          <td class="text-muted">Size <strong><?= $size ?></strong></td>
                          <td class="text-end"><strong><?= $qty ?> pcs</strong></td>
                        </tr>
                      <?php endif; ?>
                    <?php endforeach; ?>
                    <tr class="border-top border-secondary">
                      <td class="text-muted fw-bold">Total Jumlah</td>
                      <td class="text-end fw-bold"><?= $jumlah_beli ?> pcs</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Design -->
              <div class="mb-4">
                <h5 class="fw-bold mb-3">✨ Design</h5>
                <div class="alert alert-light border border-2">
                  <?= $design_info ?: 'Belum dipilih' ?>
                </div>
              </div>

              <!-- Catatan (jika ada) -->
              <?php if (!empty($post_data['catatan'] ?? '')): ?>
                <div class="mb-4">
                  <h5 class="fw-bold mb-3">💬 Catatan</h5>
                  <div class="alert alert-light border border-2">
                    <?= htmlspecialchars($post_data['catatan']) ?>
                  </div>
                </div>
              <?php endif; ?>

            </div>
          </div>
        </div>

        <!-- RINGKASAN HARGA -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 sticky-lg-top" style="top: 70px; max-height: calc(100vh - 220px); overflow-y: auto; z-index: 10;">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-4">💰 Ringkasan Harga</h5>

              <table class="table table-sm table-borderless">
                <tbody>
                  <tr>
                    <td class="text-muted">Harga Bahan</td>
                    <td class="text-end">Rp <?= number_format($harga_bahan) ?> / pcs</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Jumlah Beli</td>
                    <td class="text-end"><?= $jumlah_beli ?> pcs</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Subtotal Bahan</td>
                    <td class="text-end">Rp <?= number_format($subtotal_bahan) ?></td>
                  </tr>
                  <?php if ($harga_desain > 0): ?>
                  <tr>
                    <td class="text-muted">Harga Desain</td>
                    <td class="text-end">Rp <?= number_format($harga_desain) ?> / pcs</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Subtotal Desain</td>
                    <td class="text-end">Rp <?= number_format($subtotal_desain) ?></td>
                  </tr>
                  <?php endif; ?>
                  <tr class="border-top border-secondary">
                    <td class="fw-bold">Total Harga</td>
                    <td class="text-end fw-bold text-success">Rp <?= number_format($total_harga) ?></td>
                  </tr>
                </tbody>
              </table>

              <!-- Data Pemesan -->
              <div class="mt-4 pt-4 border-top">
                <h6 class="fw-bold mb-3">📋 Data Pemesan</h6>
                <div class="small">
                  <p class="mb-2">
                    <strong>Nama:</strong><br>
                    <?= htmlspecialchars($post_data['nama'] ?? '-') ?>
                  </p>
                  <p class="mb-2">
                    <strong>No. HP:</strong><br>
                    <?= htmlspecialchars($post_data['hp'] ?? '-') ?>
                  </p>
                  <p class="mb-0">
                    <strong>Alamat:</strong><br>
                    <?= htmlspecialchars($post_data['alamat'] ?? '-') ?>
                  </p>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- TOMBOL AKSI -->
      <div class="row mt-5">
        <div class="col-12">
          <!-- FORM SUBMIT DENGAN FILE UPLOAD -->
          <form method="POST" enctype="multipart/form-data">

            <?php if (!empty($error_pembayaran)): ?>
              <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error_pembayaran) ?>
              </div>
            <?php endif; ?>

            <!-- JENIS PEMBAYARAN -->
            <div class="mb-4">
              <label class="form-label fw-bold">Jenis Pembayaran</label>
              <div class="row g-3">
                <div class="col-md-6">
                  <input
                    type="radio"
                    class="btn-check"
                    name="jenis_pembayaran"
                    id="jenis_dp"
                    value="dp"
                    <?= ($_POST['jenis_pembayaran'] ?? '') === 'dp' ? 'checked' : '' ?>
                    required>
                  <label class="btn btn-outline-warning text-start w-100 h-100 p-3" for="jenis_dp">
                    <span class="d-block fw-bold"><i class="bi bi-wallet2 me-2"></i>Bayar DP</span>
                    <small class="d-block mt-1">Bayar sebagian sekarang, sisanya dapat dilunasi kemudian.</small>
                  </label>
                </div>
                <div class="col-md-6">
                  <input
                    type="radio"
                    class="btn-check"
                    name="jenis_pembayaran"
                    id="jenis_lunas"
                    value="lunas"
                    <?= ($_POST['jenis_pembayaran'] ?? '') === 'lunas' ? 'checked' : '' ?>
                    required>
                  <label class="btn btn-outline-success text-start w-100 h-100 p-3" for="jenis_lunas">
                    <span class="d-block fw-bold"><i class="bi bi-check-circle me-2"></i>Bayar Lunas</span>
                    <small class="d-block mt-1">Bayar seluruh total pesanan sebesar Rp <?= number_format($total_harga) ?>.</small>
                  </label>
                </div>
              </div>
            </div>

            <!-- NOMINAL DP -->
            <div id="nominal_dp_container" class="mb-4 d-none">
              <label for="nominal_dp" class="form-label fw-bold">Nominal DP</label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input
                  type="number"
                  id="nominal_dp"
                  name="nominal_dp"
                  class="form-control rounded-end-3"
                  min="1"
                  max="<?= max(1, $total_harga - 1) ?>"
                  value="<?= intval($_POST['nominal_dp'] ?? $nominal_dp_default) ?>">
              </div>
              <div class="form-text">Nominal DP harus lebih kecil dari total harga Rp <?= number_format($total_harga) ?>.</div>
            </div>

            <!-- RINGKASAN PEMBAYARAN -->
            <div id="ringkasan_pembayaran" class="alert alert-light border border-2 mb-4 d-none">
              <div class="row g-3">
                <div class="col-md-4">
                  <div class="text-muted small">Jenis Pembayaran</div>
                  <div class="fw-bold" id="jenis_pembayaran_display">-</div>
                </div>
                <div class="col-md-4">
                  <div class="text-muted small">Bayar Sekarang</div>
                  <div class="fw-bold text-success" id="jumlah_pembayaran_display">Rp 0</div>
                </div>
                <div class="col-md-4">
                  <div class="text-muted small">Sisa Pembayaran</div>
                  <div class="fw-bold" id="sisa_pembayaran_display">Rp 0</div>
                </div>
              </div>
            </div>
            
            <!-- METODE PEMBAYARAN -->
            <div class="mb-4">
              <label class="form-label fw-bold">💳 Metode Pembayaran</label>
              <select id="metode_pembayaran" name="metode_pembayaran" class="form-select rounded-3" required>
                <option value="">-- Pilih Metode --</option>
                <option value="qris" <?= ($_POST['metode_pembayaran'] ?? '') === 'qris' ? 'selected' : '' ?>>QRIS</option>
                <option value="virtual_account" <?= ($_POST['metode_pembayaran'] ?? '') === 'virtual_account' ? 'selected' : '' ?>>Virtual Account (VA)</option>
                <option value="transfer" <?= ($_POST['metode_pembayaran'] ?? '') === 'transfer' ? 'selected' : '' ?>>Transfer Bank</option>
                <option value="cash" <?= ($_POST['metode_pembayaran'] ?? '') === 'cash' ? 'selected' : '' ?>>Pembayaran Langsung</option>
              </select>
            </div>

            <!-- TUJUAN PEMBAYARAN - QRIS -->
            <div id="tujuan_qris" class="payment-destination mb-4 d-none">
              <div class="alert alert-info border-2">
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
            </div>

            <!-- TUJUAN PEMBAYARAN - VA -->
            <div id="tujuan_va" class="payment-destination mb-4 d-none">
              <div class="alert alert-info border-2">
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
            </div>

            <!-- TUJUAN PEMBAYARAN - TRANSFER BANK -->
            <div id="tujuan_transfer" class="payment-destination mb-4 d-none">
              <div class="alert alert-info border-2">
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
            </div>

            <!-- TUJUAN PEMBAYARAN - CASH -->
            <div id="tujuan_cash" class="payment-destination mb-4 d-none">
              <div class="alert alert-info border-2">
                <h6 class="fw-bold mb-3">
                  <i class="bi bi-cash-coin me-2"></i>Tujuan Pembayaran: Pembayaran Langsung
                </h6>
                <p class="mb-0">
                  Pembayaran akan dilakukan secara langsung saat barang diterima atau sesuai dengan kesepakatan antara Anda dan admin.
                </p>
              </div>
            </div>

            <!-- UPLOAD BUKTI PEMBAYARAN -->
            <div class="mb-4">
              <label class="form-label fw-bold">📸 Upload Bukti Pembayaran (Opsional)</label>
              <div class="mb-2 text-muted small">
                <i class="bi bi-info-circle"></i> Unggah foto/screenshot bukti transfer atau pembayaran Anda
              </div>
              <input type="file" name="bukti_pembayaran" accept="image/*" class="form-control rounded-3">
            </div>

            <!-- TOMBOL AKSI -->
            <div class="d-flex gap-3 justify-content-center">
              <!-- TOMBOL BATAL -->
              <a href="pesanan.php" class="btn btn-outline-secondary rounded-pill px-5 py-2 fw-semibold">
                <i class="bi bi-x-circle me-2"></i>Batal
              </a>

              <!-- TOMBOL SETUJU -->
              <button type="submit" name="confirm_pesanan" class="btn btn-success rounded-pill px-5 py-2 fw-semibold">
                <i class="bi bi-check-circle me-2"></i>Setuju & Lanjutkan
              </button>
            </div>

          </form>
        </div>
      </div>

    </div>
  </section>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const metodeSelect = document.getElementById('metode_pembayaran');
    const destinationDivs = document.querySelectorAll('.payment-destination');
    const jenisPembayaran = document.querySelectorAll('input[name="jenis_pembayaran"]');
    const nominalDpContainer = document.getElementById('nominal_dp_container');
    const nominalDpInput = document.getElementById('nominal_dp');
    const ringkasanPembayaran = document.getElementById('ringkasan_pembayaran');
    const totalHarga = <?= intval($total_harga) ?>;
    const formatRupiah = value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value);

    function updatePaymentDestination() {
      const selectedValue = metodeSelect.value;

      // Sembunyikan semua tujuan pembayaran
      destinationDivs.forEach(div => div.classList.add('d-none'));

      // Tampilkan tujuan pembayaran yang dipilih
      if (selectedValue === 'qris') {
        document.getElementById('tujuan_qris').classList.remove('d-none');
      } else if (selectedValue === 'virtual_account') {
        document.getElementById('tujuan_va').classList.remove('d-none');
      } else if (selectedValue === 'transfer') {
        document.getElementById('tujuan_transfer').classList.remove('d-none');
      } else if (selectedValue === 'cash') {
        document.getElementById('tujuan_cash').classList.remove('d-none');
      }
    }

    function updatePaymentSummary() {
      const selected = document.querySelector('input[name="jenis_pembayaran"]:checked');

      if (!selected) {
        nominalDpContainer.classList.add('d-none');
        nominalDpInput.required = false;
        ringkasanPembayaran.classList.add('d-none');
        return;
      }

      const isDp = selected.value === 'dp';
      const jumlahBayar = isDp
        ? Math.max(0, parseInt(nominalDpInput.value, 10) || 0)
        : totalHarga;

      nominalDpContainer.classList.toggle('d-none', !isDp);
      nominalDpInput.required = isDp;
      ringkasanPembayaran.classList.remove('d-none');

      document.getElementById('jenis_pembayaran_display').textContent = isDp ? 'DP' : 'Lunas';
      document.getElementById('jumlah_pembayaran_display').textContent = formatRupiah(jumlahBayar);
      document.getElementById('sisa_pembayaran_display').textContent = formatRupiah(Math.max(0, totalHarga - jumlahBayar));
    }

    metodeSelect.addEventListener('change', updatePaymentDestination);
    jenisPembayaran.forEach(input => input.addEventListener('change', updatePaymentSummary));
    nominalDpInput.addEventListener('input', updatePaymentSummary);

    updatePaymentDestination();
    updatePaymentSummary();
  });
</script>

<?php
  include '../../assets/layout/users/footer.php';
?>
