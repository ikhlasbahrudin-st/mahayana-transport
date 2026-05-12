<?php
include 'middleware.php';
include '../config/koneksi.php';

/* =========================
   SUMMARY
========================= */
$totalBooking = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings"))['total'] ?? 0;
$totalUser    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'] ?? 0;
$totalBus     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM buses"))['total'] ?? 0;




// Query mengambil 7 transaksi terakhir yang sudah dibayar
$query = "SELECT amount, paid_at FROM payments WHERE status = 'paid' ORDER BY paid_at DESC LIMIT 7";
$result = mysqli_query($conn, $query);

$labels = [];
$dataRev = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Format label jam (contoh: 01:38)
    $labels[] = date('H:i', strtotime($row['paid_at']));
    // Masukkan nominal ke array data
    $dataRev[] = (float)$row['amount'];
}

// Balik urutan agar data lama di kiri, data baru di kanan
$labels = array_reverse($labels);
$dataRev = array_reverse($dataRev);

/* =========================
   GRAFIK
========================= */
$labels = [];
$dataBooking = [];
$dataRev = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d M', strtotime($date));

    $qB = mysqli_query($conn, "SELECT COUNT(*) total FROM bookings WHERE DATE(created_at)='$date'");
    $dataBooking[] = (int)mysqli_fetch_assoc($qB)['total'];

    $qR = mysqli_query($conn, "SELECT SUM(total_price) total FROM bookings WHERE DATE(created_at)='$date' AND status='confirmed'");
    $dataRev[] = (int)mysqli_fetch_assoc($qR)['total'] ?? 0;
}

/* =========================
   REALTIME SCHEDULE ENGINE (FIX FINAL + SAFE DATETIME)
========================= */
$scheduleBaseQuery = "
SELECT 
    s.id,
    s.bus_id,
    s.route_id,
    s.departure_time,
    s.arrival_time,
    s.date,
    s.is_daily,

    b.bus_name,
    r.departure_city,
    r.arrival_city,

    STR_TO_DATE(
        CONCAT(
            CASE 
                WHEN s.is_daily = 1 THEN CURDATE()
                ELSE s.date
            END,
            ' ',
            s.departure_time
        ),
        '%Y-%m-%d %H:%i:%s'
    ) AS dep_dt,

    STR_TO_DATE(
        CONCAT(
            CASE 
                WHEN s.is_daily = 1 THEN CURDATE()
                ELSE s.date
            END,
            ' ',
            s.arrival_time
        ),
        '%Y-%m-%d %H:%i:%s'
    ) AS arr_dt

FROM schedules s
JOIN buses b ON s.bus_id = b.id
JOIN routes r ON s.route_id = r.id
";

