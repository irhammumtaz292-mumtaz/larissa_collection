<?php

    session_start();

    $laman = 'Warna';
    include '../../assets/layout/admin/header.php';

    // Akun
        // Data Akun
        if(isset($_POST['cari'])) 
        {
            $kata_cari = htmlspecialchars(strip_tags($_POST['kata_cari']));
            $data_akun = select("SELECT akun.*, customer.* FROM akun
            JOIN customer ON akun.id_customer = customer.id_customer
            WHERE CONCAT(nama, username, email, no_hp, alamat, role) LIKE '%$kata_cari%' ORDER BY role ASC, nama ASC");
        } else 
        {
            $data_akun = select("SELECT akun.*, customer.* FROM akun
            JOIN customer ON akun.id_customer = customer.id_customer ORDER BY role ASC, nama ASC");
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
    // .Akun

?>

    <!-- Dashboard Main -->
    <main class="overflow-auto" style="flex:1;">

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
                                    <input type="search" class="form-control me-3" name="kata_cari" placeholder="Cari..." aria-label="Search" value="<?php if(isset($_POST['cari'])) { echo $_POST['kata_cari']; } ?>">
                                    <button class="btn btn-outline-info me-1" type="submit" name="cari"><i class="bi bi-search"></i></button>
                                </div>
                                </form>
                            </div>

                            <button type="button" class="btn btn-primary btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
                            <i class="bi bi-person-plus me-1"></i>
                                Tambah
                            </button>  

                            <button class="btn btn-sm btn-secondary mb-2"><i class="bi bi-file-earmark-text me-1"></i>Generate Report</button>
                            <button class="btn btn-sm btn-success mb-2"><i class="bi bi-download me-1"></i>Export</button>

                            </div>
                        <!-- /.card-header -->

                        <!-- .card-body -->
                        <div class="card-body overflow-auto" style="max-height: 400px;">

                            <div class="table-responsive">

                                <table id="table" class="table table-sm table-bordered border-dark table-hover">
                                <thead class="text-center">
                                    <tr>
                                    <th scope="col" class="bg-success">#</th>
                                    <th scope="col" class="bg-success">Nama</th>
                                    <th scope="col" class="bg-success">Username</th>
                                    <th scope="col" class="bg-success">Email</th>
                                    <th scope="col" class="bg-success">No HP</th>
                                    <th scope="col" class="bg-success">Alamat</th>
                                    <th scope="col" class="bg-success">Role</th>
                                    <th scope="col" class="bg-success">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php $no = 1; ?>
                                    <?php foreach ($data_akun as $akun) : ?>
                                        <tr style="height: 10px;">
                                        <td scope="row" class="text-center"><?= $no++; ?></td>
                                        <td><?= $akun['nama'] ?></td>
                                        <td><?= $akun['username'] ?></td>
                                        <td><?= $akun['email'] ?></td>
                                        <td><?= $akun['no_hp'] ?></td>
                                        <td><?= $akun['alamat'] ?></td>
                                        <td class="text-center"><span class="badge bg-<?= ($akun['role'] == 'Admin') ? 'primary' : 'success' ?>"><?= $akun['role'] ?></span></td>
                                        <td class="text-center">
                                        
                                            <button type="button" class="btn btn-sm btn-outline-primary mb-1" data-bs-toggle="modal" data-bs-target="#modalUbah<?= $akun['id_akun'] ?>">
                                            <i class="bi bi-pen"></i> Ubah
                                            </button>

                                            <button type="button" class="btn btn-sm btn-outline-danger mb-1" data-bs-toggle="modal" data-bs-target="#modalHapus<?= $akun['id_akun'] ?>">
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
                                <h3 class="card-title"><?= $laman; ?></h3>

                                <form class="form" action="" method="post">
                                <div class="input-group">
                                    <input type="search" class="form-control me-3" name="kata_cari" placeholder="Cari..." aria-label="Search" value="<?php if(isset($_POST['cari'])) { echo $_POST['kata_cari']; } ?>">
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
                            data-bs-target="#modalTambah">
                            <i class="bi bi-person-plus me-1"></i>
                            Tambah Akun
                        </button>

                        <!-- Tombol secondary -->
                        <div class="row g-2">
                            <div class="col-6">
                                <button class="btn btn-sm btn-secondary w-100 rounded-pill">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    Generate
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-sm btn-success w-100 rounded-pill">
                                    <i class="bi bi-download me-1"></i>
                                    Export
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- LIST DATA -->
                <div class="row g-2">
                    <?php foreach ($data_akun as $akun) : ?>
                    <div class="col-6">
                        <div class="card mb-3 shadow-sm border-0 rounded-4 overflow-hidden">

                            <!-- Header -->
                            <div class="card-header bg-info d-flex justify-content-between align-items-center py-2">
                                <div class="me-2 overflow-hidden">
                                    <h6 class="mb-0 fw-semibold text-truncate"><?= $akun['nama'] ?></h6>
                                    <small class="text-muted text-truncate d-block">@<?= $akun['username'] ?></small>
                                </div>
                                <span class="badge rounded-pill px-3 py-2 bg-<?= ($akun['role'] == 'Admin') ? 'primary' : 'success'; ?>">
                                    <?= $akun['role'] ?>
                                </span>
                            </div>

                            <!-- Body -->
                            <div class="card-body py-2" style="background-color: #DCDCDC;">
                                <div class="row g-2 small">

                                    <div class="col-12">
                                        <span class="text-muted fw-medium">Email</span>
                                        <div class="text-truncate"><?= $akun['email'] ?></div>
                                    </div>

                                    <div class="col-6">
                                        <span class="text-muted fw-medium">No HP</span>
                                        <div class="text-truncate"><?= $akun['no_hp'] ?></div>
                                    </div>

                                    <div class="col-12">
                                        <span class="text-muted fw-medium">Alamat</span>
                                        <div class="text-truncate"><?= $akun['alamat'] ?></div>
                                    </div>

                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="card-footer border-0 pt-0 pb-2" style="background-color: #DCDCDC;">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary w-100 rounded-pill"
                                        data-bs-toggle="modal" data-bs-target="#modalUbah<?= $akun['id_akun'];?>">
                                        <i class="bi bi-pen me-1"></i> Ubah
                                    </button>

                                    <button class="btn btn-sm btn-outline-danger w-100 rounded-pill"
                                        data-bs-toggle="modal" data-bs-target="#modalHapus<?= $akun['id_akun'] ?>">
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

    </main>

<?php

    include '../../assets/layout/admin/footer.php';

?>