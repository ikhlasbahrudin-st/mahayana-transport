<?php
session_start();
include '../config/koneksi.php';

$id_token_input = $_POST['id_token'] ?? $_POST['credential'] ?? null;

if ($id_token_input) {
    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token_input;
    $data = json_decode(file_get_contents($url), true);

    if (isset($data['email'])) {
        $email = mysqli_real_escape_string($conn, $data['email']);
        
        // Cek data di database Mahayana
        $check_user = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        $user = mysqli_fetch_assoc($check_user);

        // Jika user ditemukan, masukkan SEMUA data dari database ke Session
        if ($user) {
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['fullname']     = $user['fullname'];
            $_SESSION['email']        = $user['email'];
            $_SESSION['phone']        = $user['phone'] ?? '-'; 
            $_SESSION['role']         = $user['role'];
            $_SESSION['user_picture'] = $data['picture']; // Update dengan foto terbaru Google
            $_SESSION['created_at']   = $user['created_at'];

            // Opsional: Update foto di DB agar sinkron
            mysqli_query($conn, "UPDATE users SET user_picture='{$data['picture']}' WHERE email='$email'");
        }

        header("Location: ../index.php");
        exit();
    }
} else {
    echo "<script>alert('Login gagal: Token tidak ditemukan!'); window.location='login.php';</script>";
    exit();
}