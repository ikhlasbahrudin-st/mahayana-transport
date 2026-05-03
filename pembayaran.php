<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/koneksi.php';
require_once 'config/helper.php';
require_once 'vendor/autoload.php';


date_default_timezone_set('Asia/Jakarta');

/* =========================
   MIDTRANS CONFIG
========================= */
\Midtrans\Config::$serverKey = "ganti server key kamu";
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

$client_key = "ganti client kamu";

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
   GET BOOKING
========================= */
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
if ($booking_id <= 0) die("Booking tidak valid");

/* =========================
   AMBIL BOOKING
========================= */
$stmt = $conn->prepare("
    SELECT b.*,
           u.fullname,
           u.email,
           s.departure_time,
           s.arrival_time,
           s.date as schedule_date,
           r.departure_city,
           r.arrival_city,
           bs.bus_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN schedules s ON b.schedule_id = s.id
    JOIN routes r ON s.route_id = r.id
    JOIN buses bs ON s.bus_id = bs.id
    WHERE b.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) die("Booking tidak ditemukan");

/* =========================
   TANGGAL AMAN
========================= */
$tanggal = $booking['travel_date'] ?: $booking['schedule_date'] ?: date('Y-m-d');

/* =========================
   HITUNG STATUS REALTIME
========================= */
$departure = strtotime("$tanggal {$booking['departure_time']}");
$arrival   = strtotime("$tanggal {$booking['arrival_time']}");

if ($arrival <= $departure) {
    $arrival = strtotime('+1 day', $arrival);
}

$travel_status = getTravelStatus($departure, $arrival);

