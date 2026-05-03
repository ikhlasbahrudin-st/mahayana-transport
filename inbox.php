<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth_user/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* =========================
   LIST INBOX
========================= */
$stmt = $conn->prepare("
    SELECT 
        b.id,
        b.booking_code,
        b.status AS payment_status,
        b.travel_status,
        b.total_price,
        b.created_at,

        r.departure_city,
        r.arrival_city

    FROM bookings b
    JOIN schedules s ON b.schedule_id = s.id
    JOIN routes r ON s.route_id = r.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

/* =========================
   DETAIL
========================= */
$detail = null;

if (isset($_GET['detail'])) {

    $id = (int) $_GET['detail'];

    $stmt2 = $conn->prepare("
        SELECT 
            b.*,
            r.departure_city,
            r.arrival_city,
            s.departure_time,
            s.arrival_time,
            s.date AS schedule_date

        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        WHERE b.id = ? AND b.user_id = ?
        LIMIT 1
    ");

    $stmt2->bind_param("ii", $id, $user_id);
    $stmt2->execute();
    $detail = $stmt2->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Inbox</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body { font-family: Inter, sans-serif; background:#f8fafc; }
.card { transition: .2s; }
.card:active { transform: scale(0.98); }
</style>
</head>

<body class="max-w-md mx-auto bg-gray-50 min-h-screen pb-24">

<?php include 'components/header.php'; ?>

<main class="p-4 space-y-4">

<!-- TITLE -->
<div class="mb-3">
    <h1 class="text-lg font-black text-[#001F3F]">Inbox</h1>
    <p class="text-xs text-gray-400">Riwayat & status tiket kamu</p>
</div>

<!-- ================= LIST ================= -->
<?php while($row = $result->fetch_assoc()): 

$status = strtolower($row['payment_status']);
$travel = strtolower($row['travel_status']);

/* ================= STATUS LOGIC ================= */
if ($travel === 'completed') {
    $label = "SELESAI";
    $color = "bg-blue-100 text-blue-700";
    $icon  = "fa-circle-check";

} elseif ($travel === 'on_progress') {
    $label = "PERJALANAN";
    $color = "bg-indigo-100 text-indigo-700";
    $icon  = "fa-train";

} elseif ($status === 'paid') {
    $label = "TERBAYAR";
    $color = "bg-emerald-100 text-emerald-700";
    $icon  = "fa-check";

} else {
    $label = "MENUNGGU";
    $color = "bg-amber-100 text-amber-700";
    $icon  = "fa-clock";
}

?>

<a href="inbox.php?detail=<?= $row['id'] ?>"
   class="block bg-white p-4 rounded-2xl shadow-sm border border-gray-100 card">

    <div class="flex justify-between items-center mb-2">

        <div>
            <p class="text-[10px] text-gray-400">Booking Code</p>
            <p class="font-bold text-[#001F3F]"><?= $row['booking_code'] ?></p>
        </div>

        <span class="text-[10px] px-2 py-1 rounded-full font-bold <?= $color ?>">
            <i class="fa <?= $icon ?>"></i> <?= $label ?>
        </span>

    </div>

    <div class="flex justify-between text-sm text-gray-700">
        <span><?= $row['departure_city'] ?></span>
        <i class="fa fa-arrow-right text-gray-300"></i>
        <span><?= $row['arrival_city'] ?></span>
    </div>

    <p class="text-[10px] text-gray-400 mt-2">
        <?= date('d M Y H:i', strtotime($row['created_at'])) ?>
    </p>

</a>

<?php endwhile; ?>

</main>

<!-- ================= DETAIL MODAL ================= -->
<?php if ($detail): ?>

<div class="fixed inset-0 bg-black/70 flex items-center justify-center p-4">

    <div class="bg-white w-full rounded-2xl p-5">

        <div class="flex justify-between mb-3">
            <h2 class="font-bold text-[#001F3F]">Detail Tiket</h2>
            <a href="inbox.php">✕</a>
        </div>

        <p class="text-sm font-bold">
            <?= $detail['departure_city'] ?> → <?= $detail['arrival_city'] ?>
        </p>

        <p class="text-xs text-gray-400 mt-1">
            <?= date('d M Y', strtotime($detail['schedule_date'])) ?>
        </p>

        <hr class="my-3">

        <p class="text-sm">
            Status: <b><?= strtoupper($detail['status']) ?></b>
        </p>

        <p class="text-xs text-gray-400 mt-2">
            Jam: <?= $detail['departure_time'] ?>
        </p>

        <a href="inbox.php"
           class="block mt-4 text-center bg-[#001F3F] text-white py-2 rounded-xl">
            Tutup
        </a>

    </div>

</div>

<?php endif; ?>

<?php include 'components/navbar.php'; ?>
<script>
setInterval(() => {
    fetch('/mahayana/sync.php')
        .then(res => res.text())
        .then(data => {
            console.log('sync:', data);
        });
}, 15000); // setiap 15 detik
</script>
</body>
</html>