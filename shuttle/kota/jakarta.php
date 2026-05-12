<?php
require_once '../../config/koneksi.php';

// Konfigurasi Kota
$city = 'Jakarta';

/* =========================
   DEFAULT DATE
========================= */
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

/* =========================
   ROUTE LIST (JAKARTA ONLY)
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
   SCHEDULE MAP (AUTO-DATE)
========================= */
$schedule_map = [];
$query_schedule = "
    SELECT s.route_id, MIN(CASE WHEN s.is_daily = 1 THEN CURDATE() ELSE s.date END) AS first_date
    FROM schedules s
    JOIN routes r ON r.id = s.route_id
    WHERE r.departure_city = '$city'
    GROUP BY s.route_id
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
    <title>Mahayana Shuttle - Jakarta</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
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
        <img src="https://images.unsplash.com/photo-1506466010722-395aa2bef877?q=80&w=800"
             class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-navy-dark/70 flex flex-col items-center justify-center text-center px-4">
            <h1 class="text-gold text-xl font-bold tracking-widest uppercase mb-1 italic">Mahayana Shuttle</h1>
            <p class="text-white text-5xl font-black uppercase tracking-tighter">Jakarta</p>
            <div class="w-16 h-1 bg-gold mt-2"></div>
        </div>
    </div>

    <!-- FORM CONTAINER -->
    <div class="px-4 -mt-10 relative z-20">
        <div class="bg-white p-6 rounded-3xl shadow-2xl border border-gray-100">
            <p class="text-center text-[10px] font-bold text-navy-dark uppercase tracking-[0.2em] mb-6">
                Booking Tiket Jakarta
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

                <!-- RETURN DATE (HIDDEN) -->
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
                <button type="submit" class="w-full bg-navy-dark hover:bg-black active:scale-[0.97] text-gold font-black py-4 rounded-2xl shadow-xl transition-all flex items-center justify-center gap-3 border border-gold/30 uppercase tracking-widest text-sm">
                    <i class="fa-solid fa-bus"></i>
                    CARI JADWAL SEKARANG
                </button>

            </form>
        </div>
    </div>

    <!-- JADETABEK GRID SECTION (UPDATED) -->
    <div class="px-4 mt-12">
        <h4 class="text-xs font-black text-gray-400 mb-6 border-b border-dashed border-gray-200 pb-2 uppercase tracking-[0.2em]">
            Jadetabek
        </h4>
        <div class="grid grid-cols-4 gap-y-8 text-center">
            <?php
            $points_jadetabek = [
                ['name' => 'Jakarta', 'link' => '/mahayana/shuttle/kota/jakarta.php'], 
                ['name' => 'Cibubur'], ['name' => 'Tangerang'], ['name' => 'Depok'], 
                ['name' => 'Karawang'], ['name' => 'Bekasi'], ['name' => 'Cikarang']
            ];

            foreach ($points_jadetabek as $p): 
                $is_jakarta = ($p['name'] === 'Jakarta');
            ?>
                <?php if ($is_jakarta): ?>
                    <a href="<?= $p['link'] ?>" class="flex flex-col items-center gap-2 relative group active:scale-95 transition-all">
                        <span class="absolute -top-2 z-10 bg-gold text-[8px] text-navy-dark px-2 py-0.5 rounded-sm font-black uppercase tracking-tighter whitespace-nowrap border border-white shadow-md animate-pulse">New</span>
                        <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center p-2 border-2 border-gold shadow-lg shadow-gold/20">
                            <img src="https://cdn-icons-png.flaticon.com/512/3261/3261054.png" alt="<?= $p['name'] ?>" class="w-full h-full object-contain">
                        </div>
                        <span class="text-[10px] font-black text-navy-dark leading-none italic uppercase"><?= $p['name'] ?></span>
                    </a>
                <?php else: ?>
                    <div class="flex flex-col items-center gap-2 relative group">
                        <span class="absolute -top-2 z-10 bg-gray-400 text-[7px] text-white px-1.5 py-0.5 rounded-sm font-black uppercase tracking-tighter whitespace-nowrap border border-white shadow-sm opacity-60">Soon</span>
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center p-2 border-2 border-gray-200 opacity-40 grayscale">
                            <img src="https://cdn-icons-png.flaticon.com/512/3261/3261054.png" alt="<?= $p['name'] ?>" class="w-full h-full object-contain">
                        </div>
                        <span class="text-[10px] font-bold text-gray-300 leading-none italic uppercase"><?= $p['name'] ?></span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- BANTUAN SECTION -->
    <section class="px-4 mt-12">
        <div class="bg-white border border-gray-100 rounded-3xl p-5 relative overflow-hidden flex items-center shadow-sm">
            <div class="flex-1 relative z-10">
                <h3 class="text-navy-dark font-black text-lg leading-tight uppercase italic">Butuh Bantuan?</h3>
                <p class="text-[10px] text-gray-400 font-medium leading-tight mt-1 mb-4">CS kami siap membantu reservasi Jakarta Anda.</p>
                <a href="https://wa.me/6282220152005" target="_blank" class="inline-flex items-center gap-2 bg-navy-dark px-4 py-2 rounded-full shadow-lg active:scale-95 transition-all">
                    <span class="text-[10px] font-black text-gold uppercase tracking-wider">Chat Admin</span>
                    <i class="fa-brands fa-whatsapp text-gold text-xs"></i>
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