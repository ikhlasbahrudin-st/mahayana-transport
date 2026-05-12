<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// FILTER VALID
$allowed = ['semua','bandung','jogja','bali','lombok'];
$filter = $_GET['kota'] ?? 'semua';
if (!in_array($filter, $allowed)) {
    $filter = 'semua';
}

// DATA (GAMBAR STABIL & TERBARU)
$paket = [
    // BANDUNG (Link Diperbarui agar lebih stabil)
    ['nama'=>'Bandung City Tour','harga'=>150000,'lokasi'=>'bandung','gambar'=>'https://images.unsplash.com/photo-1590490359854-dfba19688d70?q=80&w=800&auto=format&fit=crop'],
    ['nama'=>'Lembang Farmhouse','harga'=>200000,'lokasi'=>'bandung','gambar'=>'https://images.unsplash.com/photo-1590059510138-769062337194?q=80&w=800&auto=format&fit=crop'],
    ['nama'=>'Kawah Putih Ciwidey','harga'=>180000,'lokasi'=>'bandung','gambar'=>'https://images.unsplash.com/photo-1627664188043-4e6500b5220c?q=80&w=800&auto=format&fit=crop'],
    ['nama'=>'Dago Dreampark','harga'=>170000,'lokasi'=>'bandung','gambar'=>'https://images.unsplash.com/photo-1622329709210-951663004068?q=80&w=800&auto=format&fit=crop'],

    // JOGJA
    ['nama'=>'Borobudur Tour','harga'=>300000,'lokasi'=>'jogja','gambar'=>'https://images.unsplash.com/photo-1583394293214-28ded15ee548?auto=format&fit=crop&w=800&q=80'],
    ['nama'=>'Prambanan Tour','harga'=>250000,'lokasi'=>'jogja','gambar'=>'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=800&q=80'],
    ['nama'=>'Merapi Lava Tour','harga'=>350000,'lokasi'=>'jogja','gambar'=>'https://images.unsplash.com/photo-1578469645742-46cae010e5d4?auto=format&fit=crop&w=800&q=80'],

    // BALI
    ['nama'=>'Bali Beach Tour','harga'=>500000,'lokasi'=>'bali','gambar'=>'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80'],
    ['nama'=>'Ubud Nature','harga'=>450000,'lokasi'=>'bali','gambar'=>'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=800&q=80'],
    ['nama'=>'Nusa Penida','harga'=>650000,'lokasi'=>'bali','gambar'=>'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=800&q=80'],

    // LOMBOK
    ['nama'=>'Gili Trawangan','harga'=>600000,'lokasi'=>'lombok','gambar'=>'https://images.unsplash.com/photo-1549880338-65ddcdfd017b?auto=format&fit=crop&w=800&q=80'],
    ['nama'=>'Pantai Kuta Lombok','harga'=>400000,'lokasi'=>'lombok','gambar'=>'https://images.unsplash.com/photo-1579684453423-f84349ef60b0?auto=format&fit=crop&w=800&q=80'],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paket Wisata Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f8fafc; -webkit-tap-highlight-color: transparent; }
        .bg-navy { background: linear-gradient(135deg, #001F3F 0%, #003366 100%); }
        .text-gold { color: #C5A059; }
        .bg-gold { background-color: #C5A059; }
        .border-gold { border-color: #C5A059; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .card-tour { border-radius: 2rem; overflow: hidden; background: white; border: 1px solid rgba(0,0,0,0.03); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        
        /* Memastikan gambar selalu proposional di mobile */
        .img-container { height: 180px; position: relative; overflow: hidden; }
    </style>
</head>

<body class="bg-slate-50">

<div class="max-w-md mx-auto min-h-screen bg-white shadow-2xl relative flex flex-col">

    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="flex-1">
        <div class="bg-navy px-6 pt-6 pb-12 text-white rounded-b-[2.5rem] relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/5 rounded-full"></div>
            <h1 class="text-xl font-black italic tracking-tighter uppercase">Eksplor Wisata</h1>
            <p class="text-[11px] opacity-70 font-medium tracking-wide">Petualangan Premium Menantimu 🌿</p>
        </div>

        <div class="px-4 -mt-6 mb-6">
            <div class="flex gap-2 overflow-x-auto no-scrollbar py-2">
                <?php
                $btns = ['semua'=>'Semua','bandung'=>'Bandung','jogja'=>'Jogja','bali'=>'Bali','lombok'=>'Lombok'];
                foreach ($btns as $val => $label):
                    $active = ($filter === $val);
                ?>
                <a href="?kota=<?= $val ?>"
                   class="flex-none px-6 py-2.5 rounded-2xl text-[11px] font-bold transition-all border shadow-sm
                   <?= $active ? 'bg-navy text-gold border-gold scale-105 shadow-gold/20' : 'bg-white text-slate-500 border-gray-100' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="px-5 space-y-6">
            <?php
            $found = false;
            foreach ($paket as $item):
                if ($filter !== 'semua' && $item['lokasi'] !== $filter) continue;
                $found = true;
            ?>
            <div class="card-tour group">
                <div class="img-container">
                    <img 
                        src="<?= htmlspecialchars($item['gambar']) ?>" 
                        loading="lazy"
                        onerror="this.src='https://via.placeholder.com/400x200?text=Wisata+Indonesia'"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    >
                    <div class="absolute top-4 left-4 bg-navy/80 backdrop-blur-md px-3 py-1 rounded-full border border-gold/30">
                        <p class="text-[9px] font-bold text-gold uppercase tracking-widest">
                            <i class="fa-solid fa-location-dot mr-1"></i> <?= $item['lokasi'] ?>
                        </p>
                    </div>
                </div>

                <div class="p-5">
                    <h3 class="font-black text-navy text-base uppercase italic tracking-tighter leading-tight">
                        <?= htmlspecialchars($item['nama']) ?>
                    </h3>
                    
                    <div class="flex items-center gap-3 mt-3">
                        <div class="flex items-center gap-1 text-[9px] font-bold text-slate-400">
                            <i class="fa-solid fa-camera"></i> Photo Spot
                        </div>
                        <div class="flex items-center gap-1 text-[9px] font-bold text-slate-400">
                            <i class="fa-solid fa-car"></i> Transport
                        </div>
                        <div class="flex items-center gap-1 text-[9px] font-bold text-slate-400">
                            <i class="fa-solid fa-ticket"></i> Entry Ticket
                        </div>
                    </div>

                    <div class="h-px bg-slate-50 w-full my-4"></div>

                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-[9px] text-gray-400 font-bold uppercase mb-0.5">Mulai Dari</p>
                            <span class="text-navy font-black text-sm italic">
                                <span class="text-gold">Rp</span> <?= number_format($item['harga'],0,',','.') ?>
                            </span>
                        </div>

                        <button class="bg-navy text-gold text-[10px] px-6 py-2.5 rounded-xl font-black uppercase tracking-widest border border-gold/50 active:scale-95 transition-all shadow-md">
                            Pesan Sekarang
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (!$found): ?>
            <div class="text-center py-20">
                <i class="fa-solid fa-map-location-dot text-4xl text-slate-200 mb-3"></i>
                <p class="text-slate-400 font-bold text-xs uppercase tracking-widest">Destinasi Belum Tersedia</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="pb-32"></div>

</div>

<?php include __DIR__ . '/../components/navbar.php'; ?>
<script>
setInterval(() => {
    fetch('/mahayana/sync.php')
        .then(res => res.text())
        .then(data => {
            console.log('sync:', data);
        });
}, 15000); // setiap 15 detik
</script>
</body>
</html>