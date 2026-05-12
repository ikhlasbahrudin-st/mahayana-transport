<?php

function syncTravelStatus($conn)
{
    date_default_timezone_set('Asia/Jakarta');

    $now = time();

    // =========================
    // START TRANSACTION (AMAN)
    // =========================
    $conn->begin_transaction();

    try {

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

        if (!$query) {
            throw new Exception("Query error: " . $conn->error);
        }

        while ($row = $query->fetch_assoc()) {

            $booking_id  = (int)$row['id'];
            $schedule_id = (int)$row['schedule_id'];

            // =========================
            // 1. TENTUKAN TANGGAL
            // =========================
            if ((int)$row['is_daily'] === 1) {
                $date = $row['travel_date'];
            } else {
                $date = $row['schedule_date'];
            }

            if (empty($date) || $date == '0000-00-00') {
                continue;
            }

            // =========================
            // 2. HITUNG WAKTU
            // =========================
            $departure = strtotime($date . ' ' . $row['departure_time']);
            $arrival   = strtotime($date . ' ' . $row['arrival_time']);

            // lintas hari
            if ($arrival <= $departure) {
                $arrival = strtotime('+1 day', $arrival);
            }

            // =========================
            // 3. TENTUKAN STATUS
            // =========================
            if ($now < $departure) {
                $status = 'pending';
            } elseif ($now >= $departure && $now < $arrival) {
                $status = 'on_progress';
            } else {
                $status = 'completed';
            }

            // =========================
            // 4. UPDATE STATUS BOOKING
            // =========================
            if ($row['travel_status'] !== $status) {

                $updateBooking = $conn->query("
                    UPDATE bookings 
                    SET travel_status = '$status'
                    WHERE id = $booking_id
                ");

                if (!$updateBooking) {
                    throw new Exception("Update booking gagal: " . $conn->error);
                }
            }

            // =========================
            // 5. RELEASE KURSI (FIX TOTAL)
            // =========================
            if ($status === 'completed') {

                $releaseSeat = $conn->query("
                    UPDATE seats s
                    JOIN booking_details bd 
                        ON bd.seat_number = s.seat_number
                    JOIN bookings b 
                        ON b.id = bd.booking_id
                    SET s.status = 'available'
                    WHERE bd.booking_id = $booking_id
                    AND s.schedule_id = $schedule_id
                    AND (
                        s.travel_date = b.travel_date 
                        OR s.travel_date IS NULL
                    )
                ");

                if (!$releaseSeat) {
                    throw new Exception("Release kursi gagal: " . $conn->error);
                }
            }
        }

        // =========================
        // COMMIT
        // =========================
        $conn->commit();

    } catch (Exception $e) {

        // =========================
        // ROLLBACK (JIKA ERROR)
        // =========================
        $conn->rollback();

        error_log("SYNC ERROR: " . $e->getMessage());
    }
}