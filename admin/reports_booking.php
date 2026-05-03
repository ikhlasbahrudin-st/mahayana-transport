<?php
include 'middleware.php';
include '../config/koneksi.php';

/* =========================
   FILTER & SECURITY
========================= */
$start = isset($_GET['start']) ? mysqli_real_escape_string($conn, $_GET['start']) : '';
$end   = isset($_GET['end']) ? mysqli_real_escape_string($conn, $_GET['end']) : '';

$where = "WHERE b.status = 'paid'";

if($start && $end){
    $where .= " AND DATE(b.created_at) BETWEEN '$start' AND '$end'";
}

/* =========================
   DATA FETCHING
========================= */
$sql = "SELECT b.*, u.fullname, r.departure_city, r.arrival_city, bus.bus_name
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bus ON s.bus_id = bus.id
        $where
        ORDER BY b.created_at DESC";

$query = mysqli_query($conn, $sql);
$count_data = mysqli_num_rows($query);

/* =========================
   TOTAL CALCULATIONS
========================= */
$totalRevenueResult = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_price) as total FROM bookings b $where"));
$totalRevenue = $totalRevenueResult['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Booking Mahayana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        @media print {
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea { 
                position: absolute; 
                left: 0; 
                top: 0; 
                width: 100%; 
                padding: 0;
                box-shadow: none;
            }
            .no-print { display: none !important; }
            table { border: 1px solid #e2e8f0; }
            th { background-color: #f8fafc !important; color: black !important; }
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800">

<div class="flex min-h-screen">
    <div class="w-64 hidden md:block fixed h-full bg-white border-r">
        <?php include 'components/sidebar.php'; ?>
    </div>

    <div class="flex-1 md:ml-64 flex flex-col">
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-10 border-b">
            <?php include 'components/navbar.php'; ?>
        </header>

        <main class="p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight">Laporan Penjualan</h2>
                    <p class="text-slate-500">Kelola dan pantau data transaksi yang telah berhasil.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="window.print()" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg transition-all shadow-sm">
                        <i class="fas fa-print"></i> Cetak Laporan
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-5">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-2xl">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium uppercase tracking-wider">Total Transaksi</p>
                        <h3 class="text-2xl font-bold"><?= $count_data ?> Pesanan</h3>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-5">
                    <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-2xl">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium uppercase tracking-wider">Total Pendapatan</p>
                        <h3 class="text-2xl font-bold text-emerald-600">Rp <?= number_format($totalRevenue,0,',','.') ?></h3>
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 mb-8 no-print">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500 uppercase px-1">Mulai</label>
                        <input type="date" name="start" value="<?= $start ?>" class="block w-full border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2.5 bg-slate-50">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500 uppercase px-1">Sampai</label>
                        <input type="date" name="end" value="<?= $end ?>" class="block w-full border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2.5 bg-slate-50">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-slate-800 text-white px-6 py-2.5 rounded-lg hover:bg-black transition-colors font-medium">
                            Terapkan Filter
                        </button>
                        <a href="reports_booking.php" class="bg-slate-100 text-slate-600 px-6 py-2.5 rounded-lg hover:bg-slate-200 transition-colors font-medium text-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div id="printArea" class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                <div class="text-center mb-10 pb-6 border-b-2 border-slate-900 hidden print:block">
                    <h1 class="text-3xl font-black uppercase">MAHAYANA TRANSPORT</h1>
                    <p class="text-sm">Jl. Lintas Utama No. 123, Kota Anda | Telp: (021) 12345678</p>
                    <p class="text-sm">Email: info@mahayana.com | Website: www.mahayana.com</p>
                </div>

                <div class="mb-6 flex justify-between items-end">
                    <div>
                        <h4 class="text-lg font-bold">Laporan Penjualan Tiket</h4>
                        <p class="text-sm text-slate-500 italic">Periode: <?= $start && $end ? date('d/m/Y', strtotime($start)).' - '.date('d/m/Y', strtotime($end)) : 'Semua Waktu' ?></p>
                    </div>
                    <p class="text-xs text-slate-400 hidden print:block">Dicetak pada: <?= date('d/m/Y H:i') ?></p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-y border-slate-200">
                                <th class="py-4 px-4 font-semibold text-slate-700">Tanggal</th>
                                <th class="py-4 px-4 font-semibold text-slate-700">Kode</th>
                                <th class="py-4 px-4 font-semibold text-slate-700">Pelanggan</th>
                                <th class="py-4 px-4 font-semibold text-slate-700">Rute & Bus</th>
                                <th class="py-4 px-4 font-semibold text-slate-700 text-right">Total Harga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if($count_data > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($query)): ?>
                                <tr class="hover:bg-slate-50 transition-colors group">
                                    <td class="py-4 px-4 text-sm"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                    <td class="py-4 px-4 text-sm font-mono font-bold text-indigo-600">#<?= $row['booking_code'] ?></td>
                                    <td class="py-4 px-4">
                                        <div class="text-sm font-semibold"><?= $row['fullname'] ?></div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="text-sm"><?= $row['departure_city'] ?> <i class="fas fa-arrow-right text-[10px] text-slate-400 mx-1"></i> <?= $row['arrival_city'] ?></div>
                                        <div class="text-[11px] text-slate-500 uppercase tracking-tighter"><?= $row['bus_name'] ?></div>
                                    </td>
                                    <td class="py-4 px-4 text-right font-bold text-sm">
                                        Rp <?= number_format($row['total_price'],0,',','.') ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-slate-400 italic">Data tidak ditemukan untuk periode ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 border-t-2 border-slate-200">
                                <td colspan="4" class="py-4 px-4 text-right font-bold uppercase tracking-widest text-slate-600">Grand Total</td>
                                <td class="py-4 px-4 text-right font-black text-lg text-indigo-700">
                                    Rp <?= number_format($totalRevenue,0,',','.') ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-12 hidden print:grid grid-cols-3 gap-4">
                    <div class="text-center col-start-3">
                        <p class="mb-20 text-sm">Manajer Operasional,</p>
                        <p class="font-bold border-b border-black inline-block">________________________</p>
                        <p class="text-xs uppercase mt-1">Admin Mahayana</p>
                    </div>
                </div>
            </div>
        </main>

   
    </div>
</div>

</body>
</html>