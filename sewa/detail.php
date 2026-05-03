<?php
session_start();
require_once '../config/koneksi.php';

// ================= VALIDASI ID =================
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: index.php"); exit(); }

// ================= DATA RENTAL =================
$query = mysqli_query($conn, "SELECT * FROM rentals WHERE id = $id");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href = 'index.php';</script>";
    exit();
}

// ================= ASSETS & LOGIN =================
$cover = !empty($data['image']) ? "../assets/bus/" . $data['image'] : "https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=800";
$gallery = mysqli_query($conn, "SELECT * FROM rental_images WHERE rental_id = $id");
$user_id = $_SESSION['user_id'] ?? 0;
$user = $user_id ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'")) : null;
$nama_user = $user['fullname'] ?? 'Guest';

// ================= WHATSAPP LOGIC =================
$admin_phone = "6285759455910";
$message = "Halo Admin Travel Mahayana\nSaya *{$nama_user}* ingin konfirmasi sewa:\n\n🚌 *Bus:* {$data['bus_name']}\n📅 *Tanggal:* {$data['rental_date']}\n💰 *Total:* Rp " . number_format($data['total_price'],0,',','.') . "\n\nMohon info selanjutnya 🙏";
$wa_link = "https://wa.me/" . preg_replace('/[^0-9]/', '', $admin_phone) . "?text=" . urlencode($message);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">    <title>Detail Pesanan - <?= $data['bus_name'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background:#f8fafc; color: #1e293b; }
        .no-scrollbar::-webkit-scrollbar { display:none; }
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="antialiased">

<div class="max-w-md mx-auto min-h-screen bg-white relative pb-32 shadow-2xl shadow-slate-200">

    <div class="sticky top-0 z-50 glass border-b border-slate-100 px-4 py-4 flex items-center gap-4">
        <a href="sewa-armada.php" class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-100 text-slate-600">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1 class="font-extrabold text-lg tracking-tight">Detail Pesanan</h1>
    </div>

    <div class="p-5 space-y-7">

        <div class="relative group">
            <img src="<?= $cover ?>" class="w-full h-64 object-cover rounded-[2.5rem] shadow-xl shadow-slate-200" onerror="this.src='https://via.placeholder.com/800x400'">
            <div class="absolute bottom-4 left-4">
                <span class="bg-yellow-400 text-navy px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg">
                    Premium Class
                </span>
            </div>
        </div>

        <div class="px-2 flex justify-between items-end">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900"><?= $data['bus_name'] ?></h2>
                <p class="text-slate-400 font-medium flex items-center gap-2 mt-1">
                    <i class="fa-solid fa-users-viewfinder text-blue-500"></i>
                    Kapasitas <?= $data['capacity'] ?> Kursi
                </p>
            </div>
            <div class="text-right">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Total Bayar</span>
                <span class="text-xl font-black text-emerald-600">Rp<?= number_format($data['total_price'],0,',','.') ?></span>
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between px-2">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Gallery Armada</h3>
                <i class="fa-solid fa-circle-chevron-right text-slate-200"></i>
            </div>
            <div class="flex gap-3 overflow-x-auto no-scrollbar pb-2">
                <img src="<?= $cover ?>" class="w-28 h-28 rounded-3xl object-cover flex-shrink-0 border-2 border-white shadow-md shadow-slate-100 ring-1 ring-slate-100">
                <?php while($g = mysqli_fetch_assoc($gallery)): ?>
                    <img src="../assets/bus/<?= $g['image'] ?>" class="w-28 h-28 rounded-3xl object-cover flex-shrink-0 border-2 border-white shadow-md shadow-slate-100 ring-1 ring-slate-100">
                <?php endwhile; ?>
                <?php if(mysqli_num_rows($gallery) == 0): ?>
                    <div class="w-28 h-28 rounded-3xl bg-slate-50 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-300">
                        <i class="fa-solid fa-image mb-1"></i>
                        <span class="text-[8px] font-bold">Lainnya</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-slate-50 rounded-[2.5rem] p-6 space-y-5 border border-slate-100">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Informasi Sewa</h3>
            
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center text-blue-500 shadow-sm">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Penyewa</p>
                    <p class="text-sm font-bold text-slate-700"><?= $data['customer_name'] ?></p>
                </div>
            </div>

           

            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center text-orange-500 shadow-sm">
                    <i class="fa-solid fa-phone-volume"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Kontak Terdaftar</p>
                    <p class="text-sm font-bold text-slate-700"><?= $data['customer_phone'] ?></p>
                </div>
            </div>
        </div>

        <div class="px-2">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Keterangan</h3>
            <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm">
                <p class="text-sm text-slate-500 leading-relaxed italic">
                    <i class="fa-solid fa-quote-left text-slate-200 mr-2"></i>
                    <?= !empty($data['description']) ? $data['description'] : 'Tidak ada instruksi khusus untuk perjalanan ini.' ?>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 pt-4">
            <a href="<?= $wa_link ?>" target="_blank" rel="noopener"
               class="bg-emerald-500 hover:bg-emerald-600 text-white py-5 rounded-[2rem] font-extrabold uppercase tracking-widest text-xs text-center shadow-xl shadow-emerald-100 transition-all flex items-center justify-center gap-3">
                <i class="fa-brands fa-whatsapp text-xl"></i>
                Konfirmasi Via WhatsApp
            </a>
            
           
        </div>

    </div>

    <div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto z-50">
        <?php include __DIR__ . '/../components/navbar.php'; ?>
    </div>

</div>

</body>
</html>