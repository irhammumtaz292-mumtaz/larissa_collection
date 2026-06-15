<?php

// Variabel
$popup = false;
$statusPopup = '';
$warnaPopup = '';
$popupEksekusi = '';
// .Variabel

// Menjalankan query SELECT dan mengembalikan hasilnya sebagai array asosiatif.
function select($query)
{
    // panggil koneksi database
    global $db;

    $result = mysqli_query($db, $query);
    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}

// Memformat tanggal pesanan menjadi format tanggal dan jam yang mudah dibaca.
function format_tanggal_pesanan($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }

    $timestamp = strtotime($tanggal);

    if ($timestamp === false) {
        return '-';
    }

    return date('d/m/Y H:i', $timestamp);
}

// Mengupload satu file gambar ke folder aset yang ditentukan.
function upload_foto($fieldName = 'foto', $targetDir = 'produk')
{
    $namaFile   = $_FILES[$fieldName]['name'] ?? '';
    $ukuranFile = $_FILES[$fieldName]['size'] ?? 0;
    $error      = $_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE;
    $tmpName    = $_FILES[$fieldName]['tmp_name'] ?? '';

    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    // check file yang diupload
    $extensifileValid = ['jpg', 'jpeg', 'png', 'jfif', 'ico'];
    $extensifile      = explode('.', $namaFile);
    $extensifile      = strtolower(end($extensifile));

    // check format/extensi file
    if (!in_array($extensifile, $extensifileValid)) {
        echo "<script>
                    alert('Format File Tidak Valid');
                    document.location.href = 'admin_produk.php';
                    </script>";
        die();
    }

    // check ukuran file 2 MB
    if ($ukuranFile > 2048000) {
        echo "<script>
                    alert('Ukuran File Max 2 MB');
                    document.location.href = 'admin_produk.php';
                  </script>";
        die();
    }

    // generate nama file baru
    $namaFileBaru = uniqid();
    $namaFileBaru .= '.';
    $namaFileBaru .= $extensifile;

    $uploadPath = __DIR__ . '/../../assets/img/' . $targetDir . '/';
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    move_uploaded_file($tmpName, $uploadPath . $namaFileBaru);
    return $namaFileBaru;
}

// Mengupload beberapa file logo/desain custom dan membersihkan file jika ada yang gagal.
function upload_logo_custom($files, $targetDir = 'desain')
{
    if (!isset($files['name']) || !is_array($files['name'])) {
        return [];
    }

    $extensiValid = ['jpg', 'jpeg', 'png', 'jfif'];
    $mimeValid = ['image/jpeg', 'image/png'];
    $ukuranMaksimal = 2048000;
    $uploadPath = __DIR__ . '/../../assets/img/' . $targetDir . '/';
    $uploadedFiles = [];

    if (!is_dir($uploadPath) && !mkdir($uploadPath, 0755, true)) {
        return false;
    }

    $cleanup = static function () use (&$uploadedFiles, $targetDir) {
        foreach ($uploadedFiles as $file) {
            hapus_file_upload($targetDir, $file);
        }
    };

    $count = count($files['name']);
    for ($i = 0; $i < $count; $i++) {
        $error = $files['error'][$i] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $namaFile = $files['name'][$i] ?? '';
        $tmpName = $files['tmp_name'][$i] ?? '';
        $ukuranFile = intval($files['size'][$i] ?? 0);
        $extensi = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
        $mime = is_uploaded_file($tmpName) && function_exists('mime_content_type')
            ? mime_content_type($tmpName)
            : false;

        if (
            $error !== UPLOAD_ERR_OK ||
            $ukuranFile > $ukuranMaksimal ||
            !in_array($extensi, $extensiValid, true) ||
            !in_array($mime, $mimeValid, true)
        ) {
            $cleanup();
            return false;
        }

        $extensiSimpan = $extensi === 'jfif' ? 'jpg' : $extensi;
        $namaFileBaru = uniqid() . '.' . $extensiSimpan;

        if (!move_uploaded_file($tmpName, $uploadPath . $namaFileBaru)) {
            $cleanup();
            return false;
        }

        $uploadedFiles[] = $namaFileBaru;
    }

    return $uploadedFiles;
}

// Menghapus satu file upload dari folder aset jika file tersebut ada.
function hapus_file_upload($targetDir, $filename)
{
    $filename = trim((string) $filename);

    if ($filename === '') {
        return false;
    }

    $filename = str_replace('\\', '/', $filename);

    if (basename($filename) !== $filename) {
        return false;
    }

    $baseDir = realpath(__DIR__ . '/../../assets/img/' . $targetDir);
    if ($baseDir === false) {
        return false;
    }

    $filePath = $baseDir . DIRECTORY_SEPARATOR . $filename;
    $realFilePath = realpath($filePath);

    if (
        $realFilePath === false ||
        strpos($realFilePath, $baseDir . DIRECTORY_SEPARATOR) !== 0 ||
        !is_file($realFilePath)
    ) {
        return false;
    }

    return @unlink($realFilePath);
}

// Menghapus beberapa file upload dalam satu folder aset.
function hapus_file_uploads($targetDir, array $filenames)
{
    foreach (array_unique(array_filter($filenames)) as $filename) {
        hapus_file_upload($targetDir, $filename);
    }
}

// Mengambil daftar nama file dari data JSON desain custom.
function daftar_file_desain_custom($files)
{
    if (is_string($files)) {
        $files = json_decode($files, true);
    }

    if (!is_array($files)) {
        return [];
    }

    $filenames = [];
    $walk = function ($value) use (&$walk, &$filenames) {
        if (is_array($value)) {
            foreach ($value as $item) {
                $walk($item);
            }

            return;
        }

        if (is_string($value) && trim($value) !== '') {
            $filenames[] = $value;
        }
    };

    $walk($files);

    return $filenames;
}

