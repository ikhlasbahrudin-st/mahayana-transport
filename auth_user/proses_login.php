<?php
session_start();
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($query);

    if ($user && password_verify($password, $user['password'])) {
        // --- PINDAHKAN SEMUA DATA DARI DATABASE KE SESSION ---
        $_SESSION['user_id']      = $user['id'];
        $_SESSION['fullname']     = $user['fullname'];
        $_SESSION['email']        = $user['email'];
        $_SESSION['phone']        = $user['phone'] ?? '-'; // Jika NULL di DB, tampilkan '-'
        $_SESSION['role']         = $user['role'];
        $_SESSION['created_at']   = $user['created_at'];
        
        // Logika Foto: Jika di DB ada URL/Path, pakai itu. Jika tidak, pakai UI Avatars
        if (!empty($user['user_picture'])) {
            $_SESSION['user_picture'] = $user['user_picture'];
        } else {
            $_SESSION['user_picture'] = 'https://ui-avatars.com/api/?name=' . urlencode($user['fullname']);
        }

        header("Location: ../index.php");
        exit();
    } else {
        echo "<script>alert('Email atau Password salah!'); window.location='login.php';</script>";
    }
}