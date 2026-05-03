<?php 
// Memanggil koneksi database
require_once '../config/koneksi.php'; 
$page_title = "Pesan Shuttle"; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">    <title><?= $page_title ?> - Mahayana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        /* Animasi Modal */
        .animate-modalIn {
            animation: modalIn 0.3s ease-out;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50 max-w-md mx-auto min-h-screen flex flex-col relative pb-24 shadow-2xl">

    <?php include '../components/header.php'; ?>

    <main class="flex-grow">
<section class="bg-white p-6 rounded-b-[40px] shadow-sm mb-6">
<form id="searchForm" class="space-y-6">

    <!-- ASAL -->
    <div class="relative border-b border-gray-100 pb-2">
        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">
            Dari
        </label>

        <div class="flex items-center gap-3">
            <i class="fa-solid fa-location-dot text-red-600 text-sm w-5 text-center"></i>

            <select name="asal"
                required
                class="w-full font-black text-gray-800 focus:outline-none bg-transparent appearance-none cursor-pointer">

                <option value="">Pilih Kota Asal</option>

                <?php
                $q_asal = mysqli_query($conn, "
                    SELECT DISTINCT departure_city 
                    FROM routes 
                    ORDER BY departure_city ASC
                ");

                while ($row_asal = mysqli_fetch_assoc($q_asal)) {
                    echo "<option value='".htmlspecialchars($row_asal['departure_city'], ENT_QUOTES)."'>
                            ".htmlspecialchars($row_asal['departure_city'])."
                          </option>";
                }
                ?>
            </select>

            <i class="fa-solid fa-chevron-down text-gray-300 text-[10px]"></i>
        </div>
    </div>

    <!-- TUJUAN -->
    <div class="relative border-b border-gray-100 pb-2">
        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">
            Ke
        </label>

        <div class="flex items-center gap-3">
            <i class="fa-solid fa-map-pin text-red-600 text-sm w-5 text-center"></i>

            <select name="tujuan"
                required
                class="w-full font-black text-gray-800 focus:outline-none bg-transparent appearance-none cursor-pointer">

                <option value="">Pilih Kota Tujuan</option>

                <?php
                $q_tujuan = mysqli_query($conn, "
                    SELECT DISTINCT arrival_city 
                    FROM routes 
                    ORDER BY arrival_city ASC
                ");

                while ($row_tujuan = mysqli_fetch_assoc($q_tujuan)) {
                    echo "<option value='".htmlspecialchars($row_tujuan['arrival_city'], ENT_QUOTES)."'>
                            ".htmlspecialchars($row_tujuan['arrival_city'])."
                          </option>";
                }
                ?>
            </select>

            <i class="fa-solid fa-chevron-down text-gray-300 text-[10px]"></i>
        </div>
    </div>

    <!-- TANGGAL (OPTIONAL / fallback saja) -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-2">

        <div class="flex-1">
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">
                Pergi
            </label>

            <div class="flex items-center gap-3">
                <i class="fa-solid fa-calendar-day text-red-600 text-sm w-5 text-center"></i>

                <!-- ❗ ini sekarang hanya fallback, tidak jadi logic utama -->
                <input type="date"
                    name="tanggal"
                    value="<?= date('Y-m-d') ?>"
                    class="font-black text-gray-800 text-sm focus:outline-none bg-transparent cursor-pointer">
            </div>
        </div>

        <!-- TOGGLE -->
        <div class="text-right pl-4 border-l border-gray-100">

            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">
                Pulang Pergi?
            </label>

            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox"
                    id="togglePP"
                    name="is_round_trip"
                    value="1"
                    class="sr-only peer">

                <div class="w-10 h-5 bg-gray-200 rounded-full peer
                    peer-checked:bg-red-600
                    after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                    after:bg-white after:border after:border-gray-300
                    after:rounded-full after:h-4 after:w-4
                    after:transition-all peer-checked:after:translate-x-full">
                </div>
            </label>

        </div>
    </div>

    <!-- PULANG -->
    <div id="containerPulang"
        class="hidden animate-modalIn border-b border-gray-100 pb-2">

        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">
            Pulang
        </label>

        <div class="flex items-center gap-3">
            <i class="fa-solid fa-calendar-check text-blue-600 text-sm w-5 text-center"></i>

            <input type="date"
                name="tanggal_pulang"
                value="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                class="font-black text-gray-800 text-sm focus:outline-none bg-transparent cursor-pointer">
        </div>
    </div>

    <!-- BUTTON -->
    <button type="submit"
        class="w-full bg-[#FF4500] py-4 rounded-2xl flex items-center justify-center gap-3 shadow-lg shadow-orange-100 active:scale-95 transition-all mt-4">

        <span class="text-white font-black text-sm uppercase tracking-widest">
            Cari Jadwal
        </span>

        <i class="fa-solid fa-magnifying-glass text-white text-xs"></i>
    </button>

</form>
</section>

        <section class="px-4 mb-8">
            <div class="bg-white border border-gray-100 rounded-3xl p-5 relative overflow-hidden flex items-center shadow-sm">
                <div class="flex-1 relative z-10">
                    <h3 class="text-red-600 font-black text-lg leading-tight">Kamu Butuh Bantuan?</h3>
                    <p class="text-[10px] text-gray-400 font-medium leading-tight mt-1 mb-4">Tanya admin kami di menu Bhisa Chat Center.</p>
                    <a href="#" class="inline-flex items-center gap-2 bg-white border border-gray-200 px-4 py-2 rounded-full shadow-sm active:bg-gray-50 transition-colors">
                        <span class="text-[10px] font-black text-gray-800">Klik di sini</span>
                        <i class="fa-solid fa-comment-dots text-blue-900 text-xs"></i>
                    </a>
                </div>
                <div class="w-32 relative z-0 flex justify-end">
                    <img src="https://img.freepik.com/premium-photo/female-customer-service-assistant-desktop-call-center_484651-22322.jpg" 
                        alt="Customer Service Assistance Bhisa" 
                        class="h-32 w-auto object-cover object-center scale-[1.3] translate-y-4 origin-bottom-right shadow-2xl shadow-gray-200"
                        style="-webkit-mask-image: radial-gradient(circle at top right, black 10%, rgba(255,255,255,0.7) 30%, transparent 60%); 
                                mask-image: radial-gradient(circle at top right, black 10%, rgba(255,255,255,0.7) 30%, transparent 60%);">
                </div>
            </div>
        </section>

        <section class="px-6 mb-10">
            <div class="flex items-center gap-2 mb-8">
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-location-dot text-[#003366]"></i>
                </div>
                <h3 class="text-xl font-black text-[#003366] tracking-tighter uppercase italic">Pilih Point Shuttle</h3>
            </div>

            <div class="mb-12">
                <h4 class="text-xs font-black text-gray-400 mb-6 border-b border-dashed border-gray-200 pb-2 uppercase tracking-[0.2em]">
                    Jawa Barat & Sekitarnya
                </h4>
                <div class="grid grid-cols-4 gap-y-8 text-center">
                    <?php
                    $points_jabar = [
                        ['name' => 'Sukabumi', 'icon' => 'https://iconlogovector.com/uploads/images/2023/05/lg-e71e9b937a1c07a3843115e14969441429.jpg'],
                        ['name' => 'Garut', 'icon' => 'https://iconlogovector.com/uploads/images/2023/05/lg-49ec516693213c2634429703671fc3b315.jpg'],
                        ['name' => 'Bandung', 'icon' => 'https://cdn-icons-png.flaticon.com/512/3261/3261054.png'],
                        ['name' => 'Cianjur', 'icon' => 'https://tse4.mm.bing.net/th/id/OIP.-IiQ2pnsW3LkNcTDsTsr8QHaFj?pid=Api&P=0&h=180'],
                    ];
                    
                    foreach ($points_jabar as $p): 
                        $slug = str_replace(' ', '-', strtolower($p['name']));
                    ?>
                    <a href="/mahayana/shuttle/kota/<?= $slug ?>.php" class="flex flex-col items-center gap-2 group cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-white shadow-sm flex items-center justify-center p-2 border-2 border-gray-50 group-hover:border-red-500 group-hover:shadow-md group-active:scale-90 transition-all duration-200 overflow-hidden">
                            <img src="<?= $p['icon'] ?>" alt="<?= $p['name'] ?>" class="w-full h-full object-contain transition-all">
                        </div>
                        <span class="text-[10px] font-bold text-gray-700 leading-none group-hover:text-red-600 transition-colors">
                            <?= $p['name'] ?>
                        </span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

<div class="mb-8">
    <h4 class="text-xs font-black text-gray-400 mb-6 border-b border-dashed border-gray-200 pb-2 uppercase tracking-[0.2em]">
        Jadetabek
    </h4>
    <div class="grid grid-cols-4 gap-y-8 text-center">
        <?php
        $points_jadetabek = [
            // Link diarahkan ke http://localhost/mahayana/shuttle/kota/jakarta.php
            ['name' => 'Jakarta', 'link' => '/mahayana/shuttle/kota/jakarta.php'], 
            ['name' => 'Cibubur'], 
            ['name' => 'Tangerang'], 
            ['name' => 'Depok'], 
            ['name' => 'Karawang'], 
            ['name' => 'Bekasi'], 
            ['name' => 'Cikarang']
        ];

        foreach ($points_jadetabek as $p): 
            $is_jakarta = ($p['name'] === 'Jakarta');
        ?>
            
            <?php if ($is_jakarta): ?>
                <!-- Jakarta (Bisa Diklik & Label New) -->
                <a href="<?= $p['link'] ?>" class="flex flex-col items-center gap-2 relative group active:scale-95 transition-all">
                    <span class="absolute -top-2 z-10 bg-gold text-[8px] text-navy px-2 py-0.5 rounded-sm font-black uppercase tracking-tighter whitespace-nowrap border border-white shadow-md animate-pulse">
                        New
                    </span>
                    <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center p-2 border-2 border-gold shadow-lg shadow-gold/20">
                        <img src="https://cdn-icons-png.flaticon.com/512/3261/3261054.png" alt="<?= $p['name'] ?>" class="w-full h-full object-contain">
                    </div>
                    <span class="text-[10px] font-black text-navy leading-none italic uppercase"><?= $p['name'] ?></span>
                </a>
            <?php else: ?>
                <!-- Kota Lain (Coming Soon) -->
                <div class="flex flex-col items-center gap-2 relative group">
                    <span class="absolute -top-2 z-10 bg-gray-400 text-[7px] text-white px-1.5 py-0.5 rounded-sm font-black uppercase tracking-tighter whitespace-nowrap border border-white shadow-sm opacity-60">
                        Soon
                    </span>
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center p-2 border-2 border-gray-200 opacity-40 grayscale">
                        <img src="https://cdn-icons-png.flaticon.com/512/3261/3261054.png" alt="<?= $p['name'] ?>" class="w-full h-full object-contain">
                    </div>
                    <span class="text-[10px] font-bold text-gray-300 leading-none italic uppercase"><?= $p['name'] ?></span>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>
    </div>
</div>
        </section>
    </main>

    <div id="modalJadwal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal()"></div>
        
        <div class="bg-white w-full max-w-sm rounded-[32px] overflow-hidden relative z-10 animate-modalIn shadow-2xl">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-black text-gray-800 uppercase italic tracking-tight">Jadwal Tersedia</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-red-600 transition-colors">
                    <i class="fa-solid fa-circle-xmark text-2xl"></i>
                </button>
            </div>
            
            <div id="hasilJadwal" class="p-4 max-h-[65vh] overflow-y-auto no-scrollbar bg-white">
                </div>
        </div>
    </div>

<script>
/* =========================
   CLOSE MODAL
========================= */
function closeModal() {
    $('#modalJadwal').addClass('hidden').removeClass('flex');
    $('body').css('overflow', 'auto');
}


/* =========================
   MAIN SCRIPT
========================= */
$(document).ready(function() {

    /* =========================
       DEFAULT TANGGAL HARI INI
    ========================= */
    let today = new Date();
    let todayDate = today.toISOString().split('T')[0];

    $('input[name="tanggal"]').val(todayDate);

    /* =========================
       SEARCH FORM AJAX
    ========================= */
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();

        $('#modalJadwal').removeClass('hidden').addClass('flex');
        $('body').css('overflow', 'hidden');

        $('#hasilJadwal').html(`
            <div class="flex flex-col items-center py-12">
                <div class="w-10 h-10 border-4 border-gray-100 border-t-red-600 rounded-full animate-spin"></div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-4">
                    Mencari Jadwal...
                </p>
            </div>
        `);

        $.ajax({
            url: 'proses-cari.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#hasilJadwal').html(response);
            },
            error: function() {
                $('#hasilJadwal').html(`
                    <p class="text-center text-red-500 py-10 font-bold">
                        Terjadi kesalahan koneksi.
                    </p>
                `);
            }
        });
    });

    /* =========================
       TOGGLE PP
    ========================= */
    $('#togglePP').on('change', function() {
        $('#containerPulang').toggleClass('hidden', !$(this).is(':checked'));
    });

    /* =========================
       VALIDASI + AUTO DATE
    ========================= */
    $('select[name="asal"], select[name="tujuan"]').on('change', function() {

        let asal = $('select[name="asal"]').val();
        let tujuan = $('select[name="tujuan"]').val();

        if (asal && tujuan && asal === tujuan) {
            alert('Kota asal dan tujuan tidak boleh sama!');
            $('select[name="tujuan"]').val("");
            return;
        }

        if (asal && tujuan) {

            $.ajax({
                url: 'get-available-date.php',
                type: 'POST',
                data: { asal: asal, tujuan: tujuan },

                success: function(res) {

                    let availableDate;

                    try {
                        let data = JSON.parse(res);
                        availableDate = data.tanggal;
                    } catch (e) {
                        availableDate = res;
                    }

                    // =========================
                    // FIX: fallback ke hari ini
                    // =========================
                    if (availableDate && availableDate !== '') {
                        $('input[name="tanggal"]').val(availableDate);
                    } else {
                        $('input[name="tanggal"]').val(todayDate);
                    }

                    // =========================
                    // H+1 FIX
                    // =========================
                    let baseDate = availableDate ? new Date(availableDate) : new Date(todayDate);

                    baseDate.setDate(baseDate.getDate() + 1);

                    let formattedNextDay = baseDate.toISOString().split('T')[0];
                    $('input[name="tanggal_pulang"]').val(formattedNextDay);
                },

                error: function() {
                    console.log('Gagal mengambil tanggal tersedia');

                    // fallback aman
                    $('input[name="tanggal"]').val(todayDate);

                    let fallback = new Date(todayDate);
                    fallback.setDate(fallback.getDate() + 1);

                    $('input[name="tanggal_pulang"]').val(
                        fallback.toISOString().split('T')[0]
                    );
                }
            });
        }
    });

});
</script>

    <?php include '../components/footer.php'; ?>
    <?php include '../components/navbar.php'; ?>
</body>
</html>