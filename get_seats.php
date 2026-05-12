<?php
require_once 'config/koneksi.php';

header('Content-Type: application/json');

$schedule_id = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : 0;

if ($schedule_id <= 0) {
    echo json_encode([]);
    exit;
}

/* =========================
   AMBIL SEMUA KURSI + STATUS DYNAMIC
========================= */
$query = "
SELECT 
    s.seat_number,

    CASE 
        -- SUDAH DIBAYAR & BELUM SAMPAI
        WHEN b.id IS NOT NULL 
             AND b.status = 'paid'
             AND CONCAT(sc.date, ' ', sc.arrival_time) > NOW()
        THEN 'booked'

        -- MASIH DI HOLD (5 MENIT)
        WHEN b.id IS NOT NULL 
             AND b.status = 'pending'
             AND TIMESTAMPDIFF(MINUTE, b.created_at, NOW()) < 5
        THEN 'locked'

        ELSE 'available'
    END as status

FROM seats s

LEFT JOIN booking_details bd 
    ON bd.seat_number = s.seat_number

LEFT JOIN bookings b 
    ON b.id = bd.booking_id

LEFT JOIN schedules sc 
    ON sc.id = b.schedule_id

WHERE s.schedule_id = $schedule_id

GROUP BY s.seat_number
ORDER BY s.seat_number ASC
";

$result = mysqli_query($conn, $query);

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        'seat_number' => $row['seat_number'],
        'status' => $row['status']
    ];
}

echo json_encode($data);