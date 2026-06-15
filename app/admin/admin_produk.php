<?php

session_start();

$laman = 'Produk';
$fileLaman = 'admin_produk.php';

include '../../assets/layout/admin/header.php';

// Data Produk
if (isset($_POST['cari'])) {
    $kata_cari = htmlspecialchars(strip_tags($_POST['kata_cari']));
    $data_produk = select("SELECT * FROM produk
                WHERE CONCAT(nama_produk, deskripsi) LIKE '%$kata_cari%'
                ORDER BY nama_produk ASC");
} else {
    $data_produk = select("SELECT * FROM produk ORDER BY nama_produk ASC");
}

// Produk
// jika tombol tambah di tekan jalankan script berikut
if (isset($_POST['tambahProduk'])) {
    $result = tambah_produk($_POST, $_FILES) > 0;

    $popup = true;
    $statusPopup = $result ? 'Berhasil' : 'Gagal';
    $warnaPopup = $result ? 'success' : 'danger';
    $iconPopup = $result ? 'check2-circle' : 'x-circle';
    $popupEksekusi = 'Ditambahkan';

    $data_produk = select("SELECT * FROM produk ORDER BY nama_produk ASC");
}

// jika tombol ubah di tekan jalankan script berikut
if (isset($_POST['ubahProduk'])) {
    $result = ubah_produk($_POST, $_FILES) > 0;

    $popup = true;
    $statusPopup = $result ? 'Berhasil' : 'Gagal';
    $warnaPopup = $result ? 'success' : 'danger';
    $iconPopup = $result ? 'check2-circle' : 'x-circle';
    $popupEksekusi = 'Diubah';

    $data_produk = select("SELECT * FROM produk ORDER BY nama_produk ASC");
}

// jika tombol hapus di tekan jalankan script berikut
if (isset($_POST['hapusProduk'])) {
    $result = hapus_produk($_POST) > 0;

    $popup = true;
    $statusPopup = $result ? 'Berhasil' : 'Gagal';
    $warnaPopup = $result ? 'success' : 'danger';
    $iconPopup = $result ? 'check2-circle' : 'x-circle';
    $popupEksekusi = 'Dihapus';

    $data_produk = select("SELECT * FROM produk ORDER BY nama_produk ASC");
}

// .Produk


?>

<!-- Dashboard Main -->
<main class="overflow-auto" style="flex:1;">

    <!-- Popup -->
    <?php require_once '../popup.php'; ?>
    <!-- .Popup -->

    <!-- View Data Produk -->
    <section class="mb-3 mb-md-4" aria-label="Produk View">

        <!-- Desktop View -->
        <div class="d-none d-md-block">
            <div class="row justify-content-center">
                <div class="col-sm-6 col-md-8 col-lg-12">
                    <div class="card">

                        <!-- .card-header -->
                        <div class="card-header">

                            <div class="card-wrap d-flex justify-content-between align-items-center">
                                <h3 class="card-title">Produk</h3>

                                <form class="form" action="" method="post">
                                    <div class="input-group">
                                        <input type="search" class="form-control me-3" name="kata_cari" placeholder="Cari..." aria-label="Search" value="<?php if (isset($_POST['cari'])) {
                                                                                                                                                                echo $_POST['kata_cari'];
                                                                                                                                                            } ?>">
                                        <button class="btn btn-outline-info me-1" type="submit" name="cari"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                            </div>

                            <button type="button" class="btn btn-primary btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#modalTambahProduk">
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
                                            <th class="bg-success">Nama Produk</th>
                                            <th class="bg-success">Deskripsi</th>
                                            <th class="bg-success">Gambar Produk</th>
                                            <th class="bg-success">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php $no = 1; ?>
                                        <?php foreach ($data_produk as $produk) : ?>
                                            <tr style="height: 10px;">
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td><?= $produk['nama_produk'] ?></td>
                                                <td><?= $produk['deskripsi'] ?></td>
                                                <td>
                                                    <?php if (!empty($produk['gambar_produk'])) : ?>
                                                        <img src="../../assets/img/produk/<?= $produk['gambar_produk']; ?>" alt="<?= $produk['nama_produk'] ?>" width="50" height="50">
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-primary mb-1" data-bs-toggle="modal" data-bs-target="#modalUbahProduk<?= $produk['id_produk'] ?>">
                                                        <i class="bi bi-pen"></i> Ubah
                                                    </button>

                                                    <button type="button" class="btn btn-sm btn-outline-danger mb-1" data-bs-toggle="modal" data-bs-target="#modalHapusProduk<?= $produk['id_produk'] ?>">
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
                        <h3 class="card-title">Produk</h3>

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
                        data-bs-target="#modalTambahProduk">
                        <i class="bi bi-person-plus me-1"></i>
                        Tambah Produk
                    </button>



                </div>
            </div>

            <!-- LIST DATA -->
            <div class="row g-2">
                <?php foreach ($data_produk as $produk) : ?>
                    <div class="col-6">
                        <div class="card mb-3 shadow-sm border-0 rounded-4 overflow-hidden">

                            <div class="card-header bg-info d-flex justify-content-between align-items-center py-2">
                                <div class="me-2 overflow-hidden">
                                    <h6 class="mb-0 fw-semibold text-truncate"><?= $produk['nama_produk'] ?></h6>
                                    <small class="text-muted text-truncate d-block"><?= $produk['deskripsi'] ?></small>
                                </div>
                                <span class="badge rounded-pill px-3 py-2 bg-success">Produk</span>
                            </div>

                            <div class="card-body py-2 bg-light">
                                <div class="row g-2 small">

                                    <?php if (!empty($produk['gambar_produk'])) : ?>
                                        <div class="col-12">
                                            <img src="../../assets/img/produk/<?= $produk['gambar_produk'] ?>" class="img-fluid rounded" alt="<?= $produk['nama_produk'] ?>">
                                        </div>
                                    <?php endif; ?>

                                    <div class="col-12">
                                        <span class="text-muted fw-medium">Deskripsi</span>
                                        <div class="text-truncate"><?= $produk['deskripsi'] ?></div>
                                    </div>

                                </div>
                            </div>

                            <div class="card-footer border-0 pt-0 pb-2 bg-light">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary w-100 rounded-pill"
                                        data-bs-toggle="modal" data-bs-target="#modalUbahProduk<?= $produk['id_produk'] ?>">
                                        <i class="bi bi-pen me-1"></i> Ubah
                                    </button>

                                    <button class="btn btn-sm btn-outline-danger w-100 rounded-pill"
                                        data-bs-toggle="modal" data-bs-target="#modalHapusProduk<?= $produk['id_produk'] ?>">
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
    <!-- .View Data Produk -->

    <!-- Modal Produk -->
    <section class="modal-Produk">

        <!-- Modal Tambah Produk -->
        <div class="modal fade" id="modalTambahProduk" tabindex="-1" aria-labelledby="modalTambahProduk" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header bg-success">
                        <h5 class="modal-title" id="modalTambahProduk">Tambah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" />
                    </div>

                    <div class="modal-body">

                        <form action="" method="post" enctype="multipart/form-data">

                            <div class="form-floating mb-2">
                                <input type="text" name="nama_produk" id="namaProduk" class="form-control" minlength="3" placeholder="Nama Produk" required>
                                <label for="namaProduk">Nama Produk</label>
                            </div>

                            <div class="form-floating mb-2">
                                <textarea name="deskripsi" id="deskripsiProduk" class="form-control" placeholder="Deskripsi Produk" style="height: 120px;"></textarea>
                                <label for="deskripsiProduk">Deskripsi</label>
                            </div>

                            <div class="mb-3">
                                <label for="fotoProduk" class="form-label">Foto Produk</label>
                                <input class="form-control" type="file" name="foto" id="fotoProduk" accept="image/*">
                            </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="tambahProduk" class="btn btn-success"><i class="bi bi-floppy me-1"></i>
                            Simpan</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
        <!-- /Modal Tambah Produk -->

        <!-- Modal Ubah Produk -->
        <?php foreach ($data_produk as $produk) : ?>
            <div class="modal fade" id="modalUbahProduk<?= $produk['id_produk'] ?>" tabindex="-1" aria-labelledby="modalUbahProduk" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">

                        <div class="modal-header bg-primary">
                            <h5 class="modal-title" id="modalUbahProduk">Ubah Produk : <?= $produk['nama_produk'] ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" />
                        </div>

                        <div class="modal-body">

                            <form action="" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
                                <input type="hidden" name="existing_gambar_produk" value="<?= $produk['gambar_produk'] ?>">

                                <div class="form-floating mb-3">
                                    <input type="text" name="nama_produk" id="namaProdukEdit<?= $produk['id_produk'] ?>" class="form-control" minlength="3" placeholder="Nama Produk" value="<?= $produk['nama_produk'] ?>" required>
                                    <label for="namaProdukEdit<?= $produk['id_produk'] ?>">Nama Produk</label>
                                </div>

                                <div class="form-floating mb-3">
                                    <textarea name="deskripsi" id="deskripsiProdukEdit<?= $produk['id_produk'] ?>" class="form-control" placeholder="Deskripsi Produk" style="height: 120px;" required><?= $produk['deskripsi'] ?></textarea>
                                    <label for="deskripsiProdukEdit<?= $produk['id_produk'] ?>">Deskripsi</label>
                                </div>

                                <?php if (!empty($produk['gambar_produk'])) : ?>
                                    <div class="mb-3 text-center">
                                        <img src="../../assets/img/produk/<?= $produk['gambar_produk'] ?>" class="img-fluid rounded" alt="<?= $produk['nama_produk'] ?>" style="max-height: 140px;">
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label for="fotoProdukEdit<?= $produk['id_produk'] ?>" class="form-label">Ganti Foto Produk</label>
                                    <input class="form-control" type="file" name="foto" id="fotoProdukEdit<?= $produk['id_produk'] ?>" accept="image/*">
                                </div>

                        </div>

                        <div class="modal-footer">
                            <button type="submit" name="ubahProduk" class="btn btn-primary"><i class="bi bi-floppy me-1"></i> Simpan</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <!-- /Modal Ubah Produk -->

        <!-- Modal Hapus Produk -->
        <?php foreach ($data_produk as $produk) : ?>
            <div class="modal fade" id="modalHapusProduk<?= $produk['id_produk'] ?>" tabindex="-1" aria-labelledby="modalHapusProduk" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <div class="modal-header bg-danger">
                            <h5 class="modal-title" id="modalHapusProduk">Hapus <?= $laman; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" />
                        </div>

                        <div class="modal-body">
                            <form action="" method="post">
                                <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
                                <h6>Yakin Ingin Menghapus Produk Dengan :
                                    <ul>
                                        <li>Nama Produk : <?= $produk['nama_produk'] ?></li>
                                        <li>Deskripsi : <?= $produk['deskripsi'] ?></li>
                                    </ul>
                                </h6>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger" name="hapusProduk"><i class="bi bi-trash"></i> Hapus</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <!-- /Modal Hapus Produk -->

    </section>
    <!-- /Modal Produk -->

</main>

<?php

include '../../assets/layout/admin/footer.php';

?>