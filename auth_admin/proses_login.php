<?php
session_start();
include '../config/koneksi.php';

$email = $_POST['email'];
$password = $_POST['password'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND role='admin'");
$user = mysqli_fetch_assoc($query);

if ($user && password_verify($password, $user['password'])) {

    $_SESSION['admin'] = $user;

    header("Location: ../admin/dashboard.php");

} else {
    echo "Login admin gagal!";
}