// Menghapus seluruh file yang tercatat dalam JSON desain custom.
function hapus_file_desain_custom_json($files)
{
    hapus_file_uploads('desain_custom', daftar_file_desain_custom($files));
}

// Mengambil referensi file desain dan bukti pembayaran yang terkait dengan pesanan.
function ambil_file_pesanan_terkait($whereSql)
{
    global $db;

    $fileRefs = [
        'bukti_transaksi' => [],
        'desain_custom' => [],
    ];

    $query = "SELECT
                t.bukti_pembayaran,
                dc.id_desain_custom,
                dc.files AS desain_custom_files
            FROM pesanan p
            LEFT JOIN transaksi t ON t.id_pesanan = p.id_pesanan
            LEFT JOIN desain_custom dc ON dc.id_desain_custom = p.id_desain_custom
            WHERE $whereSql";

    $result = mysqli_query($db, $query);
    if (!$result) {
        return $fileRefs;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['bukti_pembayaran'])) {
            $fileRefs['bukti_transaksi'][] = $row['bukti_pembayaran'];
        }

        if (!empty($row['id_desain_custom']) && !empty($row['desain_custom_files'])) {
            $fileRefs['desain_custom'][(int) $row['id_desain_custom']] = $row['desain_custom_files'];
        }
    }

    return $fileRefs;
}

// Mengambil file desain custom milik customer tertentu.
function ambil_file_desain_custom_customer($id_customer)
{
    global $db;

    $fileRefs = [];
    $id_customer = intval($id_customer);
    $result = mysqli_query($db, "SELECT id_desain_custom, files FROM desain_custom WHERE id_customer = $id_customer");

    if (!$result) {
        return $fileRefs;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['id_desain_custom']) && !empty($row['files'])) {
            $fileRefs[(int) $row['id_desain_custom']] = $row['files'];
        }
    }

    return $fileRefs;
}

// Menghapus file-file yang terkait dengan pesanan dari storage.
function hapus_file_pesanan_terkait(array $fileRefs)
{
    hapus_file_uploads('bukti_transaksi', $fileRefs['bukti_transaksi'] ?? []);

    foreach (($fileRefs['desain_custom'] ?? []) as $files) {
        hapus_file_desain_custom_json($files);
    }
}

// Menghapus desain custom yang sudah tidak dipakai oleh pesanan mana pun.
function hapus_desain_custom_tidak_dipakai(array $ids)
{
    global $db;

    $deletedIds = [];
    foreach (array_unique(array_map('intval', $ids)) as $id) {
        if ($id <= 0) {
            continue;
        }

        $cek = mysqli_query($db, "SELECT COUNT(*) AS total FROM pesanan WHERE id_desain_custom = $id");
        $row = $cek ? mysqli_fetch_assoc($cek) : null;

        if (($row['total'] ?? 0) > 0) {
            continue;
        }

        mysqli_query($db, "DELETE FROM desain_custom WHERE id_desain_custom = $id");
        if (mysqli_affected_rows($db) > 0) {
            $deletedIds[] = $id;
        }
    }

    return $deletedIds;
}

// Menyaring daftar file desain custom setelah sebagian desain custom terhapus.
function filter_file_desain_custom_terhapus(array $fileRefs, array $deletedCustomIds)
{
    $allowed = array_flip(array_map('intval', $deletedCustomIds));
    $fileRefs['desain_custom'] = array_intersect_key($fileRefs['desain_custom'] ?? [], $allowed);

    return $fileRefs;
}

// Menghapus data pesanan beserta transaksi, desain custom tak terpakai, dan file terkait.
function hapus_pesanan_dan_file($id_pesanan)
{
    global $db;

    $id_pesanan = intval($id_pesanan);
    if ($id_pesanan <= 0) {
        return 0;
    }

    $fileRefs = ambil_file_pesanan_terkait("p.id_pesanan = $id_pesanan");
    $customIds = array_keys($fileRefs['desain_custom']);

    mysqli_begin_transaction($db);
    mysqli_query($db, "DELETE FROM transaksi WHERE id_pesanan = $id_pesanan");
    mysqli_query($db, "DELETE FROM pesanan WHERE id_pesanan = $id_pesanan");
    $deleted = mysqli_affected_rows($db);

    if ($deleted > 0) {
        $deletedCustomIds = hapus_desain_custom_tidak_dipakai($customIds);
        $fileRefs = filter_file_desain_custom_terhapus($fileRefs, $deletedCustomIds);
        mysqli_commit($db);
        hapus_file_pesanan_terkait($fileRefs);
        return $deleted;
    }

    mysqli_rollback($db);
    return 0;
}

// Admin

// Akun Section

