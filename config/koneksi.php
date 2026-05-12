<?php
$host     = "localhost";
$username = "root";
$password = "";
$database = "mahayana_wisata";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

date_default_timezone_set('Asia/Jakarta');
?>