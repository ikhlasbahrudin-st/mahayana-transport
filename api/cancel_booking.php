<?php
session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

if (isset($_POST['booking_id']) && isset($_SESSION['user_id'])) {
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $user_id = $_SESSION['user_id'];

    // 1. Ambil data booking untuk mendapatkan schedule_id sebelum diupdate
    // Kita cek status 'unpaid' atau 'pending' agar bisa dibatalkan
    $check_query = mysqli_query($conn, "SELECT schedule_id, status FROM bookings WHERE id = '$booking_id' AND user_id = '$user_id'");
    $booking = mysqli_fetch_assoc($check_query);

    if ($booking && ($booking['status'] == 'pending' || $booking['status'] == 'unpaid')) {
        
        mysqli_begin_transaction($conn);

        try {
            // 2. Update status booking menjadi 'canceled'
            $update_status = mysqli_query($conn, "UPDATE bookings SET status = 'canceled' WHERE id = '$booking_id'");

            // 3. LOGIKA PENGEMBALIAN KAPASITAS KURSI
            // Hitung jumlah kursi yang dibatalkan
            $q_seats = mysqli_query($conn, "SELECT COUNT(*) as total FROM booking_details WHERE booking_id = '$booking_id'");
            $count = mysqli_fetch_assoc($q_seats)['total'];

            if ($count > 0) {
                $schedule_id = $booking['schedule_id'];
                
                // Tambahkan kembali ke kapasitas bus melalui relasi schedule
                mysqli_query($conn, "
                    UPDATE buses b 
                    JOIN schedules s ON b.id = s.bus_id 
                    SET b.capacity = b.capacity + $count 
                    WHERE s.id = '$schedule_id'
                ");
            }

            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Pesanan berhasil dibatalkan dan kursi dikembalikan.']);

        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => 'Gagal memproses pembatalan.']);
        }

    } else {
        // Jika pesanan sudah success atau statusnya bukan pending/unpaid
        echo json_encode(['status' => 'ignored', 'message' => 'Pesanan tidak dalam posisi bisa dibatalkan.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
}
?>