// Tambah Akun
// Menambahkan akun customer baru beserta data customer dan token verifikasi.
function tambah_akun($post)
{
    global $db;

    $nama      = htmlspecialchars(strip_tags($post['nama']));
    $username  = htmlspecialchars(strip_tags($post['username']));
    $password  = htmlspecialchars(strip_tags($post['password']));
    $email     = htmlspecialchars(strip_tags($post['email']));
    $no_hp     = htmlspecialchars(strip_tags($post['no_hp']));
    $alamat    = htmlspecialchars(strip_tags($post['alamat']));
    $role      = htmlspecialchars(strip_tags($post['role']));

    // cek role
    if (empty($role)) {
        echo "<script>
                        alert('Role wajib dipilih!');
                        document.location.href = 'admin_user.php';
                    </script>";

        return false;
    }

    // cek username atau email sudah ada atau belum
    $check = mysqli_query($db, "SELECT * FROM akun WHERE 
                username = '$username' OR email = '$email'");

    // Cek apakah username/email sudah di pakai
    if (mysqli_num_rows($check) > 0) {

        echo "
                    <script>
                        alert('Username atau Email sudah digunakan!');
                        window.location.href='admin_user.php';
                    </script>
                    ";

        exit;
    }

    // enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT);

    // insert ke tabel customer
    $queryCustomer = "INSERT INTO customer 
                    VALUES(NULL, '$nama', '$no_hp', '$alamat')
                ";

    mysqli_query($db, $queryCustomer);

    // ambil id customer terakhir
    $id_customer = mysqli_insert_id($db);

    // insert ke tabel akun
    $queryAkun = "INSERT INTO akun VALUES(
                NULL,'$id_customer','$username','$password',
                '$email','$role','1',NULL,NULL
                    )
                ";

    mysqli_query($db, $queryAkun);

    return mysqli_affected_rows($db);
}

// Ubah Akun
// Mengubah data akun customer dan profil customer terkait.
function ubah_akun($post)
{
    global $db;

    $id_akun        = htmlspecialchars(strip_tags($post['id_akun']));
    $id_customer    = htmlspecialchars(strip_tags($post['id_customer']));
    $nama           = htmlspecialchars(strip_tags($post['nama']));
    $username       = htmlspecialchars(strip_tags($post['username']));
    $password       = htmlspecialchars(strip_tags($post['password']));
    $email          = htmlspecialchars(strip_tags($post['email']));
    $no_hp          = htmlspecialchars(strip_tags($post['no_hp']));
    $alamat         = htmlspecialchars(strip_tags($post['alamat']));
    $role           = htmlspecialchars(strip_tags($post['role']));

    // cek role
    if (empty($role)) {
        echo "<script>
                        alert('Role wajib dipilih!');
                        document.location.href = 'admin_user.php';
                    </script>";

        return false;
    }

    // =========================
    // QUERY UBAH DATA CUSTOMER
    // =========================
    $queryCustomer = "UPDATE customer SET 
                    nama = '$nama',
                    no_hp = '$no_hp',
                    alamat = '$alamat'
                    WHERE id_customer = $id_customer";

    mysqli_query($db, $queryCustomer);

    $affectedCustomer = mysqli_affected_rows($db);

    // ======================
    // QUERY UBAH DATA AKUN
    // ======================

    // jika password diisi
    if (!empty($password)) {

        // enkripsi password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $queryAkun = "UPDATE akun SET 
                        email = '$email',
                        username = '$username',
                        password = '$passwordHash',
                        role = '$role'
                        WHERE id_akun = $id_akun";
    } else {

        // jika password kosong, password lama tidak diubah
        $queryAkun = "UPDATE akun SET 
                        email = '$email',
                        username = '$username',
                        role = '$role'
                        WHERE id_akun = $id_akun";
    }

    mysqli_query($db, $queryAkun);

    $affectedAkun = mysqli_affected_rows($db);

    // total perubahan
    $totalAffected = $affectedCustomer + $affectedAkun;

    return $totalAffected;
}

// Hapus Akun
// Menghapus akun customer beserta data pesanan dan file yang terkait.
function hapus_akun($post)
{
    global $db;

    $id = intval($post['id_customer'] ?? 0);

    if ($id <= 0) {
        return 0;
    }

    $fileRefs = ambil_file_pesanan_terkait("p.id_customer = $id");
    $fileRefs['desain_custom'] = $fileRefs['desain_custom'] + ambil_file_desain_custom_customer($id);

    mysqli_begin_transaction($db);

    mysqli_query($db, "DELETE t FROM transaksi t JOIN pesanan p ON t.id_pesanan = p.id_pesanan WHERE p.id_customer = $id");
    mysqli_query($db, "DELETE FROM pesanan WHERE id_customer = $id");
    mysqli_query($db, "DELETE FROM desain_custom WHERE id_customer = $id");
    mysqli_query($db, "DELETE FROM customer WHERE id_customer = $id");

    $deleted = mysqli_affected_rows($db);

    if ($deleted > 0) {
        mysqli_commit($db);
        hapus_file_pesanan_terkait($fileRefs);
        return $deleted;
    }

    mysqli_rollback($db);
    return 0;
}

// .Akun Section

// Bahan Section

// Tambah Bahan
// Menambahkan data bahan baru untuk produk konveksi.
function tambah_bahan($post)
{
    global $db;

    $jenis_bahan    = htmlspecialchars(strip_tags($post['jenis_bahan']));
    $id_warna       = htmlspecialchars(strip_tags($post['id_warna']));

    // insert ke tabel bahan
    $queryBahan = "INSERT INTO bahan (jenis_bahan, id_warna)
                    VALUES('$jenis_bahan', '$id_warna')";

    mysqli_query($db, $queryBahan);

    return mysqli_affected_rows($db);
}

// Ubah Bahan
// Mengubah data bahan yang sudah tersimpan.
function ubah_bahan($post)
{
    global $db;

    $id_bahan       = htmlspecialchars(strip_tags($post['id_bahan']));
    $jenis_bahan    = htmlspecialchars(strip_tags($post['jenis_bahan']));
    $id_warna       = htmlspecialchars(strip_tags($post['id_warna']));

    $queryBahan = "UPDATE bahan SET 
                    jenis_bahan = '$jenis_bahan',
                    id_warna = '$id_warna'
                    WHERE id_bahan = $id_bahan";

    mysqli_query($db, $queryBahan);

    return mysqli_affected_rows($db);
}

