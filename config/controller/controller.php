<?php

    // Variabel
        $popup = false;
        $statusPopup = '';
        $warnaPopup = '';
        $popupEksekusi = '';
    // .Variabel

    // Fungsi Read
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

    // fungsi mengupload foto
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

    // Admin

        // Akun Section

            // Tambah Akun
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
            function hapus_akun($post)
            {
                global $db;
                
                $id = strip_tags($post['id_customer']);
                
                // query hapus data pengguna
                $query = "DELETE FROM customer WHERE id_customer = $id";
                
                mysqli_query($db, $query);
                
                return mysqli_affected_rows($db);
            }

        // .Akun Section

        // Bahan Section

            // Tambah Bahan
            function tambah_bahan($post)
            {
                global $db;

                $jenis_bahan    = htmlspecialchars(strip_tags($post['jenis_bahan']));
                $id_warna       = htmlspecialchars(strip_tags($post['id_warna']));
                $stok           = htmlspecialchars(strip_tags($post['stok']));
                $harga_bahan    = htmlspecialchars(strip_tags($post['harga_bahan']));

                // insert ke tabel bahan
                $queryBahan = "INSERT INTO bahan 
                    VALUES(NULL, '$jenis_bahan', '$id_warna', '$stok', '$harga_bahan')
                ";

                mysqli_query($db, $queryBahan);

                return mysqli_affected_rows($db);
            }

            // Ubah Bahan
            function ubah_bahan($post)
            {
                global $db;
                
                $id_bahan       = htmlspecialchars(strip_tags($post['id_bahan']));
                $jenis_bahan    = htmlspecialchars(strip_tags($post['jenis_bahan']));
                $id_warna       = htmlspecialchars(strip_tags($post['id_warna']));
                $stok           = htmlspecialchars(strip_tags($post['stok']));
                $harga_bahan    = htmlspecialchars(strip_tags($post['harga_bahan']));

                $queryBahan = "UPDATE bahan SET 
                    jenis_bahan = '$jenis_bahan',
                    id_warna = '$id_warna',
                    stok = '$stok',
                    harga_bahan = '$harga_bahan'
                    WHERE id_bahan = $id_bahan";

                mysqli_query($db, $queryBahan);

                return mysqli_affected_rows($db);

            }

            // Hapus Bahan
            function hapus_bahan($post)
            {
                global $db;
                
                $id = strip_tags($post['id_bahan']);
                
                // query hapus data pengguna
                $query = "DELETE FROM bahan WHERE id_bahan = $id";
                
                mysqli_query($db, $query);
                
                return mysqli_affected_rows($db);
            }
        
        // .Bahan Section

            // Tambah Warna
            function tambah_warna($post)
            {
                global $db;

                $nama_warna = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['nama_warna'])));

                $query = "INSERT INTO warna (nama_warna) VALUES('$nama_warna')";
                mysqli_query($db, $query);

                return mysqli_affected_rows($db);
            }

            // Ubah Warna
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
            function hapus_warna($post)
            {
                global $db;

                $id = strip_tags($post['id_warna']);
                $query = "DELETE FROM warna WHERE id_warna = $id";
                mysqli_query($db, $query);

                return mysqli_affected_rows($db);
            }

        // .Warna Section

        // Produk Section

            // Tambah Produk
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

                return mysqli_affected_rows($db);
            }

            // Ubah Produk
            function ubah_produk($post, $files)
            {
                global $db;

                $id_produk = htmlspecialchars(strip_tags($post['id_produk']));
                $nama_produk = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['nama_produk'])));
                $deskripsi = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['deskripsi'])));
                $gambar_produk = isset($post['existing_gambar_produk']) ? htmlspecialchars(strip_tags($post['existing_gambar_produk'])) : null;

                if (isset($files['foto']) && $files['foto']['error'] === UPLOAD_ERR_OK) {
                    $namaBaru = upload_foto();
                    if ($namaBaru) {
                        $uploadDir = __DIR__ . '/../../assets/img/produk/';
                        if ($gambar_produk && file_exists($uploadDir . $gambar_produk)) {
                            @unlink($uploadDir . $gambar_produk);
                        }
                        $gambar_produk = mysqli_real_escape_string($db, $namaBaru);
                    }
                }

                $queryProduk = "UPDATE produk SET
                    nama_produk = '$nama_produk',
                    deskripsi = '$deskripsi',
                    gambar_produk = " . ($gambar_produk ? "'$gambar_produk'" : "NULL") . "
                    WHERE id_produk = $id_produk";

                mysqli_query($db, $queryProduk);

                return mysqli_affected_rows($db);
            }

            // Hapus Produk
            function hapus_produk($post)
            {
                global $db;

                $id = strip_tags($post['id_produk']);
                $gambar_produk = null;

                $query = "SELECT gambar_produk FROM produk WHERE id_produk = $id";
                $result = mysqli_query($db, $query);
                if ($row = mysqli_fetch_assoc($result)) {
                    $gambar_produk = $row['gambar_produk'];
                }

                $query = "DELETE FROM produk WHERE id_produk = $id";
                mysqli_query($db, $query);
                $deleted = mysqli_affected_rows($db);

                if ($deleted && $gambar_produk) {
                    $filePath = __DIR__ . '/../../assets/img/produk/' . $gambar_produk;
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }

                return $deleted;
            }

        // .Produk Section

        // Desain Section

            // Tambah Desain
            function tambah_desain($post, $files)
            {
                global $db;

                $id_produk = htmlspecialchars(strip_tags($post['id_produk']));
                $nama_desain = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['nama_desain'])));
                $harga_desain = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['harga_desain'])));
                $deskripsi = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['deskripsi'])));
                $gambar_desain = upload_foto('foto', 'desain');
                $gambar_desain = $gambar_desain ?? '';

                $query = "INSERT INTO desain (id_produk, nama_desain, gambar_desain, harga_desain, deskripsi)
                    VALUES('$id_produk', '$nama_desain', '$gambar_desain', '$harga_desain', '$deskripsi')";

                mysqli_query($db, $query);
                return mysqli_affected_rows($db);
            }
            // Ubah Desain
            function ubah_desain($post, $files)
            {
                global $db;

                $id_desain = htmlspecialchars(strip_tags($post['id_desain']));
                $id_produk = htmlspecialchars(strip_tags($post['id_produk']));
                $nama_desain = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['nama_desain'])));
                $harga_desain = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['harga_desain'])));
                $deskripsi = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['deskripsi'])));
                $gambar_desain = isset($post['existing_gambar_desain']) ? htmlspecialchars(strip_tags($post['existing_gambar_desain'])) : '';

                if (isset($files['foto']) && $files['foto']['error'] === UPLOAD_ERR_OK) {
                    $namaBaru = upload_foto('foto', 'desain');
                    if ($namaBaru) {
                        $uploadDir = __DIR__ . '/../../assets/img/desain/';
                        if ($gambar_desain && file_exists($uploadDir . $gambar_desain)) {
                            @unlink($uploadDir . $gambar_desain);
                        }
                        $gambar_desain = mysqli_real_escape_string($db, $namaBaru);
                    }
                }

                $query = "UPDATE desain SET
                    id_produk = '$id_produk',
                    nama_desain = '$nama_desain',
                    gambar_desain = '$gambar_desain',
                    harga_desain = '$harga_desain',
                    deskripsi = '$deskripsi'
                    WHERE id_desain = $id_desain";

                mysqli_query($db, $query);
                return mysqli_affected_rows($db);
            }
            // Hapus Desain
            function hapus_desain($post)
            {
                global $db;

                $id = strip_tags($post['id_desain']);
                $gambar_desain = null;

                $query = "SELECT gambar_desain FROM desain WHERE id_desain = $id";
                $result = mysqli_query($db, $query);
                if ($row = mysqli_fetch_assoc($result)) {
                    $gambar_desain = $row['gambar_desain'];
                }

                $query = "DELETE FROM desain WHERE id_desain = $id";
                mysqli_query($db, $query);
                $deleted = mysqli_affected_rows($db);

                if ($deleted && $gambar_desain) {
                    $filePath = __DIR__ . '/../../assets/img/desain/' . $gambar_desain;
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }

                return $deleted;
            }

        // .Desain Section

    // .Admin

    // Customer

        // Tambah Akun Baru
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

            $link = "https://detention-eggbeater-managing.ngrok-free.dev/konveksi-app/verify.php?token=$token";

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

    // .Customer

    // Pesanan Section

        // Tambah Desain Custom
        function tambah_desain_custom($post, $files)
        {
            global $db;

            // Customer (buat jika belum ada berdasarkan no_hp)
            $nama   = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['nama'] ?? '')));
            $no_hp  = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['hp'] ?? '')));
            $alamat = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['alamat'] ?? '')));

            // coba cari customer berdasarkan no_hp
            $query = "SELECT id_customer FROM customer WHERE no_hp = '$no_hp' LIMIT 1";
            $res = mysqli_query($db, $query);
            if ($row = mysqli_fetch_assoc($res)) {
                $id_customer = $row['id_customer'];
            } else {
                $queryIns = "INSERT INTO customer VALUES(NULL, '$nama', '$no_hp', '$alamat')";
                mysqli_query($db, $queryIns);
                $id_customer = mysqli_insert_id($db);
            }

            // Upload design images
            $gambar_depan = upload_foto('tampak_depan', 'desain') ?? '';
            $gambar_belakang = upload_foto('tampak_belakang', 'desain') ?? '';
            $gambar_kanan = upload_foto('tampak_kanan', 'desain') ?? '';
            $gambar_kiri = upload_foto('tampak_kiri', 'desain') ?? '';

            // Upload multiple logos
            $logoFiles = [];
            if (isset($files['logo'])) {
                $logos = $files['logo'];
                $count = is_array($logos['name']) ? count($logos['name']) : 0;
                for ($i = 0; $i < $count; $i++) {
                    if ($logos['error'][$i] === UPLOAD_ERR_OK) {
                        $tmpName = $logos['tmp_name'][$i];
                        $orig = $logos['name'][$i];
                        $ext = pathinfo($orig, PATHINFO_EXTENSION);
                        $newName = uniqid() . "." . $ext;
                        $uploadPath = __DIR__ . '/../../assets/img/desain/';
                        if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);
                        move_uploaded_file($tmpName, $uploadPath . $newName);
                        $logoFiles[] = $newName;
                    }
                }
            }

            $gambar_logo = mysqli_real_escape_string($db, json_encode($logoFiles));

            $catatan_depan = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['catatan_depan'] ?? '')));
            $catatan_belakang = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['catatan_belakang'] ?? '')));
            $catatan_kanan = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['catatan_kanan'] ?? '')));
            $catatan_kiri = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['catatan_kiri'] ?? '')));
            $catatan_logo = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['catatan_logo'] ?? '')));

            $queryDesain = "INSERT INTO desain_custom (id_customer, gambar_depan, catatan_depan, gambar_belakang, catatan_belakang, gambar_kanan, catatan_kanan, gambar_kiri, catatan_kiri, gambar_logo, catatan_logo, status_desain)
                VALUES($id_customer, '$gambar_depan', '$catatan_depan', '$gambar_belakang', '$catatan_belakang', '$gambar_kanan', '$catatan_kanan', '$gambar_kiri', '$catatan_kiri', '$gambar_logo', '$catatan_logo', 'Menunggu')";

            mysqli_query($db, $queryDesain);
            return mysqli_insert_id($db);
        }

        // Tambah Pesanan
        function tambah_pesanan($post)
        {
            global $db;

            // Customer
            $nama   = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['nama'] ?? '')));
            $no_hp  = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['hp'] ?? '')));
            $alamat = mysqli_real_escape_string($db, htmlspecialchars(strip_tags($post['alamat'] ?? '')));

            // coba cari customer berdasarkan no_hp
            $query = "SELECT id_customer FROM customer WHERE no_hp = '$no_hp' LIMIT 1";
            $res = mysqli_query($db, $query);
            if ($row = mysqli_fetch_assoc($res)) {
                $id_customer = $row['id_customer'];
            } else {
                $queryIns = "INSERT INTO customer VALUES(NULL, '$nama', '$no_hp', '$alamat')";
                mysqli_query($db, $queryIns);
                $id_customer = mysqli_insert_id($db);
            }

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

            // Harga dasar dari bahan
            $harga = 0;
            if ($id_bahan) {
                $q = mysqli_query($db, "SELECT harga_bahan FROM bahan WHERE id_bahan = $id_bahan LIMIT 1");
                if ($r = mysqli_fetch_assoc($q)) {
                    $harga_bahan = intval($r['harga_bahan']);
                    $harga = $harga_bahan * $jumlah_beli;
                }
            }

            // Insert pesanan - id_desain_custom bisa null jika pilih design existing
            $queryPesanan = "INSERT INTO pesanan (id_customer, id_bahan, id_desain, id_desain_custom, jumlah_beli, ukuran, harga)
                VALUES($id_customer, $id_bahan, " . ($id_desain ? $id_desain : 'NULL') . ", " . ($id_desain_custom ? $id_desain_custom : 'NULL') . ", $jumlah_beli, '$ukuran_json', $harga)";

            mysqli_query($db, $queryPesanan);
            return mysqli_insert_id($db);
        }

    // .Pesanan Section

?>