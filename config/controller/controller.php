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

        // Tambah Akun
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
                $mail->Username   = 'bulmyre@gmail.com';

                // app password gmail
                $mail->Password   = 'fvun rzvk octn vwdx';

                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('bulmyre@gmail.com', 'Larissa Collection');

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
            $role     = htmlspecialchars(strip_tags($post['role']));

            // Cek role
            if (empty($_POST['role'])) {
                echo "<script>
                alert('Role wajib dipilih!');
                document.location.href = 'admin_user.php';
              </script>";
            }
            
            // enkripsi password
            $password = password_hash($password, PASSWORD_DEFAULT);
            
            // query tambah data
            $query = "INSERT INTO akun VALUES(NULL, '$nama', '$username', '$password', '$email', '$no_hp', '$alamat', '$role', '1', 'Dibuat oleh admin', '')";
            
            mysqli_query($db, $query);
            
            return mysqli_affected_rows($db);
        }

        // Ubah Akun
        function ubah_akun($post)
        {
            global $db;
            
            $id        = htmlspecialchars(strip_tags($post['id_akun']));
            $nama      = htmlspecialchars(strip_tags($post['nama']));
            $username  = htmlspecialchars(strip_tags($post['username']));
            $password  = htmlspecialchars(strip_tags($post['password']));
            $email     = htmlspecialchars(strip_tags($post['email']));
            $no_hp     = htmlspecialchars(strip_tags($post['no_hp']));
            $alamat    = htmlspecialchars(strip_tags($post['alamat']));
            $role     = htmlspecialchars(strip_tags($post['role']));

            // enkripsi password
            $password = password_hash($password, PASSWORD_DEFAULT);
            
            // query ubah data
            $query = "UPDATE akun SET nama = '$nama', alamat = '$alamat', no_hp = '$no_hp', email = '$email', username = '$username', password = '$password', role = '$role' WHERE id_akun = $id";
            
            mysqli_query($db, $query);

            return mysqli_affected_rows($db);

        }

        // Hapus Akun
        function hapus_akun($post)
        {
            global $db;
            
            $id = strip_tags($post['id_akun']);
            //$fotoLama = strip_tags($post['fotoLama']);

            // Hapus Foto
            //$filePoto = '../assets/client/foto/' . $fotoLama;

            // if (file_exists($filePoto)) {
            // if (unlink($filePoto)) {
            //     print "Foto Berhasil Di Hapus";
            // } else {
            //     print "Gagal Menghapus Foto";
            // }
            // } else {
            // print "Foto Tidak Di temukan";
            // }
            
            // query hapus data mapel
            $query = "DELETE FROM akun WHERE id_akun = $id";
            
            mysqli_query($db, $query);
            
            return mysqli_affected_rows($db);
        }

    // .Akun Section

?>