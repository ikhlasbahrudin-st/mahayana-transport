<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ================= DATA TERINTEGRASI (List & Detail) ================= */
$news_list = [
    1 => [
        'type' => 'EVENT',
        'color' => 'bg-blue-600',
        'title' => 'Kolaborasi Mahayana x F2WL : Diskon Pelajar',
        'img' => 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?q=80&w=800',
        'date' => '17 April 2026',
        'content' => 'Mahayana menjalin kolaborasi eksklusif dengan F2WL untuk memberikan potongan harga tiket khusus pelajar. Program ini bertujuan memfasilitasi mobilitas generasi muda dengan armada yang aman, nyaman, dan harga yang sangat terjangkau bagi kantong pelajar.'
    ],
    2 => [
        'type' => 'PROMO',
        'color' => 'bg-red-600',
        'title' => 'Flash Sale Ramadhan: Diskon 50%',
        'img' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=800',
        'date' => '15 April 2026',
        'content' => 'Sambut hari kemenangan dengan mudik lebih hemat bersama Mahayana. Flash Sale Ramadhan memberikan diskon tiket hingga 50% untuk keberangkatan semua rute. Promo terbatas hanya untuk pemesanan hari ini, segera amankan kursi Anda sebelum kehabisan!'
    ],
    3 => [
        'type' => 'ARTIKEL',
        'color' => 'bg-green-600',
        'title' => 'Tips Barang Aman Selama di Perjalanan',
        'img' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=800',
        'date' => '12 April 2026',
        'content' => 'Keamanan bagasi adalah prioritas utama saat bepergian jauh. Pastikan koper Anda menggunakan kunci ganda, berikan tanda pengenal yang jelas (tag nama), dan selalu simpan barang berharga seperti gadget dan dompet di tas kecil yang selalu berada dalam jangkauan Anda.'
    ],
    4 => [
        'type' => 'PROMO',
        'color' => 'bg-red-600',
        'title' => 'Cashback 20.000 QRIS BCA',
        'img' => 'https://images.unsplash.com/photo-1556742044-3c52d6e88c62?q=80&w=800',
        'date' => '10 April 2026',
        'content' => 'Bayar tiket Mahayana jadi lebih praktis dan untung dengan QRIS BCA. Dapatkan cashback langsung sebesar Rp20.000 untuk setiap transaksi minimal Rp150.000. Nikmati kemudahan pembayaran non-tunai di seluruh titik layanan Mahayana dan Mahayana.'
    ]
];

/* ================= LOGIKA AMBIL DATA ================= */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Jika ID tidak ada di daftar, berikan pesan error yang rapi
if (!isset($news_list[$id])) { 
    echo "<script>alert('Berita tidak ditemukan'); window.location.href='index.php';</script>";
    exit;
}

$news = $news_list[$id];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">

    <title><?= $news['title'] ?> - News Detail</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">

    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .content-shadow {
            box-shadow: 0 -15px 30px -5px rgba(0, 0, 0, 0.1);
        }
        .drag-handle {
            width: 40px;
            height: 5px;
            background: #e2e8f0;
            border-radius: 10px;
            margin: 0 auto 20px;
        }
        html, body {
            overflow-x: hidden;
            position: relative;
            background-color: #F8FAFC;
        }
    </style>
</head>

<body class="max-w-md mx-auto min-h-screen relative">

<div class="fixed top-0 max-w-md w-full z-50 px-4 py-4 flex items-center justify-between">
    <button onclick="window.history.back()" class="w-10 h-10 bg-white/90 backdrop-blur shadow-md flex items-center justify-center rounded-full active:scale-90 transition-all">
        <i class="fa-solid fa-arrow-left text-gray-800"></i>
    </button>
    <button class="w-10 h-10 bg-white/90 backdrop-blur shadow-md flex items-center justify-center rounded-full active:scale-90 transition-all">
        <i class="fa-solid fa-share-nodes text-gray-800 text-sm"></i>
    </button>
</div>

<div class="pb-32">

    <div class="relative w-full h-80 overflow-hidden">
        <img src="<?= $news['img'] ?>" class="w-full h-full object-cover" alt="News Image">
        
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
        
        <div class="absolute bottom-12 left-6">
            <span class="<?= $news['color'] ?> text-white text-[10px] px-4 py-1.5 rounded-lg font-black uppercase tracking-widest shadow-xl">
                <?= $news['type'] ?>
            </span>
        </div>
    </div>

    <div class="bg-white p-7 rounded-t-[40px] -mt-8 relative z-20 content-shadow min-h-[60vh]">
        
        <div class="drag-handle"></div>

        <h1 class="text-xl font-extrabold text-slate-800 leading-tight">
            <?= $news['title'] ?>
        </h1>

        <div class="flex items-center justify-between mt-5 mb-8">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-red-50 flex items-center justify-center text-red-500">
                    <i class="fa-solid fa-newspaper text-sm"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-700 leading-none">Redaksi Mahayana</p>
                    <p class="text-[10px] text-slate-400 mt-1"><?= $news['date'] ?></p>
                </div>
            </div>
            <div class="flex items-center gap-1 text-slate-300">
                <i class="fa-solid fa-eye text-[10px]"></i>
                <span class="text-[10px] font-bold">1.2k</span>
            </div>
        </div>

        <hr class="border-slate-50 mb-8">

        <div class="text-[14px] text-slate-600 leading-[1.8] space-y-4">
            <?= $news['content'] ?>
        </div>

        <div class="mt-12">
            <a href="index.php"
               class="flex items-center justify-center gap-3 w-full py-4 bg-[#001F3F] text-[#D4AF37] rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-blue-900/20 active:scale-95 transition-transform">
                <span>Booking Tiket Sekarang</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
            
            <p class="text-center text-[10px] text-slate-400 mt-6 font-medium italic">
                *Syarat dan ketentuan berlaku untuk setiap promo.
            </p>
        </div>

    </div>

</div>

<?php 
$navbar_path = __DIR__ . '/components/navbar.php';
if (file_exists($navbar_path)) include $navbar_path;
?>

</body>
</html>