<?php
// config.php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Sesuaikan dengan username MySQL Anda
define('DB_PASSWORD', '');     // Sesuaikan dengan password MySQL Anda
define('DB_NAME', 'footballgear_db');

// Koneksi ke database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Mulai session
session_start();
?>