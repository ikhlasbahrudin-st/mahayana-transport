<?php
require_once '../../config/koneksi.php';

$buses = mysqli_query($conn, "SELECT * FROM buses");
$routes = mysqli_query($conn, "SELECT * FROM routes");

if(isset($_POST['submit'])){

    $bus       = (int) $_POST['bus_id'];
    $route     = (int) $_POST['route_id'];
    $berangkat = $_POST['departure_time'] ?? '';
    $tiba      = $_POST['arrival_time'] ?? '';
    $tanggal   = $_POST['date'] ?? null;
    $is_daily  = isset($_POST['is_daily']) ? 1 : 0;

    // ================= VALIDASI =================
    if(!$bus || !$route || !$berangkat || !$tiba){
        $error = "Semua wajib diisi (kecuali tanggal jika harian)";
    } else {

        if(!$is_daily && empty($tanggal)){
            $error = "Tanggal wajib diisi jika bukan jadwal harian";
        } else {

            $busQuery = mysqli_query($conn, "SELECT * FROM buses WHERE id='$bus'");
            $busData  = mysqli_fetch_assoc($busQuery);

            if(!$busData){
                $error = "Bus tidak ditemukan";
            } else {

                $capacity = (int) $busData['capacity'];

                if($capacity <= 0){
                    $error = "Kapasitas bus tidak valid";
                } else {

                    // ================= FIX DATE NULL SAFE =================
                    $dateValue = ($is_daily || empty($tanggal)) 
                        ? "NULL" 
                        : "'" . mysqli_real_escape_string($conn, $tanggal) . "'";

                    // ================= INSERT SCHEDULE =================
                    $insert = mysqli_query($conn, "
                        INSERT INTO schedules 
                        (bus_id, route_id, departure_time, arrival_time, date, is_daily)
                        VALUES 
                        ('$bus','$route','$berangkat','$tiba',$dateValue,'$is_daily')
                    ");

                    if($insert){

                        $schedule_id = mysqli_insert_id($conn);

                        // ================= SEAT GENERATE =================
                        if(!$is_daily){
                            for($i = 1; $i <= $capacity; $i++){
                                mysqli_query($conn, "
                                    INSERT INTO seats 
                                    (schedule_id, seat_number, status)
                                    VALUES 
                                    ('$schedule_id', '$i', 'available')
                                ");
                            }
                        }

                        header("Location: index.php?success=create");
                        exit;

                    } else {
                        $error = "Gagal menyimpan schedule";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Jadwal Bus</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <div class="w-64 fixed h-full hidden md:block">
        <?php include '../components/sidebar.php'; ?>
    </div>

    <!-- CONTENT -->
    <div class="flex-1 md:ml-64 p-6">

        <div class="max-w-xl mx-auto bg-white p-6 rounded-2xl shadow">

            <h2 class="text-xl font-bold mb-4">Tambah Jadwal Bus</h2>

            <?php if(isset($error)): ?>
                <div class="bg-red-100 text-red-600 p-2 mb-3 rounded">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">

                <!-- BUS -->
                <div>
                    <label class="font-semibold">Bus</label>
                    <select name="bus_id" class="w-full border p-2 rounded mt-1">
                        <option value="">-- Pilih Bus --</option>
                        <?php while($b = mysqli_fetch_assoc($buses)): ?>
                            <option value="<?= $b['id'] ?>">
                                <?= $b['bus_name'] ?> (<?= $b['plate_number'] ?> | <?= $b['capacity'] ?> kursi)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- ROUTE -->
                <div>
                    <label class="font-semibold">Rute</label>
                    <select name="route_id" class="w-full border p-2 rounded mt-1">
                        <option value="">-- Pilih Rute --</option>
                        <?php while($r = mysqli_fetch_assoc($routes)): ?>
                            <option value="<?= $r['id'] ?>">
                                <?= $r['departure_city'] ?> → <?= $r['arrival_city'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- JAM -->
                <div>
                    <label class="font-semibold">Jam Berangkat</label>
                    <input type="time" name="departure_time" class="w-full border p-2 rounded mt-1">
                </div>

                <div>
                    <label class="font-semibold">Jam Tiba</label>
                    <input type="time" name="arrival_time" class="w-full border p-2 rounded mt-1">
                </div>

                <!-- HARIAN -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_daily" id="is_daily" onclick="toggleDate()">
                    <label for="is_daily" class="font-semibold text-sm">
                        Jadwal Harian (Setiap Hari)
                    </label>
                </div>

                <!-- TANGGAL -->
                <div id="dateField">
                    <label class="font-semibold">Tanggal</label>
                    <input type="date" name="date" class="w-full border p-2 rounded mt-1">
                </div>

                <!-- BUTTON -->
                <button name="submit"
                    class="w-full bg-yellow-400 hover:bg-yellow-500 font-bold py-2 rounded">
                    Simpan Jadwal
                </button>

            </form>

        </div>
    </div>

</div>

<script>
function toggleDate(){
    const checkbox = document.getElementById('is_daily');
    const dateField = document.getElementById('dateField');

    dateField.style.display = checkbox.checked ? 'none' : 'block';
}
</script>

</body>
</html>