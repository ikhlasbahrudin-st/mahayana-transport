<?php
include '../middleware.php';
include '../../config/koneksi.php';

$bus_id = isset($_GET['bus_id']) ? (int) $_GET['bus_id'] : 0;
$schedule_id = isset($_GET['schedule_id']) ? (int) $_GET['schedule_id'] : 0;

/* OPTIONAL (future daily system) */
$travel_date = $_GET['date'] ?? date('Y-m-d');

$buses_query = mysqli_query($conn, "SELECT * FROM buses ORDER BY bus_name ASC");

$bus = null;
$schedule = null;
$schedules_q = null;
$existing_seats = [];
$capacity = 0;

if ($bus_id) {

    $bus_q = mysqli_query($conn, "SELECT * FROM buses WHERE id=$bus_id");
    $bus = mysqli_fetch_assoc($bus_q);

    if ($bus) {

        $capacity = (int)$bus['capacity'];

        $schedules_q = mysqli_query($conn, "
            SELECT s.*, r.departure_city, r.arrival_city 
            FROM schedules s
            JOIN routes r ON s.route_id = r.id
            WHERE s.bus_id = $bus_id 
            ORDER BY s.departure_time ASC
        ");

        if ($schedule_id) {

            $schedule_q = mysqli_query($conn, "
                SELECT s.*, r.departure_city, r.arrival_city 
                FROM schedules s 
                JOIN routes r ON s.route_id = r.id 
                WHERE s.id = $schedule_id
            ");
            $schedule = mysqli_fetch_assoc($schedule_q);

            if ($schedule) {

                /* =========================
                   AMBIL SEAT LAYOUT (MASTER)
                   🔥 WAJIB: travel_date IS NULL
                ========================= */
                $seats_q = mysqli_query($conn, "
                    SELECT * FROM seats 
                    WHERE schedule_id = $schedule_id
                    AND travel_date IS NULL
                ");

                while ($s = mysqli_fetch_assoc($seats_q)) {

                    $row = $s['row_label'] ?? '';
                    $col = $s['col_number'] ?? '';

                    if ($row !== '' && $col !== '') {
                        $key = $row . $col;
                        $existing_seats[$key] = $s['seat_number'];
                    }
                }
            }
        }
    }
}

/* =========================
   FUNCTION RENDER SEAT
========================= */
function renderSeatInput($row, $col, $existing_seats, $is_driver = false) {

    $key = $row . $col;
    $val = isset($existing_seats[$key]) ? htmlspecialchars($existing_seats[$key]) : '';

    /* DRIVER */
    if ($is_driver) {
        return "
        <div class='flex flex-col items-center'>
            <div class='seat-box flex items-center justify-center bg-slate-800 text-white border-2 border-slate-900 shadow-sm uppercase'>
                DRV
            </div>
        </div>";
    }

    $placeholder = $row . $col;

    return "
    <div class='flex flex-col items-center'>
        <input 
            type='text' 
            name='seats[$row][$col]' 
            value='$val' 
            placeholder='$placeholder'
            class='seat-box text-center uppercase bg-white border-slate-300 text-slate-900 focus:ring-2 focus:ring-blue-500 outline-none transition-all border-2 shadow-sm'>
        
        <input type='hidden' name='meta[$row][$col][row]' value='$row'>
        <input type='hidden' name='meta[$row][$col][col]' value='$col'>
    </div>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Layout Kursi - Mahayana Trans</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
html, body { height: 100%; overflow: hidden; }
.seat-box { width: 40px; height: 40px; border-radius: 8px; font-weight: 800; font-size: 10px; }
.bus-frame { border: 10px solid #1e293b; border-radius: 60px 60px 20px 20px; padding: 40px 15px; background: #f8fafc; position: relative; }
.steering-wheel { position: absolute; top: 45px; left: 35px; font-size: 24px; color: #475569; }
.custom-scroll::-webkit-scrollbar { width: 6px; }
.custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>
</head>

<body class="bg-slate-50 flex h-screen">

<div class="h-full flex-shrink-0">
<?php include '../components/sidebar.php'; ?>
</div>

<main class="flex-1 h-full overflow-y-auto custom-scroll">
<div class="p-8 max-w-4xl mx-auto">

<header class="mb-8 text-center">
    <h2 class="text-2xl font-black text-slate-800 uppercase">Manajemen Layout Kursi</h2>
    <p class="text-slate-500 text-sm">Konfigurasi Baris: Depan (1) - Tengah (3) - Belakang (4)</p>
</header>

<!-- FILTER -->
<div class="bg-white p-5 rounded-2xl shadow-sm mb-8 border">
<form method="GET" class="flex gap-4">

<div class="flex-1">
<label class="text-xs font-bold uppercase text-slate-400">Pilih Bus</label>
<select name="bus_id" class="w-full border p-2 rounded mt-1" onchange="this.form.submit()">
<option value="">-- Pilih Bus --</option>
<?php mysqli_data_seek($buses_query, 0); while($b = mysqli_fetch_assoc($buses_query)): ?>
<option value="<?= $b['id'] ?>" <?= $bus_id==$b['id']?'selected':'' ?>>
<?= $b['bus_name'] ?> (<?= $b['capacity'] ?> Kursi)
</option>
<?php endwhile; ?>
</select>
</div>

<div class="flex-1">
<label class="text-xs font-bold uppercase text-slate-400">Jadwal</label>
<select name="schedule_id" class="w-full border p-2 rounded mt-1">
<option value="">-- Pilih Jadwal --</option>
<?php if($schedules_q): while($sc = mysqli_fetch_assoc($schedules_q)): ?>
<option value="<?= $sc['id'] ?>" <?= $schedule_id==$sc['id']?'selected':'' ?>>
<?= $sc['departure_city'] ?> → <?= $sc['arrival_city'] ?> (<?= $sc['departure_time'] ?>)
</option>
<?php endwhile; endif; ?>
</select>
</div>

<div class="flex items-end">
<button class="bg-slate-900 text-white px-6 py-2 rounded font-bold hover:bg-slate-800">
Tampilkan
</button>
</div>

</form>
</div>

<?php if($bus && $schedule): ?>
<form action="simpan_kursi.php" method="POST">

<input type="hidden" name="bus_id" value="<?= $bus_id ?>">
<input type="hidden" name="schedule_id" value="<?= $schedule_id ?>">
<input type="hidden" name="travel_date" value="<?= $travel_date ?>">

<div class="bus-frame max-w-[320px] mx-auto shadow-2xl">
<i class="fa-solid fa-dharmachakra steering-wheel animate-spin-slow"></i>

<div class="flex flex-col gap-4">

<?php 
$current = 0;
$rowIndex = 0;
$labels = range('A','Z');

/* DEPAN */
$rowLabel = $labels[$rowIndex++];
echo "<div class='grid grid-cols-4 gap-2 mb-6'>";
echo renderSeatInput('DRV',0,[],true);
echo "<div></div><div></div>";

if($current < $capacity){
    echo renderSeatInput($rowLabel,1,$existing_seats);
    $current++;
}
echo "</div>";

/* TENGAH */
while($current < ($capacity-4)){
$rowLabel = $labels[$rowIndex++];
echo "<div class='grid grid-cols-4 gap-2'>";

for($c=1;$c<=3;$c++){
    if($current < ($capacity-4)){
        echo renderSeatInput($rowLabel,$c,$existing_seats);
        $current++;
        if($c==1) echo "<div></div>";
    }
}
echo "</div>";
}

/* BELAKANG */
if($current < $capacity){
$rowLabel = $labels[$rowIndex++];
echo "<div class='grid grid-cols-4 gap-2 mt-2'>";

for($c=1;$c<=4;$c++){
    if($current < $capacity){
        echo renderSeatInput($rowLabel,$c,$existing_seats);
        $current++;
    } else {
        echo "<div></div>";
    }
}
echo "</div>";
}
?>

</div>
</div>

<div class="mt-8 text-center">
<span class="text-xs font-bold text-slate-400">
<?= $current ?> / <?= $capacity ?> kursi
</span>
</div>

<div class="max-w-[320px] mx-auto mt-6">
<button class="w-full bg-emerald-500 text-white py-4 rounded-2xl font-black">
SIMPAN LAYOUT
</button>
</div>

</form>
<?php endif; ?>

</div>
</main>

</body>
</html>