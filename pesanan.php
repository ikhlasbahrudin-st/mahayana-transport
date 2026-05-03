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

$user_id = $_SESSION['user_id'];

/* =========================
   REALTIME STATUS FUNCTION
========================= */
function getTravelStatus($departure, $arrival) {
    $now = time();

    if ($now < $departure) return 'pending';
    if ($now >= $departure && $now < $arrival) return 'on_progress';
    return 'completed';
}

/* =========================
   QUERY PESANAN (NO AUTO UPDATE DB)
========================= */
$stmt = $conn->prepare("
    SELECT 
        b.id,
        b.booking_code,
        b.group_code,
        b.schedule_id,
        b.created_at,
        b.travel_date,

        COALESCE(b.status,'pending') AS payment_status,
        COALESCE(p.paid_at,'') AS paid_at,

        r.departure_city,
        r.arrival_city,

        s.date,
        s.departure_time,
        s.arrival_time

    FROM bookings b
    LEFT JOIN payments p ON p.group_code = b.group_code
    JOIN schedules s ON s.id = b.schedule_id
    JOIN routes r ON r.id = s.route_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

/* =========================
   GROUP DATA
========================= */
$pesanan_aktif = [];
$pesanan_riwayat = [];

while ($row = $result->fetch_assoc()) {

    $payment_status = strtolower($row['payment_status'] ?? 'pending');

    /* =========================
       AMBIL TANGGAL AMAN
    ========================= */
    $travel_date = $row['travel_date'] ?: $row['date'] ?: date('Y-m-d');

    if (empty($travel_date) || $travel_date == '0000-00-00') {
        $travel_date = date('Y-m-d');
    }

    $departure_time = strtotime($travel_date . ' ' . ($row['departure_time'] ?? '00:00:00'));
    $arrival_time   = strtotime($travel_date . ' ' . ($row['arrival_time'] ?? '00:00:00'));

    if ($arrival_time < $departure_time) {
        $arrival_time = strtotime('+1 day', $arrival_time);
    }

    /* =========================
       REALTIME STATUS (NO DB UPDATE)
    ========================= */
    $travel_status = getTravelStatus($departure_time, $arrival_time);

    $row['travel_status'] = $travel_status;

    /* =========================
       FILTER AKTIF / RIWAYAT
    ========================= */

    if (
        in_array($payment_status, ['paid','settlement']) &&
        $travel_status != 'completed'
    ) {
        $pesanan_aktif[] = $row;
    } else {
        $pesanan_riwayat[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Pesanan Saya</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
body { font-family: 'Inter', sans-serif; }
[x-cloak] { display:none !important; }
</style>

</head>

<body class="bg-gray-50 max-w-md mx-auto min-h-screen border-x shadow-inner">

<?php include 'components/header.php'; ?>

<main class="p-5 pb-32">

<div class="mb-6">
    <h1 class="text-2xl font-black text-[#003366] uppercase">Pesanan Saya</h1>
    <p class="text-xs text-gray-400 uppercase">Kelola tiket Anda</p>
</div>

<div x-data="{ tab: 'aktif' }">

<!-- TAB -->
<div class="flex bg-gray-200 p-1 rounded-xl mb-4">
    <button @click="tab='aktif'"
        :class="tab=='aktif' ? 'bg-white text-red-600' : 'text-gray-500'"
        class="flex-1 py-2 text-xs font-bold rounded-xl">
        Aktif
    </button>

    <button @click="tab='riwayat'"
        :class="tab=='riwayat' ? 'bg-white text-red-600' : 'text-gray-500'"
        class="flex-1 py-2 text-xs font-bold rounded-xl">
        Riwayat
    </button>
</div>

<!-- ================= AKTIF ================= -->
<div x-show="tab==='aktif'" x-transition class="px-1">

<?php $ada_data = false; ?>

<?php if (!empty($pesanan_aktif)): ?>

    <?php foreach($pesanan_aktif as $row): 

    $status = strtolower($row['payment_status'] ?? 'pending');
    $travel = strtolower($row['travel_status'] ?? 'pending');

    /* ================= LOGIC ================= */

    $is_unpaid = in_array($status, ['pending','expired','failed']);
    $is_active_trip = ($status === 'paid' && in_array($travel, ['pending','on_progress']));

    if ($travel === 'completed') continue;

    if ($is_unpaid || $is_active_trip):

        $ada_data = true;

        if ($is_unpaid) {
            $label = 'BELUM BAYAR';
            $class = 'bg-red-100 text-red-600 border-red-200';
            $icon  = 'fa-credit-card';
        }
        elseif ($travel === 'on_progress') {
            $label = 'SEDANG BERJALAN';
            $class = 'bg-blue-100 text-blue-700 border-blue-200';
            $icon  = 'fa-bus';
        }
        else {
            $label = 'TIKET AKTIF';
            $class = 'bg-emerald-100 text-emerald-700 border-emerald-200';
            $icon  = 'fa-circle-check';
        }

        $tanggal = !empty($row['travel_date'])
            ? date('d M Y', strtotime($row['travel_date']))
            : (!empty($row['date']) ? date('d M Y', strtotime($row['date'])) : '-');

    ?>

    <!-- ================= CARD ================= -->
    <div class="bg-white rounded-3xl mb-5 shadow-sm border overflow-hidden">

        <!-- HEADER -->
        <div class="bg-[#001F3F] p-4 flex justify-between items-center">
            <div>
                <p class="text-[9px] text-gray-400 uppercase">Kode Booking</p>
                <p class="text-sm font-black text-white">
                    <?= htmlspecialchars($row['booking_code']) ?>
                </p>
            </div>

            <span class="text-[9px] px-3 py-1 rounded-xl font-black border <?= $class ?>">
                <i class="fa-solid <?= $icon ?>"></i> <?= $label ?>
            </span>
        </div>

        <!-- BODY -->
        <div class="p-5">
            <p class="font-bold">
                <?= htmlspecialchars($row['departure_city']) ?> →
                <?= htmlspecialchars($row['arrival_city']) ?>
            </p>

            <p class="text-xs text-gray-500 mt-1">
                <?= $tanggal ?>
            </p>

            <!-- BUTTON -->
            <?php if ($is_unpaid): ?>
                <a href="pembayaran.php?booking_id=<?= $row['id'] ?>"
                   class="block mt-4 bg-red-600 text-white text-center py-2 rounded-xl font-bold">
                    BAYAR SEKARANG
                </a>
            <?php else: ?>
                <a href="detail_pesanan.php?id=<?= $row['id'] ?>"
                   class="block mt-4 bg-[#001F3F] text-[#D4AF37] text-center py-2 rounded-xl font-bold">
                    LIHAT E-TIKET
                </a>
            <?php endif; ?>

        </div>

    </div>

    <?php endif; ?>
    <?php endforeach; ?>

<?php endif; ?>

<!-- ================= EMPTY STATE + BUTTON ================= -->
<?php if (!$ada_data): ?>
    <div class="text-center py-10">

        <p class="text-gray-400 mb-4">Tidak ada tiket aktif</p>

<a href="http://localhost/mahayana/shuttle/shuttle.php"
   class="inline-flex items-center justify-center px-8 py-4 
          bg-gradient-to-br from-[#d4af37] via-[#f2d06b] to-[#b8860b] 
          text-[#001F3F] font-black uppercase tracking-widest text-sm
          rounded-xl shadow-[0_10px_20px_-5px_rgba(212,175,55,0.4)] 
          hover:shadow-[0_15px_25px_-5px_rgba(212,175,55,0.5)] 
          hover:-translate-y-1 active:scale-95 
          transition-all duration-300 border-b-4 border-[#b8860b]
          group">
    
    <!-- Icon Plus dengan animasi hover -->

    
    BELI TIKET
</a>

    </div>
<?php endif; ?>

</div>

<!-- ================= RIWAYAT ================= -->
<div x-show="tab==='riwayat'" x-transition x-cloak class="px-2">

<?php if (!empty($pesanan_riwayat)): ?>
<?php foreach($pesanan_riwayat as $row):

$status = strtolower($row['payment_status'] ?? 'pending');
$travel = strtolower($row['travel_status'] ?? 'pending');

/* ================= STATUS ================= */
$status = strtolower($row['payment_status'] ?? 'pending');
$travel = strtolower($row['travel_status'] ?? 'pending');

/* ================= STATUS ================= */
if ($status === 'paid') {
    $label = 'TERBAYAR';
    $class = 'bg-emerald-50 text-emerald-600';
    $icon  = 'fa-circle-check';
}
elseif ($status === 'expired') {
    $label = 'EXPIRED';
    $class = 'bg-red-50 text-red-500';
    $icon  = 'fa-circle-xmark';
}
elseif ($status === 'failed') {
    $label = 'FAILED';
    $class = 'bg-red-50 text-red-500';
    $icon  = 'fa-circle-xmark';
}
elseif ($status === 'pending') {
    $label = 'BELUM BAYAR';
    $class = 'bg-amber-50 text-amber-600';
    $icon  = 'fa-clock';
}
else {
    $label = strtoupper($status);
    $class = 'bg-gray-100 text-gray-500';
    $icon  = 'fa-circle-info';
}
/* ================= DATE SAFE ================= */
$tanggal = !empty($row['date'])
    ? date('d M Y', strtotime($row['date']))
    : (!empty($row['travel_date'])
        ? date('d M Y', strtotime($row['travel_date']))
        : '-');
?>

<!-- CARD -->
<div class="bg-white rounded-2xl p-4 mb-4 shadow-sm border-l-4 
    <?= $travel=='completed' ? 'border-green-400' : 'border-gray-200' ?>">

    <div class="flex justify-between mb-2">
        <p class="font-bold text-sm">
            <?= htmlspecialchars($row['booking_code']) ?>
        </p>

        <span class="text-[10px] px-2 py-1 rounded <?= $class ?>">
            <i class="fa-solid <?= $icon ?>"></i>
            <?= $label ?>
        </span>
    </div>

    <p class="text-sm">
        <?= htmlspecialchars($row['departure_city']) ?> →
        <?= htmlspecialchars($row['arrival_city']) ?>
    </p>

    <p class="text-xs text-gray-400 mt-1">
        <?= $tanggal ?>
    </p>

</div>

<?php endforeach; ?>

<?php else: ?>
    <p class="text-center text-gray-400">Belum ada riwayat</p>
<?php endif; ?>

</div>

</div>
</main>

<?php include 'components/navbar.php'; ?>
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