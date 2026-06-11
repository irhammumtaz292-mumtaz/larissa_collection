<?php

  session_start();

  if (!isset($_SESSION['id_akun'])) {
    header('Location: ../../login.php');
    exit;
  }

  if (!isset($_SESSION['draft_pesanan'])) {
    header('Location: index.php');
    exit;
  }

  require_once '../../config/db/db.php';
  require_once '../../config/controller/controller.php';

  $title = 'Konfirmasi Pesanan';
  $halmut = './';
  $post_data = $_SESSION['draft_pesanan'];
  $design_type = $_SESSION['draft_design_type'] ?? null;
  $error_pesanan = '';

  if (isset($_POST['confirm_pesanan'])) {
    $id_pesanan = tambah_pesanan($post_data);

    if ($id_pesanan) {
      unset($_SESSION['draft_pesanan']);
      unset($_SESSION['draft_design_type']);
      unset($_SESSION['design_type']);
      unset($_SESSION['id_desain']);
      unset($_SESSION['id_desain_custom_uploaded']);

      $_SESSION['success'] = 'Pesanan berhasil dikirim. Status pesanan Anda pending sampai admin memberikan harga.';
      header('Location: riwayat_pesanan.php');
      exit;
    }

    $error_pesanan = 'Pesanan gagal dikirim. Silakan coba kembali.';
  }

  $id_produk = intval($post_data['id_produk'] ?? 1);
  $produk_data = select("SELECT nama_produk, deskripsi, gambar_produk FROM produk WHERE id_produk = $id_produk LIMIT 1");
  $produk = $produk_data[0] ?? [];
  $nama_produk = $produk['nama_produk'] ?? 'Produk';

  $id_bahan = intval($post_data['id_bahan'] ?? 0);
  $bahan_data = select("SELECT b.jenis_bahan, w.nama_warna FROM bahan b JOIN warna w ON b.id_warna = w.id_warna WHERE b.id_bahan = $id_bahan LIMIT 1");
  $bahan = $bahan_data[0] ?? [];

  $design_info = 'Belum dipilih';
  $design_image = '';
  $design_description = '';
  $custom_files = [];
  $custom_notes = '';

  if ($design_type === 'existing' && !empty($post_data['id_desain'])) {
    $id_desain = intval($post_data['id_desain']);
    $desain_data = select("SELECT nama_desain, gambar_desain, deskripsi FROM desain WHERE id_desain = $id_desain LIMIT 1");

    if (!empty($desain_data)) {
      $design_info = $desain_data[0]['nama_desain'];
      $design_image = $desain_data[0]['gambar_desain'] ?? '';
      $design_description = $desain_data[0]['deskripsi'] ?? '';
    }
  } elseif ($design_type === 'upload' && !empty($post_data['id_desain_custom'])) {
    $design_info = 'Design Custom';
    $id_desain_custom = intval($post_data['id_desain_custom']);
    $custom_data = select("SELECT files, catatan FROM desain_custom WHERE id_desain_custom = $id_desain_custom LIMIT 1");

    if (!empty($custom_data)) {
      $custom_files = json_decode($custom_data[0]['files'] ?? '', true) ?: [];
      $custom_notes = $custom_data[0]['catatan'] ?? '';
    }
  }

  $sizes = [
    'S' => intval($post_data['size_s'] ?? 0),
    'M' => intval($post_data['size_m'] ?? 0),
    'L' => intval($post_data['size_l'] ?? 0),
    'XL' => intval($post_data['size_xl'] ?? 0),
    'XXL' => intval($post_data['size_xxl'] ?? 0),
    'XXXL' => intval($post_data['size_xxxl'] ?? 0),
  ];
  $jumlah_beli = array_sum($sizes);

  include '../../assets/layout/users/header.php';

?>

