<?php
require_once 'config/koneksi.php'; // Pastikan path koneksi benar
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">    <title>Semua Promo - Mahayana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .filter-btn.active { background-color: #ef4444; color: white; border-color: #ef4444; shadow: 0 4px 12px rgba(239, 68, 68, 0.3); }
    </style>
</head>
<body class="bg-gray-50 max-w-md mx-auto min-h-screen relative pb-24 shadow-2xl border-x">

    <?php include 'components/header.php'; ?>

    <div class="px-4 py-4 bg-white border-b sticky top-0 z-40">
        <div class="flex items-center gap-3">
            <a href="index.php" class="text-gray-800 hover:text-red-600 transition-colors">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <h1 class="text-lg font-bold text-gray-800">Promo Spesial Mahayana</h1>
        </div>
    </div>

    <div class="flex gap-2 px-4 py-4 overflow-x-auto no-scrollbar bg-white sticky top-[61px] z-30 shadow-sm">
        <button onclick="filterPromo('semua', this)" class="filter-btn active border border-gray-200 text-gray-500 px-5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all">Semua</button>
        <button onclick="filterPromo('Shuttle', this)" class="filter-btn border border-gray-200 text-gray-500 px-5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all">Shuttle</button>
        <button onclick="filterPromo('Wisata', this)" class="filter-btn border border-gray-200 text-gray-500 px-5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all">Wisata</button>
        <button onclick="filterPromo('Sewa Armada', this)" class="filter-btn border border-gray-200 text-gray-500 px-5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all">Sewa Armada</button>
    </div>

    <section class="px-4 py-6 space-y-5" id="promo-container">
        <?php
        // Query ambil data dari tabel promos
        $query = mysqli_query($conn, "SELECT * FROM promos WHERE is_active = 1 ORDER BY id DESC");
        
        if (mysqli_num_rows($query) > 0) {
            while ($promo = mysqli_fetch_assoc($query)): 
        ?>
            <div class="promo-card bg-white rounded-[2rem] overflow-hidden border border-gray-100 shadow-sm group active:scale-[0.98] transition-all duration-300" 
                 data-category="<?= $promo['tipe_promo'] ?>">
                
                <div class="relative aspect-[21/9] overflow-hidden">
                    <img src="uploads/promo/<?= $promo['image'] ?>" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                         alt="<?= $promo['title'] ?>">
                    
                    <span class="absolute top-4 left-4 bg-black/50 backdrop-blur-md text-white text-[10px] px-3 py-1.5 rounded-full font-bold uppercase tracking-widest">
                        <?= $promo['type'] ?>
                    </span>

                    <div class="absolute bottom-4 right-4 bg-red-600 text-white px-3 py-1 rounded-lg shadow-lg font-bold text-xs">
                        <?= $promo['points'] ?>% OFF
                    </div>
                </div>

                <div class="p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-blue-50 text-blue-600 text-[10px] font-bold px-2 py-0.5 rounded-md uppercase">
                            <?= $promo['tipe_promo'] ?>
                        </span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-base mb-1 group-hover:text-red-600 transition-colors">
                        <?= $promo['title'] ?>
                    </h3>
                    <p class="text-xs text-gray-500 leading-relaxed mb-4 line-clamp-2">
                        Nikmati promo <?= $promo['tipe_promo'] ?> terbaik dengan potongan harga spesial Mahayana Shuttle.
                    </p>
                    
                    <div class="flex justify-between items-center pt-4 border-t border-gray-50">
                        <div class="text-[10px] text-gray-400 flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar-check text-red-500"></i>
                            Tersedia Hari Ini
                        </div>
                        <button class="bg-slate-900 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-red-600 transition-all flex items-center gap-2">
                            Klaim Promo <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php 
            endwhile; 
        } else {
            echo '<div class="text-center py-20">
                    <i class="fa-solid fa-ticket-simple text-5xl text-gray-200 mb-4"></i>
                    <p class="text-gray-400 font-medium text-sm">Belum ada promo tersedia.</p>
                  </div>';
        }
        ?>
    </section>

    <?php include 'components/navbar.php'; ?>

    <script>
        function filterPromo(category, element) {
            // 1. Update status tombol aktif
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(btn => {
                btn.classList.remove('active', 'bg-red-600', 'text-white', 'border-red-600');
                btn.classList.add('text-gray-500', 'border-gray-200');
            });
            
            element.classList.add('active');
            element.classList.remove('text-gray-500', 'border-gray-200');

            // 2. Filter kartu promo dengan animasi
            const cards = document.querySelectorAll('.promo-card');
            
            cards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                
                if (category === 'semua' || cardCategory === category) {
                    card.style.display = 'block';
                    // Tambahkan sedikit delay animasi muncul
                    card.classList.add('animate-in', 'fade-in', 'zoom-in-95');
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>

</body>
</html>