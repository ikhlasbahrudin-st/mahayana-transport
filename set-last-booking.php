<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/koneksi.php';

header('Content-Type: text/plain');

$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;

if ($booking_id <= 0) {
    echo "ERROR";
    exit;
}

/* =========================
   (OPTIONAL BUT RECOMMENDED)
   VALIDASI BOOKING MILIK USER
========================= */
if (isset($_SESSION['user_id'])) {

    $user_id = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT id 
        FROM bookings 
        WHERE id = ? AND user_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if (!$res) {
        echo "INVALID";
        exit;
    }
}

/* =========================
   SIMPAN SESSION
========================= */
$_SESSION['last_booking_id'] = $booking_id;

echo "OK";