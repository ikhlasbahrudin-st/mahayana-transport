<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once 'config/koneksi.php';
require_once 'config/helper.php';
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user_id'])) {
    header("Location: auth_user/login.php");
    exit();
}

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id <= 0) {
    header("Location: pesanan.php");
    exit();
}

/* ===================== DATA FETCHING ===================== */
$query = "
SELECT bk.*, s.departure_time, r.departure_city, r.arrival_city, b.bus_name, u.fullname, p.status AS payment_status
FROM bookings bk
LEFT JOIN schedules s ON bk.schedule_id = s.id
LEFT JOIN routes r ON s.route_id = r.id
LEFT JOIN buses b ON s.bus_id = b.id
LEFT JOIN users u ON bk.user_id = u.id
LEFT JOIN payments p ON p.group_code = bk.group_code
WHERE bk.id = ? AND bk.user_id = ?
LIMIT 1
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) die("Data tidak ditemukan");

$tanggal = (!empty($data['travel_date'])) ? date('d M Y', strtotime($data['travel_date'])) : '-';

/* AMBIL DATA KURSI */
$seats = [];
$stmtSeat = $conn->prepare("SELECT seat_number FROM booking_details WHERE booking_id = ?");
$stmtSeat->bind_param("i", $booking_id);
$stmtSeat->execute();
$resSeat = $stmtSeat->get_result();
while($s = $resSeat->fetch_assoc()) { $seats[] = $s['seat_number']; }
$list_kursi = implode(", ", $seats);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>E-Ticket #<?= $data['booking_code'] ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    <!-- Library untuk konversi HTML ke Gambar PNG -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f8fafc; 
            margin: 0;
            padding-top: 70px; 
            padding-bottom: 90px;
        }
        .font-mono { font-family: 'Space Mono', monospace; }

        #header-sticky {
            position: fixed;
            top: 0; left: 0; width: 100%;
            z-index: 1000;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        #navbar-sticky {
            position: fixed;
            bottom: 0; left: 0; width: 100%;
            z-index: 1000;
            background: white;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }

        .ticket-wrapper {
            background: white;
            border-radius: 24px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
        }

        .cut-out {
            position: absolute;
            width: 30px; height: 30px;
            background: #f8fafc; 
            border-radius: 50%;
            bottom: 235px; 
            z-index: 20;
        }
        .cut-left { left: -15px; }
        .cut-right { right: -15px; }

        .perforation {
            position: absolute;
            bottom: 249px;
            left: 20px; right: 20px;
            border-top: 2px dashed #e2e8f0;
            z-index: 10;
        }

        /* Sembunyikan elemen saat proses "pemotretan" gambar jika perlu */
        .html2canvas-container { width: 400px !important; height: auto !important; }
    </style>
</head>
<body>

<header id="header-sticky" class="no-print">
    <?php 
    $header_path = __DIR__ . '/components/header.php';
    if (file_exists($header_path)) {
        include $header_path;
    } else {
        echo '<div class="p-4 text-center font-bold text-[#001F3F]">E-TICKET</div>';
    }
    ?>
</header>

