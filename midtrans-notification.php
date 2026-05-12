<?php
require_once 'config/koneksi.php';
require_once 'vendor/autoload.php';

date_default_timezone_set('Asia/Jakarta');

/* =========================
   MIDTRANS CONFIG
========================= */
\Midtrans\Config::$serverKey = "";
\Midtrans\Config::$isProduction = false;

/* =========================
   SAFE INIT
========================= */
try {
    $notif = new \Midtrans\Notification();
} catch (Exception $e) {
    http_response_code(200);
    exit("OK");
}

/* =========================
   DATA SAFETY
========================= */
$transaction  = $notif->transaction_status ?? '';
$order_id     = $notif->order_id ?? '';
$payment_type = $notif->payment_type ?? '';
$fraud        = $notif->fraud_status ?? '';

if (!$order_id) {
    http_response_code(200);
    exit("OK");
}

/* =========================
   STATUS MAPPING
========================= */
$status = 'pending';

if ($transaction === 'capture') {

    if ($payment_type === 'credit_card' && $fraud === 'challenge') {
        $status = 'pending';
    } else {
        $status = 'paid';
    }

} elseif ($transaction === 'settlement') {
    $status = 'paid';

} elseif ($transaction === 'pending') {
    $status = 'pending';

} elseif (in_array($transaction, ['expire','cancel'])) {
    $status = 'expired';

} elseif ($transaction === 'deny') {
    $status = 'failed';
}

/* =========================
   GET PAYMENT DATA
========================= */
$order_id_safe = mysqli_real_escape_string($conn, $order_id);

$get = mysqli_query($conn, "
    SELECT group_code 
    FROM payments 
    WHERE order_id = '$order_id_safe'
    LIMIT 1
");

$data = mysqli_fetch_assoc($get);
$group_code = $data['group_code'] ?? null;

if (!$group_code) {
    http_response_code(200);
    exit("OK");
}

$group_safe = mysqli_real_escape_string($conn, $group_code);

/* =========================
   TRANSACTION SAFE
========================= */
mysqli_begin_transaction($conn);

try {

    /* =========================
       UPDATE PAYMENTS
    ========================= */
    mysqli_query($conn, "
        UPDATE payments 
        SET 
            status = '$status',
            payment_method = '$payment_type',
            paid_at = IF('$status'='paid', NOW(), paid_at)
        WHERE group_code = '$group_safe'
    ");

    /* =========================
       SNAP TOKEN HANDLING (FIX UTAMA)
    ========================= */

    if ($status === 'paid') {

        // hapus snap_token setelah sukses bayar
        mysqli_query($conn, "
            UPDATE payments 
            SET snap_token = NULL
            WHERE group_code = '$group_safe'
        ");

    } elseif (in_array($status, ['expired','failed'])) {

        // juga hapus token kalau gagal/expired
        mysqli_query($conn, "
            UPDATE payments 
            SET snap_token = NULL
            WHERE group_code = '$group_safe'
        ");
    }

    /* =========================
       UPDATE BOOKINGS (HANYA STATUS PAYMENT)
    ========================= */
    mysqli_query($conn, "
        UPDATE bookings 
        SET status = '$status'
        WHERE group_code = '$group_safe'
    ");

    /* =========================
       SEAT HANDLING
    ========================= */
    if ($status === 'paid') {

        mysqli_query($conn, "
            UPDATE seats s
            JOIN booking_details bd ON bd.seat_number = s.seat_number
            JOIN bookings b ON b.id = bd.booking_id
            SET s.status = 'booked'
            WHERE b.group_code = '$group_safe'
            AND s.schedule_id = b.schedule_id
        ");

    } elseif (in_array($status, ['expired','failed'])) {

        mysqli_query($conn, "
            UPDATE seats s
            JOIN booking_details bd ON bd.seat_number = s.seat_number
            JOIN bookings b ON b.id = bd.booking_id
            SET s.status = 'available'
            WHERE b.group_code = '$group_safe'
            AND s.schedule_id = b.schedule_id
        ");

        mysqli_query($conn, "
            DELETE bd FROM booking_details bd
            JOIN bookings b ON bd.booking_id = b.id
            WHERE b.group_code = '$group_safe'
        ");
    }

    mysqli_commit($conn);

} catch (Exception $e) {
    mysqli_rollback($conn);
}

/* =========================
   RESPONSE WAJIB
========================= */
http_response_code(200);
echo "OK";