// Hapus Bahan
// Menghapus data bahan beserta pesanan dan file terkait.
function hapus_bahan($post)
{
    global $db;

    $id = intval($post['id_bahan'] ?? 0);

    if ($id <= 0) {
        return 0;
    }

    $fileRefs = ambil_file_pesanan_terkait("p.id_bahan = $id");
    $customIds = array_keys($fileRefs['desain_custom']);

    mysqli_begin_transaction($db);
    mysqli_query($db, "DELETE FROM bahan WHERE id_bahan = $id");
    $deleted = mysqli_affected_rows($db);

    if ($deleted > 0) {
        $deletedCustomIds = hapus_desain_custom_tidak_dipakai($customIds);
        $fileRefs = filter_file_desain_custom_terhapus($fileRefs, $deletedCustomIds);
        mysqli_commit($db);
        hapus_file_pesanan_terkait($fileRefs);
        return $deleted;
    }

    mysqli_rollback($db);
    return 0;
}

// .Bahan Section

// Bahan Section
// Tambah Warna
// Menambahkan data warna baru.
function tambah_warna($post)
{
    global $db;

    $nama_warna = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['nama_warna'])));

    $query = "INSERT INTO warna (nama_warna) VALUES('$nama_warna')";
    mysqli_query($db, $query);

    return mysqli_affected_rows($db);
}

// Ubah Warna
// Mengubah data warna yang sudah tersimpan.
function ubah_warna($post)
{
    global $db;

    $id_warna = htmlspecialchars(strip_tags($post['id_warna']));
    $nama_warna = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['nama_warna'])));

    $query = "UPDATE warna SET nama_warna = '$nama_warna' WHERE id_warna = $id_warna";
    mysqli_query($db, $query);

    return mysqli_affected_rows($db);
}

// Hapus Warna
// Menghapus warna beserta bahan, pesanan, dan file yang terkait.
function hapus_warna($post)
{
    global $db;

    $id = intval($post['id_warna'] ?? 0);

    if ($id <= 0) {
        return 0;
    }

    $fileRefs = ambil_file_pesanan_terkait("p.id_bahan IN (SELECT id_bahan FROM bahan WHERE id_warna = $id)");
    $customIds = array_keys($fileRefs['desain_custom']);

    mysqli_begin_transaction($db);
    mysqli_query($db, "DELETE FROM warna WHERE id_warna = $id");
    $deleted = mysqli_affected_rows($db);

    if ($deleted > 0) {
        $deletedCustomIds = hapus_desain_custom_tidak_dipakai($customIds);
        $fileRefs = filter_file_desain_custom_terhapus($fileRefs, $deletedCustomIds);
        mysqli_commit($db);
        hapus_file_pesanan_terkait($fileRefs);
        return $deleted;
    }

    mysqli_rollback($db);
    return 0;
}

// .Warna Section

// Produk Section

// Tambah Produk
// Menambahkan produk baru beserta gambar produk.
function tambah_produk($post, $files)
{
    global $db;

    $nama_produk = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['nama_produk'])));
    $deskripsi = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['deskripsi'])));
    $gambar_produk = upload_foto();

    $queryProduk = "INSERT INTO produk (nama_produk, deskripsi, gambar_produk)
                    VALUES('$nama_produk', '$deskripsi', " . ($gambar_produk ? "'$gambar_produk'" : "NULL") . ")
                ";

    mysqli_query($db, $queryProduk);
    $affected = mysqli_affected_rows($db);

    if ($affected <= 0 && $gambar_produk) {
        hapus_file_upload('produk', $gambar_produk);
    }

    return $affected;
}

// Ubah Produk
// Mengubah data produk dan mengganti gambar jika ada upload baru.
function ubah_produk($post, $files)
{
    global $db;

    $id_produk = intval($post['id_produk'] ?? 0);
    $nama_produk = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['nama_produk'])));
    $deskripsi = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['deskripsi'])));
    $produk_lama = select("SELECT gambar_produk FROM produk WHERE id_produk = $id_produk LIMIT 1");

    if (empty($produk_lama)) {
        return 0;
    }

    $gambar_lama = $produk_lama[0]['gambar_produk'] ?? null;
    $gambar_produk = $gambar_lama;
    $gambar_baru = null;

    if (isset($files['foto']) && $files['foto']['error'] === UPLOAD_ERR_OK) {
        $namaBaru = upload_foto();
        if ($namaBaru) {
            $gambar_baru = $namaBaru;
            $gambar_produk = mysqli_real_escape_string($db, $namaBaru);
        }
    }

    $queryProduk = "UPDATE produk SET
                    nama_produk = '$nama_produk',
                    deskripsi = '$deskripsi',
                    gambar_produk = " . ($gambar_produk ? "'$gambar_produk'" : "NULL") . "
                    WHERE id_produk = $id_produk";

    $success = mysqli_query($db, $queryProduk);
    $affected = mysqli_affected_rows($db);

    if ($success && $gambar_baru && $gambar_lama && $gambar_lama !== $gambar_baru) {
        hapus_file_upload('produk', $gambar_lama);
    } elseif (!$success && $gambar_baru) {
        hapus_file_upload('produk', $gambar_baru);
    }

    return $affected;
}

