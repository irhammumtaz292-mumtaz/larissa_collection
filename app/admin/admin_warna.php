<?php

session_start();

$laman = 'Warna';
$fileLaman = 'admin_warna.php';
include '../../assets/layout/admin/header.php';

if (isset($_POST['cari'])) {
    $kata_cari = htmlspecialchars(strip_tags($_POST['kata_cari']));
    $data_warna = select("SELECT * FROM warna WHERE nama_warna LIKE '%$kata_cari%' ORDER BY nama_warna ASC");
} else {
    $data_warna = select("SELECT * FROM warna ORDER BY nama_warna ASC");
}

if (isset($_POST['tambahWarna'])) {
    $result = tambah_warna($_POST) > 0;

    $popup = true;
    $statusPopup = $result ? 'Berhasil' : 'Gagal';
    $warnaPopup = $result ? 'success' : 'danger';
    $iconPopup = $result ? 'check2-circle' : 'x-circle';
    $popupEksekusi = 'Ditambahkan';

    $data_warna = select("SELECT * FROM warna ORDER BY nama_warna ASC");
}

if (isset($_POST['ubahWarna'])) {
    $result = ubah_warna($_POST) > 0;

    $popup = true;
    $statusPopup = $result ? 'Berhasil' : 'Gagal';
    $warnaPopup = $result ? 'success' : 'danger';
    $iconPopup = $result ? 'check2-circle' : 'x-circle';
    $popupEksekusi = 'Diubah';

    $data_warna = select("SELECT * FROM warna ORDER BY nama_warna ASC");
}

if (isset($_POST['hapusWarna'])) {
    $result = hapus_warna($_POST) > 0;

    $popup = true;
    $statusPopup = $result ? 'Berhasil' : 'Gagal';
    $warnaPopup = $result ? 'success' : 'danger';
    $iconPopup = $result ? 'check2-circle' : 'x-circle';
    $popupEksekusi = 'Dihapus';

    $data_warna = select("SELECT * FROM warna ORDER BY nama_warna ASC");
}
?>

