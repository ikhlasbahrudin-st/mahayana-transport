<?php
require_once '../../config/koneksi.php';

$city = 'Sukabumi';

/* =========================
   DEFAULT DATE
========================= */
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

/* =========================
   ROUTE LIST (SUKABUMI ONLY)
========================= */
$query_destinations = "
    SELECT id AS route_id, departure_city, arrival_city
    FROM routes
    WHERE departure_city = '$city'
    ORDER BY arrival_city ASC
";

$result_destinations = mysqli_query($conn, $query_destinations);

$destinations = [];
if ($result_destinations) {
    while ($row = mysqli_fetch_assoc($result_destinations)) {
        $destinations[] = $row;
    }
}

/* =========================
   SCHEDULE MAP
========================= */
$schedule_map = [];
$query_schedule = "
    SELECT route_id, MIN(date) as first_date
    FROM schedules
    GROUP BY route_id
";

$result_schedule = mysqli_query($conn, $query_schedule);

if ($result_schedule) {
    while ($row = mysqli_fetch_assoc($result_schedule)) {
        if (!empty($row['first_date'])) {
            $schedule_map[$row['route_id']] = $row['first_date'];
        }
    }
}

$schedule_json = json_encode($schedule_map);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mahayana Shuttle - Sukabumi</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-navy-dark { background-color: #0a192f; }
        .text-gold { color: #d4af37; }
        .bg-gold { background-color: #d4af37; }
        .border-gold { border-color: #d4af37; }
        
        /* Custom Toggle Style */
        .toggle-dot { transition: all 0.3s ease-in-out; }
        input:checked ~ .toggle-bg { background-color: #d4af37; }
        input:checked ~ .toggle-dot { transform: translateX(100%); }
    </style>
</head>

<body class="bg-gray-50 max-w-md mx-auto min-h-screen shadow-2xl border-x relative">

<?php include '../../components/header.php'; ?>

<main class="pb-32">

    <!-- HERO SECTION -->
    <div class="relative h-64 w-full overflow-hidden">
        <img src="https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=800"
             class="w-full h-full object-cover">
        <!-- Overlay Navy Gelap -->
        <div class="absolute inset-0 bg-navy-dark/70 flex flex-col items-center justify-center text-center px-4">
            <h1 class="text-gold text-xl font-bold tracking-widest uppercase mb-1">Mahayana Shuttle</h1>
            <p class="text-white text-5xl font-black uppercase tracking-tighter shadow-sm">Sukabumi</p>
            <div class="w-16 h-1 bg-gold mt-2"></div>
        </div>
    </div>

    <!-- FORM CONTAINER -->
    <div class="px-4 -mt-10 relative z-20">
        <div class="bg-white p-6 rounded-3xl shadow-2xl border border-gray-100">
            <p class="text-center text-[10px] font-bold text-navy-dark uppercase tracking-[0.2em] mb-6">
                Booking Tiket Perjalanan
            </p>

            <form action="../kota/hasil_jadwal.php" method="GET" class="space-y-6">
                
                <!-- ROUTE SELECT -->
                <div class="relative">
                    <label class="text-[10px] uppercase font-bold text-gray-400 ml-1">Tujuan Perjalanan</label>
                    <div class="flex items-center border-b-2 border-gray-100 focus-within:border-gold transition-all py-2">
                        <i class="fa-solid fa-map-location-dot text-navy-dark mr-3"></i>
                        <select name="route_id" id="routeSelect" required
                            class="w-full bg-transparent font-extrabold text-navy-dark outline-none appearance-none">
                            <option value="">Pilih Tujuan</option>
                            <?php foreach ($destinations as $d): ?>
                                <option value="<?= $d['route_id'] ?>">
                                    <?= $d['departure_city'] ?> → <?= $d['arrival_city'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fa-solid fa-chevron-down text-[10px] text-gray-400"></i>
                    </div>
                </div>

                <!-- DEPARTURE DATE -->
                <div class="relative">
                    <label class="text-[10px] uppercase font-bold text-gray-400 ml-1">Tanggal Keberangkatan</label>
                    <div class="flex items-center border-b-2 border-gray-100 focus-within:border-gold transition-all py-2">
                        <i class="fa-solid fa-calendar-alt text-navy-dark mr-3"></i>
                        <input type="date" name="date" id="dateInput" required
                               value="<?= $today ?>"
                               class="w-full bg-transparent font-extrabold text-navy-dark outline-none">
                    </div>
                </div>

                <!-- RETURN DATE (HIDDEN BY DEFAULT) -->
                <div class="relative hidden transition-all duration-300" id="returnBox">
                    <label class="text-[10px] uppercase font-bold text-gold ml-1">Tanggal Kepulangan</label>
                    <div class="flex items-center border-b-2 border-gray-100 focus-within:border-gold transition-all py-2">
                        <i class="fa-solid fa-calendar-check text-gold mr-3"></i>
                        <input type="date" name="return_date" id="returnInput"
                               value="<?= $tomorrow ?>"
                               class="w-full bg-transparent font-extrabold text-navy-dark outline-none">
                    </div>
                </div>

                <!-- ROUND TRIP TOGGLE -->
                <div class="flex justify-between items-center bg-gray-50 p-4 rounded-2xl border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-navy-dark rounded-xl flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-repeat text-gold text-xs"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-navy-dark leading-none">Pulang Pergi</span>
                            <span class="text-[9px] text-gray-400">Pesan tiket sekaligus</span>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_round_trip" id="rt_toggle" value="1" class="sr-only peer">
                        <div class="w-12 h-6 bg-gray-300 peer-focus:outline-none rounded-full toggle-bg transition-all"></div>
                        <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full toggle-dot transition-all shadow-md"></div>
                    </label>
                </div>

                <!-- SUBMIT BUTTON -->
                <button type="submit" class="w-full bg-navy-dark hover:bg-black active:scale-[0.97] text-gold font-black py-4 rounded-2xl shadow-xl transition-all flex items-center justify-center gap-3 border border-gold/30">
                    <i class="fa-solid fa-ticket"></i>
                    CARI JADWAL
                </button>

            </form>
        </div>
    </div>

    <!-- BANTUAN SECTION (TAMPILAN SEMULA - WARNA DISESUAIKAN) -->
    <section class="px-4 mt-8">
        <div class="bg-white border border-gray-100 rounded-3xl p-5 relative overflow-hidden flex items-center shadow-sm">
            <div class="flex-1 relative z-10">
                <h3 class="text-red-600 font-black text-lg leading-tight">Kamu Butuh Bantuan?</h3>
                <p class="text-[10px] text-gray-400 font-medium leading-tight mt-1 mb-4">Tanya admin kami di menu Bhisa Chat Center.</p>
                <a href="#" class="inline-flex items-center gap-2 bg-white border border-gray-200 px-4 py-2 rounded-full shadow-sm active:bg-gray-50 transition-colors">
                    <span class="text-[10px] font-black text-navy-dark uppercase">Klik di sini</span>
                    <i class="fa-solid fa-comment-dots text-navy-dark text-xs"></i>
                </a>
            </div>
            <div class="w-32 relative z-0 flex justify-end">
                <img src="https://img.freepik.com/premium-photo/female-customer-service-assistant-desktop-call-center_484651-22322.jpg" 
                    alt="Customer Service" 
                    class="h-32 w-auto object-cover object-center scale-[1.3] translate-y-4 origin-bottom-right"
                    style="-webkit-mask-image: radial-gradient(circle at top right, black 10%, rgba(255,255,255,0.7) 30%, transparent 60%); 
                            mask-image: radial-gradient(circle at top right, black 10%, rgba(255,255,255,0.7) 30%, transparent 60%);">
            </div>
        </div>
    </section>

</main>

<?php include '../../components/navbar.php'; ?>

<script>
const scheduleMap = <?= $schedule_json ?>;
const routeSelect = document.getElementById('routeSelect');
const dateInput = document.getElementById('dateInput');
const returnInput = document.getElementById('returnInput');
const toggle = document.getElementById('rt_toggle');
const returnBox = document.getElementById('returnBox');

/* AUTO SET DATE */
routeSelect.addEventListener('change', function () {
    let id = this.value;
    if (scheduleMap[id]) {
        dateInput.value = scheduleMap[id];
        let d = new Date(scheduleMap[id]);
        d.setDate(d.getDate() + 1);
        returnInput.value = d.toISOString().split('T')[0];
    }
});

/* TOGGLE RETURN BOX */
toggle.addEventListener('change', function () {
    returnBox.classList.toggle('hidden', !this.checked);
});
</script>

</body>
</html>