<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/koneksi.php';
date_default_timezone_set('Asia/Jakarta');

/* =========================
   PARAMETER
========================= */
$schedule_id = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : 0;
$go_id   = isset($_GET['go']) ? (int)$_GET['go'] : 0;
$back_id = isset($_GET['back']) ? (int)$_GET['back'] : 0;

// 🔥 TAMBAHAN (WAJIB)
$travel_date = $_GET['date'] ?? date('Y-m-d');

$user_id   = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['fullname'] ?? 'Pemilik Akun';

$is_pp = ($go_id > 0 && $back_id > 0);

/* =========================
   VALIDASI
========================= */
if (!$is_pp && $schedule_id <= 0) {
    die("<h3 style='text-align:center;margin-top:40px'>Schedule tidak valid</h3>");
}

/* =========================
   GET SCHEDULE
========================= */
function getSchedule($conn, $id){
    $stmt = $conn->prepare("
        SELECT s.id as schedule_id, s.*, 
               r.departure_city, r.arrival_city, r.base_price,
               b.bus_name, b.capacity
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        JOIN buses b ON s.bus_id = b.id
        WHERE s.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/* =========================
   AMBIL DATA
========================= */
if($is_pp){
    $data_go   = getSchedule($conn, $go_id);
    $data_back = getSchedule($conn, $back_id);

    if(!$data_go || !$data_back) die("Jadwal tidak valid");

    $data = $data_go;
    $schedule_id = $go_id;
} else {
    $data = getSchedule($conn, $schedule_id);
    if(!$data) die("Schedule tidak ditemukan");
}

$capacity = (int)$data['capacity'];

/* =========================
   AMBIL SEAT + STATUS
========================= */
$selectable_seats = [];
$booked_seats = [];

$stmt = $conn->prepare("
    SELECT 
        s.seat_number,
        s.is_driver,

        CASE 
            WHEN EXISTS (
                SELECT 1 
                FROM booking_details bd
                JOIN bookings b ON b.id = bd.booking_id
                WHERE bd.seat_number = s.seat_number
                AND b.schedule_id = s.schedule_id
                AND b.status IN ('pending','paid','settlement')
            )
            OR s.status = 'booked'
            THEN 'booked'
            ELSE 'available'
        END AS status

    FROM seats s
    WHERE s.schedule_id = ?
    ORDER BY s.seat_number ASC
");

$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();

while ($s = $result->fetch_assoc()) {

    $seat = strtoupper(trim($s['seat_number']));

    // skip driver
    if ($s['is_driver'] == 1) continue;

    $selectable_seats[] = [
        'seat_number' => $seat,
        'status' => $s['status']
    ];

    if ($s['status'] === 'booked' || $s['is_driver'] == 1) {
        $booked_seats[] = $seat;
    }
}

/* =========================
   AUTO FILL
========================= */
$current_count = count($selectable_seats);

if ($current_count < $capacity) {
    for ($i = $current_count + 1; $i <= $capacity; $i++) {

        $seat = 'S' . $i;

        $selectable_seats[] = [
            'seat_number' => $seat,
            'status' => 'available'
        ];
    }
}

/* =========================
   JIKA PP → GABUNG BOOKED
========================= */
if($is_pp){

    $booked_go = [];
    $booked_back = [];

    // GO
    $stmt1 = $conn->prepare("
        SELECT seat_number FROM seats 
        WHERE schedule_id = ? AND status='booked'
    ");
    $stmt1->bind_param("i", $go_id);
    $stmt1->execute();
    $res1 = $stmt1->get_result();

    while($r = $res1->fetch_assoc()){
        $booked_go[] = strtoupper(trim($r['seat_number']));
    }

    // BACK
    $stmt2 = $conn->prepare("
        SELECT seat_number FROM seats 
        WHERE schedule_id = ? AND status='booked'
    ");
    $stmt2->bind_param("i", $back_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();

    while($r = $res2->fetch_assoc()){
        $booked_back[] = strtoupper(trim($r['seat_number']));
    }

    $booked_seats = array_unique(array_merge($booked_go, $booked_back));
}

/* =========================
   SORT BIAR RAPI
========================= */
usort($selectable_seats, function($a, $b){
    return strcmp($a['seat_number'], $b['seat_number']);
});

/* =========================
   LAYOUT
========================= */
$hot_seat = array_shift($selectable_seats);
$rows = array_chunk($selectable_seats, 3);
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">    <title>Pilih Kursi - <?= $data['bus_name'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .content-wrapper { padding-top: 80px; padding-bottom: 240px; }
        .bus-frame { background: white; border: 4px solid #e2e8f0; border-radius: 40px 40px 20px 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .seat-btn { transition: all 0.2s; position: relative; border-bottom-width: 4px; }
        .seat-btn:active { transform: scale(0.95); }
        .headrest { position: absolute; top: 0; left: 20%; right: 20%; height: 4px; background: rgba(0,0,0,0.1); border-radius: 0 0 4px 4px; }
        .aisle-line { background: repeating-linear-gradient(0deg, #f1f5f9, #f1f5f9 10px, #ffffff 10px, #ffffff 20px); }
        .floating-bar { position: fixed; bottom: 85px; left: 0; right: 0; z-index: 40; }
    </style>
</head>
<body class="max-w-md mx-auto min-h-screen relative">

<?php include '../components/header.php'; ?>

<main class="content-wrapper px-6" x-data="seatApp()">
    <div class="bg-slate-900 rounded-3xl p-6 mb-6 text-white shadow-xl relative overflow-hidden">
        <p class="text-[10px] text-blue-400 font-extrabold tracking-widest mb-1 uppercase">Executive Class • <?= $capacity ?> Seats</p>
        <h2 class="text-lg font-bold flex items-center gap-2 italic">
            <?= $data['departure_city'] ?> <i class="fa-solid fa-arrow-right text-xs text-blue-400"></i> <?= $data['arrival_city'] ?>
        </h2>
    </div>

    <div class="grid grid-cols-4 gap-2 mb-8 bg-white p-3 rounded-2xl shadow-sm border text-[9px] font-bold text-slate-500 uppercase text-center">
        <div class="flex flex-col items-center gap-1"><div class="w-4 h-4 bg-white border border-slate-300 rounded"></div>Kosong</div>
        <div class="flex flex-col items-center gap-1"><div class="w-4 h-4 bg-slate-300 rounded"></div>Terisi</div>
        <div class="flex flex-col items-center gap-1"><div class="w-4 h-4 bg-red-600 rounded"></div>Pilihan</div>
        <div class="flex flex-col items-center gap-1"><div class="w-4 h-4 bg-slate-800 rounded"></div>Sopir</div>
    </div>

    <form method="POST" action="proses_pilih_kursi.php">
        <input type="hidden" name="travel_date" value="<?= $travel_date ?>">
        <input type="hidden" name="schedule_id" value="<?= $schedule_id ?>">
        <input type="hidden" name="go_id" value="<?= $go_id ?>">
        <input type="hidden" name="back_id" value="<?= $back_id ?>">
        <input type="hidden" name="selected_seats" :value="selectedSeats.join(',')">
        <input type="hidden" name="passenger_names" :value="JSON.stringify(getPassengers())">
        

        <div class="bus-frame p-6 relative mb-8">
            <div class="h-10 bg-slate-800 rounded-t-3xl mb-8 flex items-center justify-center border-b-4 border-blue-500/20">
                <div class="w-12 h-1 bg-slate-600 rounded-full"></div>
            </div>

            <div class="flex justify-between items-center mb-10 px-1">
                <div class="w-12 h-12">
                    <?php if($hot_seat): ?>
                        <button type="button" @click="toggleSeat('<?= $hot_seat['seat_number'] ?>')"
                            :disabled="bookedSeats.includes('<?= $hot_seat['seat_number'] ?>')"
                            class="seat-btn w-full h-full rounded-xl text-xs font-bold"
                            :class="getSeatClass('<?= $hot_seat['seat_number'] ?>')">
                            <div class="headrest"></div><?= $hot_seat['seat_number'] ?>
                        </button>
                    <?php endif; ?>
                </div>
                <i class="fa-solid fa-dharmachakra text-2xl text-slate-300 animate-spin-slow"></i>
                <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center text-white border-b-4 border-slate-950">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
            </div>

            <div class="space-y-4">
                <?php foreach ($rows as $row): ?>
                <div class="flex justify-between items-center">
                    <div class="w-12 h-12">
                        <?php if(isset($row[0])): ?>
                        <button type="button" @click="toggleSeat('<?= $row[0]['seat_number'] ?>')"
                            class="seat-btn w-full h-full rounded-xl text-xs font-bold"
                            :class="getSeatClass('<?= $row[0]['seat_number'] ?>')">
                            <div class="headrest"></div><?= $row[0]['seat_number'] ?>
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="flex-1 mx-4 h-10 aisle-line rounded-lg opacity-40"></div>

                    <div class="flex gap-2">
                        <?php for($i=1; $i<3; $i++): ?>
                            <div class="w-12 h-12">
                                <?php if(isset($row[$i])): ?>
                                <button type="button" @click="toggleSeat('<?= $row[$i]['seat_number'] ?>')"
                                    class="seat-btn w-full h-full rounded-xl text-xs font-bold"
                                    :class="getSeatClass('<?= $row[$i]['seat_number'] ?>')">
                                    <div class="headrest"></div><?= $row[$i]['seat_number'] ?>
                                </button>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="space-y-3">
            <div class="bg-white p-4 rounded-2xl border flex items-center gap-4 shadow-sm">
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Utama</p>
                    <p class="font-bold text-slate-800"><?= $user_name ?></p>
                </div>
            </div>

            <template x-if="selectedSeats.length >= 2">
                <div class="bg-blue-600 p-4 rounded-2xl shadow-lg shadow-blue-100 border border-blue-500">
                    <label class="text-[10px] font-bold text-blue-100 uppercase block mb-1">Nama Penumpang Ke-2</label>
                    <input type="text" x-model="friendName" placeholder="Masukkan nama..."
                        class="w-full bg-white/20 border border-white/30 rounded-xl p-3 text-white placeholder:text-blue-200 outline-none focus:bg-white/30 transition-all font-bold">
                </div>
            </template>
        </div>

        <div class="floating-bar px-6">
            <div class="bg-white/95 backdrop-blur-md p-4 rounded-3xl shadow-[0_-10px_30px_rgba(0,0,0,0.1)] border border-white flex justify-between items-center">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Kursi</p>
                    <p class="text-lg font-extrabold text-slate-900" x-text="selectedSeats.join(', ') || '-'"></p>
                </div>
                <button type="submit" :disabled="!canCheckout()"
                    class="bg-red-600 hover:bg-red-700 text-white px-8 py-4 rounded-2xl font-bold transition-all shadow-lg shadow-red-200 disabled:bg-slate-300 disabled:shadow-none">
                    Lanjut <i class="fa-solid fa-chevron-right ml-1 text-xs"></i>
                </button>
            </div>
        </div>
    </form>
</main>

<?php include '../components/navbar.php'; ?>

<script>
function seatApp(){
    return {
        selectedSeats: [],
        bookedSeats: <?= json_encode($booked_seats) ?>,
        friendName: '',
        toggleSeat(seat){
            if(this.bookedSeats.includes(seat)) return;
            if(this.selectedSeats.includes(seat)){
                this.selectedSeats = this.selectedSeats.filter(s => s !== seat);
                return;
            }
            if(this.selectedSeats.length >= 2){
                alert('Maksimal 2 kursi per transaksi');
                return;
            }
            this.selectedSeats.push(seat);
        },
        getSeatClass(id){
            if(this.bookedSeats.includes(id)) return 'bg-slate-200 border-slate-300 text-slate-400 cursor-not-allowed';
            if(this.selectedSeats.includes(id)) return 'bg-red-600 border-red-800 text-white shadow-inner';
            return 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50';
        },
        getPassengers(){
            let names = {};
            if(this.selectedSeats.length >= 2){
                names[this.selectedSeats[1]] = this.friendName;
            }
            return names;
        },
        canCheckout(){
            if(this.selectedSeats.length === 0) return false;
            if(this.selectedSeats.length === 2) return this.friendName.trim().length > 2;
            return true;
        }
    }
}
</script>
<script>
function loadSeats() {

    fetch('get_seats.php?schedule_id=<?= $schedule_id ?>')
    .then(res => res.json())
    .then(data => {

        data.forEach(seat => {

            let el = document.querySelector(`[data-seat="${seat.seat_number}"]`);

            if (!el) return;

            el.classList.remove('bg-green-500','bg-red-500','bg-yellow-400');

            if (seat.status === 'booked') {
                el.classList.add('bg-red-500');
                el.disabled = true;

            } else if (seat.status === 'locked') {
                el.classList.add('bg-yellow-400');
                el.disabled = true;

            } else {
                el.classList.add('bg-green-500');
                el.disabled = false;
            }

        });

    });
}

/* LOAD AWAL */
loadSeats();

/* AUTO REFRESH TIAP 5 DETIK */
setInterval(loadSeats, 5000);
</script>
</body>
</html>