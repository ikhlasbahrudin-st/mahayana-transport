<?php
require_once '../config/koneksi.php';
date_default_timezone_set('Asia/Jakarta');

/* =========================
   INPUT
========================= */
$asal   = trim($_POST['asal'] ?? '');
$tujuan = trim($_POST['tujuan'] ?? '');
$is_pp  = $_POST['is_round_trip'] ?? '0';

/* sanitize */
$asal   = mysqli_real_escape_string($conn, $asal);
$tujuan = mysqli_real_escape_string($conn, $tujuan);

$now = time();

/* =========================
   VALIDASI
========================= */
if ($asal === '' || $tujuan === '') {
    echo "<div class='text-center py-10 text-red-500 font-bold'>Form belum lengkap</div>";
    exit;
}

if ($asal === $tujuan) {
    echo "<div class='text-center py-10 text-red-500 font-bold'>Asal & tujuan tidak boleh sama</div>";
    exit;
}

/* =========================
   SEAT INFO
========================= */
function getSeatInfo($conn, $schedule_id)
{
    $schedule_id = (int)$schedule_id;

    $total_q = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM seats
        WHERE schedule_id=$schedule_id AND is_driver=0
    ");
    $total = mysqli_fetch_assoc($total_q)['total'] ?? 0;

    $avail_q = mysqli_query($conn, "
        SELECT COUNT(*) AS available
        FROM seats
        WHERE schedule_id=$schedule_id
        AND status='available'
        AND is_driver=0
    ");
    $available = mysqli_fetch_assoc($avail_q)['available'] ?? 0;

    return [
        'total' => (int)$total,
        'available' => (int)$available
    ];
}

/* =========================
   FIX: NORMALIZE DATETIME (INI KUNCI UTAMA)
========================= */
function getDepartureTimestamp($departure_time)
{
    $today = date('Y-m-d');
    $ts_today = strtotime($today . ' ' . $departure_time);

    // jika jam kecil (00-05) & sekarang malam → dianggap BESOK
    $nowHour = (int)date('H');
    $depHour = (int)substr($departure_time, 0, 2);

    if ($nowHour >= 20 && $depHour < 6) {
        return strtotime('+1 day ' . $departure_time);
    }

    return $ts_today;
}

/* =========================
   STATUS ENGINE
========================= */
function getScheduleStatus($departure_time)
{
    $dep = getDepartureTimestamp($departure_time);
    $now = time();

    if ($dep <= $now) return 'departed';

    return 'upcoming';
}

/* =========================
   TIME LEFT LABEL
========================= */
function timeDiffLabel($departure_time)
{
    $dep = getDepartureTimestamp($departure_time);
    $now = time();

    if ($dep <= $now) {
        return "Sudah berangkat";
    }

    $diff = $dep - $now;

    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);

    if ($hours > 0) {
        return "Berangkat dalam {$hours} jam {$minutes} menit";
    }

    return "Berangkat dalam {$minutes} menit";
}

/* =========================
   CARD RENDER
========================= */
function renderCard($row, $conn)
{
    $id = (int)$row['schedule_id'];
    $seat = getSeatInfo($conn, $id);

    $sisa = $seat['available'];
    $kapasitas = (int)($row['capacity'] ?? 0);

    $jam1 = date('H:i', strtotime($row['departure_time']));
    $jam2 = date('H:i', strtotime($row['arrival_time']));

    $status = getScheduleStatus($row['departure_time']);
    $time_info = timeDiffLabel($row['departure_time']);

    $is_full = ($sisa <= 0);
?>

<div class="bg-white border rounded-2xl p-4 mb-3">

    <!-- HEADER -->
    <div class="flex justify-between">
        <div class="text-red-600 font-bold text-xs">
            <?= htmlspecialchars($row['bus_name']) ?>
        </div>

        <div class="text-right text-xs text-gray-500">
            <div>Sisa <?= $sisa ?></div>
            <div class="text-[10px]"><?= $sisa ?> / <?= $kapasitas ?></div>
        </div>
    </div>

    <!-- STATUS -->
    <div class="mt-1 text-[10px] font-bold space-y-1">

        <?php if ($status === 'departed'): ?>
            <span class="text-red-600">SUDAH BERANGKAT</span>

        <?php elseif ($is_full): ?>
            <span class="text-gray-600">FULL</span>

        <?php else: ?>
            <span class="text-green-600">TERSEDIA</span>
        <?php endif; ?>

        <div class="text-gray-400 font-medium">
            <?= $time_info ?>
        </div>

    </div>

    <!-- TIME -->
    <div class="flex justify-between mt-2">
        <div>
            <div class="font-bold text-lg"><?= $jam1 ?></div>
            <div class="text-xs"><?= htmlspecialchars($row['departure_city']) ?></div>
        </div>

        <div>➡️</div>

        <div class="text-right">
            <div class="font-bold text-lg"><?= $jam2 ?></div>
            <div class="text-xs"><?= htmlspecialchars($row['arrival_city']) ?></div>
        </div>
    </div>

    <!-- ACTION -->
    <div class="flex justify-between mt-3 items-center">

        <div class="text-green-600 font-bold">
            Rp <?= number_format($row['base_price']) ?>
        </div>

        <?php if ($status === 'departed'): ?>
            <button class="bg-gray-300 text-gray-500 px-3 py-1 rounded-lg text-xs" disabled>
                Closed
            </button>

        <?php elseif ($is_full): ?>
            <button class="bg-gray-300 text-gray-500 px-3 py-1 rounded-lg text-xs" disabled>
                Full
            </button>

        <?php else: ?>
            <a href="pilih-kursi.php?schedule_id=<?= $id ?>"
               class="bg-black text-white px-3 py-1 rounded-lg text-xs">
                Pilih
            </a>
        <?php endif; ?>

    </div>

</div>

<?php
}

/* =========================
   QUERY
========================= */
$qPergi = mysqli_query($conn, "
SELECT 
    s.id AS schedule_id,
    s.*,
    b.bus_name,
    b.capacity,
    r.departure_city,
    r.arrival_city,
    r.base_price
FROM schedules s
JOIN buses b ON s.bus_id=b.id
JOIN routes r ON s.route_id=r.id
WHERE LOWER(r.departure_city)=LOWER('$asal')
AND LOWER(r.arrival_city)=LOWER('$tujuan')
AND s.is_daily=1
ORDER BY s.departure_time ASC
");

/* =========================
   OUTPUT
========================= */
echo "<h3 class='font-bold mb-2'>Jadwal Tersedia</h3>";

if (mysqli_num_rows($qPergi) > 0) {
    while ($row = mysqli_fetch_assoc($qPergi)) {
        renderCard($row, $conn);
    }
} else {
    echo "<div class='text-gray-400'>Tidak ada jadwal</div>";
}

exit;
?>