$conn->query("
    UPDATE bookings 
    SET travel_status='$travel_status'
    WHERE id=$booking_id
");

/* =========================
   AMBIL KURSI (FIX FIELD)
========================= */
$qSeat = $conn->prepare("
    SELECT seat_number, passenger_name
    FROM booking_details
    WHERE booking_id = ?
    ORDER BY id ASC
");
$qSeat->bind_param("i", $booking_id);
$qSeat->execute();
$resSeat = $qSeat->get_result();

$passenger_list = [];
while ($row = $resSeat->fetch_assoc()) {
    $passenger_list[] = $row;
}

/* =========================
   PAYMENT CHECK / UPSERT FIX
========================= */
$check = $conn->prepare("
    SELECT * FROM payments 
    WHERE group_code = ?
    ORDER BY id DESC
    LIMIT 1
");
$check->bind_param("s", $booking['group_code']);
$check->execute();
$payment = $check->get_result()->fetch_assoc();

/* =========================
   ORDER ID
========================= */
$order_id = 'MHYN-' . $booking_id . '-' . time();

/* =========================
   GENERATE SNAP TOKEN
========================= */
$params = [
    'transaction_details' => [
        'order_id' => $order_id,
        'gross_amount' => (int)$booking['total_price'],
    ]
];

try {
    $snap_token = \Midtrans\Snap::getSnapToken($params);
} catch (Exception $e) {
    die("Midtrans Error: " . $e->getMessage());
}

/* =========================
   UPSERT PAYMENT (FIX UTAMA)
========================= */
if (!$payment) {

    $stmtPay = $conn->prepare("
        INSERT INTO payments (
            group_code, amount, payment_method,
            status, order_id, snap_token, created_at
        ) VALUES (?, ?, 'midtrans', 'pending', ?, ?, NOW())
    ");

    $stmtPay->bind_param(
        "sdss",
        $booking['group_code'],
        $booking['total_price'],
        $order_id,
        $snap_token
    );

    $stmtPay->execute();

} else {

    // 🔥 FIX: kalau ada tapi snap_token kosong → update
    if (empty($payment['snap_token'])) {

        $conn->query("
            UPDATE payments
            SET 
                snap_token = '$snap_token',
                order_id = '$order_id',
                status = 'pending'
            WHERE group_code = '".$booking['group_code']."'
        ");
    } else {
        $snap_token = $payment['snap_token'];
    }
}

/* =========================
   EXPIRE LOGIC
========================= */
$limit = strtotime($booking['created_at']) + 300;
$remaining = $limit - time();

if ($remaining <= 0 && $booking['status'] === 'pending') {

    $group_code = $booking['group_code'];

    $conn->query("
        UPDATE bookings 
        SET status='expired', travel_status='cancelled'
        WHERE group_code='$group_code'
    ");

    $conn->query("
        UPDATE payments 
        SET status='expired', snap_token=NULL
        WHERE group_code='$group_code'
    ");

    $conn->query("
        DELETE bd FROM booking_details bd
        JOIN bookings b ON bd.booking_id = b.id
        WHERE b.group_code='$group_code'
    ");

    header("Location: pesanan.php");
    exit;
}

/* =========================
   PAYMENT STATUS CHECK
   (INI TIDAK BOLEH HAPUS SNAP TOKEN DI SINI!)
========================= */
$payCheck = $conn->prepare("
    SELECT status 
    FROM payments 
    WHERE group_code=?
    ORDER BY id DESC
    LIMIT 1
");
$payCheck->bind_param("s", $booking['group_code']);
$payCheck->execute();
$payData = $payCheck->get_result()->fetch_assoc();

$payment_status = $payData['status'] ?? 'pending';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pembayaran Tiket</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <?php if (!empty($snap_token)): ?>
        <script src="https://app.sandbox.midtrans.com/snap/snap.js"
                data-client-key="<?= $client_key ?>"></script>
    <?php endif; ?>

    <style>
        body { font-family: Inter, sans-serif; background:#f8fafc; }
        .bg-navy { background:#001F3F; }
        .text-gold { color:#C5A059; }
        .card { background:white; border-radius:16px; padding:16px; }
        .btn-pay {
            background: linear-gradient(135deg,#001F3F,#003366);
            color:#C5A059;
            border:1px solid #C5A059;
        }
    </style>
</head>

<body>

<?php include __DIR__ . '/components/header.php'; ?>

<div class="max-w-md mx-auto min-h-screen flex flex-col">

<main class="flex-1 p-5 pt-20 space-y-4">

    <!-- TIMER -->
    <div class="card flex justify-between items-center">
        <div>
            <p class="text-xs text-gray-400">Selesaikan Dalam</p>
            <p id="timer" class="text-2xl font-bold text-navy">--:--</p>
        </div>
        <i class="fa fa-clock text-gold text-xl"></i>
    </div>

    <!-- DETAIL -->
    <div class="card">
        <h2 class="font-bold text-navy text-lg mb-3">
            <?= $booking['departure_city'] ?> → <?= $booking['arrival_city'] ?>
        </h2>

        <p class="text-sm text-gray-500">
            <?= $booking['bus_name'] ?> |
            <?= date('d M Y', strtotime($tanggal)) ?>
        </p>

        <hr class="my-3">

            <?php foreach ($passenger_list as $p): ?>
                <div class="flex justify-between text-sm">
                    <span><?= htmlspecialchars($p['passenger_name'] ?? '-') ?></span>
                    <b>Kursi <?= htmlspecialchars($p['seat_number'] ?? '-') ?></b>
                </div>
            <?php endforeach; ?>
    </div>

    <!-- TOTAL -->
    <div class="card text-center">
        <p class="text-gray-400 text-xs">Total</p>
        <h1 class="text-2xl font-bold text-navy">
            Rp <?= number_format($booking['total_price'],0,',','.') ?>
        </h1>
    </div>

    <!-- BUTTON -->
    <?php if (!empty($snap_token)): ?>
        <button id="pay" class="btn-pay w-full py-3 rounded-xl font-bold">
            BAYAR SEKARANG
        </button>
    <?php else: ?>
        <div class="text-red-500 text-sm text-center">
            Token pembayaran tidak tersedia
        </div>
    <?php endif; ?>

</main>

</div>

<?php include __DIR__ . '/components/navbar.php'; ?>

<script>
let time = <?= (int)$remaining ?>;
let booking_id = <?= (int)$booking_id ?>;

function updateTimer() {
    let m = Math.floor(time / 60);
    let s = time % 60;
    document.getElementById("timer").innerText =
        String(m).padStart(2,'0') + ":" + String(s).padStart(2,'0');
}

updateTimer();

let interval = setInterval(() => {

    time--;

    if (time <= 0) {
        clearInterval(interval);

        fetch('expire-booking.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'booking_id=' + booking_id
        }).then(() => {
            window.location.href = "pesanan.php";
        });

        return;
    }

    updateTimer();

}, 1000);

/* =========================
   MIDTRANS SAFE FIX
========================= */
document.getElementById('pay')?.addEventListener('click', function () {

    const token = "<?= $snap_token ?>";

    if (!token || token === '') {
        alert("Token tidak tersedia");
        return;
    }

    snap.pay(token, {
        onSuccess: function () {
            window.location.href = "tiket.php?booking_id=<?= $booking_id ?>";
        },
        onPending: function () {
            window.location.href = "pesanan.php";
        },
        onError: function () {
            alert("Pembayaran gagal");
        },
        onClose: function () {
            console.log("User menutup popup");
        }
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