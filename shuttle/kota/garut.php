<?php
require_once '../../config/koneksi.php';

// Konfigurasi Kota
$city = 'Garut';
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

/* =========================
   AMBIL DAFTAR RUTE DARI GARUT
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
   MAP JADWAL (UNTUK AUTO-DATE)
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
        $schedule_map[$row['route_id']] = $row['first_date'];
    }
}
$schedule_json = json_encode($schedule_map);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mahayana Shuttle - Garut</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        .bg-navy { background-color: #0a192f; }
        .text-gold { color: #d4af37; }
        .bg-gold { background-color: #d4af37; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        
        /* Custom Toggle */
        input:checked ~ .toggle-bg { background-color: #d4af37; }
        input:checked ~ .toggle-dot { transform: translateX(100%); }
    </style>
</head>
<body class="bg-gray-50 max-w-md mx-auto min-h-screen relative shadow-2xl border-x">

    <?php include '../../components/header.php'; ?>

    <main class="pb-32">
        <!-- HERO SECTION -->
        <div class="relative h-64 w-full overflow-hidden">
            <img src="https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=800" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-navy/70 flex flex-col items-center justify-center text-center px-4">
                <h1 class="text-gold text-xl font-bold tracking-widest uppercase mb-1 italic">Mahayana Shuttle</h1>
                <p class="text-white text-5xl font-black uppercase tracking-tighter">Garut</p>
                <div class="w-16 h-1 bg-gold mt-2"></div>
            </div>
        </div>

        <!-- FORM CONTAINER (FORM DIMULAI DI SINI UNTUK KESERAGAMAN) -->
        <div class="px-4 -mt-10 relative z-20">
            <div class="bg-white rounded-3xl p-6 shadow-xl border border-gray-100">
                <p class="text-center text-[10px] font-bold text-navy uppercase tracking-[0.2em] mb-6">
                    Reservasi Tiket Garut
                </p>

                <form action="../kota/hasil_jadwal.php" method="GET" class="space-y-6">
                    
                    <!-- TUJUAN -->
                    <div class="relative">
                        <label class="text-[10px] uppercase font-bold text-gray-400 ml-1">Tujuan Perjalanan</label>
                        <div class="flex items-center border-b-2 border-gray-100 focus-within:border-gold transition-all py-2">
                            <i class="fa-solid fa-map-location-dot text-navy mr-3"></i>
                            <select name="route_id" id="routeSelect" required
                                class="w-full bg-transparent font-extrabold text-navy outline-none appearance-none">
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

                    <!-- TANGGAL PERGI -->
                    <div class="relative">
                        <label class="text-[10px] uppercase font-bold text-gray-400 ml-1">Tanggal Keberangkatan</label>
                        <div class="flex items-center border-b-2 border-gray-100 focus-within:border-gold transition-all py-2">
                            <i class="fa-solid fa-calendar-day text-navy mr-3"></i>
                            <input type="date" name="date" id="dateInput" required value="<?= $today ?>"
                                   class="w-full bg-transparent font-extrabold text-navy outline-none">
                        </div>
                    </div>

                    <!-- TANGGAL PULANG (HIDDEN) -->
                    <div class="relative hidden" id="returnBox">
                        <label class="text-[10px] uppercase font-bold text-gold ml-1">Tanggal Kepulangan</label>
                        <div class="flex items-center border-b-2 border-gray-100 focus-within:border-gold transition-all py-2">
                            <i class="fa-solid fa-calendar-check text-gold mr-3"></i>
                            <input type="date" name="return_date" id="returnInput" value="<?= $tomorrow ?>"
                                   class="w-full bg-transparent font-extrabold text-navy outline-none">
                        </div>
                    </div>

                    <!-- TOGGLE PULANG PERGI -->
                    <div class="flex justify-between items-center bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-navy rounded-xl flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-repeat text-gold text-xs"></i>
                            </div>
                            <div>
                                <span class="block text-sm font-bold text-navy leading-none">Pulang Pergi</span>
                                <span class="text-[9px] text-gray-400 font-bold uppercase">Booking Sekaligus</span>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_round_trip" id="rt_toggle" value="1" class="sr-only peer">
                            <div class="w-12 h-6 bg-gray-300 rounded-full toggle-bg transition-all"></div>
                            <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full toggle-dot transition-all shadow-md"></div>
                        </label>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <button type="submit" class="w-full bg-navy hover:bg-black active:scale-[0.98] text-gold font-black py-4 rounded-2xl shadow-xl transition-all flex items-center justify-center gap-3 border border-gold/30 uppercase tracking-widest text-sm">
                        <i class="fa-solid fa-bus"></i>
                        CARI JADWAL SEKARANG
                    </button>
                </form>
            </div>
        </div>

        <!-- HELP / WA SECTION -->
        <div class="px-4 mt-8">
            <div class="bg-white rounded-3xl flex items-center justify-between relative overflow-hidden shadow-sm border border-gray-100 min-h-[140px]">
                <div class="z-10 pl-6 py-6 w-3/5">
                    <h3 class="text-lg font-black text-navy leading-tight uppercase italic tracking-tighter">Butuh Bantuan?</h3>
                    <p class="text-[10px] text-gray-500 font-bold mt-1 mb-4">CS kami siap membantu reservasi Anda.</p>
                    <a href="https://wa.me/6282220152005" target="_blank" class="bg-navy rounded-full px-5 py-2.5 inline-flex items-center gap-2 text-gold font-black text-[9px] hover:bg-black transition-all active:scale-95 uppercase tracking-wider shadow-lg">
                        Chat Admin Garut <i class="fa-brands fa-whatsapp text-sm"></i>
                    </a>
                </div>
                <div class="w-32 relative z-0 flex justify-end self-end">
                    <img src="https://img.freepik.com/premium-photo/female-customer-service-assistant-desktop-call-center_484651-22322.jpg" 
                         class="h-32 w-auto object-cover scale-[1.3] translate-y-4 origin-bottom-right"
                         style="-webkit-mask-image: radial-gradient(circle at top right, black 10%, rgba(255,255,255,0.7) 30%, transparent 60%); 
                                mask-image: radial-gradient(circle at top right, black 10%, rgba(255,255,255,0.7) 30%, transparent 60%);">
                </div>
            </div>
        </div>
    </main>

    <?php include '../../components/navbar.php'; ?>

    <script>
    const scheduleMap = <?= $schedule_json ?>;
    const routeSelect = document.getElementById('routeSelect');
    const dateInput = document.getElementById('dateInput');
    const returnInput = document.getElementById('returnInput');
    const toggle = document.getElementById('rt_toggle');
    const returnBox = document.getElementById('returnBox');

    /* Auto-set date based on schedule availability */
    routeSelect.addEventListener('change', function () {
        const id = this.value;
        if (scheduleMap[id]) {
            dateInput.value = scheduleMap[id];
            
            // Set default return to +1 day from departure
            let d = new Date(scheduleMap[id]);
            d.setDate(d.getDate() + 1);
            let y = d.getFullYear(), m = ('0'+(d.getMonth()+1)).slice(-2), dd = ('0'+d.getDate()).slice(-2);
            returnInput.value = `${y}-${m}-${dd}`;
        }
    });

    /* Toggle return section visibility */
    toggle.addEventListener('change', function () {
        if(this.checked) {
            returnBox.classList.remove('hidden');
        } else {
            returnBox.classList.add('hidden');
        }
    });
    </script>
</body>
</html>