// Hapus Produk
// Menghapus produk beserta gambar, pesanan, dan file terkait.
function hapus_produk($post)
{
    global $db;

    $id = intval($post['id_produk'] ?? 0);

    if ($id <= 0) {
        return 0;
    }

    $produk = select("SELECT gambar_produk FROM produk WHERE id_produk = $id LIMIT 1");
    $gambar_produk = $produk[0]['gambar_produk'] ?? null;

    $gambar_desain = [];
    $resultDesain = mysqli_query($db, "SELECT gambar_desain FROM desain WHERE id_produk = $id");
    if ($resultDesain) {
        while ($row = mysqli_fetch_assoc($resultDesain)) {
            if (!empty($row['gambar_desain'])) {
                $gambar_desain[] = $row['gambar_desain'];
            }
        }
    }

    $fileRefs = ambil_file_pesanan_terkait("p.id_produk = $id");
    $customIds = array_keys($fileRefs['desain_custom']);

    mysqli_begin_transaction($db);
    mysqli_query($db, "DELETE FROM produk WHERE id_produk = $id");
    $deleted = mysqli_affected_rows($db);

    if ($deleted > 0) {
        $deletedCustomIds = hapus_desain_custom_tidak_dipakai($customIds);
        $fileRefs = filter_file_desain_custom_terhapus($fileRefs, $deletedCustomIds);
        mysqli_commit($db);

        hapus_file_upload('produk', $gambar_produk);
        hapus_file_uploads('desain', $gambar_desain);
        hapus_file_pesanan_terkait($fileRefs);

        return $deleted;
    }

    mysqli_rollback($db);
    return 0;
}

// .Produk Section

// Desain Section

// Tambah Desain
// Menambahkan desain katalog baru untuk produk tertentu.
function tambah_desain($post, $files)
{
    global $db;

    $id_produk = htmlspecialchars(strip_tags($post['id_produk']));
    $nama_desain = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['nama_desain'])));
    $deskripsi = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['deskripsi'])));
    $gambar_desain = upload_foto('foto', 'desain');
    $gambar_desain = $gambar_desain ?? '';

    $query = "INSERT INTO desain (id_produk, nama_desain, gambar_desain, deskripsi)
                    VALUES('$id_produk', '$nama_desain', '$gambar_desain', '$deskripsi')";

    mysqli_query($db, $query);
    $affected = mysqli_affected_rows($db);

    if ($affected <= 0 && $gambar_desain) {
        hapus_file_upload('desain', $gambar_desain);
    }

    return $affected;
}
// Ubah Desain
// Mengubah data desain katalog dan mengganti gambar jika diperlukan.
function ubah_desain($post, $files)
{
    global $db;

    $id_desain = intval($post['id_desain'] ?? 0);
    $id_produk = intval($post['id_produk'] ?? 0);
    $nama_desain = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['nama_desain'])));
    $deskripsi = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['deskripsi'])));
    $desain_lama = select("SELECT gambar_desain FROM desain WHERE id_desain = $id_desain LIMIT 1");

    if (empty($desain_lama)) {
        return 0;
    }

    $gambar_lama = $desain_lama[0]['gambar_desain'] ?? '';
    $gambar_desain = $gambar_lama;
    $gambar_baru = null;

    if (isset($files['foto']) && $files['foto']['error'] === UPLOAD_ERR_OK) {
        $namaBaru = upload_foto('foto', 'desain');
        if ($namaBaru) {
            $gambar_baru = $namaBaru;
            $gambar_desain = mysqli_real_escape_string($db, $namaBaru);
        }
    }

    $query = "UPDATE desain SET
                    id_produk = '$id_produk',
                    nama_desain = '$nama_desain',
                    gambar_desain = '$gambar_desain',
                    deskripsi = '$deskripsi'
                    WHERE id_desain = $id_desain";

    $success = mysqli_query($db, $query);
    $affected = mysqli_affected_rows($db);

    if ($success && $gambar_baru && $gambar_lama && $gambar_lama !== $gambar_baru) {
        hapus_file_upload('desain', $gambar_lama);
    } elseif (!$success && $gambar_baru) {
        hapus_file_upload('desain', $gambar_baru);
    }

    return $affected;
}
// Hapus Desain
// Menghapus desain katalog beserta gambar dan pesanan terkait.
function hapus_desain($post)
{
    global $db;

    $id = intval($post['id_desain'] ?? 0);

    if ($id <= 0) {
        return 0;
    }

    $desain = select("SELECT gambar_desain FROM desain WHERE id_desain = $id LIMIT 1");
    $gambar_desain = $desain[0]['gambar_desain'] ?? null;
    $fileRefs = ambil_file_pesanan_terkait("p.id_desain = $id");
    $customIds = array_keys($fileRefs['desain_custom']);

    mysqli_begin_transaction($db);
    mysqli_query($db, "DELETE FROM desain WHERE id_desain = $id");
    $deleted = mysqli_affected_rows($db);

    if ($deleted > 0) {
        $deletedCustomIds = hapus_desain_custom_tidak_dipakai($customIds);
        $fileRefs = filter_file_desain_custom_terhapus($fileRefs, $deletedCustomIds);
        mysqli_commit($db);

        hapus_file_upload('desain', $gambar_desain);
        hapus_file_pesanan_terkait($fileRefs);

        return $deleted;
    }

    mysqli_rollback($db);
    return 0;
}

// .Desain Section

// .Admin

// Customer