/* =========================
   UPCOMING
========================= */
$upcoming = mysqli_query($conn, "
    SELECT *,
            TIMESTAMPDIFF(MINUTE, NOW(), dep_dt) AS min_left
    FROM ($scheduleBaseQuery) x
    WHERE dep_dt > NOW()
    ORDER BY dep_dt ASC
    LIMIT 5
");

/* =========================
   ON TRIP
========================= */
$onTrip = mysqli_query($conn, "
    SELECT *,
           TIMESTAMPDIFF(MINUTE, dep_dt, NOW()) as elapsed,
           TIMESTAMPDIFF(MINUTE, dep_dt, arr_dt) as total_duration
    FROM ($scheduleBaseQuery) x
    WHERE NOW() BETWEEN dep_dt AND arr_dt
    ORDER BY dep_dt DESC
");

/* =========================
   FINISHED (24 JAM)
========================= */
$finished = mysqli_query($conn, "
    SELECT *,
           TIMESTAMPDIFF(MINUTE, dep_dt, NOW()) AS min_ago
    FROM ($scheduleBaseQuery) x
    WHERE NOW() > arr_dt
    AND dep_dt >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ORDER BY dep_dt DESC
    LIMIT 5
");

/* =========================
   STATUS CHART
========================= */
$statusQuery = mysqli_query($conn, "
    SELECT status, COUNT(*) total 
    FROM bookings 
    GROUP BY status
");

$statusLabels = [];
$statusData   = [];

while ($r = mysqli_fetch_assoc($statusQuery)) {
    $statusLabels[] = ucfirst($r['status']);
    $statusData[]   = (int)$r['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Dashboard Mahayana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden text-slate-900">

<!-- SIDEBAR -->
<div class="w-64 hidden md:block border-r bg-white shadow-sm z-20">
    <?php include 'components/sidebar.php'; ?>
</div>

<div class="flex-1 flex flex-col min-w-0">
    <!-- NAVBAR -->
    <?php include 'components/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto custom-scrollbar p-6">
        <!-- HEADER SECTION -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Operasional Armada</h1>
                <p class="text-slate-500 mt-1">Monitoring status armada dan statistik transaksi real-time.</p>
            </div>
            <div class="bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-200 text-right">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Waktu Server</p>
                <p class="text-sm font-bold text-blue-600"><i class="fa-regular fa-calendar-check mr-2"></i><?= date('d M Y | H:i') ?></p>
            </div>
        </div>

        <!-- STAT CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-5 hover:border-blue-200 transition-colors cursor-default">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Booking</p>
                    <h3 class="text-2xl font-black text-slate-800"><?= number_format($totalBooking) ?></h3>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-5 hover:border-blue-200 transition-colors cursor-default">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Pelanggan</p>
                    <h3 class="text-2xl font-black text-slate-800"><?= number_format($totalUser) ?></h3>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-5 hover:border-blue-200 transition-colors cursor-default">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-bus-simple"></i>
                </div>
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Armada Aktif</p>
                    <h3 class="text-2xl font-black text-slate-800"><?= number_format($totalBus) ?></h3>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            
            <!-- LEFT COLUMN: OPERATIONAL LISTS -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- UPCOMING -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center">
                        <h3 class="font-bold text-slate-700 text-sm uppercase tracking-wider flex items-center">
                            <span class="w-2 h-6 bg-blue-600 rounded-full mr-3"></span>
                            Akan Berangkat
                        </h3>
                        <span class="text-[10px] bg-blue-100 text-blue-700 font-bold px-2 py-1 rounded-md uppercase">Top 5 Segera</span>
                    </div>
                    <div class="p-4 space-y-3">
                        <?php if(mysqli_num_rows($upcoming) == 0): ?>
                            <div class="text-center py-8">
                                <p class="text-slate-400 text-sm italic">Belum ada jadwal terdekat</p>
                            </div>
                        <?php endif; ?>
                        <?php while($s = mysqli_fetch_assoc($upcoming)): ?>
                        <div class="group flex items-center justify-between p-4 rounded-xl border border-slate-100 bg-white hover:bg-slate-50 transition-all hover:shadow-md">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-500 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    <i class="fa-solid fa-calendar-day text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800"><?= $s['bus_name'] ?></h4>
                                    <p class="text-xs text-slate-500 font-medium flex items-center">
                                        <?= $s['departure_city'] ?> 
                                        <i class="fa-solid fa-chevron-right mx-2 text-[8px] text-slate-300"></i> 
                                        <?= $s['arrival_city'] ?>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <?php 
                                    $hours = floor($s['min_left'] / 60);
                                    $mins = $s['min_left'] % 60;
                                    $timeStr = ($hours > 0 ? $hours."j " : "") . $mins . "m";
                                ?>
                                <span class="block font-black text-blue-600 text-lg leading-none"><?= $timeStr ?></span>
                                <span class="text-[10px] uppercase font-extrabold text-slate-400">Menuju Keberangkatan</span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- ON TRIP -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-amber-50 border-b border-amber-100 p-4">
                        <h3 class="font-bold text-amber-800 text-sm uppercase tracking-wider flex items-center">
                            <span class="w-2 h-6 bg-amber-500 rounded-full mr-3"></span>
                            Sedang Dalam Perjalanan
                        </h3>
                    </div>
                    <div class="p-6">
                        <?php if(mysqli_num_rows($onTrip) == 0): ?>
                            <div class="text-center py-6">
                                <i class="fa-solid fa- van-shuttle text-slate-200 text-4xl mb-3"></i>
                                <p class="text-slate-400 text-sm italic">Saat ini tidak ada armada di perjalanan</p>
                            </div>
                        <?php endif; ?>
                        <?php while($s = mysqli_fetch_assoc($onTrip)): 
                            // Hitung progress bar dinamis
                            $percent = ($s['total_duration'] > 0) ? ($s['elapsed'] / $s['total_duration']) * 100 : 0;
                            $percent = max(5, min(95, $percent)); // minimal 5% maksimal 95% agar tetap terlihat 'berjalan'
                        ?>
                        <div class="mb-8 last:mb-0">
                            <div class="flex justify-between items-end mb-3">
                                <div>
                                    <span class="inline-block px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded mb-1 uppercase tracking-tighter">Live Tracking</span>
                                    <h4 class="font-bold text-slate-800 text-base leading-tight"><?= $s['bus_name'] ?></h4>
                                    <p class="text-xs text-slate-500 uppercase font-semibold tracking-wide"><?= $s['departure_city'] ?> Ke <?= $s['arrival_city'] ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded flex items-center gap-2">
                                        <span class="relative flex h-2 w-2">
                                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                          <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                        </span>
                                        In Transit
                                    </span>
                                </div>
                            </div>
                            <div class="relative w-full bg-slate-100 h-3 rounded-full overflow-hidden shadow-inner border border-slate-200">
                                <div class="bg-gradient-to-r from-amber-400 to-amber-600 h-full transition-all duration-1000 ease-in-out relative" style="width: <?= $percent ?>%">
                                    <div class="absolute right-0 top-0 bottom-0 w-8 bg-white/20 skew-x-12"></div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- FINISHED -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-800 p-4 flex justify-between items-center text-white">
                        <h3 class="font-bold text-sm uppercase tracking-wider flex items-center">
                            <i class="fa-solid fa-circle-check text-emerald-400 mr-3"></i>
                            Baru Saja Berangkat
                        </h3>
                        <span class="text-[10px] font-bold opacity-60 uppercase tracking-widest">Logs 24 Jam</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                    <th class="p-4">Informasi Armada</th>
                                    <th class="p-4 text-right">Status Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php if(mysqli_num_rows($finished) == 0): ?>
                                    <tr>
                                        <td colspan="2" class="p-8 text-center text-slate-400 text-sm italic">Belum ada riwayat perjalanan selesai hari ini</td>
                                    </tr>
                                <?php endif; ?>
                                <?php while($s = mysqli_fetch_assoc($finished)): 
                                    $minAgo = (int) ($s['min_ago'] ?? 0);
                                    if ($minAgo < 1) { $timeText = "Baru saja"; }
                                    elseif ($minAgo < 60) { $timeText = $minAgo . " mnt lalu"; }
                                    else { $timeText = floor($minAgo / 60) . " jam lalu"; }
                                ?>
                                <tr class="hover:bg-slate-50 transition group">
                                    <td class="p-4">
                                        <p class="font-bold text-slate-700 group-hover:text-blue-600 transition-colors">
                                            <?= htmlspecialchars($s['bus_name'] ?? '-') ?>
                                        </p>
                                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">
                                            <?= htmlspecialchars($s['departure_city'] ?? '-') ?>
                                            <i class="fa-solid fa-arrow-right mx-1 opacity-30"></i>
                                            <?= htmlspecialchars($s['arrival_city'] ?? '-') ?>
                                        </p>
                                    </td>
                                    <td class="p-4 text-right">
                                        <span class="text-slate-500 text-xs font-medium bg-slate-100 px-3 py-1.5 rounded-full inline-block">
                                            Berangkat <?= $timeText ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: CHARTS -->
            <div class="space-y-8">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold text-slate-800 text-sm uppercase tracking-widest flex items-center gap-2">
                        <span class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></span>
                        Statistik Keuangan
                    </h3>
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <i class="fa-solid fa-receipt text-blue-600 text-xs"></i>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Total Transaksi Terkini</p>
                    <div class="flex items-baseline gap-2">
                        <!-- Menampilkan data terakhir dari array -->
                        <h4 class="text-2xl font-black text-slate-900">
                            Rp <?= number_format(end($dataRev), 0, ',', '.') ?>
                        </h4>
                        <span class="text-[10px] font-bold text-emerald-500 bg-emerald-50 px-1.5 py-0.5 rounded">
                            <i class="fa-solid fa-arrow-up mr-0.5"></i>Paid
                        </span>
                    </div>
                </div>

                <div class="relative h-[180px]">
                    <canvas id="chart1"></canvas>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-extrabold text-slate-800 text-sm uppercase tracking-wider">Status Pesanan</h3>
                        <i class="fa-solid fa-chart-pie text-indigo-600"></i>
                    </div>
                    <div class="relative">
                        <canvas id="chart2" class="max-h-[220px]"></canvas>
                    </div>
                </div>

                <?php
                // ambil semua schedule
                $cal_q = mysqli_query($conn, "
                    SELECT s.*, r.departure_city, r.arrival_city
                    FROM schedules s
                    LEFT JOIN routes r ON s.route_id = r.id
                    ORDER BY s.date ASC, s.departure_time ASC
                ");
                ?>

            <!-- CALENDAR SCHEDULE -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-6 rounded-2xl shadow-lg text-white relative overflow-hidden">

                <div class="relative z-10">
                    <h4 class="font-bold text-lg mb-3">📅 Kalender Jadwal Bus</h4>

                    <p class="text-slate-300 text-xs mb-4">
                        Semua jadwal keberangkatan bus berdasarkan tanggal dan waktu real.
                    </p>

                    <!-- LIST CALENDAR -->
                    <div class="max-h-72 overflow-y-auto space-y-3 pr-2">

                        <?php while($row = mysqli_fetch_assoc($cal_q)): ?>
                            
                            <?php
                                $date = date('d M Y', strtotime($row['date']));
                                $time = date('H:i', strtotime($row['departure_time']));
                            ?>

                            <div class="bg-white/10 hover:bg-white/20 transition p-3 rounded-xl border border-white/10">

                                <div class="flex justify-between items-center">

                                    <div>
                                        <div class="text-sm font-bold">
                                            <?= $row['departure_city'] ?? 'N/A' ?> → <?= $row['arrival_city'] ?? 'N/A' ?>
                                        </div>

                                        <div class="text-xs text-slate-300 mt-1">
                                            🗓 <?= $date ?> • ⏰ <?= $time ?>
                                        </div>
                                    </div>

                                    <div class="text-xs px-3 py-1 rounded-lg bg-blue-600 text-white">
                                        Bus #<?= $row['bus_id'] ?>
                                    </div>

                                </div>

                            </div>

                        <?php endwhile; ?>

                    </div>
                </div>

                <!-- BACKGROUND ICON -->
                <i class="fa-solid fa-calendar-days absolute -right-6 -bottom-6 text-8xl text-white/10 rotate-12"></i>
            </div>
            </div>

        </div>
    </main>
</div>

<script>
// Chart 1: Line (Revenue)
const ctx1 = document.getElementById('chart1').getContext('2d');
const gradient = ctx1.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

new Chart(ctx1, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Revenue',
            data: <?= json_encode($dataRev) ?>,
            borderColor: '#2563eb',
            borderWidth: 3,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#2563eb',
            pointHoverRadius: 6,
            tension: 0.4,
            fill: true,
            backgroundColor: gradient
        }]
    },
    options: { 
        plugins: { legend: { display: false } }, 
        scales: { 
            y: { display: false }, 
            x: { 
                grid: { display: false },
                ticks: { font: { size: 10, weight: '600' }, color: '#94a3b8' } 
            } 
        },
        interaction: { intersect: false, mode: 'index' }
    }
});

// Chart 2: Doughnut (Booking Status)
new Chart(document.getElementById('chart2'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($statusLabels) ?>,
        datasets: [{
            data: <?= json_encode($statusData) ?>,
            backgroundColor: ['#2563eb', '#f59e0b', '#10b981', '#ef4444'],
            hoverOffset: 10,
            borderWidth: 0
        }]
    },
    options: { 
        cutout: '75%', 
        plugins: { 
            legend: { 
                position: 'bottom', 
                labels: { 
                    padding: 20,
                    boxWidth: 8, 
                    usePointStyle: true,
                    font: { size: 11, weight: '600' } 
                } 
            } 
        } 
    }
});
</script>
<script>
    const ctx1 = document.getElementById('chart1').getContext('2d');

    const blueGradient = ctx1.createLinearGradient(0, 0, 0, 180);
    blueGradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
    blueGradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode($dataRev) ?>,
                borderColor: '#2563eb',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                backgroundColor: blueGradient,
                pointRadius: 0, // Sembunyikan titik agar lebih clean
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#2563eb',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#1e293b',
                    titleFont: { size: 10 },
                    bodyFont: { size: 13, weight: 'bold' },
                    callbacks: {
                        label: (context) => ' Rp ' + context.parsed.y.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                y: { display: false },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#94a3b8', font: { size: 10, weight: '600' } }
                }
            }
        }
    });
</script>


</body>
</html>