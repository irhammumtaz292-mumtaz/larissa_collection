<?php

session_start();

$laman = 'Bahan';
$fileLaman = 'admin_bahan.php';

include '../../assets/layout/admin/header.php';

// Warna
// Data Warna
if (isset($_POST['cari'])) {
    $kata_cari = htmlspecialchars(strip_tags($_POST['kata_cari']));
    $data_warna = select("SELECT * FROM warna
            WHERE CONCAT(nama_warna) LIKE '%$kata_cari%' ORDER BY nama_warna ASC");
} else {
    $data_warna = select("SELECT * FROM warna
           ORDER BY nama_warna ASC");
}

// jika tombol tambah di tekan jalankan script berikut
if (isset($_POST['tambah'])) {
    $result = tambah_akun($_POST) > 0;

    $popup = true;
    $statusPopup = $result ? 'Berhasil' : 'Gagal';
    $warnaPopup = $result ? 'success' : 'danger';
    $iconPopup = $result ? 'check2-circle' : 'x-circle';
    $popupEksekusi = 'Ditambahkan';
}

// jika tombol ubah di tekan jalankan script berikut
if (isset($_POST['ubah'])) {
    $result = ubah_akun($_POST) > 0;

    $popup = true;
    $statusPopup = $result ? 'Berhasil' : 'Gagal';
    $warnaPopup = $result ? 'success' : 'danger';
    $iconPopup = $result ? 'check2-circle' : 'x-circle';
    $popupEksekusi = 'Diubah';
}

// jika tombol hapus di tekan jalankan script berikut
if (isset($_POST['hapus'])) {
    $result = hapus_akun($_POST) > 0;

    $popup = true;
    $statusPopup = $result ? 'Berhasil' : 'Gagal';
    $warnaPopup = $result ? 'success' : 'danger';
    $iconPopup = $result ? 'check2-circle' : 'x-circle';
    $popupEksekusi = 'Dihapus';
}
// .Warna

// Bahan
// Data Akun
if (isset($_POST['cari'])) {
    $kata_cari = htmlspecialchars(strip_tags($_POST['kata_cari']));
    $data_bahan = select("SELECT bahan.*, warna.* FROM bahan
            JOIN warna ON bahan.id_warna = warna.id_warna
            WHERE CONCAT(jenis_bahan, nama_warna) LIKE '%$kata_cari%' ORDER BY jenis_bahan ASC, nama_warna ASC");
} else {
    $data_bahan = select("SELECT bahan.*, warna.* FROM bahan
            JOIN warna ON bahan.id_warna = warna.id_warna ORDER BY jenis_bahan ASC, nama_warna ASC");
}

// jika tombol tambah di tekan jalankan script berikut
if (isset($_POST['tambahBahan'])) {
    $result = tambah_bahan($_POST) > 0;

    $popup = true;
    $statusPopup = $result ? 'Berhasil' : 'Gagal';
    $warnaPopup = $result ? 'success' : 'danger';
    $iconPopup = $result ? 'check2-circle' : 'x-circle';
    $popupEksekusi = 'Ditambahkan';
}

// jika tombol ubah di tekan jalankan script berikut
if (isset($_POST['ubahBahan'])) {
    $result = ubah_bahan($_POST) > 0;

    $popup = true;
    $statusPopup = $result ? 'Berhasil' : 'Gagal';
    $warnaPopup = $result ? 'success' : 'danger';
    $iconPopup = $result ? 'check2-circle' : 'x-circle';
    $popupEksekusi = 'Diubah';
}

// jika tombol hapus di tekan jalankan script berikut
if (isset($_POST['hapusBahan'])) {
    $result = hapus_bahan($_POST) > 0;

    $popup = true;
    $statusPopup = $result ? 'Berhasil' : 'Gagal';
    $warnaPopup = $result ? 'success' : 'danger';
    $iconPopup = $result ? 'check2-circle' : 'x-circle';
    $popupEksekusi = 'Dihapus';
}
// .Bahan

?>

<!-- Dashboard Main -->
<main class="overflow-auto" style="flex:1;">

    <!-- Popup -->
    <?php require_once '../popup.php'; ?>
    <!-- .Popup -->

    <!-- View Data Bahan -->
    <section class="mb-3 mb-md-4" aria-label="Bahan View">

        <!-- Desktop View -->
        <div class="d-none d-md-block">
            <div class="row justify-content-center">
                <div class="col-sm-6 col-md-8 col-lg-12">
                    <div class="card">

                        <!-- .card-header -->
                        <div class="card-header">

                            <div class="card-wrap d-flex justify-content-between align-items-center">
                                <h3 class="card-title">Bahan</h3>

                                <form class="form" action="" method="post">
                                    <div class="input-group">
                                        <input type="search" class="form-control me-3" name="kata_cari" placeholder="Cari..." aria-label="Search" value="<?php if (isset($_POST['cari'])) {
                                                                                                                                                                echo $_POST['kata_cari'];
                                                                                                                                                            } ?>">
                                        <button class="btn btn-outline-info me-1" type="submit" name="cari"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                            </div>

                            <button type="button" class="btn btn-primary btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#modalTambahBahan">
                                <i class="bi bi-person-plus me-1"></i>
                                Tambah
                            </button>


                        </div>
                        <!-- /.card-header -->

                        <!-- .card-body -->
                        <div class="card-body overflow-auto" style="max-height: 400px;">

                            <div class="table-responsive">

                                <table id="table" class="table table-sm table-bordered border-dark table-hover">
                                    <thead class="text-center">
                                        <tr>
                                            <th class="bg-success">#</th>
                                            <th class="bg-success">Jenis Bahan</th>
                                            <th class="bg-success">Warna</th>
                                            <th class="bg-success">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php $no = 1; ?>
                                        <?php foreach ($data_bahan as $bahan) : ?>
                                            <tr style="height: 10px;">
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td><?= $bahan['jenis_bahan'] ?></td>
                                                <td><?= $bahan['nama_warna'] ?></td>
                                                <td class="text-center">

                                                    <button type="button" class="btn btn-sm btn-outline-primary mb-1" data-bs-toggle="modal" data-bs-target="#modalUbahBahan<?= $bahan['id_bahan'] ?>">
                                                        <i class="bi bi-pen"></i> Ubah
                                                    </button>

                                                    <button type="button" class="btn btn-sm btn-outline-danger mb-1" data-bs-toggle="modal" data-bs-target="#modalHapusBahan<?= $bahan['id_bahan'] ?>">
                                                        <i class="bi bi-trash"></i> Hapus
                                                    </button>

                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                            </div>

                        </div>
                        <!-- /.card-body -->

                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile View -->
        <div class="d-block d-md-none">

            <!-- ACTION BUTTON -->
            <div class="card mb-3 shadow-sm border-0 rounded-4 overflow-hidden">

                <div class="card-header py-2">
                    <div class="card-wrap d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Bahan</h3>

                        <form class="form" action="" method="post">
                            <div class="input-group">
                                <input type="search" class="form-control me-3" name="kata_cari" placeholder="Cari..." aria-label="Search" value="<?php if (isset($_POST['cari'])) {
                                                                                                                                                        echo $_POST['kata_cari'];
                                                                                                                                                    } ?>">
                                <button class="btn btn-outline-info me-1" type="submit" name="cari"><i class="bi bi-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body p-2">

                    <!-- Tombol utama -->
                    <button type="button"
                        class="btn btn-sm btn-primary w-100 rounded-pill fw-semibold shadow-sm mb-2"
                        data-bs-toggle="modal"
                        data-bs-target="#modalTambahBahan">
                        <i class="bi bi-person-plus me-1"></i>
                        Tambah Bahan
                    </button>



                </div>
            </div>

            <!-- LIST DATA -->
            <div class="row g-2">
                <?php foreach ($data_bahan as $bahan) : ?>
                    <div class="col-6">
                        <div class="card mb-3 shadow-sm border-0 rounded-4 overflow-hidden">

                            <!-- Header -->
                            <div class="card-header bg-info d-flex justify-content-between align-items-center py-2">
                                <div class="me-2 overflow-hidden">
                                    <h6 class="mb-0 fw-semibold text-truncate"><?= $bahan['jenis_bahan'] ?></h6>
                                    <small class="text-muted text-truncate d-block"><?= $bahan['nama_warna'] ?></small>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="card-body py-2 bg-light">
                                <div class="row g-2 small">

                                    <div class="col-12">
                                        <span class="text-muted fw-medium">Warna</span>
                                        <div><?= $bahan['nama_warna'] ?></div>
                                    </div>

                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="card-footer border-0 pt-0 pb-2 bg-light">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary w-100 rounded-pill"
                                        data-bs-toggle="modal" data-bs-target="#modalUbahBahan<?= $bahan['id_bahan'] ?>">
                                        <i class="bi bi-pen me-1"></i> Ubah
                                    </button>

                                    <button class="btn btn-sm btn-outline-danger w-100 rounded-pill"
                                        data-bs-toggle="modal" data-bs-target="#modalHapusBahan<?= $bahan['id_bahan'] ?>">
                                        <i class="bi bi-trash me-1"></i> Hapus
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

    </section>
    <!-- .View Data Bahan -->

    <!-- Modal Bahan -->
    <section class="modal-bahan">

        <!-- Modal Tambah Bahan -->
        <div class="modal fade" id="modalTambahBahan" tabindex="-1" aria-labelledby="modalTambahBahan" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header bg-success">
                        <h5 class="modal-title" id="modalTambahBahan">Tambah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" />
                    </div>

                    <div class="modal-body">

                        <form action="" method="post" enctype="multipart/form-data">

                            <div class="form-floating mb-2">
                                <input type="text" name="jenis_bahan" id="floatingInput" class="form-control" minlength="5" placeholder="Jenis Bahan" required>
                                <label for="floatingInput">Jenis Bahan</label>
                            </div>

                            <div class="form-group mb-2">
                                <label for="nama_warna">Warna</label>
                                <select name="id_warna" id="nama_warna" class="form-control" minlength="5" required>
                                    <option value="" disabled selected>-- Pilih --</option>
                                    <?php foreach ($data_warna as $warna) : ?>
                                        <option value="<?= $warna['id_warna'] ?>"><?= $warna['nama_warna'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="tambahBahan" class="btn btn-success"><i class="bi bi-floppy me-1"></i>
                            Simpan</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
        <!-- /Modal Tambah Bahan -->

        <!-- Modal Ubah Bahan -->
        <?php foreach ($data_bahan as $bahan) : ?>
            <div class="modal fade" id="modalUbahBahan<?= $bahan['id_bahan'] ?>" tabindex="-1" aria-labelledby="modalUbahBahan" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">

                        <div class="modal-header bg-primary">
                            <h5 class="modal-title" id="modalUbahBahan">Ubah Bahan Dengan :
                                <ul>
                                    <li>Jenis Bahan : <?= $bahan['jenis_bahan']; ?></li>
                                    <li>Warna : <?= $bahan['nama_warna'] ?></li>
                                </ul>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" />
                        </div>

                        <div class="modal-body">

                            <form action="" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="id_bahan" value="<?= $bahan['id_bahan'] ?>">

                                <div class="form-floating mb-3">
                                    <input type="text" name="jenis_bahan" id="floatingInput" class="form-control" minlength="5" placeholder="Jenis Bahan" value="<?= $bahan['jenis_bahan'] ?>" required>
                                    <label for="floatingInput">Jenis Bahan</label>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="nama_warna">Warna</label>
                                    <select name="id_warna" id="nama_warna" class="form-control" required>
                                        <?php foreach ($data_warna as $warna) : ?>
                                            <option value="<?= $warna['id_warna'] ?>" <?= ($bahan['id_warna'] == $warna['id_warna']) ? 'selected' : '' ?>><?= $warna['nama_warna'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                        </div>

                        <div class="modal-footer">
                            <button type="submit" name="ubahBahan" class="btn btn-primary"><i class="bi bi-floppy me-1"></i> Simpan</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <!-- /Modal Ubah Bahan -->

        <!-- Modal Hapus Bahan -->
        <?php foreach ($data_bahan as $bahan) : ?>
            <div class="modal fade" id="modalHapusBahan<?= $bahan['id_bahan'] ?>" tabindex="-1" aria-labelledby="modalHapusBahan" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <div class="modal-header bg-danger">
                            <h5 class="modal-title" id="modalHapusBahan">Hapus <?= $laman; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" />
                        </div>

                        <div class="modal-body">
                            <form action="" method="post">
                                <input type="hidden" name="id_bahan" value="<?= $bahan['id_bahan'] ?>">
                                <h6>Yakin Ingin Menghapus Bahan Dengan :
                                    <ul>
                                        <li>Jenis Bahan : <?= $bahan['jenis_bahan'] ?></li>
                                        <li>Warna : <?= $bahan['nama_warna'] ?></li>
                                    </ul>
                                </h6>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger" name="hapusBahan"><i class="bi bi-trash"></i> Hapus</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <!-- /Modal Hapus Bahan -->

    </section>
    <!-- /Modal Bahan -->

</main>

<?php

include '../../assets/layout/admin/footer.php';

?>