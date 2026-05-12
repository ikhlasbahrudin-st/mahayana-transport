<?php
include '../config/koneksi.php';
session_start();

// CEK APAKAH INI DARI GOOGLE ATAU FORM MANUAL
if (isset($_POST['id_token'])) {
    // --- PROSES GOOGLE REGISTER/LOGIN ---
    $id_token = $_POST['id_token'];

    // Decode JWT Payload untuk mengambil data user
    $payload = explode('.', $id_token)[1];
    $decoded_payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);

    if ($decoded_payload) {
        $fullname     = mysqli_real_escape_string($conn, $decoded_payload['name']);
        $email        = mysqli_real_escape_string($conn, $decoded_payload['email']);
        $user_picture = mysqli_real_escape_string($conn, $decoded_payload['picture']); // URL Foto Google
        $password     = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT); 
    } else {
        die("Token Google tidak valid.");
    }
} else {
    // --- PROSES FORM MANUAL ---
    $fullname     = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email        = mysqli_real_escape_string($conn, $_POST['email']);
    $password     = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_picture = "default.png"; // Foto default jika daftar manual
}

// 1. Cek apakah email sudah ada di database
$cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

if (mysqli_num_rows($cek) > 0) {
    // JIKA USER SUDAH ADA
    $user_data = mysqli_fetch_assoc($cek);

    if (isset($_POST['id_token'])) {
        // Jika login via Google, update foto profil terbaru dari Google (opsional)
        mysqli_query($conn, "UPDATE users SET user_picture='$user_picture' WHERE email='$email'");
        
        $_SESSION['user_id']  = $user_data['id'];
        $_SESSION['fullname'] = $user_data['fullname'];
        $_SESSION['picture']  = $user_picture; // Simpan foto di session agar bisa tampil di navbar
        
        echo "<script>alert('Selamat datang kembali!'); window.location='../index.php';</script>";
    } else {
        echo "<script>alert('Email sudah digunakan!'); window.location='register.php';</script>";
    }
} else {
    // JIKA USER BELUM ADA (DAFTAR BARU)
    // Masukkan fullname, email, password, role, dan user_picture
    $sql = "INSERT INTO users (fullname, email, password, role, user_picture) 
            VALUES ('$fullname', '$email', '$password', 'user', '$user_picture')";
    
    if (mysqli_query($conn, $sql)) {
        if (isset($_POST['id_token'])) {
            $new_id = mysqli_insert_id($conn);
            $_SESSION['user_id']  = $new_id;
            $_SESSION['fullname'] = $fullname;
            $_SESSION['picture']  = $user_picture;
            
            echo "<script>alert('Berhasil Daftar dengan Google!'); window.location='../index.php';</script>";
        } else {
            echo "<script>alert('Berhasil Daftar! Silakan Login.'); window.location='login.php';</script>";
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>