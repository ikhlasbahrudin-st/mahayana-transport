<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/koneksi.php';
header('Content-Type: application/json');

/* =========================
   VALIDASI LOGIN
========================= */
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$booking_id = isset($_POST['booking_id']) ? (int) $_POST['booking_id'] : 0;

if ($booking_id <= 0) {
    echo json_encode(['status' => 'invalid']);
    exit;
}

mysqli_begin_transaction($conn);

try {

    /* =========================
       AMBIL BOOKING
    ========================= */
    $stmt = $conn->prepare("
        SELECT id, group_code, booking_code, status
        FROM bookings
        WHERE id = ? AND user_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();

    if (!$booking) {
        throw new Exception("Booking tidak ditemukan");
    }

    $group_code = $booking['group_code'];

    /* =========================
       JIKA SUDAH PAID
       (cleanup token saja)
    ========================= */
    if ($booking['status'] === 'paid') {

        mysqli_query($conn, "
            UPDATE payments
            SET snap_token = NULL
            WHERE group_code = '$group_code'
        ");

        mysqli_commit($conn);

        echo json_encode(['status' => 'paid_ignored']);
        exit;
    }

    /* =========================
       AMBIL SEMUA BOOKING (PP FIX)
    ========================= */
    $booking_ids = [];

    $qAll = mysqli_query($conn, "
        SELECT id FROM bookings
        WHERE group_code = '$group_code'
    ");

    while ($row = mysqli_fetch_assoc($qAll)) {
        $booking_ids[] = $row['id'];
    }

    if (empty($booking_ids)) {
        throw new Exception("Booking group tidak ditemukan");
    }

    $ids = implode(',', array_map('intval', $booking_ids));

    /* =========================
       RELEASE SEATS (SEMUA PP)
    ========================= */
    mysqli_query($conn, "
        UPDATE seats s
        JOIN booking_details bd ON bd.seat_number = s.seat_number
        SET s.status = 'available'
        WHERE bd.booking_id IN ($ids)
    ");

    /* =========================
       DELETE SEMUA DETAIL (PP FIX)
    ========================= */
    mysqli_query($conn, "
        DELETE FROM booking_details
        WHERE booking_id IN ($ids)
    ");

    /* =========================
       EXPIRE SEMUA BOOKING (PP FIX)
    ========================= */
    mysqli_query($conn, "
        UPDATE bookings
        SET status = 'expired'
        WHERE group_code = '$group_code'
    ");

    /* =========================
       EXPIRE PAYMENT + HAPUS TOKEN
    ========================= */
    mysqli_query($conn, "
        UPDATE payments
        SET 
            status = 'expired',
            snap_token = NULL
        WHERE group_code = '$group_code'
    ");

    mysqli_commit($conn);

    echo json_encode([
        'status' => 'expired',
        'message' => 'Booking (termasuk PP) berhasil di-expire'
    ]);

} catch (Exception $e) {

    mysqli_rollback($conn);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}