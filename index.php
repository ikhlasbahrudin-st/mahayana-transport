
<?php
session_start();
require_once 'config/koneksi.php';
require_once 'config/helper.php';

// ================= PROMO QUERY =================
$promo_list = mysqli_query($conn, "
    SELECT * FROM promos 
    WHERE is_active = 1 
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">    <title>mahayana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }

/* Kunci scroll saat splash screen aktif */
    body.splash-active {
        overflow: hidden;
    }

    /* --- STYLING EFEK KILAU KACA (GLASS SHINE) --- */
    .shine-effect {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }

    /* Membuat Lapisan Cahaya Kilau */
    .shine-effect::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%; /* Mulai dari luar kiri teks */
        width: 50%; /* Lebar kilauan */
        height: 100%;
        
        /* Gradien Cahaya Miring (Putih Emas Transparan) */
        background: linear-gradient(
            to right, 
            rgba(255, 255, 255, 0) 0%, 
            rgba(255, 255, 255, 0.1) 20%, 
            rgba(212, 175, 55, 0.4) 50%, /* Warna emas muda di tengah */
            rgba(255, 255, 255, 0.1) 80%, 
            rgba(255, 255, 255, 0) 100%
        );
        
        /* Membuat kilauan miring (skew) agar terlihat dinamis */
        transform: skewX(-25deg); 
        
        /* Jalankan Animasi */
        animation: shineLoop 1.5s ease-in-out infinite;
    }

    /* Keyframes untuk Menggerakkan Cahaya dari Kiri ke Kanan */
    @keyframes shineLoop {
        0% {
            left: -100%;
        }
        100% {
            left: 150%; /* Berakhir di luar kanan teks */
        }
    }
 


    </style>
</head>
<!-- POPUP MOBILE -->

