<?php

session_start();

// hapus semua session
$_SESSION = [];

// hancurkan session
session_unset();
session_destroy();

// redirect ke login
header("Location: ./");
exit;