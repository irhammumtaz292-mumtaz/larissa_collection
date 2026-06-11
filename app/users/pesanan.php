<?php

  // Mulai session: diperlukan untuk mengakses data user yang login
  session_start();

  // Metadata halaman (judul dan rute relatif) digunakan di header/layout
  $title = 'Pemesanan Konveksi';
  $halmut = './';
  $halpem = 'pesanan.php';

  // Ambil data user dari session jika tersedia dan lakukan sanitasi sebelum ditampilkan
  // Nilai kosong digunakan sebagai fallback apabila session belum menyimpan data
  $user_nama = isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : '';
  $user_hp = isset($_SESSION['no_hp']) ? htmlspecialchars($_SESSION['no_hp']) : '';
  $user_alamat = isset($_SESSION['alamat']) ? htmlspecialchars($_SESSION['alamat']) : '';

  // Sertakan header bersama (navbar, stylesheet, dll.)
  include '../../assets/layout/users/header.php';

  // Jika salah satu data pemesan kosong di session, coba ambil dari tabel customer melalui id_akun
  // Fungsi `select()` adalah helper yang mengembalikan array hasil query
  if (empty($user_nama) || empty($user_hp) || empty($user_alamat)) {
      if (isset($_SESSION['id_akun'])) {
          $id_akun = intval($_SESSION['id_akun']);
          // Gabungkan tabel akun -> customer untuk mendapatkan info kontak lengkap
          $user_data = select("SELECT c.nama, c.no_hp, c.alamat FROM akun a JOIN customer c ON a.id_customer = c.id_customer WHERE a.id_akun = $id_akun LIMIT 1");
          if (!empty($user_data)) {
              // Hanya isi nilai yang masih kosong (biarkan nilai session tetap jika sudah ada)
              $user_nama = $user_nama ?: htmlspecialchars($user_data[0]['nama']);
              $user_hp = $user_hp ?: htmlspecialchars($user_data[0]['no_hp']);
              $user_alamat = $user_alamat ?: htmlspecialchars($user_data[0]['alamat']);
          }
      }
  }

  // Ambil daftar 'bahan' dari database dengan JOIN warna untuk opsi select pada form
  // Diurutkan berdasarkan jenis_bahan, kemudian warna agar opgroup teorganisir
  $data_bahan = select("
    SELECT b.*, w.nama_warna 
    FROM bahan b
    JOIN warna w ON b.id_warna = w.id_warna 
    ORDER BY b.jenis_bahan ASC, w.nama_warna ASC
  ");

  // Tentukan produk berdasarkan parameter URL 'produk', dengan fallback 'Kaos'
  $nama_produk = htmlspecialchars($_GET['produk'] ?? 'Kaos');
  $data_produk = select("SELECT id_produk FROM produk WHERE nama_produk = '$nama_produk' LIMIT 1");
  $id_produk = !empty($data_produk) ? $data_produk[0]['id_produk'] : 1;

  // Ambil desain yang tersedia untuk produk ini (digunakan di modal pilih desain)
  $data_desain = select("SELECT * FROM desain WHERE id_produk = $id_produk ORDER BY nama_desain ASC");

  // ===== POST HANDLING =====
  // Di bawah ini terdapat beberapa handler untuk form POST:
  // 1) Upload design dari modal (tombol 'Simpan' di modal upload)
  // 2) Pilih design dari daftar (modal pilih desain)
  // 3) Submit pesanan (menghasilkan record pesanan + transaksi)

  // 1) Upload Design dari Modal
  // Memanggil helper tambah_desain_custom() yang menangani upload file dan menyimpan record desain custom
  if (isset($_POST['tambah'])) {
    $id_desain_custom = tambah_desain_custom($_POST, $_FILES);
    if ($id_desain_custom) {
      // Simpan id desain hasil upload di session agar dapat dipakai saat submit pesanan
      $_SESSION['id_desain_custom_uploaded'] = $id_desain_custom;
      $_SESSION['design_type'] = 'upload';
      echo '<div class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: auto; max-width: 400px;">
              <i class="bi bi-check-circle me-2"></i>Design berhasil disimpan!
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
    } else {
      unset($_SESSION['id_desain_custom_uploaded'], $_SESSION['design_type']);
      echo '<div class="alert alert-danger alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: auto; max-width: 400px;">
              <i class="bi bi-exclamation-circle me-2"></i>Design gagal disimpan. Pastikan logo berupa JPG, JPEG, JFIF, atau PNG dengan ukuran maksimal 2 MB.
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
    }
  }

  // 2) Pilih Design dari Database
  // Menangani pemilihan desain dari modal 'Pilih Design' — menyimpan id desain di session
  if (isset($_POST['pilih_design']) && !empty($_POST['pilih_design'])) {
    $_SESSION['id_desain'] = intval($_POST['pilih_design']);
    $_SESSION['design_type'] = 'existing';
    // Tampilkan notifikasi sukses singkat
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: auto; max-width: 400px;">
            <i class="bi bi-check-circle me-2"></i>Design berhasil dipilih!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
  }

  // 3) Submit Pesanan
  // Simpan draft terlebih dahulu agar customer bisa mengecek detail di halaman konfirmasi.
  if (isset($_POST['submit_pesanan'])) {
    // Pastikan user sudah memilih atau mengupload design sebelumnya
    if (!isset($_SESSION['design_type'])) {
      // Simpan pesan error ke session dan tampilkan alert
      $_SESSION['error'] = 'Silakan pilih atau upload design terlebih dahulu!';
      echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-circle me-2"></i>' . $_SESSION['error'] . '
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
    } else {
      // Clone data POST agar tidak merusak original; tambahkan id_produk
      $post_data = $_POST;
      $post_data['id_produk'] = $id_produk;
      
      // Jika user memilih desain existing, sertakan id_desain
      if ($_SESSION['design_type'] === 'existing') {
        $post_data['id_desain'] = $_SESSION['id_desain'];
      }
      
      // Jika user mengupload desain, sertakan id_desain_custom yang sudah diupload
      if ($_SESSION['design_type'] === 'upload') {
        $post_data['id_desain_custom'] = $_SESSION['id_desain_custom_uploaded'];
      }

      $_SESSION['draft_pesanan'] = $post_data;
      $_SESSION['draft_design_type'] = $_SESSION['design_type'];

      echo "<script>window.location.href='konfirmasi_pesanan.php';</script>";
      exit;
    }
  }

?>



<main class="">
  <section aria-label="Judul" class="mb-0" style="padding:10px 0;">

    <div class="p-2 mb-0 bg-body rounded-4 shadow-sm mx-auto" style="max-width: 760px; margin-bottom: 5px;">
      <div class="text-center">
        <h3 class="fw-semibold mb-1">Pesan <?= htmlspecialchars($nama_produk) ?> Sekarang</h3>
        <p class="text-muted mb-0">Melayani kaos, hoodie, jaket, dan kemeja dengan kualitas terbaik.</p>
      </div>
    </div>

  </section>

  <section class="py-0" style="padding:5px 0 !important;" aria-label="Form Pemesanan">
      <div class="container">
          <div class="row justify-content-center g-4">

        <!-- FORM -->
        <div class="col-xl-8 col-lg-9">
          <div class="card border-0 shadow-lg rounded-5">
            <div class="card-body p-5">

              <!-- HEADER -->
              <div class="mb-4">
                <h4 class="fw-bold mb-1">
                  <i class="bi bi-clipboard2-check me-2"></i>
                  Form Pemesanan
                </h4>
                <p class="text-muted small mb-0">
                  Lengkapi data berikut untuk melakukan pemesanan.
                </p>
              </div>

              <form method="POST" enctype="multipart/form-data">

                <!-- DATA PEMESAN -->
                <div class="mb-4">
                  <h6 class="fw-semibold mb-3">
                    <i class="bi bi-person-circle me-2"></i>Data Pemesan
                  </h6>

                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label fw-medium">Nama Lengkap</label>
                      <input type="text" name="nama" class="form-control rounded-3" placeholder="Masukkan nama" value="<?= $user_nama ?>" required>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-medium">No HP</label>
                      <input type="number" name="hp" class="form-control rounded-3" placeholder="08xxxxxxxxxx" value="<?= $user_hp ?>" required>
                    </div>

                    <div class="col-12">
                      <label class="form-label fw-medium">Alamat</label>
                      <textarea name="alamat" class="form-control rounded-3" rows="3" placeholder="Masukkan alamat lengkap"><?= $user_alamat ?></textarea>
                    </div>
                  </div>
                </div>

                <!-- DESIGN -->
                <div class="mb-4">
                  <h6 class="fw-semibold mb-3">
                    <i class="bi bi-palette me-2"></i>Design
                  </h6>

                  <div class="row g-3">

                    <div class="col-md-6">
                      <button 
                        type="button"
                        class="btn btn-outline-warning w-100 rounded-pill py-3 fw-semibold"
                        data-bs-toggle="modal"
                        data-bs-target="#modalUpload">

                        <i class="bi bi-upload fs-5 d-block mb-2"></i>
                        Upload Design
                      </button>
                    </div>

                    <div class="col-md-6">
                      <button 
                        type="button"
                        class="btn btn-outline-secondary w-100 rounded-pill py-3 fw-semibold"
                        data-bs-toggle="modal"
                        data-bs-target="#modalPilih">

                        <i class="bi bi-images fs-5 d-block mb-2"></i>
                        Pilih Design
                      </button>
                    </div>

                  </div>
                </div>

                <!-- DETAIL PESANAN -->
                <div class="mb-4">
                  <h6 class="fw-semibold mb-3">
                    <i class="bi bi-bag-check me-2"></i>Detail Pesanan
                  </h6>

                  <div class="row g-3">

                    <div class="col-md-6">
                      <label class="form-label fw-medium">Bahan & Warna</label>
                      <select name="id_bahan" class="form-select rounded-3" required>
                        <option value="" selected disabled>Pilih Bahan</option>
                        <?php
                          $prev_jenis = '';
                          $close_group = false;
                          foreach($data_bahan as $bhn):
                            // Jika jenis bahan berbeda, tutup optgroup sebelumnya dan buka yang baru
                            if ($prev_jenis !== $bhn['jenis_bahan']) {
                              if ($close_group) echo '</optgroup>';
                              echo '<optgroup label="' . htmlspecialchars($bhn['jenis_bahan']) . '">';
                              $close_group = true;
                              $prev_jenis = $bhn['jenis_bahan'];
                            }
                        ?>
                          <option value="<?= $bhn['id_bahan'] ?>">
                            <?= htmlspecialchars($bhn['nama_warna']) ?>
                          </option>
                        <?php endforeach; if ($close_group) echo '</optgroup>'; ?>
                      </select>
                      <small class="text-muted d-block mt-2">Memilih bahan akan menampilkan pilihan warna tersedia</small>
                    </div>

                  </div>
                </div>

                 <div class="card border-0 shadow-sm p-4 rounded-4">

                        <!-- JUDUL -->
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-rulers me-2"></i>
                            <h5 class="fw-bold mb-0">Rincian Ukuran</h5>
                        </div>

                        <!-- SUBTITLE -->
                        <p class="text-muted small mb-4">
                            Masukkan jumlah per ukuran (total min. 24 pcs)
                        </p>

                        <!-- FORM UKURAN -->
                        <div class="row g-3">

                            <!-- S -->
                            <div class="col">
                                <label class="form-label fw-semibold">S</label>
                                <input type="number" name="size_s" class="form-control text-center rounded-4" value="0" min="0">
                            </div>

                            <!-- M -->
                            <div class="col">
                                <label class="form-label fw-semibold">M</label>
                                <input type="number" name="size_m" class="form-control text-center rounded-4" value="0" min="0">
                            </div>

                            <!-- L -->
                            <div class="col">
                                <label class="form-label fw-semibold">L</label>
                                <input type="number" name="size_l" class="form-control text-center rounded-4" value="0" min="0">
                            </div>

                            <!-- XL -->
                            <div class="col">
                                <label class="form-label fw-semibold">XL</label>
                                <input type="number" name="size_xl" class="form-control text-center rounded-4" value="0" min="0">
                            </div>

                            <!-- XXL -->
                            <div class="col">
                                <label class="form-label fw-semibold">XXL</label>
                                <input type="number" name="size_xxl" class="form-control text-center rounded-4" value="0" min="0">
                            </div>

                            <!-- XXXL -->
                            <div class="col">
                                <label class="form-label fw-semibold">XXXL</label>
                                <input type="number" name="size_xxxl" class="form-control text-center rounded-4" value="0" min="0">
                            </div>

                        </div>

                    </div>

                <input type="hidden" name="id_produk" value="<?= $id_produk ?>">

                <!-- CATATAN -->
                <div class="mb-4">
                  <label class="form-label fw-medium">Catatan Tambahan</label>
                  <textarea 
                    name="catatan"
                    class="form-control rounded-3" 
                    rows="3"
                    placeholder="Contoh: warna hitam, sablon depan belakang, dll"></textarea>
                </div>

                <!-- BUTTON -->
                <button type="submit" name="submit_pesanan" class="btn btn-warning w-100 py-3 rounded-pill fw-semibold text-dark">
                  <i class="bi bi-cart-check me-2"></i>
                  Konfirmasi Pesanan
                </button>

              </form>

            </div>
          </div>
        </div>

      </div>

  </section>

  <section aria-label="Modal">
    
    <!-- Modal Upload Desain -->
        <div class="modal fade" id="modalUpload" tabindex="-1" aria-labelledby="ModalUpload" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header bg-success">
                        <h5 class="modal-title" id="modalUpload">Upload Design</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"/>
                    </div>

                  <div class="modal-body">

                      <form action="" method="post" enctype="multipart/form-data">

                          <!-- Tampak Depan -->
                          <div class="card mb-3 border-0 shadow-sm">
                              <div class="card-body">
                                  <h6 class="mb-3">
                                      <i class="bi bi-image me-2"></i>Tampak Depan
                                  </h6>

                                  <div class="mb-2">
                                      <label class="form-label">Upload Gambar</label>
                                      <input type="file" 
                                            name="tampak_depan" 
                                            class="form-control" 
                                            accept="image/*" 
                                            required>
                                  </div>

                                  <div class="form-floating">
                                      <textarea name="catatan_depan"
                                                class="form-control"
                                                placeholder="Catatan Tampak Depan"
                                                style="height: 100px"></textarea>
                                      <label>Catatan Tampak Depan</label>
                                  </div>
                              </div>
                          </div>

                          <!-- Tampak Belakang -->
                          <div class="card mb-3 border-0 shadow-sm">
                              <div class="card-body">
                                  <h6 class="mb-3">
                                      <i class="bi bi-image me-2"></i>Tampak Belakang
                                  </h6>

                                  <div class="mb-2">
                                      <label class="form-label">Upload Gambar</label>
                                      <input type="file" 
                                            name="tampak_belakang" 
                                            class="form-control" 
                                            accept="image/*" 
                                            required>
                                  </div>

                                  <div class="form-floating">
                                      <textarea name="catatan_belakang"
                                                class="form-control"
                                                placeholder="Catatan Tampak Belakang"
                                                style="height: 100px"></textarea>
                                      <label>Catatan Tampak Belakang</label>
                                  </div>
                              </div>
                          </div>

                          <!-- Tampak Kanan -->
                          <div class="card mb-3 border-0 shadow-sm">
                              <div class="card-body">
                                  <h6 class="mb-3">
                                      <i class="bi bi-image me-2"></i>Tampak Kanan
                                  </h6>

                                  <div class="mb-2">
                                      <label class="form-label">Upload Gambar</label>
                                      <input type="file" 
                                            name="tampak_kanan" 
                                            class="form-control" 
                                            accept="image/*" 
                                            required>
                                  </div>

                                  <div class="form-floating">
                                      <textarea name="catatan_kanan"
                                                class="form-control"
                                                placeholder="Catatan Tampak Kanan"
                                                style="height: 100px"></textarea>
                                      <label>Catatan Tampak Kanan</label>
                                  </div>
                              </div>
                          </div>

                          <!-- Tampak Kiri -->
                          <div class="card mb-3 border-0 shadow-sm">
                              <div class="card-body">
                                  <h6 class="mb-3">
                                      <i class="bi bi-image me-2"></i>Tampak Kiri
                                  </h6>

                                  <div class="mb-2">
                                      <label class="form-label">Upload Gambar</label>
                                      <input type="file" 
                                            name="tampak_kiri" 
                                            class="form-control" 
                                            accept="image/*" 
                                            required>
                                  </div>

                                  <div class="form-floating">
                                      <textarea name="catatan_kiri"
                                                class="form-control"
                                                placeholder="Catatan Tampak Kiri"
                                                style="height: 100px"></textarea>
                                      <label>Catatan Tampak Kiri</label>
                                  </div>
                              </div>
                          </div>

                          <!-- Logos -->
                          <div class="card mb-3 border-0 shadow-sm">
                              <div class="card-body">

                                  <h6 class="mb-3">
                                      <i class="bi bi-image me-2"></i>Logo
                                  </h6>

                                  <div class="mb-2">
                                      <label class="form-label">Upload Gambar</label>

                                      <input type="file"
                                            name="logo[]"
                                            class="form-control"
                                            accept="image/*"
                                            multiple
                                            required>
                                  </div>

                                  <div class="form-floating">
                                      <textarea name="catatan_logo"
                                                class="form-control"
                                                placeholder="Catatan Logo"
                                                style="height: 100px"></textarea>

                                      <label>Catatan Logo</label>
                                  </div>

                              </div>
                          </div>

                  </div>

                  <div class="modal-footer">
                      <button type="submit" name="tambah" class="btn btn-success">
                          <i class="bi bi-floppy me-1"></i>
                          Simpan
                      </button>
                      </form>
                  </div>
                    
                </div>
            </div>
        </div>
    <!-- /Modal Upload Desain -->

    <!-- Modal Pilih Design -->
    <div class="modal fade" id="modalPilih" tabindex="-1" aria-labelledby="ModalPilih" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="ModalPilih">Pilih Design untuk <?= htmlspecialchars($nama_produk) ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form method="post">
                        <div class="row g-3">
                            <?php 
                              if (!empty($data_desain)):
                                foreach($data_desain as $des): 
                            ?>
                            <div class="col-md-6">
                                <div class="card border h-100 cursor-pointer design-card" data-id="<?= $des['id_desain'] ?>">
                                    <div style="height: 150px; overflow: hidden; background: #f0f0f0;">
                                        <img src="../../assets/img/desain/<?= htmlspecialchars($des['gambar_desain']) ?>" 
                                             alt="<?= htmlspecialchars($des['nama_desain']) ?>" 
                                             class="w-100 h-100 object-fit-cover">
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title"><?= htmlspecialchars($des['nama_desain']) ?></h6>
                                        <p class="text-muted small mb-2"><?= htmlspecialchars(substr($des['deskripsi'], 0, 50)) ?></p>
                                        <div class="form-check">
                                            <input class="form-check-input design-radio" type="radio" name="pilih_design" value="<?= $des['id_desain'] ?>" id="design<?= $des['id_desain'] ?>">
                                            <label class="form-check-label" for="design<?= $des['id_desain'] ?>">
                                                Pilih Design Ini
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                endforeach;
                              else:
                                echo '<div class="alert alert-info w-100">Belum ada design yang tersedia</div>';
                              endif;
                            ?>
                        </div>

                        <div class="modal-footer mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>
                                Pilih Design
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <!-- /Modal Pilih Design -->

  </section>

</main>

<?php

  include '../../assets/layout/users/footer.php';

?>
