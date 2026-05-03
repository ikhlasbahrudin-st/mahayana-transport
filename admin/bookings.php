<?php
include 'middleware.php';
include '../config/koneksi.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$query_string = "
    SELECT b.*, u.fullname, u.phone as user_phone, s.date as travel_date 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    LEFT JOIN schedules s ON b.schedule_id = s.id
    WHERE 1=1
";

if ($search) {
    $query_string .= " AND (b.booking_code LIKE '%$search%' OR u.fullname LIKE '%$search%')";
}

if ($status_filter) {
    $query_string .= " AND b.status = '$status_filter'";
}

$query_string .= " ORDER BY b.created_at DESC";
$bookings_query = mysqli_query($conn, $query_string);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Pemesanan</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-slate-50 h-screen overflow-hidden">

<!-- WRAPPER -->
<div class="flex h-full">

    <!-- SIDEBAR (FIXED) -->
    <div class="w-64 hidden md:block fixed h-full z-20">
        <?php include 'components/sidebar.php'; ?>
    </div>

    <!-- MAIN -->
    <div class="flex-1 flex flex-col md:ml-64 h-full">

        <!-- NAVBAR -->
        <div class="sticky top-0 z-10 bg-white shadow">
            <?php include 'components/navbar.php'; ?>
        </div>

        <!-- CONTENT (SCROLLABLE) -->
        <main class="flex-1 overflow-y-auto p-6">

            <div class="max-w-7xl mx-auto">

                <header class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">
                            Daftar Booking
                        </h2>
                        <p class="text-slate-500 text-sm">
                            Monitor semua transaksi pemesanan tiket masuk.
                        </p>
                    </div>

                    <a href="reports_booking.php"
                       class="bg-white border-2 border-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-sm hover:bg-slate-50 flex items-center gap-2">
                        <i class="fa-solid fa-file-export"></i> Export
                    </a>
                </header>

                <!-- FILTER -->
                <div class="bg-white p-4 rounded-2xl shadow-sm mb-6 border flex flex-wrap md:flex-nowrap gap-4 items-center">
                    <form method="GET" class="flex flex-1 gap-4 w-full">

                        <div class="relative flex-1">
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="search" value="<?= $search ?>"
                                   placeholder="Cari Kode Booking atau Nama..."
                                   class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border-2 focus:border-blue-500 rounded-xl outline-none text-sm">
                        </div>

                        <select name="status" onchange="this.form.submit()"
                                class="bg-slate-50 border-2 focus:border-blue-500 rounded-xl px-4 py-2.5 text-sm font-bold">
                            <option value="">Semua Status</option>
                            <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $status_filter == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>

                        <button type="submit"
                                class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-blue-700 text-sm">
                            FILTER
                        </button>

                    </form>
                </div>

                <!-- TABLE -->
                <div class="bg-white rounded-3xl shadow-sm border overflow-hidden">

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">

                            <thead>
                            <tr class="bg-slate-50 border-b">
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase">Waktu</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase">Kode</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase">Pemesan</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase">Harga</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase">Status</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase">Perjalanan</th>
                                <th class="p-4 text-[10px] font-black text-center text-slate-400 uppercase">Aksi</th>
                            </tr>
                            </thead>

                            <tbody class="divide-y">

                            <?php if(mysqli_num_rows($bookings_query) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($bookings_query)): ?>

                                    <tr class="hover:bg-slate-50">

                                        <td class="p-4 text-sm">
                                            <?= date('d M Y H:i', strtotime($row['created_at'])) ?>
                                        </td>

                                        <td class="p-4 font-bold text-blue-600 text-sm">
                                            #<?= $row['booking_code'] ?>
                                        </td>

                                        <td class="p-4 text-sm">
                                            <?= $row['fullname'] ?><br>
                                            <span class="text-xs text-slate-400"><?= $row['user_phone'] ?></span>
                                        </td>

                                        <td class="p-4 font-bold text-sm">
                                            Rp <?= number_format($row['total_price'], 0, ',', '.') ?>
                                        </td>

                                        <td class="p-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100">
                                                <?= $row['status'] ?>
                                            </span>
                                        </td>

                                        <td class="p-4 text-xs">
                                            <?= $row['travel_status'] ?? 'Scheduled' ?>
                                        </td>

                                        <td class="p-4 text-center">
                                            <div class="flex justify-center gap-2">

                                                <a href="booking_detail.php?id=<?= $row['id'] ?>"
                                                   class="w-8 h-8 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </a>

                                                <button onclick="confirmCancel(<?= $row['id'] ?>)"
                                                        class="w-8 h-8 flex items-center justify-center bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-600 hover:text-white">
                                                    <i class="fa-solid fa-xmark text-xs"></i>
                                                </button>

                                            </div>
                                        </td>

                                    </tr>

                                <?php endwhile; ?>
                            <?php endif; ?>

                            </tbody>
                        </table>
                    </div>

                </div>

            </div>

        </main>



    </div>

</div>

<script>
function confirmCancel(id) {
    if(confirm('Apakah yakin ingin membatalkan?')) {
        window.location.href = 'booking_action.php?action=cancel&id=' + id;
    }
}
</script>
<script src="/mahayana/admin/assets/js/auto-refresh.js"></script>
<script>
let scrollPos = 0;

setInterval(() => {
    scrollPos = window.scrollY;
    localStorage.setItem("scrollPos", scrollPos);
}, 500);

/* restore */
window.addEventListener("load", () => {
    const saved = localStorage.getItem("scrollPos");
    if (saved) {
        window.scrollTo({ top: saved, behavior: "instant" });
    }
});
</script>
</body>
</html>