<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"><title>Kirim Paket - Premium Delivery</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap');
    
    body {
        font-family: 'Inter', sans-serif;
        background: #f8fafc;
        margin: 0;
        padding: 0;
    }

    .bg-navy { 
        background: linear-gradient(135deg, #001F3F 0%, #003366 100%);
    }
    
    .text-gold { color: #C5A059; }

    .card-elegant {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }

    .input-custom {
        width: 100%;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 11px;
        font-weight: 600;
        outline: none;
    }
    
    .input-custom:focus {
        border-color: #C5A059;
    }

    /* Menghilangkan scrollbar pada elemen tertentu jika perlu */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
</style>
</head>

<body class="bg-slate-100">

<div class="max-w-md mx-auto bg-white min-h-screen shadow-2xl relative flex flex-col">

    <header class="sticky top-0 z-50 bg-white">
        <?php include __DIR__ . '/../components/header.php'; ?>
    </header>

    <main class="flex-grow">
        
        <div class="bg-navy px-5 pt-6 pb-12 text-white rounded-b-[2.5rem]">
            <h1 class="text-[18px] font-black uppercase tracking-tight leading-tight">
                Kirim Paket
            </h1>
            <p class="text-[11px] opacity-70 mt-1 tracking-wide">
                Layanan Logistik Premium 📦
            </p>
        </div>

        <div class="px-5 -mt-8 relative z-10">
            <div class="card-elegant p-5 space-y-4 border border-gray-50">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-1">Asal</label>
                        <input type="text" placeholder="Kota Asal" class="input-custom mt-1">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-1">Tujuan</label>
                        <input type="text" placeholder="Kota Tujuan" class="input-custom mt-1">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-1">Berat Barang</label>
                    <div class="relative mt-1">
                        <input type="number" placeholder="0.0" class="input-custom pr-12">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 font-black">
                            KG
                        </span>
                    </div>
                </div>

                <button class="w-full bg-navy text-gold py-3 rounded-xl font-black text-[11px] uppercase tracking-widest active:scale-95 transition-transform">
                    Cek Tarif Sekarang
                </button>
            </div>
        </div>

        <div class="px-6 py-6 space-y-3">
            <div class="flex items-center gap-2">
                <div class="h-[1px] bg-gray-100 flex-grow"></div>
                <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest">Petunjuk Pengiriman</span>
                <div class="h-[1px] bg-gray-100 flex-grow"></div>
            </div>

            <ul class="text-[10px] text-gray-500 font-semibold space-y-2 italic">
                <li class="flex gap-2 items-start">
                    <i class="fa-solid fa-circle-info text-navy mt-0.5"></i>
                    <span>Gunakan alamat lengkap (No. Rumah, RT/RW, Kode Pos).</span>
                </li>
                <li class="flex gap-2 items-start">
                    <i class="fa-solid fa-circle-info text-navy mt-0.5"></i>
                    <span>Pastikan berat barang akurat untuk tarif yang sesuai.</span>
                </li>
            </ul>
        </div>

        <div class="px-5 mb-10">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-[12px] font-black text-navy uppercase tracking-tight">
                    Pengiriman Terbaru
                </h2>
                <a href="#" class="text-[10px] text-gold font-bold bg-gold/10 px-2 py-1 rounded">Lihat Semua</a>
            </div>

            <?php
            $paket = [
                ['resi'=>'PREM-990123','tujuan'=>'Bandung','status'=>'Dalam Perjalanan','harga'=>20000],
                ['resi'=>'PREM-990124','tujuan'=>'Jakarta','status'=>'Sudah Sampai','harga'=>35000],
                ['resi'=>'PREM-990200','tujuan'=>'Surabaya','status'=>'Sudah Sampai','harga'=>45000],
            ];

            foreach($paket as $item):
            ?>
            <div class="card-elegant p-4 mb-3 border border-gray-50">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-[12px] font-black text-navy tracking-tight">
                            <?= $item['resi'] ?>
                        </p>
                        <p class="text-[10px] text-gray-400 font-bold flex items-center gap-1 mt-1">
                            <i class="fa-solid fa-location-arrow text-gold"></i>
                            Ke: <?= $item['tujuan'] ?>
                        </p>
                    </div>
                    <span class="text-[8px] px-2 py-1.5 rounded-lg font-black uppercase tracking-tighter
                        <?= $item['status']=='Sudah Sampai' 
                            ? 'bg-emerald-50 text-emerald-600' 
                            : 'bg-amber-50 text-amber-600' ?>">
                        <?= $item['status'] ?>
                    </span>
                </div>
                
                <div class="mt-4 pt-3 border-t border-gray-50 flex justify-between items-center">
                    <p class="text-[9px] font-bold text-gray-300 uppercase">Biaya Layanan</p>
                    <p class="text-xs font-black text-navy italic">
                        Rp <?= number_format($item['harga'],0,',','.') ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="pb-32"></div>
    </main>

    <div class="fixed bottom-0 left-0 w-full z-50">
        <?php include __DIR__ . '/../components/navbar.php'; ?>
    </div>

</div>

</body>
</html>