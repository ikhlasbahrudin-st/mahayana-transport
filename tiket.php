<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once 'config/koneksi.php';
date_default_timezone_set('Asia/Jakarta');

/* ===================== LOGIN CHECK ===================== */
if (!isset($_SESSION['user_id'])) {
    header("Location: auth_user/login.php");
    exit();
}

/* ===================== BOOKING ID ===================== */
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id <= 0) {
    header("Location: pesanan.php");
    exit();
}

/* ===================== AMBIL BOOKING UTAMA ===================== */
$query = "
SELECT bk.*, 
       s.departure_time, s.arrival_time, s.date,
       s.id as schedule_id,
       r.departure_city, r.arrival_city, 
       b.bus_name, b.plate_number,
       u.fullname,
       p.status AS payment_status,
       p.order_id
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

if (!$data) {
    die("Data tiket tidak ditemukan");
}

/* ===================== AMBIL SEMUA BOOKING (PP SUPPORT) ===================== */
$stmtAll = $conn->prepare("
SELECT * FROM bookings 
WHERE group_code = ?
");

$stmtAll->bind_param("s", $data['group_code']);
$stmtAll->execute();
$resAll = $stmtAll->get_result();

$booking_ids = [];
while ($b = $resAll->fetch_assoc()) {
    $booking_ids[] = $b['id'];
}

/* ===================== AMBIL PENUMPANG (SEMUA BOOKING) ===================== */
$passengers = [];

if (!empty($booking_ids)) {

    $ids = implode(',', $booking_ids);

    $seatQuery = mysqli_query($conn, "
        SELECT seat_number, passenger_name, booking_id
        FROM booking_details
        WHERE booking_id IN ($ids)
        ORDER BY booking_id ASC
    ");

    while ($row = mysqli_fetch_assoc($seatQuery)) {
        $passengers[] = $row;
    }
}

/* ===================== PAYMENT STATUS ===================== */
$payment_status = strtolower($data['payment_status'] ?? 'pending');

$status_class = 'bg-gray-400 text-white';
$status_text  = strtoupper($payment_status);

if ($payment_status == 'paid' || $payment_status == 'settlement') {
    $status_class = 'bg-emerald-500 text-white';
    $status_text = 'PAID';
} elseif ($payment_status == 'pending') {
    $status_class = 'bg-yellow-400 text-black';
    $status_text = 'PENDING';
} elseif ($payment_status == 'expired') {
    $status_class = 'bg-gray-500 text-white';
    $status_text = 'EXPIRED';
} elseif (in_array($payment_status, ['cancel','failed','rejected'])) {
    $status_class = 'bg-red-500 text-white';
    $status_text = 'FAILED';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Tiket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        @media print {
            .no-print { display: none; }
            body { background-color: white; }
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

<div class="max-w-md w-full space-y-6">

<?php foreach ($all_tickets as $data): ?>

<?php
    // FIX LABEL PP (GO / BACK lebih aman)
    $is_go = stripos($data['booking_code'], 'GO') !== false;
    $label = $is_go ? 'PERGI' : 'PULANG';
?>

    <!-- CARD -->
    <div class="bg-white shadow-2xl rounded-2xl overflow-hidden relative">

        <!-- HEADER -->
        <div class="bg-[#003366] text-white p-5 text-center">
            <h1 class="text-lg font-black tracking-wide text-yellow-400">
                MAHAYANA
            </h1>

            <!-- LABEL TRIP -->
            <div class="mt-2 text-[10px] font-bold uppercase tracking-widest">
                <?= $label ?>
            </div>

            <p class="text-[10px] uppercase tracking-[0.2em] opacity-80 mt-1">
                E-Ticket Receipt
            </p>

            <div class="mt-3 inline-block bg-green-500 text-[10px] font-bold px-3 py-1 rounded-full uppercase">
                LUNAS
            </div>
        </div>

        <!-- TEAR -->
        <div class="flex justify-between px-4">
            <div class="w-6 h-6 bg-gray-100 rounded-full -mt-3"></div>
            <div class="w-6 h-6 bg-gray-100 rounded-full -mt-3"></div>
        </div>

        <!-- CONTENT -->
        <div class="p-5 space-y-4">

            <!-- ROUTE -->
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-[10px] text-gray-400">Asal</p>
                    <p class="font-black text-lg">
                        <?= htmlspecialchars($data['departure_city'] ?? '-') ?>
                    </p>
                </div>

                <div class="text-center flex-1">
                    <i class="fa-solid fa-bus text-gray-300"></i>
                    <div class="border-t border-dashed mt-1"></div>
                </div>

                <div class="text-right">
                    <p class="text-[10px] text-gray-400">Tujuan</p>
                    <p class="font-black text-lg">
                        <?= htmlspecialchars($data['arrival_city'] ?? '-') ?>
                    </p>
                </div>
            </div>

            <!-- DETAIL -->
            <div class="bg-navy/5 p-4 rounded-2xl mb-8 border border-navy/5">
    <p class="text-[10px] text-slate-400 font-black uppercase mb-3">
        Detail Penumpang & Kursi (PP Support)
    </p>

    <div class="space-y-2">
        <?php 
        $grouped = [];
        foreach ($passengers as $p) {
            $grouped[$p['booking_id']][] = $p;
        }
        ?>

        <?php foreach ($grouped as $bid => $items): ?>
            <div class="mb-3">
                <p class="text-[10px] font-bold text-gray-400 mb-1">
                    Booking ID: <?= $bid ?>
                </p>

                <?php foreach ($items as $p): ?>
                    <div class="flex justify-between items-center border-b border-navy/5 pb-2">
                        <span class="text-xs font-semibold text-navy uppercase">
                            <?= htmlspecialchars($p['passenger_name']) ?>
                        </span>
                        <span class="bg-navy text-gold text-[10px] px-2 py-0.5 rounded-md">
                            <?= $p['seat_number'] ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
        </div>

<!-- QR SECTION -->
<div class="bg-gray-50 border-t border-dashed p-6 text-center relative overflow-hidden">

    <!-- QR IMAGE -->
    <div class="flex justify-center">
        <img
            class="w-32 h-32 object-contain rounded-lg shadow-sm bg-white p-2"
            src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=<?= urlencode($data['booking_code']) ?>"
            alt="QR Code"
        >
    </div>

    <!-- BOOKING CODE -->
    <p class="mt-3 tracking-[0.25em] font-extrabold text-gray-800 text-sm">
        <?= htmlspecialchars($data['booking_code']) ?>
    </p>

    <!-- INFO -->
    <p class="text-[11px] text-gray-500 mt-1">
        Tunjukkan QR ini saat boarding
    </p>

    <!-- TICKET CUT EFFECT -->
    <div class="absolute -bottom-3 left-0 right-0 flex justify-between px-5">
        <div class="w-6 h-6 bg-white rounded-full shadow-inner"></div>
        <div class="w-6 h-6 bg-white rounded-full shadow-inner"></div>
    </div>

</div>
    </div>

<?php endforeach; ?>

    <!-- BUTTON -->
    <div class="grid grid-cols-2 gap-3 mt-5 no-print">

        <button onclick="downloadPDF()"
            class="bg-white border py-3 rounded-xl text-sm font-semibold active:scale-95">
            <i class="fa-solid fa-download mr-1 text-blue-500"></i>
            Unduh
        </button>

        <a href="pesanan.php"
           class="bg-[#003366] text-white text-center py-3 rounded-xl text-sm font-bold active:scale-95">
            Selesai
        </a>

    </div>

</div>

<script>
function downloadPDF() {
    document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
    window.print();
    setTimeout(() => {
        document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
    }, 1000);
}
</script>

</body>
</html>