<!-- STICKY TOP BANNER -->
<div id="topBannerWrapper" class="fixed top-0 left-0 w-full z-[999] flex justify-center pointer-events-none">

    <div id="topBanner"
        class="pointer-events-auto w-[92%] max-w-md mt-3 bg-[#0a192f] text-[#D4AF37] 
               px-4 py-3 rounded-2xl shadow-xl flex items-center justify-between gap-3
               transform -translate-y-20 opacity-0 transition-all duration-500">

        <!-- ICON -->
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-[#D4AF37]/10 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-mobile-screen text-sm"></i>
            </div>

            <!-- TEXT -->
            <div class="leading-tight">
                <p class="text-[10px] font-black uppercase tracking-widest">
                    Lebih Nyaman di Mobile
                </p>
                <p class="text-[10px] text-white/70">
                    Buka di HP untuk pengalaman terbaik
                </p>
            </div>
        </div>

        <!-- CLOSE -->
        <button onclick="closeBanner()" class="text-white/70 hover:text-white">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>

    </div>
</div>



<div id="splash-screen" class="fixed inset-0 z-[9999] bg-[#001F3F] flex flex-col items-center justify-center transition-all duration-1000">
    <div class="flex flex-col items-center">
        
        <h1 class="shine-effect text-[#D4AF37] text-4xl font-black tracking-[0.5em] uppercase mb-3 relative overflow-hidden">
            MAHAYANA
        </h1>
        
        <div class="w-16 h-[2px] bg-[#D4AF37] rounded-full"></div>
    </div>
    
    <div class="absolute bottom-16">
        <div class="w-6 h-6 border-2 border-[#D4AF37]/20 border-t-[#D4AF37] rounded-full animate-spin"></div>
    </div>
</div>




<body class="bg-gray-50 max-w-md mx-auto min-h-screen relative pb-24 shadow-2xl">

<?php include 'components/header.php'; ?>

<section class="px-4 -mt-2 pb-4">
    <div class="bg-white rounded-xl border border-[#D4AF37]/20 p-4 flex justify-between items-center shadow-md relative z-10">
        
        <div class="flex items-center gap-3">
            <div class="text-[#0a192f] text-base border-2 border-[#D4AF37]/20 p-2 rounded-xl bg-[#D4AF37]/5">
                <i class="fa-solid fa-gem text-[#D4AF37]"></i>
            </div>
            <div>
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-[0.15em] mb-0.5">Your Points</p>
                <div class="flex items-baseline gap-1">
                    <p class="font-cinzel font-bold text-[#0a192f] text-xl leading-none">0</p>
                    <p class="text-[10px] text-[#D4AF37] font-bold uppercase">pts</p>
                </div>
            </div>
        </div>

        <button class="flex items-center gap-2 bg-[#0a192f] text-[#D4AF37] border border-[#D4AF37]/40 px-4 py-2 rounded-full text-[10px] font-bold uppercase tracking-wider hover:bg-[#112240] transition-all active:scale-95 shadow-sm">
            <span>Pakai Point</span>
            <i class="fa-solid fa-circle-chevron-right text-[12px]"></i>
        </button>

    </div>
</section>

<section class="bg-white py-5 mb-4 rounded-b-3xl shadow-sm">
    <div class="grid grid-cols-3 gap-y-4 gap-x-3 px-4">

        <?php
        $main_menus = [
            [
                'img' => 'https://bhisa.id/img/logo-bhisa/hiace.png',
                'label' => 'Shuttle',
                'bg' => 'bg-slate-50',
                'link' => 'shuttle/shuttle.php'
            ],
            [
                'img' => 'https://bhisa.id/img/logo-bhisa/hiacenbus.png',
                'label' => 'Paket Wisata',
                'bg' => 'bg-slate-50',
                'link' => 'wisata/wisata.php'
            ],
            [
                'img' => 'https://bhisa.id/img/logo-bhisa/kirim.png',
                'label' => 'Kirim Paket',
                'bg' => 'bg-slate-50',
                'link' => 'paket/paket.php'
            ],
            [
                'img' => 'https://bhisa.id/img/logo-bhisa/bus.png',
                'label' => 'Sewa Armada',
                'bg' => 'bg-slate-50',
                'link' => 'sewa/sewa-armada.php'
            ],
        ];

        foreach ($main_menus as $menu): ?>

        <a href="<?= $menu['link'] ?>" class="flex flex-col items-center group">

            <!-- ICON BOX (LEBIH BULAT & PADAT) -->
            <div class="w-20 h-20 <?= $menu['bg'] ?> border border-slate-100 rounded-3xl flex items-center justify-center shadow-sm transition-all duration-300 group-active:scale-95 group-hover:shadow-md">

                <img src="<?= $menu['img'] ?>"
                     class="w-20 h-20 object-contain"
                     alt="<?= $menu['label'] ?>">

            </div>

            <!-- LABEL -->
            <span class="text-[10px] font-bold text-slate-600 mt-1.5 text-center uppercase tracking-widest leading-tight">
                <?= $menu['label'] ?>
            </span>

        </a>

        <?php endforeach; ?>

    </div>
</section>

<section class="px-4 mb-8">
    <div class="relative rounded-2xl overflow-hidden aspect-[16/9] bg-gray-200 shadow-lg border border-gray-100 group">
        
        <div id="slider" class="flex transition-transform duration-500 ease-in-out h-full">
            <img src="https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=600" class="min-w-full h-full object-cover">
            <img src="https://images.unsplash.com/photo-1570125909232-eb263c188f7e?auto=format&fit=crop&q=80&w=600" class="min-w-full h-full object-cover">
            <img src="https://images.unsplash.com/photo-1557223562-6c77ef16210f?auto=format&fit=crop&q=80&w=600" class="min-w-full h-full object-cover">
            <img src="https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=600" class="min-w-full h-full object-cover">
        </div>

        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2">
            <div class="dot w-2 h-2 rounded-full bg-white transition-all duration-300" data-index="0"></div>
            <div class="dot w-2 h-2 rounded-full bg-white/50 transition-all duration-300" data-index="1"></div>
            <div class="dot w-2 h-2 rounded-full bg-white/50 transition-all duration-300" data-index="2"></div>
            <div class="dot w-2 h-2 rounded-full bg-white/50 transition-all duration-300" data-index="3"></div>
        </div>
    </div>
</section>

<section class="mb-8">
    <a href="semua-promo.php" class="flex justify-between items-center px-4 mb-3 group">
        <h3 class="font-bold text-gray-800 text-base tracking-tight">
            Spesial Promo 🔥
        </h3>
        <div class="text-gray-400 group-hover:text-red-600 transition-colors">
            <i class="fa-solid fa-chevron-right text-sm"></i>
        </div>
    </a>

    <div id="promoSlider" class="flex overflow-x-auto gap-3 px-4 no-scrollbar scroll-smooth snap-x snap-mandatory pb-2">

        <?php
        $promo_list = mysqli_query($conn, "SELECT * FROM promos WHERE is_active = 1 ORDER BY id DESC");
        if (mysqli_num_rows($promo_list) > 0):
            while ($p = mysqli_fetch_assoc($promo_list)):
        ?>

        <div class="min-w-[160px] max-w-[160px] flex-shrink-0 snap-start">
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden relative flex flex-col h-full active:scale-95 transition-transform">

                <div class="absolute top-1 left-1 z-10">
                    <span class="bg-red-500/90 backdrop-blur-sm text-white text-[8px] font-bold px-1.5 py-0.5 rounded-md uppercase shadow-sm">
                        <?= htmlspecialchars($p['type']) ?>
                    </span>
                </div>

                <div class="h-20 w-full bg-slate-50 flex-shrink-0 border-b border-slate-50">
                    <img src="uploads/promo/<?= $p['image'] ?>"
                         class="w-full h-full object-cover"
                         loading="lazy"
                         alt="<?= htmlspecialchars($p['title']) ?>">
                </div>

                <div class="p-2 flex flex-col justify-between flex-grow bg-white">
                    <h4 class="text-slate-800 font-bold text-[10px] leading-[1.3] line-clamp-2 mb-1">
                        <?= htmlspecialchars($p['title']) ?>
                    </h4>
                    
                    <div class="flex items-end justify-between">
                        <div class="flex items-center gap-0.5">
                            <span class="text-red-600 font-black text-sm">
                                <?= rtrim($p['points'], ' %') ?>%
                            </span>
                            <span class="text-[7px] text-slate-400 font-bold uppercase tracking-tighter leading-none">OFF</span>
                        </div>
                        <i class="fa-solid fa-circle-arrow-right text-slate-200 text-xs"></i>
                    </div>
                </div>

            </div>
        </div>

        <?php 
            endwhile; 
        else:
        ?>
            <div class="w-full py-4 text-center text-slate-400 text-xs italic">Belum ada promo tersedia</div>
        <?php endif; ?>

    </div>
</section>

<style>
    /* Sembunyikan scrollbar di Chrome, Safari, dan Opera */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    /* Sembunyikan scrollbar di IE, Edge, dan Firefox */
    .no-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
        -webkit-overflow-scrolling: touch; /* Smooth scroll iOS */
    }

    /* Limit teks judul agar tidak merusak layout kartu */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<script>
    // Logic untuk Slider (Jika ingin menambahkan auto-scroll atau navigasi tombol nantinya)
    document.addEventListener('DOMContentLoaded', function() {
        const slider = document.getElementById('promoSlider');
        
        let isDown = false;
        let startX;
        let scrollLeft;

        // Mendukung drag mouse untuk tampilan Desktop (Testing)
        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            slider.classList.add('active');
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });
        slider.addEventListener('mouseleave', () => {
            isDown = false;
        });
        slider.addEventListener('mouseup', () => {
            isDown = false;
        });
        slider.addEventListener('mousemove', (e) => {
            if(!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 2;
            slider.scrollLeft = scrollLeft - walk;
        });
    });