// Tambah Akun Baru
// Mendaftarkan akun baru dari halaman publik dan mengirim email verifikasi.
function daftar_akun_baru($post)
{
    global $db;

    require 'assets/vendor/autoload.php';

    $nama      = htmlspecialchars(strip_tags($post['nama']));
    $username  = htmlspecialchars(strip_tags($post['username']));
    $password  = htmlspecialchars(strip_tags($post['password']));
    $email     = htmlspecialchars(strip_tags($post['email']));
    $no_hp     = htmlspecialchars(strip_tags($post['no_hp']));
    $alamat    = htmlspecialchars(strip_tags($post['alamat']));

    // cek username atau email sudah ada atau belum
    $check = mysqli_query($db, "SELECT * FROM akun 
                WHERE username = '$username' 
                OR email = '$email'
            ");

    // Cek apakah username/email sudah di pakai
    if (mysqli_num_rows($check) > 0) {

        echo "
                <script>
                    alert('Username atau Email sudah digunakan!');
                    window.location.href='register.php';
                </script>
                ";

        exit;
    }

    // enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT);

    // token verifikasi
    $token = bin2hex(random_bytes(32));

    // expired 1 jam
    $expired = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // insert ke tabel customer
    $queryCustomer = "INSERT INTO customer 
                VALUES(NULL, '$nama', '$no_hp', '$alamat')
            ";

    mysqli_query($db, $queryCustomer);

    // ambil id customer terakhir
    $id_customer = mysqli_insert_id($db);

    // query tambah data akun
    $queryAkun = "INSERT INTO akun VALUES
            (
                NULL, '$id_customer', '$username', '$password', '$email',
                'Customer', '0', '$token', '$expired'
            )";

    mysqli_query($db, $queryAkun);

    // =========================
    // KIRIM EMAIL VERIFIKASI
    // =========================

    $link = " https://frosting-cadmium-critter.ngrok-free.dev/konveksi-app/verify.php?token=$token";

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        // gmail pengirim
        $mail->Username   = 'larissanoreply@gmail.com';

        // app password gmail
        $mail->Password   = 'gkfd gdxd vcsh fnpl';

        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('larissanoreply@gmail.com', 'Larissa Collection');

        $mail->addAddress($email);

        $mail->isHTML(true);

        $mail->Subject = 'Verifikasi Akun';

        $mail->Body = "
                <h2>Verifikasi Akun</h2>

                Klik link berikut untuk verifikasi akun:

                <br><br>

                <a href='$link'>$link</a>
                ";

        $mail->send();
    } catch (Exception $e) {


        echo "Mailer Error: " . $mail->ErrorInfo;
    }

    return mysqli_affected_rows($db);
}


// Memproses permintaan lupa sandi dan mengirim link reset ke email akun.
function lupa_sandi_akun($post)
{
    global $db;

    require 'assets/vendor/autoload.php';

    $email = trim($post['reset_email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'type' => 'danger',
            'message' => 'Format email tidak valid.',
        ];
    }

    $stmt = mysqli_prepare($db, "SELECT id_akun, username, is_verified FROM akun WHERE email = ? LIMIT 1");

    if (!$stmt) {
        return [
            'type' => 'danger',
            'message' => 'Permintaan reset sandi gagal diproses.',
        ];
    }

    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $idAkun, $username, $isVerified);
    $akunDitemukan = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$akunDitemukan) {
        return [
            'type' => 'danger',
            'message' => 'Email tidak terdaftar.',
        ];
    }

    if ((int) $isVerified !== 1) {
        return [
            'type' => 'warning',
            'message' => 'Akun belum diverifikasi. Silakan verifikasi akun terlebih dahulu.',
        ];
    }

    $token = bin2hex(random_bytes(32));
    $expired = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmtUpdate = mysqli_prepare($db, "UPDATE akun SET token = ?, token_expired = ? WHERE id_akun = ?");

    if (!$stmtUpdate) {
        return [
            'type' => 'danger',
            'message' => 'Permintaan reset sandi gagal diproses.',
        ];
    }

    mysqli_stmt_bind_param($stmtUpdate, 'ssi', $token, $expired, $idAkun);
    $updateBerhasil = mysqli_stmt_execute($stmtUpdate);
    mysqli_stmt_close($stmtUpdate);

    if (!$updateBerhasil) {
        return [
            'type' => 'danger',
            'message' => 'Permintaan reset sandi gagal diproses.',
        ];
    }

    $link = "https://frosting-cadmium-critter.ngrok-free.dev/konveksi-app/reset_sandi.php?token=$token";

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        // gmail pengirim
        $mail->Username   = 'larissanoreply@gmail.com';

        // app password gmail
        $mail->Password   = 'gkfd gdxd vcsh fnpl';

        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('larissanoreply@gmail.com', 'Larissa Collection');

        $mail->addAddress($email);

        $mail->isHTML(true);

        $mail->Subject = 'Reset Sandi Akun';

        $mail->Body = "
                <h2>Reset Sandi Akun</h2>
                <p>Halo " . htmlspecialchars($username) . ", klik link berikut untuk membuat sandi baru:</p>
                <p><a href='$link'>$link</a></p>
                <p>Link ini berlaku selama 1 jam.</p>
                ";

        $mail->send();
    } catch (Exception $e) {
        return [
            'type' => 'danger',
            'message' => 'Email reset sandi gagal dikirim.',
        ];
    }

    return [
        'type' => 'success',
        'message' => 'Link reset sandi sudah dikirim ke email Anda.',
    ];
}