<main>
  <section aria-label="Konfirmasi Pesanan" class="py-5">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="fw-bold mb-2">Konfirmasi Pesanan Anda</h2>
        <p class="text-muted mb-0">Periksa detail pesanan sebelum dikirim ke admin.</p>
      </div>

      <?php if (!empty($error_pesanan)): ?>
        <div class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error_pesanan) ?>
        </div>
      <?php endif; ?>

      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
              <div class="d-flex align-items-start gap-3 mb-4">
                <?php if (!empty($produk['gambar_produk'])): ?>
                  <img
                    src="../../assets/img/produk/<?= htmlspecialchars($produk['gambar_produk']) ?>"
                    alt="<?= htmlspecialchars($nama_produk) ?>"
                    class="rounded-3 border"
                    style="width: 96px; height: 96px; object-fit: cover;">
                <?php endif; ?>
                <div>
                  <p class="text-muted small mb-1">Produk</p>
                  <h4 class="fw-bold mb-1"><?= htmlspecialchars($nama_produk) ?></h4>
                  <?php if (!empty($produk['deskripsi'])): ?>
                    <p class="text-muted mb-0"><?= htmlspecialchars($produk['deskripsi']) ?></p>
                  <?php endif; ?>
                </div>
              </div>

              <hr>

              <div class="row g-4">
                <div class="col-md-6">
                  <p class="text-muted small mb-1">Bahan</p>
                  <p class="fw-bold mb-0"><?= htmlspecialchars($bahan['jenis_bahan'] ?? '-') ?></p>
                </div>
                <div class="col-md-6">
                  <p class="text-muted small mb-1">Warna</p>
                  <p class="fw-bold mb-0"><?= htmlspecialchars($bahan['nama_warna'] ?? '-') ?></p>
                </div>
              </div>

              <hr>

              <div class="mb-4">
                <h5 class="fw-bold mb-3">Rincian Ukuran</h5>
                <div class="table-responsive">
                  <table class="table table-sm table-borderless align-middle mb-0">
                    <tbody>
                      <?php foreach ($sizes as $size => $qty): ?>
                        <?php if ($qty > 0): ?>
                          <tr>
                            <td class="text-muted">Size <strong><?= htmlspecialchars($size) ?></strong></td>
                            <td class="text-end fw-bold"><?= intval($qty) ?> pcs</td>
                          </tr>
                        <?php endif; ?>
                      <?php endforeach; ?>
                      <tr class="border-top">
                        <td class="fw-bold">Total Jumlah</td>
                        <td class="text-end fw-bold"><?= intval($jumlah_beli) ?> pcs</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <hr>

              <div class="mb-4">
                <h5 class="fw-bold mb-3">Design</h5>
                <div class="alert alert-light border mb-3">
                  <strong><?= htmlspecialchars($design_info) ?></strong>
                  <?php if (!empty($design_description)): ?>
                    <p class="text-muted mb-0 mt-1"><?= htmlspecialchars($design_description) ?></p>
                  <?php endif; ?>
                </div>

                <?php if (!empty($design_image)): ?>
                  <img
                    src="../../assets/img/desain/<?= htmlspecialchars($design_image) ?>"
                    alt="<?= htmlspecialchars($design_info) ?>"
                    class="rounded-3 border"
                    style="max-width: 220px; width: 100%; height: 160px; object-fit: cover;">
                <?php endif; ?>

                <?php if (!empty($custom_files)): ?>
                  <?php
                    $custom_images = [
                      'depan' => 'Tampak Depan',
                      'belakang' => 'Tampak Belakang',
                      'kanan' => 'Tampak Kanan',
                      'kiri' => 'Tampak Kiri',
                    ];
                  ?>
                  <div class="row g-3 mt-1">
                    <?php foreach ($custom_images as $key => $label): ?>
                      <?php if (!empty($custom_files[$key])): ?>
                        <div class="col-sm-6 col-md-3">
                          <div class="border rounded-3 overflow-hidden bg-light">
                            <img
                              src="../../assets/img/desain_custom/<?= htmlspecialchars($custom_files[$key]) ?>"
                              alt="<?= htmlspecialchars($label) ?>"
                              class="w-100"
                              style="height: 120px; object-fit: cover;">
                            <div class="small fw-semibold p-2"><?= htmlspecialchars($label) ?></div>
                          </div>
                        </div>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </div>

                  <?php if (!empty($custom_files['logo']) && is_array($custom_files['logo'])): ?>
                    <div class="mt-3">
                      <p class="fw-semibold mb-2">Logo</p>
                      <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($custom_files['logo'] as $logo): ?>
                          <img
                            src="../../assets/img/desain_custom/<?= htmlspecialchars($logo) ?>"
                            alt="Logo"
                            class="rounded-3 border bg-light"
                            style="width: 88px; height: 88px; object-fit: contain;">
                        <?php endforeach; ?>
                      </div>
                    </div>
                  <?php endif; ?>

                  <?php if (!empty($custom_notes)): ?>
                    <div class="alert alert-light border mt-3 mb-0">
                      <?= nl2br(htmlspecialchars($custom_notes)) ?>
                    </div>
                  <?php endif; ?>
                <?php endif; ?>
              </div>

              <?php if (!empty($post_data['catatan'] ?? '')): ?>
                <hr>
                <div>
                  <h5 class="fw-bold mb-3">Catatan Tambahan</h5>
                  <div class="alert alert-light border mb-0">
                    <?= nl2br(htmlspecialchars($post_data['catatan'])) ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 sticky-lg-top" style="top: 80px;">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-3">Data Pemesan</h5>
              <div class="mb-3">
                <p class="text-muted small mb-1">Nama</p>
                <p class="fw-bold mb-0"><?= htmlspecialchars($post_data['nama'] ?? '-') ?></p>
              </div>
              <div class="mb-3">
                <p class="text-muted small mb-1">No. HP</p>
                <p class="fw-bold mb-0"><?= htmlspecialchars($post_data['hp'] ?? '-') ?></p>
              </div>
              <div class="mb-4">
                <p class="text-muted small mb-1">Alamat</p>
                <p class="fw-bold mb-0"><?= nl2br(htmlspecialchars($post_data['alamat'] ?? '-')) ?></p>
              </div>

              <div class="alert alert-info border-0">
                <i class="bi bi-info-circle me-2"></i>
                Harga belum ditampilkan. Admin akan menentukan harga setelah pesanan dikirim.
              </div>

              <form method="POST" class="d-grid gap-2">
                <a href="pesanan.php?id_produk=<?= intval($id_produk) ?>" class="btn btn-outline-secondary rounded-pill py-2 fw-semibold">
                  <i class="bi bi-pencil-square me-2"></i>Ubah Pesanan
                </a>
                <button type="submit" name="confirm_pesanan" class="btn btn-success rounded-pill py-2 fw-semibold">
                  <i class="bi bi-send-check me-2"></i>Kirim Pesanan
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php
  include '../../assets/layout/users/footer.php';
?>