</script>

<script src="js/promo-slider.js"></script>






<section class="px-4 mb-8 bg-white py-6">
    <h3 class="font-bold text-gray-800 text-lg mb-4 uppercase tracking-tighter">
        News di Mahayana
    </h3>

    <!-- FILTER -->
    <div class="flex gap-2 mb-6 overflow-x-auto no-scrollbar">
        <button onclick="filterNews('all', this)"
            class="filter-btn bg-red-600 text-white px-5 py-1.5 rounded-full text-xs font-bold shadow-md">
            Semua
        </button>

        <button onclick="filterNews('Promo', this)"
            class="filter-btn bg-white text-gray-500 border px-5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap">
            Promo
        </button>

        <button onclick="filterNews('Event', this)"
            class="filter-btn bg-white text-gray-500 border px-5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap">
            Event
        </button>

        <button onclick="filterNews('Artikel', this)"
            class="filter-btn bg-white text-gray-500 border px-5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap">
            Artikel
        </button>
    </div>

    <!-- GRID NEWS -->
    <div class="grid grid-cols-2 gap-4">
        <?php
        $news_list = [
            [
                'type' => 'Event',
                'color' => 'bg-blue-600',
                'title' => 'Mahayana Goes to Campus 2026',
                'img' => 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?q=80&w=300',
                'link' => 'news-detail.php?id=1'
            ],
            [
                'type' => 'Promo',
                'color' => 'bg-red-600',
                'title' => 'Flash Sale Tiket Shuttle 50%',
                'img' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=300',
                'link' => 'news-detail.php?id=2'
            ],
            [
                'type' => 'Promo',
                'color' => 'bg-red-600',
                'title' => 'Cashback 20rb via QRIS',
                'img' => 'https://images.unsplash.com/photo-1556742044-3c52d6e88c62?q=80&w=300',
                'link' => 'news-detail.php?id=3'
            ],
            [
                'type' => 'Artikel',
                'color' => 'bg-green-600',
                'title' => 'Tips Nyaman Perjalanan Jauh',
                'img' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=300',
                'link' => 'news-detail.php?id=4'
            ]
        ];

        foreach ($news_list as $news): ?>
            <a href="<?= $news['link'] ?>"
               class="news-item border rounded-xl overflow-hidden bg-white shadow-sm active:scale-95 transition-all"
               data-type="<?= $news['type'] ?>">

                <div class="relative">
                    <img src="<?= $news['img'] ?>" class="w-full h-28 object-cover">

                    <span class="absolute top-2 left-2 <?= $news['color'] ?> text-white text-[8px] px-2 py-0.5 rounded font-bold uppercase tracking-wider">
                        <?= $news['type'] ?>
                    </span>
                </div>

                <div class="p-2">
                    <p class="text-[10px] font-bold text-gray-800 leading-tight line-clamp-2 h-7 uppercase">
                        <?= $news['title'] ?>
                    </p>

                    <p class="text-[8px] text-gray-400 mt-2 font-medium">
                        Info Mahayana • 17 April
                    </p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- BUTTON -->
    <a href="semua-news.php"
       class="block w-full text-center mt-6 py-3 bg-gray-50 border border-gray-100 text-gray-600 rounded-xl text-xs font-black uppercase tracking-widest active:bg-gray-200 transition-colors">
        Lihat Semua <i class="fa-solid fa-arrow-right ml-1"></i>
    </a>
