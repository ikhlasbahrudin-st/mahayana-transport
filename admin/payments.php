<?php
include 'middleware.php';
include '../config/koneksi.php';

/* =========================
   FILTER
========================= */
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$where = "WHERE 1=1";

if($search){
    $search = mysqli_real_escape_string($conn, $search);
    $where .= " AND (b.booking_code LIKE '%$search%' OR u.fullname LIKE '%$search%')";
}

if($status){
    $where .= " AND b.status = '$status'";
}

/* =========================
   QUERY
========================= */
$query = mysqli_query($conn, "
SELECT 
    b.*, 
    u.fullname,
    u.phone,
    r.departure_city,
    r.arrival_city,
    bus.bus_name
FROM bookings b
JOIN users u ON b.user_id = u.id
JOIN schedules s ON b.schedule_id = s.id
JOIN routes r ON s.route_id = r.id
JOIN buses bus ON s.bus_id = bus.id
$where
ORDER BY b.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Manajemen Pembayaran</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<!-- SIDEBAR -->
<div class="hidden md:block fixed top-0 left-0 w-64 h-screen z-20">
    <?php include 'components/sidebar.php'; ?>
</div>

<!-- MAIN -->
<div class="md:ml-64 flex flex-col min-h-screen">

    <!-- NAVBAR -->
    <div class="sticky top-0 bg-white shadow z-10">
        <?php include 'components/navbar.php'; ?>
    </div>

    <!-- CONTENT -->
    <main class="flex-1 p-6">

        <h2 class="text-2xl font-bold mb-6">Manajemen Pembayaran</h2>

        <!-- FILTER -->
        <form method="GET" class="bg-white p-4 rounded-xl shadow mb-6 flex flex-wrap gap-3">

            <input type="text" name="search" value="<?= $search ?>"
                placeholder="Cari kode / nama..."
                class="border p-2 rounded w-full md:w-64">

            <select name="status" class="border p-2 rounded">
                <option value="">Semua Status</option>
                <option value="pending" <?= $status=='pending'?'selected':'' ?>>Pending</option>
                <option value="paid" <?= $status=='paid'?'selected':'' ?>>Paid</option>
                <option value="failed" <?= $status=='failed'?'selected':'' ?>>Failed</option>
            </select>

            <button class="bg-blue-600 text-white px-4 py-2 rounded">
                Filter
            </button>

            <a href="payments.php" class="bg-gray-300 px-4 py-2 rounded">
                Reset
            </a>

        </form>

        <!-- TABLE -->
        <div class="bg-white rounded-xl shadow overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">Tanggal</th>
                        <th class="p-3 text-left">Kode</th>
                        <th class="p-3 text-left">User</th>
                        <th class="p-3 text-left">Rute</th>
                        <th class="p-3 text-left">Bus</th>
                        <th class="p-3 text-left">Total</th>
                        <th class="p-3 text-left">Status</th>
                    </tr>
                </thead>

                <tbody>

                <?php if(mysqli_num_rows($query) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($query)): ?>

                <tr class="border-t hover:bg-gray-50">

                    <td class="p-3">
                        <?= date('d M Y H:i', strtotime($row['created_at'])) ?>
                    </td>

                    <td class="p-3 font-bold text-blue-600">
                        #<?= $row['booking_code'] ?>
                    </td>

                    <td class="p-3">
                        <?= $row['fullname'] ?><br>
                        <span class="text-xs text-gray-400"><?= $row['phone'] ?></span>
                    </td>

                    <td class="p-3">
                        <?= $row['departure_city'] ?> → <?= $row['arrival_city'] ?>
                    </td>

                    <td class="p-3">
                        <?= $row['bus_name'] ?>
                    </td>

                    <td class="p-3 font-bold text-green-600">
                        Rp <?= number_format($row['total_price'],0,',','.') ?>
                    </td>

                    <td class="p-3">
                        <?php
                        $status = strtolower($row['status']);
                        $color = match($status){
                            'paid' => 'bg-green-100 text-green-600',
                            'pending' => 'bg-yellow-100 text-yellow-600',
                            'failed' => 'bg-red-100 text-red-600',
                            default => 'bg-gray-100 text-gray-600'
                        };
                        ?>
                        <span class="px-3 py-1 rounded-full text-xs font-bold <?= $color ?>">
                            <?= strtoupper($row['status']) ?>
                        </span>
                    </td>

                </tr>

                <?php endwhile; ?>
                <?php else: ?>

                <tr>
                    <td colspan="7" class="text-center p-6 text-gray-400">
                        Tidak ada data pembayaran
                    </td>
                </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </main>



</div>

</body>
</html>