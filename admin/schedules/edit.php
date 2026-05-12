<?php
require_once '../../config/koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// ================= AMBIL DATA =================
$data = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM schedules WHERE id=$id
"));

if(!$data){
    die("Data tidak ditemukan");
}

$buses  = mysqli_query($conn, "SELECT * FROM buses");
$routes = mysqli_query($conn, "SELECT * FROM routes");

// ================= UPDATE =================
if(isset($_POST['submit'])){

    $bus       = (int) $_POST['bus_id'];
    $route     = (int) $_POST['route_id'];
    $berangkat = $_POST['departure_time'] ?? '';
    $tiba      = $_POST['arrival_time'] ?? '';
    $tanggal   = $_POST['date'] ?? '';
    $is_daily  = isset($_POST['is_daily']) ? 1 : 0;

    // ================= VALIDASI =================
    if(!$bus || !$route || !$berangkat || !$tiba){
        $error = "Semua wajib diisi";
    } else {

        // ================= FIX PENTING =================
        // DATE SELALU ADA (WAJIB)
        if(empty($tanggal)){
            $tanggal = date('Y-m-d'); // fallback otomatis hari ini
        }

        $tanggal = mysqli_real_escape_string($conn, $tanggal);

        $update = mysqli_query($conn, "
            UPDATE schedules SET
                bus_id='$bus',
                route_id='$route',
                departure_time='$berangkat',
                arrival_time='$tiba',
                date='$tanggal',
                is_daily='$is_daily'
            WHERE id=$id
        ");

        if($update){
            header("Location: index.php?success=update");
            exit;
        } else {
            $error = "Gagal update schedule";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Jadwal</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <div class="w-64 fixed h-full hidden md:block">
        <?php include '../components/sidebar.php'; ?>
    </div>

    <div class="flex-1 md:ml-64 p-6">

        <div class="max-w-lg mx-auto bg-white rounded-2xl shadow p-6">

            <h2 class="text-xl font-bold mb-4">Edit Jadwal</h2>

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
                        <?php while($b = mysqli_fetch_assoc($buses)): ?>
                        <option value="<?= $b['id'] ?>" <?= $b['id']==$data['bus_id']?'selected':'' ?>>
                            <?= $b['bus_name'] ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- ROUTE -->
                <div>
                    <label class="font-semibold">Rute</label>
                    <select name="route_id" class="w-full border p-2 rounded mt-1">
                        <?php while($r = mysqli_fetch_assoc($routes)): ?>
                        <option value="<?= $r['id'] ?>" <?= $r['id']==$data['route_id']?'selected':'' ?>>
                            <?= $r['departure_city'] ?> → <?= $r['arrival_city'] ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- JAM -->
                <div>
                    <label class="font-semibold">Jam Berangkat</label>
                    <input type="time" name="departure_time"
                        value="<?= $data['departure_time'] ?>"
                        class="w-full border p-2 rounded mt-1">
                </div>

                <div>
                    <label class="font-semibold">Jam Tiba</label>
                    <input type="time" name="arrival_time"
                        value="<?= $data['arrival_time'] ?>"
                        class="w-full border p-2 rounded mt-1">
                </div>

                <!-- DAILY -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_daily" id="is_daily"
                        <?= $data['is_daily'] == 1 ? 'checked' : '' ?>
                        onclick="toggleDate()">

                    <label for="is_daily" class="font-semibold text-sm">
                        Jadwal Harian
                    </label>
                </div>

                <!-- DATE (TETAP AKTIF, TIDAK DISABLED) -->
                <div id="dateField">
                    <label class="font-semibold">Tanggal</label>
                    <input type="date" name="date"
                        value="<?= $data['date'] ?>"
                        class="w-full border p-2 rounded mt-1">
                </div>

                <!-- BUTTON -->
                <button name="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded font-bold">
                    Update Jadwal
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

window.onload = toggleDate;
</script>

</body>
</html>