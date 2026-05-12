<?php
include 'middleware.php'; 
include '../config/koneksi.php'; 

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$booking_id) {
    header("Location: bookings.php");
    exit;
}

// 1. Query Data Utama (Booking + User + Schedule + Route + Bus)
$query = "
    SELECT b.*, u.fullname, u.email, u.phone, 
           s.date as travel_date, s.departure_time,
           r.departure_city, r.arrival_city,
           bs.bus_name, bs.plate_number
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    LEFT JOIN schedules s ON b.schedule_id = s.id
    LEFT JOIN routes r ON s.route_id = r.id
    LEFT JOIN buses bs ON s.bus_id = bs.id
    WHERE b.id = $booking_id
";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("<div class='p-10 text-center font-bold text-slate-500'>Data booking tidak ditemukan!</div>");
}

// 2. Query Detail Penumpang & Kursi dari tabel booking_details
$details_query = mysqli_query($conn, "SELECT * FROM booking_details WHERE booking_id = $booking_id");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Booking #<?= $data['booking_code'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-50 flex">



<div class="flex-1 p-8">
    <div class="max-w-5xl mx-auto">
        
        <div class="mb-6 flex justify-between items-center">
            <a href="bookings.php" class="text-slate-400 hover:text-blue-600 font-bold text-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <div class="flex gap-2">
                <button onclick="window.print()" class="bg-white border-2 border-slate-200 px-4 py-2 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all">
                    <i class="fa-solid fa-print mr-1"></i> Cetak
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-800 p-6 text-white flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-black italic tracking-tighter">MAHAYANA TRANS</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Electronic Ticket Management</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Kode Booking</p>
                            <h3 class="text-2xl font-black text-blue-400 leading-none">#<?= $data['booking_code'] ?></h3>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="flex items-center justify-between mb-10">
                            <div class="w-1/3">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Dari</span>
                                <h4 class="text-xl font-black text-slate-800"><?= $data['departure_city'] ?></h4>
                                <p class="text-sm text-slate-500"><?= date('d M Y', strtotime($data['travel_date'])) ?></p>
                            </div>
                            <div class="flex-1 px-4 flex flex-col items-center">
                                <i class="fa-solid fa-bus text-slate-300 text-xl mb-2"></i>
                                <div class="w-full h-[2px] bg-slate-100 relative">
                                    <div class="absolute inset-0 border-t-2 border-dashed border-slate-300"></div>
                                </div>
                                <span class="text-[9px] font-bold text-slate-400 uppercase mt-2"><?= $data['trip_type'] ?></span>
                            </div>
                            <div class="w-1/3 text-right">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Tujuan</span>
                                <h4 class="text-xl font-black text-slate-800"><?= $data['arrival_city'] ?></h4>
                                <p class="text-sm text-slate-500"><?= $data['departure_time'] ?> WIB</p>
                            </div>
                        </div>

                        <div class="mt-8">
                            <h5 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-users text-blue-500"></i> Daftar Penumpang & Kursi
                            </h5>
                            <div class="border border-slate-100 rounded-2xl overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase">No</th>
                                            <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase">Nama Penumpang</th>
                                            <th class="px-4 py-3 text-center text-[10px] font-bold text-slate-400 uppercase">Nomor Kursi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <?php 
                                        $no = 1;
                                        while($detail = mysqli_fetch_assoc($details_query)): 
                                        ?>
                                        <tr>
                                            <td class="px-4 py-3 text-slate-400 font-bold"><?= $no++ ?></td>
                                            <td class="px-4 py-3 font-bold text-slate-700"><?= $detail['passenger_name'] ?></td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-lg font-black border border-blue-100">
                                                    <?= $detail['seat_number'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Informasi Kontak</p>
                    <div class="space-y-4">
                        <div>
                            <label class="text-[9px] font-bold text-slate-400 uppercase">Pemesan (User)</label>
                            <p class="font-bold text-slate-800 leading-tight"><?= $data['fullname'] ?></p>
                        </div>
                        <div>
                            <label class="text-[9px] font-bold text-slate-400 uppercase">Email / WhatsApp</label>
                            <p class="text-sm text-slate-600"><?= $data['email'] ?></p>
                            <p class="text-sm text-slate-600"><?= $data['phone'] ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Ringkasan Pembayaran</p>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-xs font-bold text-slate-500 uppercase">Total Bayar</span>
                            <span class="text-lg font-black text-blue-600">Rp <?= number_format($data['total_price'], 0, ',', '.') ?></span>
                        </div>
                        
                        <?php 
                        $status = strtolower($data['status']);
                        $statusColor = ($status == 'confirmed') ? 'emerald' : (($status == 'pending') ? 'amber' : 'rose');
                        ?>
                        <div class="text-center p-4 rounded-2xl border-2 border-<?= $statusColor ?>-100 bg-<?= $statusColor ?>-50">
                            <span class="block text-[9px] font-bold text-<?= $statusColor ?>-500 uppercase">Status Saat Ini</span>
                            <span class="text-sm font-black text-<?= $statusColor ?>-700 uppercase"><?= $data['status'] ?></span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-2">
                        <?php if($status == 'pending'): ?>
                        <button onclick="updateBooking('confirmed')" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-xl font-bold transition-all shadow-lg shadow-emerald-100">
                            Konfirmasi Pembayaran
                        </button>
                        <?php endif; ?>
                        
                        <button onclick="updateBooking('cancelled')" class="w-full bg-white border-2 border-rose-100 text-rose-500 py-3 rounded-xl font-bold hover:bg-rose-50 transition-all">
                            Batalkan Pesanan
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function updateBooking(status) {
    let msg = status === 'confirmed' ? 'Konfirmasi pembayaran ini?' : 'Batalkan pesanan ini?';
    if(confirm(msg)) {
        window.location.href = 'booking_action.php?action=update&status=' + status + '&id=<?= $data['id'] ?>';
    }
}
</script>

</body>
</html>