<main class="max-w-md mx-auto px-4 py-6">

    <!-- ID 'myTicket' ditambahkan untuk target download -->
    <div id="myTicket" class="ticket-wrapper">
        <div class="bg-[#001F3F] p-4 text-center">
            <p class="text-[#d4af37] text-[10px] font-black tracking-[0.3em] uppercase">Mahayana </p>
        </div>

        <div class="p-6 pb-20"> 
            <div class="flex justify-between items-center mb-8 px-2">
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-bold tracking-wider">Origin</p>
                    <p class="text-xl font-black text-[#001F3F] uppercase"><?= $data['departure_city'] ?></p>
                </div>
                <div class="flex flex-col items-center opacity-20">
                    <i class="fa-solid fa-bus text-sm"></i>
                    <div class="w-16 border-t-2 border-[#001F3F] my-1"></div>
                </div>
                <div class="text-right">
                    <p class="text-gray-400 text-[10px] uppercase font-bold tracking-wider">Destination</p>
                    <p class="text-xl font-black text-[#001F3F] uppercase"><?= $data['arrival_city'] ?></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-y-6 border-t border-gray-50 pt-6">
                <div class="col-span-2">
                    <p class="text-gray-400 text-[9px] uppercase font-bold">Passenger Name</p>
                    <p class="text-sm font-bold text-slate-800 uppercase"><?= htmlspecialchars($data['fullname']) ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-[9px] uppercase font-bold">Travel Date</p>
                    <p class="text-sm font-bold text-slate-800"><?= $tanggal ?></p>
                </div>
                <div class="text-right">
                    <p class="text-gray-400 text-[9px] uppercase font-bold">Departure Time</p>
                    <p class="text-sm font-bold text-slate-800"><?= date('H:i', strtotime($data['departure_time'])) ?> WIB</p>
                </div>
                <div>
                    <p class="text-gray-400 text-[9px] uppercase font-bold">Bus Name</p>
                    <p class="text-xs font-bold text-slate-600"><?= htmlspecialchars($data['bus_name']) ?></p>
                </div>
                <div class="text-right">
                    <p class="text-gray-400 text-[9px] uppercase font-bold">Seat Number</p>
                    <p class="text-sm font-bold text-[#001F3F] bg-amber-50 px-3 py-1 rounded-lg border border-amber-100 inline-block">
                        <?= $list_kursi ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="cut-out cut-left"></div>
        <div class="cut-out cut-right"></div>
        <div class="perforation"></div>

        <div class="p-8 pt-12 bg-slate-50/50 flex flex-col items-center border-t border-gray-100">
            <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100 mb-4">
                <!-- Gunakan crossOrigin jika gambar dari server luar agar tidak kena taint canvas -->
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= $data['booking_code'] ?>" 
                     alt="QR Code" class="w-32 h-32" crossorigin="anonymous">
            </div>
            <p class="font-mono text-lg font-black text-[#001F3F] tracking-[0.2em] uppercase"><?= $data['booking_code'] ?></p>
            <p class="text-[9px] text-slate-400 italic mt-2 uppercase tracking-widest text-center">Tunjukkan QR Code ini kepada petugas<br>saat keberangkatan</p>
        </div>
    </div>

    <!-- TOMBOL AKSI -->
    <div class="no-print mt-8 px-2 space-y-6 text-center">
        <!-- Fungsi downloadTicket dipanggil di sini -->
        <button id="btnDownload" onclick="downloadTicket()" class="w-full bg-[#001F3F] text-[#d4af37] py-4 rounded-2xl font-black shadow-lg active:scale-95 transition-all flex items-center justify-center gap-3">
            <i class="fa-solid fa-download"></i>
            DOWNLOAD PNG
        </button>

        <div>
            <a href="pesanan.php" class="text-slate-500 hover:text-slate-800 text-sm font-semibold underline decoration-slate-300 underline-offset-4">
                Kembali ke Pesanan Saya
            </a>
        </div>
    </div>

</main>

<nav id="navbar-sticky" class="no-print">
    <?php 
    $navbar_path = __DIR__ . '/components/navbar.php';
    if (file_exists($navbar_path)) {
        include $navbar_path;
    }
    ?>
</nav>

<script>
function downloadTicket() {
    const btn = document.getElementById('btnDownload');
    const ticket = document.getElementById('myTicket');
    
    // Ubah teks tombol saat loading
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
    btn.disabled = true;

    // Gunakan html2canvas untuk mengambil elemen tiket
    html2canvas(ticket, {
        scale: 3, // Tingkatkan kualitas gambar agar tidak pecah
        useCORS: true, // Izinkan gambar dari domain luar (seperti QR code)
        backgroundColor: "#f8fafc" // Sesuai warna background sobekan
    }).then(canvas => {
        // Konversi canvas ke format PNG
        const image = canvas.toDataURL("image/png");
        
        // Buat link download otomatis
        const link = document.createElement('a');
        link.download = 'Ticket-<?= $data['booking_code'] ?>.png';
        link.href = image;
        link.click();

        // Kembalikan tombol ke semula
        btn.innerHTML = '<i class="fa-solid fa-download"></i> DOWNLOAD PNG';
        btn.disabled = false;
    }).catch(err => {
        console.error("Gagal mendownload tiket:", err);
        alert("Terjadi kesalahan saat mengunduh gambar.");
        btn.innerHTML = '<i class="fa-solid fa-download"></i> DOWNLOAD PNG';
        btn.disabled = false;
    });
}
</script>

</body>
</html>