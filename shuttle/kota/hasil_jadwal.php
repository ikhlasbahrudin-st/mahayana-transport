<?php
require_once '../../config/koneksi.php';

date_default_timezone_set('Asia/Jakarta');

$route_id = mysqli_real_escape_string($conn, $_GET['route_id'] ?? '');
$date = mysqli_real_escape_string($conn, $_GET['date'] ?? '');
$is_round_trip = $_GET['is_round_trip'] ?? '0';
$return_date = mysqli_real_escape_string($conn, $_GET['return_date'] ?? '');

$is_pp = ($is_round_trip == '1');

if (empty($route_id) || empty($date)) {
    die("Parameter tidak lengkap");
}

$today = date('Y-m-d');
$now_time = date('H:i:s');

$data_pergi = [];
$data_pulang = [];

/* ======================================================
   PERGI (DATE + DAILY)
====================================================== */
$query_pergi = "
SELECT schedules.*, routes.departure_city, routes.arrival_city, routes.base_price
FROM schedules
JOIN routes ON schedules.route_id = routes.id
WHERE schedules.route_id = '$route_id'
AND (
    schedules.date = '$date'
    OR schedules.is_daily = 1
)
ORDER BY schedules.departure_time ASC
";

$res = mysqli_query($conn, $query_pergi);

if ($res && mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {

        // filter jam hanya kalau TANGGAL SAMA DENGAN HARI INI
        if ($date == $today && $row['departure_time'] < $now_time) {
            continue;
        }

        $data_pergi[] = $row;
    }
}

/* ======================================================
   PULANG (JIKA PP)
====================================================== */
if ($is_pp && !empty($return_date)) {

    $route = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT departure_city, arrival_city 
        FROM routes 
        WHERE id='$route_id'
    "));

    if ($route) {

        $reverse = mysqli_fetch_assoc(mysqli_query($conn,"
            SELECT id FROM routes 
            WHERE departure_city='{$route['arrival_city']}'
            AND arrival_city='{$route['departure_city']}'
        "));

        if (!empty($reverse['id'])) {

            $reverse_id = $reverse['id'];

            $query_back = "
            SELECT schedules.*, routes.departure_city, routes.arrival_city, routes.base_price
            FROM schedules
            JOIN routes ON schedules.route_id = routes.id
            WHERE schedules.route_id = '$reverse_id'
            AND (
                schedules.date = '$return_date'
                OR schedules.is_daily = 1
            )
            ORDER BY schedules.departure_time ASC
            ";

            $res2 = mysqli_query($conn, $query_back);

            if ($res2 && mysqli_num_rows($res2) > 0) {
                while ($row = mysqli_fetch_assoc($res2)) {

                    if ($return_date == $today && $row['departure_time'] < $now_time) {
                        continue;
                    }

                    $data_pulang[] = $row;
                }
            }
        }
    }
}

function formatTgl($t){
    return date('d M Y', strtotime($t));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pilih Jadwal</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
body { font-family: 'Plus Jakarta Sans', sans-serif; }
.card-gradient { background: linear-gradient(135deg,#fff,#f8fafc); }
</style>
</head>

<body class="bg-slate-50 max-w-md mx-auto min-h-screen border-x shadow-2xl">

<?php include '../../components/header.php'; ?>

<main class="p-5 pb-32">

<!-- HEADER -->
<div class="mb-8">
    <h1 class="text-2xl font-extrabold">Pilih Jadwal</h1>
    <p class="text-xs text-slate-500 mt-1">Tersedia tiket untuk rute pilihan Anda</p>
</div>

<!-- ================= PERGI ================= -->
<section class="mb-8">

<div class="flex justify-between mb-4">
    <div>
        <h2 class="font-bold">Keberangkatan</h2>
        <p class="text-xs text-slate-500"><?= formatTgl($date) ?></p>
    </div>
    <span class="text-xs bg-orange-100 text-orange-600 px-2 py-1 rounded">PERGI</span>
</div>

<?php if (count($data_pergi) > 0): ?>

    <?php foreach ($data_pergi as $row): ?>

        <div class="card-gradient p-4 mb-3 rounded-xl border flex justify-between items-center">

            <div>
                <div class="flex gap-2 font-bold text-lg">
                    <span><?= $row['departure_time'] ?></span>
                    <span>→</span>
                    <span><?= $row['arrival_time'] ?></span>
                </div>
                <p class="text-xs text-slate-500">
                    <?= $row['departure_city'] ?> → <?= $row['arrival_city'] ?>
                </p>
            </div>

            <div class="text-right">
                <div class="font-bold text-orange-600">
                    Rp<?= number_format($row['base_price']) ?>
                </div>

<a href="../pilih-kursi.php?schedule_id=<?= $row['id'] ?>"
   class="inline-block bg-slate-900 text-white text-xs px-4 py-2 rounded mt-2 text-center">
    Beli
</a>
            </div>

        </div>

    <?php endforeach; ?>

<?php else: ?>

    <div class="p-6 text-center text-slate-500 border rounded-xl">
        Jadwal tidak ditemukan untuk hari ini, mungkin besok
    </div>

<?php endif; ?>

</section>

<!-- ================= PULANG ================= -->
<?php if ($is_pp): ?>
<section>

<div class="flex justify-between mb-4">
    <div>
        <h2 class="font-bold">Kepulangan</h2>
        <p class="text-xs text-slate-500"><?= formatTgl($return_date) ?></p>
    </div>
    <span class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded">PULANG</span>
</div>

<?php if (count($data_pulang) > 0): ?>

    <?php foreach ($data_pulang as $row): ?>

        <div class="card-gradient p-4 mb-3 rounded-xl border flex justify-between items-center">

            <div>
                <div class="font-bold text-lg">
                    <?= $row['departure_time'] ?> → <?= $row['arrival_time'] ?>
                </div>
                <p class="text-xs text-slate-500">
                    <?= $row['departure_city'] ?> → <?= $row['arrival_city'] ?>
                </p>
            </div>

            <div class="text-right font-bold text-blue-600">
                Rp<?= number_format($row['base_price']) ?>
            </div>

        </div>

    <?php endforeach; ?>

<?php else: ?>

    <div class="p-6 text-center text-slate-500 border rounded-xl">
        Jadwal pulang tidak tersedia
    </div>

<?php endif; ?>

</section>
<?php endif; ?>
<?php include '../../components/navbar.php'; ?>

<script>

let selectedScheduleId = null;

function openSeatModal(id) {
    selectedScheduleId = id;
    document.getElementById('seatModal').classList.remove('hidden');
    document.getElementById('seatModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('seatModal').classList.add('hidden');
    document.getElementById('seatModal').classList.remove('flex');
}

document.getElementById('confirmBtn').addEventListener('click', function () {
    if (selectedScheduleId) {
        window.location.href = "pilih-kursi.php?schedule_id=" + selectedScheduleId;
    }
});

</script>

</main>

</body>
</html>