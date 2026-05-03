<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>News & Update - Mahayana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .news-item { transition: all 0.4s ease; }
    </style>
</head>
<body class="bg-gray-50 max-w-md mx-auto min-h-screen relative pb-24 shadow-2xl">

    <?php include 'components/header.php'; ?>

    <div class="px-4 py-4 bg-white border-b sticky top-0 z-40 flex items-center gap-3">
        <a href="index.php" class="w-8 h-8 flex items-center justify-center rounded-full active:bg-gray-100 transition-colors">
            <i class="fa-solid fa-arrow-left text-gray-800"></i>
        </a>
        <h1 class="text-lg font-black text-gray-800 uppercase tracking-tighter">News & Update</h1>
    </div>

    <div class="sticky top-[61px] z-30 bg-white/80 backdrop-blur-md border-b">
        <div id="filter-container" class="flex gap-2 px-4 py-3 overflow-x-auto no-scrollbar">
            <button data-filter="SEMUA" class="filter-btn bg-white text-red-600 border-2 border-red-600 px-6 py-2 rounded-full text-xs font-black transition-all duration-300 shadow-sm">SEMUA</button>
            <button data-filter="PROMO" class="filter-btn bg-gray-50 text-gray-400 border-2 border-transparent px-6 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all duration-300">PROMO</button>
            <button data-filter="EVENT" class="filter-btn bg-gray-50 text-gray-400 border-2 border-transparent px-6 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all duration-300">EVENT</button>
            <button data-filter="ARTIKEL" class="filter-btn bg-gray-50 text-gray-400 border-2 border-transparent px-6 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all duration-300">ARTIKEL</button>
        </div>
    </div>

    <main id="news-list" class="p-4 space-y-6">
        <?php
        // DATA SEKARANG MENGGUNAKAN KEY SEBAGAI ID (1, 2, 3, 4)
        $news_data = [
            1 => ['category' => 'EVENT', 'color' => 'bg-blue-600', 'title' => 'Kolaborasi Mahayana x F2WL : Diskon Pelajar', 'date' => '17 April 2026', 'img' => 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?q=80&w=600', 'excerpt' => 'Potongan harga khusus untuk kegiatan sekolah...'],
            2 => ['category' => 'PROMO', 'color' => 'bg-red-600', 'title' => 'Flash Sale Ramadhan: Diskon 50%', 'date' => '15 April 2026', 'img' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=600', 'excerpt' => 'Persiapkan mudik Anda lebih awal dengan harga terbaik.'],
            3 => ['category' => 'ARTIKEL', 'color' => 'bg-green-600', 'title' => 'Tips Barang Aman Selama di Perjalanan', 'date' => '12 April 2026', 'img' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=600', 'excerpt' => '5 tips jitu agar koper Anda tetap dalam pantauan.'],
            4 => ['category' => 'PROMO', 'color' => 'bg-red-600', 'title' => 'Cashback 20.000 QRIS BCA', 'date' => '10 April 2026', 'img' => 'https://images.unsplash.com/photo-1556742044-3c52d6e88c62?q=80&w=600', 'excerpt' => 'Nikmati kemudahan pembayaran non-tunai di Mahayana.']
        ];

        foreach ($news_data as $id => $item): ?>
            <div class="news-item" data-category="<?= $item['category'] ?>">
                <a href="news-detail.php?id=<?= $id ?>" class="block group">
                    <div class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm transition-all active:scale-[0.98]">
                        <div class="relative aspect-video">
                            <img src="<?= $item['img'] ?>" class="w-full h-full object-cover grayscale-[20%] group-hover:grayscale-0 transition-all duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                            <span class="absolute top-3 left-3 <?= $item['color'] ?> text-white text-[9px] px-3 py-1 rounded-md font-black tracking-widest uppercase shadow-md">
                                <?= $item['category'] ?>
                            </span>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center gap-2 mb-2 text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                                <i class="fa-regular fa-calendar-check text-red-500"></i>
                                <?= $item['date'] ?>
                            </div>
                            <h2 class="text-base font-black text-gray-800 leading-tight mb-2 group-hover:text-red-600 transition-colors">
                                <?= $item['title'] ?>
                            </h2>
                            <p class="text-[11px] text-gray-500 line-clamp-2 leading-relaxed"><?= $item['excerpt'] ?></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>

        <div id="empty-state" class="hidden py-16 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-newspaper text-gray-300 text-3xl"></i>
            </div>
            <p class="text-gray-400 text-xs font-bold uppercase tracking-widest">Belum ada berita di kategori ini</p>
        </div>
    </main>

    <?php include 'components/navbar.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const newsItems = document.querySelectorAll('.news-item');
        const emptyState = document.getElementById('empty-state');

        filterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const filterValue = btn.getAttribute('data-filter');

                // 1. Update UI Tombol (Active State)
                filterButtons.forEach(b => {
                    // Reset semua tombol ke style default (abu-abu)
                    b.classList.remove('text-red-600', 'border-red-600', 'bg-white', 'font-black', 'shadow-sm');
                    b.classList.add('text-gray-400', 'border-transparent', 'bg-gray-50', 'font-bold');
                });
                
                // Set tombol yang diklik menjadi aktif (merah)
                btn.classList.remove('text-gray-400', 'border-transparent', 'bg-gray-50', 'font-bold');
                btn.classList.add('text-red-600', 'border-red-600', 'bg-white', 'font-black', 'shadow-sm');

                // 2. Logika Filtering dengan Animasi Fade
                let visibleCount = 0;

                newsItems.forEach(item => {
                    const itemCategory = item.getAttribute('data-category');
                    
                    // Cek apakah kategori cocok atau filter adalah 'SEMUA'
                    if (filterValue === 'SEMUA' || itemCategory === filterValue) {
                        item.style.display = 'block';
                        // Tambahkan sedikit delay animasi agar lebih halus
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        }, 10);
                        visibleCount++;
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(10px)';
                        item.style.display = 'none';
                    }
                });

                // 3. Tampilkan Pesan Jika Data Kosong (Empty State)
                if (visibleCount === 0) {
                    emptyState.classList.remove('hidden');
                    emptyState.style.opacity = '1';
                } else {
                    emptyState.classList.add('hidden');
                }
            });
        });

        // Optional: Trigger klik pada tombol "SEMUA" saat halaman pertama dimuat
        // agar state awal konsisten
        const initialBtn = document.querySelector('[data-filter="SEMUA"]');
        if(initialBtn) initialBtn.click();
    });
</script>

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