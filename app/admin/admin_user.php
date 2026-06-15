<?php

session_start();

// // membatasi halaman sebelum login
// if (!isset($_SESSION["login"])) {
//     echo "<script>
//             alert('AKSES DI TOLAK!');
//             document.location.href = '../../.';
//         </script>";
//     exit;
// }

// // membatasi halaman sesuai user login
// if ($_SESSION["role"] != 'Admin') {
//     echo "<script>
//         alert('AKSES DI TOLAK!');
//         document.location.href = '../../.';
//         </script>";
//     exit;
// }

// Halaman
$laman = 'Pengguna';
$fileLaman = 'admin_user.php';

// Header
include '../../assets/layout/admin/header.php';

// Akun
// Data Akun
if (isset($_POST['cari'])) {
    $kata_cari = htmlspecialchars(strip_tags($_POST['kata_cari']));
    $data_akun = select("SELECT akun.*, customer.* FROM akun
            JOIN customer ON akun.id_customer = customer.id_customer
            WHERE CONCAT(nama, username, email, no_hp, alamat, role) LIKE '%$kata_cari%' ORDER BY role ASC, nama ASC");
} else {
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

<!-- Pengguna Main -->
<main class="overflow-auto" style="flex:1;">

    <!-- Popup -->
    <?php require_once '../popup.php'; ?>
    <!-- .Popup -->

    <!-- View Data Akun -->
    <section aria-label="Users View">

        <!-- Desktop View -->
        <div class="d-none d-md-block">
            <div class="row justify-content-center">
                <div class="col-sm-6 col-md-8 col-lg-12">
                    <div class="card">

                        <!-- .card-header -->
                        <div class="card-header">

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

                            <button type="button" class="btn btn-primary btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
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
                                            <th class="bg-success">Nama</th>
                                            <th class="bg-success">Username</th>
                                            <th class="bg-success">Email</th>
                                            <th class="bg-success">No HP</th>
                                            <th class="bg-success">Alamat</th>
                                            <th class="bg-success">Role</th>
                                            <th class="bg-success">Action</th>
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
                                                <td class="text-center"><span class="badge admin-role-badge <?= ($akun['role'] == 'Admin') ? 'admin-role-admin' : 'admin-role-customer' ?>"><?= $akun['role'] ?></span></td>
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
                                <span class="badge rounded-pill px-3 py-2 admin-role-badge <?= ($akun['role'] == 'Admin') ? 'admin-role-admin' : 'admin-role-customer'; ?>">
                                    <?= $akun['role'] ?>
                                </span>
                            </div>

                            <!-- Body -->
                            <div class="card-body py-2">
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
                            <div class="card-footer border-0 pt-0 pb-2">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary w-100 rounded-pill"
                                        data-bs-toggle="modal" data-bs-target="#modalUbah<?= $akun['id_akun']; ?>">
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
    <!-- .View Data Akun -->

    <!-- Modal Akun -->
    <section class="modal-akun">

        <!-- Modal Tambah Akun -->
        <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header bg-success">
                        <h5 class="modal-title" id="exampleModalLabel">Tambah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" />
                    </div>

                    <div class="modal-body">

                        <form action="" method="post" enctype="multipart/form-data">

                            <div class="form-floating mb-2">
                                <input type="text" name="nama" id="floatingInput" class="form-control" minlength="5" placeholder="Nama" required>
                                <label for="floatingInput">Nama</label>
                            </div>

                            <div class="form-floating mb-2">
                                <input type="text" name="username" id="floatingInput" class="form-control" minlength="5" placeholder="username" required>
                                <label for="floatingInput">Username</label>
                            </div>

                            <div class="form-floating mb-2">
                                <input type="password" name="password" id="floatingInput" class="form-control" minlength="5" placeholder="Password" required>
                                <label for="floatingInput">Password</label>
                            </div>

                            <div class="form-floating mb-2">
                                <input type="email" name="email" id="floatingInput" class="form-control" minlength="5" placeholder="Email" required>
                                <label for="floatingInput">Email</label>
                            </div>

                            <div class="form-floating mb-2">
                                <input type="number" name="no_hp" id="floatingInput" class="form-control" minlength="12" placeholder="Nomor Handphone" required>
                                <label for="floatingInput">Nomor Handphone</label>
                            </div>

                            <div class="form mb-2">
                                <textarea name="alamat" class="form-control" minlength="5" placeholder="Alamat" required></textarea>
                            </div>

                            <div class="form-group mb-2">
                                <label for="Role">Role</label>
                                <select name="role" id="role" class="form-control" minlength="5" required>
                                    <option value="" disabled selected>-- Pilih --</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Customer">Customer</option>
                                </select>
                            </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="tambah" class="btn btn-success"><i class="bi bi-floppy me-1"></i>
                            Simpan</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
        <!-- /Modal Tambah Akun -->

        <!-- Modal Ubah Akun -->
        <?php foreach ($data_akun as $akun) : ?>
            <div class="modal fade" id="modalUbah<?= $akun['id_akun'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">

                        <div class="modal-header bg-primary">
                            <h5 class="modal-title" id="exampleModalLabel">Ubah <?= $laman; ?> Dengan :
                                <ul>
                                    <li>Username : <span class="badge bg-info"><?= $akun['username']; ?></span></li>
                                    <li>Role : <span class="badge admin-role-badge <?= ($akun['role'] == 'Admin') ? 'admin-role-admin' : 'admin-role-customer' ?>"><?= $akun['role'] ?></span></li>
                                </ul>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" />
                        </div>

                        <div class="modal-body">

                            <form action="" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="id_akun" value="<?= $akun['id_akun'] ?>">
                                <input type="hidden" name="id_customer" value="<?= $akun['id_customer'] ?>">

                                <div class="form-floating mb-3">
                                    <input type="text" name="nama" id="floatingInput" class="form-control" minlength="5" placeholder="Nama" value="<?= $akun['nama'] ?>" required>
                                    <label for="floatingInput">Nama</label>
                                </div>

                                <div class="form-floating mb-3">
                                    <input type="text" name="username" id="floatingInput" class="form-control" minlength="5" placeholder="username" value="<?= $akun['username'] ?>" required>
                                    <label for="floatingInput">Username</label>
                                </div>

                                <div class="form-floating mb-3">
                                    <input type="password" name="password" id="floatingInput" class="form-control" placeholder="Password">
                                    <label for="floatingInput">Password</label>
                                </div>

                                <div class="form-floating mb-3">
                                    <input type="email" name="email" id="floatingInput" class="form-control" minlength="5" placeholder="Email" value="<?= $akun['email']; ?>" required>
                                    <label for="floatingInput">Email</label>
                                </div>

                                <div class="form-floating mb-3">
                                    <input type="number" name="no_hp" id="floatingInput" class="form-control" minlength="12" placeholder="Nomor Handphone" value="<?= $akun['no_hp'] ?>" required>
                                    <label for="floatingInput">Nomor Handphone</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <textarea name="alamat" id="floatingInput" class="form-control" minlength="5" placeholder="" required><?= $akun['alamat'] ?></textarea>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="role">Role</label>
                                    <select name="role" id="role" class="form-control" minlength="5" required>
                                        <option value="Admin" <?= ($akun['role'] == 'Admin') ? 'selected' : '' ?>>Admin</option>
                                        <option value="Customer" <?= ($akun['role'] == 'Customer') ? 'selected' : '' ?>>Customer</option>
                                    </select>
                                </div>

                        </div>

                        <div class="modal-footer">
                            <button type="submit" name="ubah" class="btn btn-primary"><i class="bi bi-floppy me-1"></i> Simpan</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <!-- /Modal Ubah AKun -->

        <!-- Modal Hapus Akun -->
        <?php foreach ($data_akun as $akun) : ?>
            <div class="modal fade" id="modalHapus<?= $akun['id_akun'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <div class="modal-header bg-danger">
                            <h5 class="modal-title" id="exampleModalLabel">Hapus <?= $laman; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" />
                        </div>

                        <div class="modal-body">
                            <form action="" method="post">
                                <input type="hidden" name="id_akun" value="<?= $akun['id_akun'] ?>">
                                <input type="hidden" name="id_customer" value="<?= $akun['id_customer'] ?>">
                                <h6>Yakin Ingin Menghapus <?= $laman; ?> Dengan :
                                    <ul>
                                        <li>Username : <span class="badge bg-info"><?= $akun['username'] ?></span></li>
                                        <li>Role : <span class="badge admin-role-badge <?= ($akun['role'] == 'Admin') ? 'admin-role-admin' : 'admin-role-customer' ?>"><?= $akun['role'] ?></span></li>
                                    </ul>
                                </h6>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger" name="hapus"><i class="bi bi-trash"></i> Hapus</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <!-- /Modal Hapus Akun -->

    </section>
    <!-- /Modal Akun -->

</main>

<?php

// Footer
include '../../assets/layout/admin/footer.php';

?>
