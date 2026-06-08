<?php

    session_start();

    $laman = 'Desain';
    $fileLaman = 'admin_desain.php';

    include '../../assets/layout/admin/header.php';

    $data_produk = select("SELECT * FROM produk ORDER BY nama_produk ASC");

    // Data Desain
    if (isset($_POST['cari'])) {
        $kata_cari = htmlspecialchars(strip_tags($_POST['kata_cari']));
        $data_desain = select("SELECT desain.*, produk.nama_produk FROM desain
            JOIN produk ON desain.id_produk = produk.id_produk
            WHERE CONCAT(nama_desain, nama_produk, deskripsi, harga_desain) LIKE '%$kata_cari%'
            ORDER BY nama_desain ASC");
    } else {
        $data_desain = select("SELECT desain.*, produk.nama_produk FROM desain
            JOIN produk ON desain.id_produk = produk.id_produk
            ORDER BY nama_desain ASC");
    }

    if (isset($_POST['tambahDesain'])) {
        $result = tambah_desain($_POST, $_FILES) > 0;

        $popup = true;
        $statusPopup = $result ? 'Berhasil' : 'Gagal';
        $warnaPopup = $result ? 'success' : 'danger';
        $iconPopup = $result ? 'check2-circle' : 'x-circle';
        $popupEksekusi = 'Ditambahkan';

        $data_desain = select("SELECT desain.*, produk.nama_produk FROM desain
            JOIN produk ON desain.id_produk = produk.id_produk
            ORDER BY nama_desain ASC");
    }

    if (isset($_POST['ubahDesain'])) {
        $result = ubah_desain($_POST, $_FILES) > 0;

        $popup = true;
        $statusPopup = $result ? 'Berhasil' : 'Gagal';
        $warnaPopup = $result ? 'success' : 'danger';
        $iconPopup = $result ? 'check2-circle' : 'x-circle';
        $popupEksekusi = 'Diubah';

        $data_desain = select("SELECT desain.*, produk.nama_produk FROM desain
            JOIN produk ON desain.id_produk = produk.id_produk
            ORDER BY nama_desain ASC");
    }

    if (isset($_POST['hapusDesain'])) {
        $result = hapus_desain($_POST) > 0;

        $popup = true;
        $statusPopup = $result ? 'Berhasil' : 'Gagal';
        $warnaPopup = $result ? 'success' : 'danger';
        $iconPopup = $result ? 'check2-circle' : 'x-circle';
        $popupEksekusi = 'Dihapus';

        $data_desain = select("SELECT desain.*, produk.nama_produk FROM desain
            JOIN produk ON desain.id_produk = produk.id_produk
            ORDER BY nama_desain ASC");
    }

?>

    <!-- Dashboard Main -->
    <main class="overflow-auto" style="flex:1;">

        <!-- Popup -->
            <?php require_once '../popup.php';?>
        <!-- .Popup -->

        <!-- View Data Desain -->
        <section class="mb-3 mb-md-4" aria-label="Desain View">

            <!-- Desktop View -->
            <div class="d-none d-md-block">
                <div class="row justify-content-center">
                    <div class="col-sm-6 col-md-8 col-lg-12">
                        <div class="card">

                            <div class="card-header">
                                <div class="card-wrap d-flex justify-content-between align-items-center">
                                    <h3 class="card-title">Desain</h3>

                                    <form class="form" action="" method="post">
                                        <div class="input-group">
                                            <input type="search" class="form-control me-3" name="kata_cari" placeholder="Cari..." aria-label="Search" value="<?php if(isset($_POST['cari'])) { echo $_POST['kata_cari']; } ?>">
                                            <button class="btn btn-outline-info me-1" type="submit" name="cari"><i class="bi bi-search"></i></button>
                                        </div>
                                    </form>
                                </div>

                                <button type="button" class="btn btn-primary btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#modalTambahDesain">
                                    <i class="bi bi-person-plus me-1"></i>
                                    Tambah
                                </button>

                                <button class="btn btn-sm btn-secondary mb-2"><i class="bi bi-file-earmark-text me-1"></i>Generate Report</button>
                                <button class="btn btn-sm btn-success mb-2"><i class="bi bi-download me-1"></i>Export</button>
                            </div>

                            <div class="card-body overflow-auto" style="max-height: 400px;">
                                <div class="table-responsive">
                                    <table id="table" class="table table-sm table-bordered border-dark table-hover">
                                        <thead class="text-center">
                                            <tr>
                                                <th class="bg-success">#</th>
                                                <th class="bg-success">Nama Desain</th>
                                                <th class="bg-success">Produk</th>
                                                <th class="bg-success">Harga</th>
                                                <th class="bg-success">Gambar</th>
                                                <th class="bg-success">Deskripsi</th>
                                                <th class="bg-success">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            <?php foreach ($data_desain as $desain) : ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++; ?></td>
                                                    <td><?= htmlspecialchars($desain['nama_desain']) ?></td>
                                                    <td><?= htmlspecialchars($desain['nama_produk']) ?></td>
                                                    <td><?= htmlspecialchars($desain['harga_desain']) ?></td>
                                                    <td class="text-center">
                                                        <?php if (!empty($desain['gambar_desain'])) : ?>
                                                            <img src="../../assets/img/desain/<?= htmlspecialchars($desain['gambar_desain']) ?>" alt="<?= htmlspecialchars($desain['nama_desain']) ?>" width="60" height="60">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($desain['deskripsi']) ?></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-primary mb-1" data-bs-toggle="modal" data-bs-target="#modalUbahDesain<?= $desain['id_desain'] ?>">
                                                            <i class="bi bi-pen"></i> Ubah
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger mb-1" data-bs-toggle="modal" data-bs-target="#modalHapusDesain<?= $desain['id_desain'] ?>">
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
                            <h3 class="card-title">Desain</h3>
                            <form class="form" action="" method="post">
                                <div class="input-group">
                                    <input type="search" class="form-control me-3" name="kata_cari" placeholder="Cari..." aria-label="Search" value="<?php if(isset($_POST['cari'])) { echo $_POST['kata_cari']; } ?>">
                                    <button class="btn btn-outline-info me-1" type="submit" name="cari"><i class="bi bi-search"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card-body p-2">
                        <button type="button" class="btn btn-sm btn-primary w-100 rounded-pill fw-semibold shadow-sm mb-2" data-bs-toggle="modal" data-bs-target="#modalTambahDesain">
                            <i class="bi bi-person-plus me-1"></i> Tambah Desain
                        </button>
                        <div class="row g-2">
                            <div class="col-6">
                                <button class="btn btn-sm btn-secondary w-100 rounded-pill">
                                    <i class="bi bi-file-earmark-text me-1"></i> Generate
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-sm btn-success w-100 rounded-pill">
                                    <i class="bi bi-download me-1"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-2">
                    <?php foreach ($data_desain as $desain) : ?>
                        <div class="col-6">
                            <div class="card mb-3 shadow-sm border-0 rounded-4 overflow-hidden">
                                <div class="card-header bg-info d-flex justify-content-between align-items-center py-2">
                                    <div class="me-2 overflow-hidden">
                                        <h6 class="mb-0 fw-semibold text-truncate"><?= htmlspecialchars($desain['nama_desain']) ?></h6>
                                        <small class="text-muted text-truncate d-block"><?= htmlspecialchars($desain['nama_produk']) ?></small>
                                    </div>
                                    <span class="badge rounded-pill px-3 py-2 bg-success">Rp <?= htmlspecialchars($desain['harga_desain']) ?></span>
                                </div>
                                <div class="card-body py-2 bg-light">
                                    <?php if (!empty($desain['gambar_desain'])) : ?>
                                        <div class="mb-2">
                                            <img src="../../assets/img/desain/<?= htmlspecialchars($desain['gambar_desain']) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($desain['nama_desain']) ?>">
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <span class="text-muted fw-medium">Deskripsi</span>
                                        <div class="text-truncate"><?= htmlspecialchars($desain['deskripsi']) ?></div>
                                    </div>
                                </div>
                                <div class="card-footer border-0 pt-0 pb-2 bg-light">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalUbahDesain<?= $desain['id_desain'] ?>">
                                            <i class="bi bi-pen me-1"></i> Ubah
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalHapusDesain<?= $desain['id_desain'] ?>">
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

        <!-- Modal Desain -->
        <section class="modal-desain">

            <div class="modal fade" id="modalTambahDesain" tabindex="-1" aria-labelledby="modalTambahDesain" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-success">
                            <h5 class="modal-title" id="modalTambahDesain">Tambah Desain</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" method="post" enctype="multipart/form-data">
                                <div class="form-floating mb-2">
                                    <input type="text" name="nama_desain" id="floatingInput" class="form-control" placeholder="Nama Desain" required>
                                    <label for="floatingInput">Nama Desain</label>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="produk_id">Produk</label>
                                    <select name="id_produk" id="produk_id" class="form-control" required>
                                        <option value="" disabled selected>-- Pilih Produk --</option>
                                        <?php foreach ($data_produk as $produk) : ?>
                                            <option value="<?= $produk['id_produk'] ?>"><?= htmlspecialchars($produk['nama_produk']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-floating mb-2">
                                    <input type="number" name="harga_desain" id="floatingInput" class="form-control" placeholder="Harga Desain" required>
                                    <label for="floatingInput">Harga Desain</label>
                                </div>
                                <div class="form-floating mb-2">
                                    <textarea name="deskripsi" id="floatingInput" class="form-control" rows="3" placeholder="Deskripsi" required></textarea>
                                    <label for="floatingInput">Deskripsi</label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Gambar Desain</label>
                                    <input class="form-control" type="file" name="foto" accept="image/*" required>
                                </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="tambahDesain" class="btn btn-success"><i class="bi bi-floppy me-1"></i> Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php foreach ($data_desain as $desain) : ?>
                <div class="modal fade" id="modalUbahDesain<?= $desain['id_desain'] ?>" tabindex="-1" aria-labelledby="modalUbahDesain" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-primary">
                                <h5 class="modal-title" id="modalUbahDesain">Ubah Desain - <?= htmlspecialchars($desain['nama_desain']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form action="" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="id_desain" value="<?= $desain['id_desain'] ?>">
                                    <input type="hidden" name="existing_gambar_desain" value="<?= htmlspecialchars($desain['gambar_desain']) ?>">

                                    <div class="form-floating mb-3">
                                        <input type="text" name="nama_desain" id="floatingInput" class="form-control" placeholder="Nama Desain" value="<?= htmlspecialchars($desain['nama_desain']) ?>" required>
                                        <label for="floatingInput">Nama Desain</label>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="produk_id_<?= $desain['id_desain'] ?>">Produk</label>
                                        <select name="id_produk" id="produk_id_<?= $desain['id_desain'] ?>" class="form-control" required>
                                            <?php foreach ($data_produk as $produk) : ?>
                                                <option value="<?= $produk['id_produk'] ?>" <?= ($desain['id_produk'] == $produk['id_produk']) ? 'selected' : '' ?>><?= htmlspecialchars($produk['nama_produk']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="number" name="harga_desain" id="floatingInput" class="form-control" placeholder="Harga Desain" value="<?= htmlspecialchars($desain['harga_desain']) ?>" required>
                                        <label for="floatingInput">Harga Desain</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <textarea name="deskripsi" id="floatingInput" class="form-control" rows="3" placeholder="Deskripsi" required><?= htmlspecialchars($desain['deskripsi']) ?></textarea>
                                        <label for="floatingInput">Deskripsi</label>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Gambar Desain</label>
                                        <input class="form-control" type="file" name="foto" accept="image/*">
                                        <?php if (!empty($desain['gambar_desain'])) : ?>
                                            <small class="text-muted">Gambar saat ini: <?= htmlspecialchars($desain['gambar_desain']) ?></small>
                                        <?php endif; ?>
                                    </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="ubahDesain" class="btn btn-primary"><i class="bi bi-floppy me-1"></i> Simpan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php foreach ($data_desain as $desain) : ?>
                <div class="modal fade" id="modalHapusDesain<?= $desain['id_desain'] ?>" tabindex="-1" aria-labelledby="modalHapusDesain" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger">
                                <h5 class="modal-title" id="modalHapusDesain">Hapus Desain</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form action="" method="post">
                                    <input type="hidden" name="id_desain" value="<?= $desain['id_desain'] ?>">
                                    <h6>Yakin ingin menghapus desain:</h6>
                                    <ul>
                                        <li>Nama Desain: <?= htmlspecialchars($desain['nama_desain']) ?></li>
                                        <li>Produk: <?= htmlspecialchars($desain['nama_produk']) ?></li>
                                    </ul>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-danger" name="hapusDesain"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </section>
        <!-- /Modal Desain -->

    </main>

<?php

    include '../../assets/layout/admin/footer.php';

?>
