<?php

function syncTravelStatus($conn)
{
    date_default_timezone_set('Asia/Jakarta');

    $now = time();

    $query = $conn->query("
        SELECT 
            b.id,
            b.schedule_id,
            b.travel_date,
            b.status,
            b.travel_status,
            s.departure_time,
            s.arrival_time,
            s.date AS schedule_date,
            s.is_daily
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        WHERE b.status = 'paid'
    ");

    while ($row = $query->fetch_assoc()) {

        $booking_id  = (int)$row['id'];
        $schedule_id = (int)$row['schedule_id'];

        /* =========================
           1. TENTUKAN TANGGAL DASAR
        ========================= */

        if ((int)$row['is_daily'] === 1) {
            // HARUS dari booking
            $date = $row['travel_date'];
        } else {
            // JADWAL KHUSUS dari schedule
            $date = $row['schedule_date'];
        }

        if (empty($date) || $date == '0000-00-00') {
            continue;
        }

        /* =========================
           2. HITUNG TIMESTAMP
        ========================= */

        $departure = strtotime($date . ' ' . $row['departure_time']);
        $arrival   = strtotime($date . ' ' . $row['arrival_time']);

        // kalau lintas hari
        if ($arrival <= $departure) {
            $arrival = strtotime('+1 day', $arrival);
        }

        /* =========================
           DEBUG (aktifkan kalau perlu)
        ========================= */
/*
        echo "NOW      : " . date('Y-m-d H:i:s', $now) . "<br>";
        echo "DEPARTURE: " . date('Y-m-d H:i:s', $departure) . "<br>";
        echo "ARRIVAL  : " . date('Y-m-d H:i:s', $arrival) . "<br>";
        exit;
*/

        /* =========================
           3. LOGIC STATUS
        ========================= */

        if ($now < $departure) {
            $status = 'pending';
        }
        elseif ($now >= $departure && $now < $arrival) {
            $status = 'on_progress';
        }
        else {
            $status = 'completed';
        }

        /* =========================
           4. UPDATE HANYA JIKA BERUBAH
        ========================= */

        if ($row['travel_status'] !== $status) {
            $conn->query("
                UPDATE bookings 
                SET travel_status = '$status'
                WHERE id = $booking_id
            ");
        }

        /* =========================
           5. RELEASE SEAT
        ========================= */

        if ($status === 'completed') {

            $conn->query("
                UPDATE seats s
                JOIN booking_details bd ON bd.seat_number = s.seat_number
                JOIN bookings b ON b.id = bd.booking_id
                SET s.status = 'available'
                WHERE bd.booking_id = $booking_id
                AND s.schedule_id = $schedule_id
            ");
        }
    }
}