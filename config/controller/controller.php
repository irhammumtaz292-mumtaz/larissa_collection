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

    // Akun Section

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
            $check = mysqli_query($db, "
                SELECT * FROM akun 
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
            
            // query tambah data
            $query = "INSERT INTO akun 
            VALUES(
            NULL,
            '$nama',
            '$username',
            '$password',
            '$email',
            '$no_hp',
            '$alamat',
            'User',
            '0',
            '$token',
            '$expired'
            )";
            
            mysqli_query($db, $query);

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
                $mail->Password   = 'qjmo slnw slev kwff';

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

                // 
            }
            
            return mysqli_affected_rows($db);
        }

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
    // .Bahan Section

?>