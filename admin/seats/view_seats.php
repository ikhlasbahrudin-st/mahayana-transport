<?php
include '../middleware.php';
include '../../config/koneksi.php';

$bus_id = isset($_GET['bus_id']) ? (int) $_GET['bus_id'] : 0;
$schedule_id = isset($_GET['schedule_id']) ? (int) $_GET['schedule_id'] : 0;

$all_buses_q = mysqli_query($conn, "SELECT id, bus_name, capacity, plate_number FROM buses ORDER BY bus_name ASC");

$schedules_q = null;
$bus = null;
$schedule = null;
$grid_data = [];
$booked_from_transactions = [];

if ($bus_id > 0) {
    $bus_q = mysqli_query($conn, "SELECT * FROM buses WHERE id = $bus_id");
    $bus = mysqli_fetch_assoc($bus_q);

    $schedules_q = mysqli_query($conn, "
        SELECT s.*, r.departure_city, r.arrival_city 
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        WHERE s.bus_id = $bus_id 
        ORDER BY s.date DESC, s.departure_time ASC
    ");

    if ($schedule_id > 0) {
        $schedule_q = mysqli_query($conn, "
            SELECT s.*, r.departure_city, r.arrival_city 
            FROM schedules s
            JOIN routes r ON s.route_id = r.id
            WHERE s.id = $schedule_id
        ");
        $schedule = mysqli_fetch_assoc($schedule_q);

        if ($schedule) {
            $trans_q = mysqli_query($conn, "
                SELECT bd.seat_number 
                FROM booking_details bd
                JOIN bookings b ON bd.booking_id = b.id
                WHERE b.schedule_id = $schedule_id 
                AND b.status IN ('pending', 'paid', 'confirmed')
            ");
            while ($t = mysqli_fetch_assoc($trans_q)) {
                $booked_from_transactions[] = $t['seat_number'];
            }

            $seats_q = mysqli_query($conn, "
                SELECT * FROM seats 
                WHERE schedule_id = $schedule_id 
                ORDER BY row_label ASC, col_number ASC
            ");

            while ($s = mysqli_fetch_assoc($seats_q)) {
                if (in_array($s['seat_number'], $booked_from_transactions)) {
                    $s['status'] = 'booked';
                }
                $grid_data[$s['row_label']][$s['col_number']] = $s;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Kursi - Mahayana Trans</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&display=swap');
        
        /* 1. Kunci tinggi layar agar body tidak scroll */
        html, body { 
            height: 100%; 
            overflow: hidden; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }

        /* Custom Scrollbar untuk area konten */
        .custom-scroll::-webkit-scrollbar { width: 5px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 flex h-screen">

    <div class="h-full flex-shrink-0">
        <?php include '../components/sidebar.php'; ?>
    </div>

    <div class="flex-1 flex flex-col h-full overflow-hidden">
        
        <div class="flex-shrink-0">
            <?php include '../components/navbar.php'; ?>
        </div>

        <main class="flex-1 overflow-y-auto custom-scroll p-6 md:p-10">
            <div class="max-w-6xl mx-auto pb-10">
                
                <div class="mb-8 flex justify-between items-end">
                    <div>
                        <h1 class="text-3xl font-black text-[#0a192f] uppercase tracking-tight">Monitoring Armada</h1>
                        <p class="text-slate-500">Pantau ketersediaan kursi secara real-time berdasarkan keberangkatan.</p>
                    </div>
                    <?php if($schedule): ?>
                    <div class="bg-white px-4 py-2 rounded-2xl shadow-sm border border-slate-200">
                         <span class="text-[10px] block font-bold text-slate-400 uppercase">Status Update</span>
                         <span class="text-xs font-bold text-emerald-600"><i class="fa-solid fa-circle-check mr-1"></i> Live Connected</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm mb-10 border border-slate-200">
                    <form method="GET" class="flex flex-col md:flex-row items-end gap-4">
                        <div class="flex-1 w-full">
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest">Pilih Armada</label>
                            <select name="bus_id" class="w-full border-2 border-slate-100 p-3.5 rounded-2xl bg-slate-50 focus:border-[#D4AF37] outline-none transition-all" onchange="this.form.submit()">
                                <option value="">-- Pilih Bus --</option>
                                <?php mysqli_data_seek($all_buses_q, 0); ?>
                                <?php while($b = mysqli_fetch_assoc($all_buses_q)): ?>
                                    <option value="<?= $b['id'] ?>" <?= $bus_id == $b['id'] ? 'selected' : '' ?>>
                                        <?= $b['bus_name'] ?> (<?= $b['plate_number'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="flex-1 w-full">
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest">Pilih Keberangkatan</label>
                            <select name="schedule_id" class="w-full border-2 border-slate-100 p-3.5 rounded-2xl bg-slate-50 focus:border-[#D4AF37] outline-none transition-all">
                                <option value="">-- Pilih Jadwal --</option>
                                <?php if ($schedules_q): ?>
                                    <?php while($sc = mysqli_fetch_assoc($schedules_q)): ?>
                                        <option value="<?= $sc['id'] ?>" <?= $schedule_id == $sc['id'] ? 'selected' : '' ?>>
                                            <?= date('d M', strtotime($sc['date'])) ?> | <?= $sc['departure_city'] ?> ➔ <?= $sc['arrival_city'] ?> (<?= $sc['departure_time'] ?>)
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <button type="submit" class="bg-[#0a192f] text-[#D4AF37] px-10 py-4 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-slate-800 transition-all shadow-lg shadow-blue-900/20">
                            Load Layout
                        </button>
                    </form>
                </div>

                <?php if (!$bus || !$schedule): ?>
                    <div class="text-center py-24 bg-white rounded-[4rem] border-4 border-dashed border-slate-100">
                        <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fa-solid fa-couch text-4xl text-slate-200"></i>
                        </div>
                        <h2 class="text-xl font-bold text-slate-400">Menunggu Input Data</h2>
                        <p class="text-slate-400 text-sm max-w-xs mx-auto mt-2">Pilih Armada dan Jadwal untuk memantau status kursi.</p>
                    </div>
                <?php else: ?>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                        
                        <div class="lg:col-span-4 space-y-6">
                            <div class="bg-[#0a192f] p-8 rounded-[3rem] text-white shadow-2xl relative overflow-hidden">
                                <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-500/10 rounded-full"></div>
                                <h2 class="text-xs font-bold text-[#D4AF37] uppercase tracking-[0.3em] mb-4">Armada Aktif</h2>
                                <h3 class="text-3xl font-black mb-1 uppercase tracking-tighter"><?= $bus['bus_name'] ?></h3>
                                <p class="text-blue-300 font-bold mb-8 italic"><?= $bus['plate_number'] ?></p>
                                
                                <div class="space-y-4">
                                    <div class="bg-white/5 p-4 rounded-2xl border border-white/10 backdrop-blur-md">
                                        <p class="text-[10px] text-blue-300 uppercase font-black mb-1 tracking-widest">Rute</p>
                                        <p class="text-sm font-bold uppercase"><?= $schedule['departure_city'] ?> <i class="fa-solid fa-arrow-right mx-2 text-[#D4AF37]"></i> <?= $schedule['arrival_city'] ?></p>
                                    </div>
                                    <div class="bg-white/5 p-4 rounded-2xl border border-white/10 backdrop-blur-md">
                                        <p class="text-[10px] text-blue-300 uppercase font-black mb-1 tracking-widest">Waktu</p>
                                        <p class="text-sm font-bold uppercase"><?= date('l, d F Y', strtotime($schedule['date'])) ?> | <?= $schedule['departure_time'] ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-slate-100">
                                <h4 class="font-black text-[#0a192f] text-xs uppercase mb-6 tracking-widest">Legenda Kursi</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white border-2 border-[#D4AF37] rounded-2xl shadow-sm flex items-center justify-center text-[#D4AF37]">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-black text-slate-700">Available</span>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase">Siap Dipesan</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-slate-200 border-2 border-slate-300 rounded-2xl flex items-center justify-center text-slate-400">
                                            <i class="fa-solid fa-user-lock text-xs"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-black text-slate-700">Occupied</span>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase">Sudah Terisi</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-8">
                            <div class="max-w-md mx-auto bg-white p-10 rounded-[5rem] border-[8px] border-[#0a192f] shadow-2xl relative">
                                <div class="absolute -top-1 left-1/2 -translate-x-1/2 w-32 h-2 bg-[#0a192f] rounded-b-full"></div>
                                
                                <div class="flex justify-between items-center mb-10 px-4">
                                    <div class="w-14 h-14 rounded-full border-4 border-slate-50 flex items-center justify-center bg-slate-50/50">
                                        <i class="fa-solid fa-circle-notch text-slate-200 text-3xl"></i>
                                    </div>
                                    <div class="h-1.5 bg-slate-100 w-20 rounded-full"></div>
                                </div>

                                <?php foreach ($grid_data as $row_label => $cols): 
                                    $count = count($cols);
                                    $gridClass = 'grid-cols-4 gap-3';
                                    if ($count == 2) $gridClass = 'grid-cols-2 gap-20';
                                    if ($count == 3) $gridClass = 'grid-cols-3 gap-6';
                                ?>
                                    <div class="grid <?= $gridClass ?> mb-6">
                                        <?php foreach ($cols as $col_num => $seat): 
                                            $is_available = ($seat['status'] == 'available');
                                            $is_driver = (int)$seat['is_driver'];
                                            
                                            if ($is_driver) {
                                                $bg_style = 'bg-slate-50 border-slate-100 text-slate-300';
                                            } elseif ($is_available) {
                                                $bg_style = 'bg-white border-[#D4AF37] text-[#0a192f] shadow-md shadow-yellow-500/5 hover:scale-105';
                                            } else {
                                                $bg_style = 'bg-slate-200 border-slate-300 text-slate-400';
                                            }
                                        ?>
                                            <div class="h-14 rounded-[1.5rem] border-2 flex flex-col items-center justify-center transition-all duration-300 <?= $bg_style ?>">
                                                <?php if ($is_driver): ?>
                                                    <i class="fa-solid fa-id-card text-xs"></i>
                                                    <span class="text-[7px] font-black uppercase mt-1">Pilot</span>
                                                <?php else: ?>
                                                    <span class="font-black text-sm"><?= $seat['seat_number'] ?></span>
                                                    <span class="text-[7px] font-black uppercase opacity-60 tracking-tighter"><?= $seat['status'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>

                                <div class="mt-10 pt-8 border-t-4 border-dotted border-slate-100 flex justify-between items-center px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-3 h-3 bg-emerald-500 rounded-full animate-ping"></div>
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Pintu Utama</span>
                                    </div>
                                    <i class="fa-solid fa-truck-ramp-box text-slate-100 text-2xl"></i>
                                </div>
                            </div>
                            
                            <div class="mt-8 flex justify-center gap-6">
                                <div class="text-center">
                                    <span class="block text-2xl font-black text-[#0a192f]">
                                        <?php 
                                            $total_booked = 0;
                                            foreach($grid_data as $r) foreach($r as $s) if($s['status'] != 'available' && !$s['is_driver']) $total_booked++;
                                            echo $total_booked;
                                        ?>
                                    </span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase">Terisi</span>
                                </div>
                                <div class="w-px h-10 bg-slate-200"></div>
                                <div class="text-center">
                                    <span class="block text-2xl font-black text-[#D4AF37]">
                                        <?php 
                                            $total_avail = 0;
                                            foreach($grid_data as $r) foreach($r as $s) if($s['status'] == 'available' && !$s['is_driver']) $total_avail++;
                                            echo $total_avail;
                                        ?>
                                    </span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase">Tersedia</span>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>