// Memvalidasi token reset dan menyimpan password baru.
function reset_sandi_akun($post, $token)
{
    global $db;

    $token = trim($token);
    $password = $post['password'] ?? '';
    $konfirmasiPassword = $post['konfirmasi_password'] ?? '';

    if ($token === '') {
        return [
            'type' => 'danger',
            'message' => 'Token reset sandi tidak valid.',
        ];
    }

    if (strlen($password) < 5) {
        return [
            'type' => 'danger',
            'message' => 'Password baru minimal 5 karakter.',
        ];
    }

    if ($password !== $konfirmasiPassword) {
        return [
            'type' => 'danger',
            'message' => 'Konfirmasi password tidak sama.',
        ];
    }

    $stmt = mysqli_prepare($db, "SELECT id_akun, token_expired FROM akun WHERE token = ? AND is_verified = 1 LIMIT 1");

    if (!$stmt) {
        return [
            'type' => 'danger',
            'message' => 'Reset sandi gagal diproses.',
        ];
    }

    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $idAkun, $tokenExpired);
    $tokenDitemukan = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$tokenDitemukan) {
        return [
            'type' => 'danger',
            'message' => 'Token reset sandi tidak valid.',
        ];
    }

    if (empty($tokenExpired) || strtotime($tokenExpired) < time()) {
        return [
            'type' => 'danger',
            'message' => 'Token reset sandi sudah kedaluwarsa.',
        ];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmtUpdate = mysqli_prepare($db, "UPDATE akun SET password = ?, token = NULL, token_expired = NULL WHERE id_akun = ?");

    if (!$stmtUpdate) {
        return [
            'type' => 'danger',
            'message' => 'Reset sandi gagal diproses.',
        ];
    }

    mysqli_stmt_bind_param($stmtUpdate, 'si', $passwordHash, $idAkun);
    $updateBerhasil = mysqli_stmt_execute($stmtUpdate);
    mysqli_stmt_close($stmtUpdate);

    if (!$updateBerhasil) {
        return [
            'type' => 'danger',
            'message' => 'Reset sandi gagal diproses.',
        ];
    }

    return [
        'type' => 'success',
        'message' => 'Password berhasil diperbarui. Silakan login dengan password baru.',
    ];
}

// Pesanan Section

// Tambah Desain Custom
// Menyimpan desain custom customer beserta file tampak dan logo.
function tambah_desain_custom($post, $files)
{
    global $db;

    // Customer - Gunakan dari session jika user login
    if (isset($_SESSION['id_akun'])) {
        $id_akun = intval($_SESSION['id_akun']);
        $akun_data = select("SELECT id_customer FROM akun WHERE id_akun = $id_akun LIMIT 1");
        if (!empty($akun_data)) {
            $id_customer = intval($akun_data[0]['id_customer']);
        } else {
            return false;
        }
    } else {
        // Fallback: buat customer baru jika belum login
        $nama   = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['nama'] ?? '')));
        $no_hp  = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['hp'] ?? '')));
        $alamat = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['alamat'] ?? '')));

        $query = "SELECT id_customer FROM customer WHERE no_hp = '$no_hp' LIMIT 1";
        $res = mysqli_query($db, $query);
        if ($row = mysqli_fetch_assoc($res)) {
            $id_customer = $row['id_customer'];
        } else {
            $queryIns = "INSERT INTO customer VALUES(NULL, '$nama', '$no_hp', '$alamat')";
            mysqli_query($db, $queryIns);
            $id_customer = mysqli_insert_id($db);
        }
    }

    // Collect all uploaded files
    $uploadedFiles = [];
    $customDesignDir = 'desain_custom';

    // Upload design images
    $gambar_depan = upload_foto('tampak_depan', $customDesignDir);
    if ($gambar_depan) $uploadedFiles['depan'] = $gambar_depan;

    $gambar_belakang = upload_foto('tampak_belakang', $customDesignDir);
    if ($gambar_belakang) $uploadedFiles['belakang'] = $gambar_belakang;

    $gambar_kanan = upload_foto('tampak_kanan', $customDesignDir);
    if ($gambar_kanan) $uploadedFiles['kanan'] = $gambar_kanan;

    $gambar_kiri = upload_foto('tampak_kiri', $customDesignDir);
    if ($gambar_kiri) $uploadedFiles['kiri'] = $gambar_kiri;

    // Upload multiple logos
    $logoFiles = upload_logo_custom($files['logo'] ?? [], $customDesignDir);
    if ($logoFiles === false) {
        hapus_file_uploads($customDesignDir, daftar_file_desain_custom($uploadedFiles));
        return false;
    }

    if (!empty($logoFiles)) {
        $uploadedFiles['logo'] = $logoFiles;
    }

    // Format files as JSON or NULL
    $filesJson = !empty($uploadedFiles) ? mysqli_real_escape_string($db, json_encode($uploadedFiles)) : NULL;
    $filesValue = $filesJson !== NULL ? "'$filesJson'" : "NULL";

    // Combine all catatan
    $catatan_parts = [];
    if (!empty($post['catatan_depan'])) $catatan_parts[] = 'Depan: ' . htmlspecialchars(strip_tags($post['catatan_depan']));
    if (!empty($post['catatan_belakang'])) $catatan_parts[] = 'Belakang: ' . htmlspecialchars(strip_tags($post['catatan_belakang']));
    if (!empty($post['catatan_kanan'])) $catatan_parts[] = 'Kanan: ' . htmlspecialchars(strip_tags($post['catatan_kanan']));
    if (!empty($post['catatan_kiri'])) $catatan_parts[] = 'Kiri: ' . htmlspecialchars(strip_tags($post['catatan_kiri']));
    if (!empty($post['catatan_logo'])) $catatan_parts[] = 'Logo: ' . htmlspecialchars(strip_tags($post['catatan_logo']));

    $catatan = !empty($catatan_parts) ? mysqli_real_escape_string($db, implode("\n", $catatan_parts)) : NULL;
    $catatanValue = $catatan !== NULL ? "'$catatan'" : "NULL";

    $queryDesain = "INSERT INTO desain_custom (id_customer, files, catatan, status_desain)
                    VALUES($id_customer, $filesValue, $catatanValue, 'Menunggu')";

    if (mysqli_query($db, $queryDesain)) {
        return mysqli_insert_id($db);
    }

    hapus_file_uploads($customDesignDir, daftar_file_desain_custom($uploadedFiles));
    return false;
}