<!-- Dashboard Main -->
<main class="overflow-auto" style="flex:1;">

    <!-- Popup -->
    <?php require_once '../popup.php'; ?>
    <!-- .Popup -->

    <!-- View Data Warna -->
    <section class="mb-3 mb-md-4" aria-label="Warna View">

        <!-- Desktop View -->
        <div class="d-none d-md-block">
            <div class="row justify-content-center">
                <div class="col-sm-6 col-md-8 col-lg-12">
                    <div class="card">

                        <div class="card-header">
                            <div class="card-wrap d-flex justify-content-between align-items-center">
                                <h3 class="card-title">Warna</h3>

                                <form class="form" action="" method="post">
                                    <div class="input-group">
                                        <input type="search" class="form-control me-3" name="kata_cari" placeholder="Cari..." aria-label="Search" value="<?php if (isset($_POST['cari'])) {
                                                                                                                                                                echo $_POST['kata_cari'];
                                                                                                                                                            } ?>">
                                        <button class="btn btn-outline-info me-1" type="submit" name="cari"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                            </div>

                            <button type="button" class="btn btn-primary btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#modalTambahWarna">
                                <i class="bi bi-person-plus me-1"></i>
                                Tambah
                            </button>

                        </div>

                        <div class="card-body overflow-auto" style="max-height: 400px;">
                            <div class="table-responsive">
                                <table id="table" class="table table-sm table-bordered border-dark table-hover">
                                    <thead class="text-center">
                                        <tr>
                                            <th class="bg-success">#</th>
                                            <th class="bg-success">Nama Warna</th>
                                            <th class="bg-success">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; ?>
                                        <?php foreach ($data_warna as $warna) : ?>
                                            <tr>
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td><?= htmlspecialchars($warna['nama_warna']) ?></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-primary mb-1" data-bs-toggle="modal" data-bs-target="#modalUbahWarna<?= $warna['id_warna'] ?>">
                                                        <i class="bi bi-pen"></i> Ubah
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger mb-1" data-bs-toggle="modal" data-bs-target="#modalHapusWarna<?= $warna['id_warna'] ?>">
                                                        <i class="bi bi-trash"></i> Hapus
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile View -->
        <div class="d-block d-md-none">
            <div class="card mb-3 shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header py-2">
                    <div class="card-wrap d-flex justify-content-between align-items-center">
                        <h3 class="card-title"><?= $laman; ?></h3>
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
                    <button type="button" class="btn btn-sm btn-primary w-100 rounded-pill fw-semibold shadow-sm mb-2" data-bs-toggle="modal" data-bs-target="#modalTambahWarna">
                        <i class="bi bi-person-plus me-1"></i>
                        Tambah Warna
                    </button>

                </div>
            </div>

            <div class="row g-2">
                <?php foreach ($data_warna as $warna) : ?>
                    <div class="col-6">
                        <div class="card mb-3 shadow-sm border-0 rounded-4 overflow-hidden">
                            <div class="card-header bg-info d-flex justify-content-between align-items-center py-2">
                                <div class="me-2 overflow-hidden">
                                    <h6 class="mb-0 fw-semibold text-truncate"><?= htmlspecialchars($warna['nama_warna']) ?></h6>
                                </div>
                            </div>
                            <div class="card-body py-2 bg-light">
                                <p class="small text-muted mb-0">Warna tersedia</p>
                            </div>
                            <div class="card-footer border-0 pt-0 pb-2 bg-light">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalUbahWarna<?= $warna['id_warna'] ?>">
                                        <i class="bi bi-pen me-1"></i> Ubah
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalHapusWarna<?= $warna['id_warna'] ?>">
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

    <!-- Modal Warna -->
    <section class="modal-warna">
        <div class="modal fade" id="modalTambahWarna" tabindex="-1" aria-labelledby="modalTambahWarna" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title" id="modalTambahWarna">Tambah Warna</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="" method="post">
                            <div class="form-floating mb-3">
                                <input type="text" name="nama_warna" id="floatingNamaWarna" class="form-control" placeholder="Nama Warna" required>
                                <label for="floatingNamaWarna">Nama Warna</label>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="tambahWarna" class="btn btn-success"><i class="bi bi-floppy me-1"></i> Simpan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php foreach ($data_warna as $warna) : ?>
            <div class="modal fade" id="modalUbahWarna<?= $warna['id_warna'] ?>" tabindex="-1" aria-labelledby="modalUbahWarna" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <h5 class="modal-title" id="modalUbahWarna">Ubah Warna</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" method="post">
                                <input type="hidden" name="id_warna" value="<?= $warna['id_warna'] ?>">
                                <div class="form-floating mb-3">
                                    <input type="text" name="nama_warna" id="floatingNamaWarna<?= $warna['id_warna'] ?>" class="form-control" placeholder="Nama Warna" value="<?= htmlspecialchars($warna['nama_warna']) ?>" required>
                                    <label for="floatingNamaWarna<?= $warna['id_warna'] ?>">Nama Warna</label>
                                </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="ubahWarna" class="btn btn-primary"><i class="bi bi-floppy me-1"></i> Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php foreach ($data_warna as $warna) : ?>
            <div class="modal fade" id="modalHapusWarna<?= $warna['id_warna'] ?>" tabindex="-1" aria-labelledby="modalHapusWarna" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger">
                            <h5 class="modal-title" id="modalHapusWarna">Hapus Warna</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" method="post">
                                <input type="hidden" name="id_warna" value="<?= $warna['id_warna'] ?>">
                                <h6>Yakin ingin menghapus warna <strong><?= htmlspecialchars($warna['nama_warna']) ?></strong>?</h6>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="hapusWarna" class="btn btn-danger"><i class="bi bi-trash me-1"></i> Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </section>
    <!-- /Modal Warna -->

</main>

<?php

include '../../assets/layout/admin/footer.php';

?>