</section>

    <script src="js/news-filter.js"></script>


<section class="px-4 mb-8">
    <h3 class="font-bold text-gray-800 text-lg mb-4 uppercase tracking-tighter">Why Us?</h3>
    
    <div class="grid grid-cols-2 gap-3">
        <?php
        $why_us = [
            [
                'title' => 'Driver Profesional',
                'short_desc' => 'Pelatihan berkala & sertifikasi.',
                'full_desc' => 'Seluruh driver kami menjalani training safety driving dan etika pelayanan secara berkala untuk menjamin keamanan serta kenyamanan Anda selama di perjalanan.',
                'img' => 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?q=80&w=400',
                'icon' => 'fa-id-card'
            ],
            [
                'title' => 'Real-time GPS',
                'short_desc' => 'Pantau posisi unit setiap saat.',
                'full_desc' => 'Demi keamanan ekstra, setiap unit Mahayana dilengkapi dengan sistem GPS yang terintegrasi. Anda dan keluarga bisa merasa tenang karena posisi kendaraan terpantau 24/7.',
                'img' => 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?q=80&w=400',
                'icon' => 'fa-location-dot'
            ],
            [
                'title' => 'Armada Terbaru',
                'short_desc' => 'Unit bersih & terawat.',
                'full_desc' => 'Kami menggunakan unit keluaran terbaru yang selalu dijaga kebersihannya dan menjalani servis rutin di bengkel resmi untuk meminimalisir kendala teknis.',
                'img' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=400',
                'icon' => 'fa-bus'
            ],
            [
                'title' => 'Harga Transparan',
                'short_desc' => 'Tanpa biaya tersembunyi.',
                'full_desc' => 'Harga yang Anda lihat adalah harga yang Anda bayar. Sudah termasuk asuransi perjalanan tanpa ada biaya tambahan yang mendadak di akhir perjalanan.',
                'img' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=400',
                'icon' => 'fa-tags'
            ]
        ];

        foreach ($why_us as $index => $item): ?>
            <div onclick="openModal(<?= $index ?>)" class="border border-gray-100 rounded-2xl p-2 bg-white shadow-sm active:scale-95 transition-all duration-200 cursor-pointer">
                <div class="relative overflow-hidden rounded-xl mb-2">
                    <img src="<?= $item['img'] ?>" class="w-full h-24 object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                </div>
                <p class="font-black text-[10px] text-gray-800 uppercase leading-none mb-1 tracking-tight"><?= $item['title'] ?></p>
                <p class="text-[9px] text-gray-500 leading-tight font-medium"><?= $item['short_desc'] ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div id="whyUsModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center px-6">
    <div onclick="closeModal()" class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300"></div>
    
    <div id="modalContent" class="bg-white rounded-[32px] overflow-hidden shadow-2xl w-full max-w-sm relative z-10 opacity-0 scale-90 transition-all duration-300 transform">
        <button onclick="closeModal()" class="absolute top-4 right-4 bg-black/20 hover:bg-black/40 text-white w-9 h-9 rounded-full flex items-center justify-center backdrop-blur-md z-20 transition-all">
            <i class="fa-solid fa-xmark"></i>
        </button>
        
        <img id="modalImg" src="" class="w-full h-52 object-cover">
        
        <div class="p-6">
            <div class="flex items-center gap-2 mb-3 text-red-600">
                <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                    <i id="modalIcon" class="fa-solid text-sm"></i>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.15em]">Keunggulan Mahayana</span>
            </div>
            
            <h4 id="modalTitle" class="text-xl font-black text-gray-900 mb-3 uppercase tracking-tighter leading-tight"></h4>
            <p id="modalDesc" class="text-[13px] text-gray-600 leading-relaxed font-medium mb-6"></p>
            
            <button onclick="closeModal()" class="w-full py-4 bg-red-600 active:bg-red-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-red-200">
                Mengerti
            </button>
        </div>
    </div>