// Tambah Pesanan
// Menambahkan pesanan customer dari data form pemesanan.
function tambah_pesanan($post)
{
    global $db;

    // Customer: Ambil id_customer dari user yang login via session
    // User sudah ter-autentikasi, jadi gunakan id_customer yang sudah terdaftar
    if (!isset($_SESSION['id_akun'])) {
        return false; // User tidak login
    }

    $id_akun = intval($_SESSION['id_akun']);
    $akun_data = select("SELECT id_customer FROM akun WHERE id_akun = $id_akun LIMIT 1");

    if (empty($akun_data)) {
        return false; // Akun tidak valid
    }

    $id_customer = intval($akun_data[0]['id_customer']);

    // Update data customer dengan info terbaru dari form (jika ada perubahan)
    $nama   = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['nama'] ?? '')));
    $no_hp  = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['hp'] ?? '')));
    $alamat = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['alamat'] ?? '')));

    $queryUpdate = "UPDATE customer SET nama = '$nama', no_hp = '$no_hp', alamat = '$alamat' WHERE id_customer = $id_customer";
    mysqli_query($db, $queryUpdate);

    // Produk
    $id_produk = intval($post['id_produk'] ?? 1);

    // Design (uploaded or existing)
    $id_desain = isset($post['id_desain']) ? intval($post['id_desain']) : null;
    $id_desain_custom = isset($post['id_desain_custom']) ? intval($post['id_desain_custom']) : null;

    // Bahan
    $id_bahan = intval($post['id_bahan'] ?? 0);

    // Sizes
    $sizes = [
        'S' => intval($post['size_s'] ?? 0),
        'M' => intval($post['size_m'] ?? 0),
        'L' => intval($post['size_l'] ?? 0),
        'XL' => intval($post['size_xl'] ?? 0),
        'XXL' => intval($post['size_xxl'] ?? 0),
        'XXXL' => intval($post['size_xxxl'] ?? 0),
    ];
    $jumlah_beli = array_sum($sizes);
    $ukuran_json = mysqli_real_escape_string($db, json_encode($sizes));

    // Harga pesanan ditentukan oleh admin setelah pesanan dibuat.
    $harga = 0;
    $harga_dp = 0;
    $status_harga = 'Menunggu Harga';

    // Insert pesanan - id_desain_custom bisa null jika pilih design existing
    $queryPesanan = "INSERT INTO pesanan (id_customer, id_produk, id_bahan, id_desain, id_desain_custom, jumlah_beli, ukuran, harga, harga_dp, total_harga, catatan_harga, status_harga, tanggal_pesan, tanggal_selesai)
                    VALUES($id_customer, $id_produk, $id_bahan, " . ($id_desain ? $id_desain : 'NULL') . ", " . ($id_desain_custom ? $id_desain_custom : 'NULL') . ", $jumlah_beli, '$ukuran_json', $harga, $harga_dp, NULL, NULL, '$status_harga', NOW(), NULL)";

    mysqli_query($db, $queryPesanan);
    return mysqli_insert_id($db);
}

// Tambah Transaksi
// - Menerima data metode pembayaran dan total harga
// - Menyimpan record transaksi terkait `pesanan`
// - Jika tersedia, simpan nama file bukti pembayaran (gambar) ke kolom `bukti_pembayaran`
// Parameter:
//   $post: array POST data dari form
//   $id_pesanan: id pesanan yang terkait
//   $total_harga: total harga pesanan (dari tabel pesanan)
//   $bukti_filename: (optional) nama file bukti yang sudah di-upload
// Menentukan nilai bukti pembayaran berdasarkan metode pembayaran.
function nilai_bukti_pembayaran($metode, $bukti_filename = null)
{
    global $db;

    if (!empty($bukti_filename)) {
        return mysqli_real_escape_string($db, $bukti_filename);
    }

    if ($metode === 'qris') {
        return 'QRIS';
    }

    if ($metode === 'virtual_account') {
        return 'VA BCA 1234567890';
    }

    return strtoupper($metode);
}

// Menambahkan transaksi pembayaran untuk pesanan tertentu.
function tambah_transaksi($post, $id_pesanan, $total_harga, $bukti_filename = null)
{
    global $db;

    $metode = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['metode_pembayaran'] ?? '')));
    $jenis_pembayaran = htmlspecialchars(strip_tags($post['jenis_pembayaran'] ?? ''));
    $total_harga = max(0, intval($total_harga));
    $jumlah_bayar = max(0, intval($post['jumlah_bayar'] ?? 0));
    $metodeValid = ['qris', 'virtual_account', 'transfer', 'cash'];

    if (
        !in_array($metode, $metodeValid, true) ||
        !in_array($jenis_pembayaran, ['dp', 'lunas'], true) ||
        $id_pesanan <= 0 ||
        $total_harga <= 0
    ) {
        return false;
    }

    if ($jenis_pembayaran === 'lunas') {
        $jumlah_bayar = $total_harga;
    } elseif ($jumlah_bayar < intdiv($total_harga + 1, 2) || $jumlah_bayar >= $total_harga) {
        return false;
    }

    $status_pembayaran = 'Pending';
    $bukti_pembayaran = nilai_bukti_pembayaran($metode, $bukti_filename);

    $tanggal = date('Y-m-d');

    $queryTransaksi = "INSERT INTO transaksi (id_pesanan, metode_pembayaran, status_pembayaran, jumlah_bayar, bukti_pembayaran, tanggal_pembayaran)
                    VALUES($id_pesanan, '$metode', '$status_pembayaran', $jumlah_bayar, '$bukti_pembayaran', '$tanggal')";

    mysqli_query($db, $queryTransaksi);
    return mysqli_insert_id($db);
}

// Mengubah nilai DP pada data pesanan.
function update_harga_dp_pesanan($id_pesanan, $harga_dp)
{
    global $db;
    $harga_dp = intval($harga_dp);
    $query = "UPDATE pesanan SET harga_dp = $harga_dp WHERE id_pesanan = $id_pesanan";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}

// .Pesanan Section

// .Customer
