<?php
require '../config/koneksi.php';

header('Content-Type: application/json');

$code = $_POST['code'] ?? '';

// =========================
// VALIDASI INPUT
// =========================
if (!$code) {
    echo json_encode([
        "status" => "error",
        "message" => "QR kosong"
    ]);
    exit;
}

$code = mysqli_real_escape_string($conn, $code);

// =========================
// AMBIL DATA BOOKING
// =========================
$q = mysqli_query($conn, "
    SELECT * FROM bookings 
    WHERE booking_code='$code'
    LIMIT 1
");

$data = mysqli_fetch_assoc($q);

// =========================
// CEK EXIST
// =========================
if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "Tiket tidak ditemukan"
    ]);
    exit;
}

// =========================
// CEK SUDAH DIPAKAI QR
// =========================
if ((int)$data['qr_used'] === 1) {
    echo json_encode([
        "status" => "error",
        "message" => "Tiket sudah dipakai"
    ]);
    exit;
}

// =========================
// UPDATE: QR + STATUS TRAVEL
// =========================
$update = mysqli_query($conn, "
    UPDATE bookings 
    SET 
        qr_used = 1,
        qr_used_at = NOW(),
        travel_status = 'on_progress'
    WHERE booking_code = '$code'
");

// =========================
// RESPONSE
// =========================
if ($update) {
    echo json_encode([
        "status" => "success",
        "message" => "Tiket valid - perjalanan dimulai",
        "travel_status" => "on_progress"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal update tiket"
    ]);
}
?>