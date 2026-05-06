<?php

require_once ("config/db/db.php");

$token = $_GET['token'];

$data = mysqli_query($db,
"SELECT * FROM akun WHERE token='$token'");

$user = mysqli_fetch_assoc($data);

if ($user) {

    // cek expired
    if (strtotime($user['token_expired']) < time()) {

        echo "Token expired!";
        exit;
    }

    // aktifkan akun
    mysqli_query($db,
    "UPDATE akun
    SET is_verified='1',
    token=NULL
    WHERE token='$token'");

    echo "<h1 style='font-size: 50px;'>Akun berhasil diverifikasi!</h1>";
    echo "<h2>Klik : <a href='https://detention-eggbeater-managing.ngrok-free.dev/konveksi-app/login.php' target='_blank'>Login</a></h2>";

} else {

    echo "Token tidak valid!";
}
?>