</div>

<script>
    window.whyUsData = <?= json_encode($why_us) ?>;
</script>
<script src="js/why_Us.js"></script>

    
<?php include 'components/footer.php'; ?>


<?php include 'components/navbar.php'; ?>
<script src="js/slider.js"></script>
<script>
function showBanner() {
    const banner = document.getElementById('topBanner');

    setTimeout(() => {
        banner.classList.remove('-translate-y-20','opacity-0');
        banner.classList.add('translate-y-0','opacity-100');
    }, 300);

    // auto hide setelah 5 detik
    setTimeout(() => {
        closeBanner();
    }, 5000);
}

function closeBanner() {
    const banner = document.getElementById('topBanner');

    banner.classList.remove('translate-y-0','opacity-100');
    banner.classList.add('-translate-y-20','opacity-0');
}

// DETEKSI DEVICE (opsional)
function isMobile() {
    return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
}

window.onload = function() {
    // tampil hanya di desktop biar gak ganggu mobile
    if (!isMobile()) {
        showBanner();
    }
}
</script>
    
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const splash = document.getElementById('splash-screen');
        
        // Kunci Scroll
        document.body.classList.add('splash-active');

        // Durasi tampilan (Teks akan berkilau terus selama durasi ini)
        setTimeout(() => {
            // Efek transisi menghilang (Fade out & Zoom Out)
            splash.style.opacity = '0';
            splash.style.transform = 'scale(1.1)';
            
            setTimeout(() => {
                splash.remove(); // Hapus elemen dari DOM
                document.body.classList.remove('splash-active'); // Buka Scroll kembali
            }, 1000); // Durasi transisi fade (1 detik)
        }, 3000); // Durasi teks berkilau diam di tempat (3 detik)
    });
</script>
    

<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.init({
      appId: "9628cb1d-13f8-4e6a-96f9-7ef00f54fcd9",
    });
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