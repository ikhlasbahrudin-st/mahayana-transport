<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/koneksi.php';

/* =========================
   VALIDASI LOGIN
========================= */
if (!isset($_SESSION['user_id'])) {
    die("Silakan login terlebih dahulu");
}

$user_id = (int) $_SESSION['user_id'];

/* =========================
   INPUT
========================= */
$schedule_id = (int)($_POST['schedule_id'] ?? 0);
$go_id   = (int)($_POST['go_id'] ?? 0);
$back_id = (int)($_POST['back_id'] ?? 0);

$selected_seats = trim($_POST['selected_seats'] ?? '');
$passenger_json = $_POST['passenger_names'] ?? '{}';

$seats = array_values(array_filter(array_map('trim', explode(',', $selected_seats))));
$passengers = json_decode($passenger_json, true) ?? [];

$is_pp = ($go_id > 0 && $back_id > 0);

/* =========================
   VALIDASI INPUT
========================= */
if ((!$is_pp && !$schedule_id) || empty($seats)) {
    die("Data tidak lengkap");
}

if (count($seats) > 2) {
    die("Maksimal 2 kursi saja");
}

/* =========================
   AMBIL USER
========================= */
$stmt = $conn->prepare("SELECT fullname, email FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User tidak ditemukan");
}

/* =========================
   GET SCHEDULE
========================= */
function getScheduleData($conn, $id){
    $stmt = $conn->prepare("
        SELECT s.id, r.base_price, b.capacity, s.is_daily, s.date
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        JOIN buses b ON s.bus_id = b.id
        WHERE s.id = ?
        FOR UPDATE
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/* =========================
   🔥 FIX: CEK SEAT REAL LOCK (WAJIB)
========================= */
function isSeatTaken($conn, $schedule_id, $seat){
    $stmt = $conn->prepare("
        SELECT bd.id
        FROM booking_details bd
        JOIN bookings b ON bd.booking_id = b.id
        WHERE b.schedule_id = ?
        AND bd.seat_number = ?
        AND b.status IN ('pending','paid')
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->bind_param("is", $schedule_id, $seat);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/* =========================
   PROCESS SEAT (LOCK SAFE)
========================= */
function processSeats($conn, $booking_id, $schedule_id, $seats, $passengers, $user){

    foreach ($seats as $i => $seat) {

        $seat = trim($seat);

        $name = ($i === 0)
            ? $user['fullname']
            : trim($passengers[$seat] ?? '');

        if ($i > 0 && $name === '') {
            throw new Exception("Nama penumpang ke-" . ($i+1) . " wajib diisi");
        }

        // 🔥 DOUBLE CHECK LOCK
        if (isSeatTaken($conn, $schedule_id, $seat)) {
            throw new Exception("Gagal: Kursi $seat sudah diambil orang lain");
        }

        $stmt = $conn->prepare("
            INSERT INTO booking_details 
            (booking_id, seat_number, passenger_name)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iss", $booking_id, $seat, $name);
        $stmt->execute();
    }
}

/* =========================
   START TRANSACTION
========================= */
$conn->begin_transaction();

try {

    $group_code = 'GRP-' . time();

    /* =========================
       ONE WAY
    ========================= */
    if(!$is_pp){

        $data = getScheduleData($conn, $schedule_id);
        if(!$data) throw new Exception("Schedule tidak ditemukan");

        $travel_date = ($data['is_daily'] == 1)
            ? date('Y-m-d')
            : $data['date'];

        // 🔥 CEK KAPASITAS REAL
        $stmt = $conn->prepare("
            SELECT COUNT(DISTINCT bd.seat_number) total
            FROM booking_details bd
            JOIN bookings b ON bd.booking_id = b.id
            WHERE b.schedule_id = ?
            AND b.status IN ('pending','paid')
            FOR UPDATE
        ");
        $stmt->bind_param("i", $schedule_id);
        $stmt->execute();
        $booked = $stmt->get_result()->fetch_assoc()['total'];

        if(($booked + count($seats)) > $data['capacity']){
            throw new Exception("Kursi penuh");
        }

        $total = count($seats) * $data['base_price'];
        $code = 'BK-' . time();

        $stmt = $conn->prepare("
            INSERT INTO bookings 
            (user_id, schedule_id, total_price, status, booking_code, group_code, trip_type, created_at, travel_status, travel_date)
            VALUES (?, ?, ?, 'pending', ?, ?, 'one_way', NOW(), 'pending', ?)
        ");
        $stmt->bind_param("iidsss", $user_id, $schedule_id, $total, $code, $group_code, $travel_date);
        $stmt->execute();

        $booking_id = $conn->insert_id;

        processSeats($conn, $booking_id, $schedule_id, $seats, $passengers, $user);
    }

    /* =========================
       ROUND TRIP
    ========================= */
    else{

        $data_go   = getScheduleData($conn, $go_id);
        $data_back = getScheduleData($conn, $back_id);

        if(!$data_go || !$data_back){
            throw new Exception("Jadwal PP tidak valid");
        }

        $travel_date_go = ($data_go['is_daily'] == 1) ? date('Y-m-d') : $data_go['date'];
        $travel_date_back = ($data_back['is_daily'] == 1) ? date('Y-m-d') : $data_back['date'];

        $total = count($seats) * ($data_go['base_price'] + $data_back['base_price']);

        /* GO */
        $code_go = 'GO-' . time();

        $stmt = $conn->prepare("
            INSERT INTO bookings 
            (user_id, schedule_id, total_price, status, booking_code, group_code, trip_type, created_at, travel_status, travel_date)
            VALUES (?, ?, ?, 'pending', ?, ?, 'round_trip', NOW(), 'pending', ?)
        ");
        $stmt->bind_param("iidsss", $user_id, $go_id, $total, $code_go, $group_code, $travel_date_go);
        $stmt->execute();

        $booking_go = $conn->insert_id;
        processSeats($conn, $booking_go, $go_id, $seats, $passengers, $user);

        /* BACK */
        $code_back = 'BACK-' . time();

        $stmt = $conn->prepare("
            INSERT INTO bookings 
            (user_id, schedule_id, total_price, status, booking_code, group_code, trip_type, created_at, travel_status, travel_date)
            VALUES (?, ?, ?, 'pending', ?, ?, 'round_trip', NOW(), 'pending', ?)
        ");
        $stmt->bind_param("iidsss", $user_id, $back_id, $total, $code_back, $group_code, $travel_date_back);
        $stmt->execute();

        $booking_back = $conn->insert_id;
        processSeats($conn, $booking_back, $back_id, $seats, $passengers, $user);

        $booking_id = $booking_go;
    }

    $conn->commit();

    header("Location: ../pembayaran.php?booking_id=$booking_id");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Gagal: " . $e->getMessage());
}