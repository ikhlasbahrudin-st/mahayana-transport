<?php
session_start();
require_once '../config/koneksi.php';

$filter = $_GET['tipe'] ?? 'semua';

/* =========================
   AMBIL DATA RENTALS SAJA
========================= */
$query = "
SELECT *
FROM rentals
";

if ($filter != 'semua') {
    $query .= " WHERE capacity = " . (int)$filter;
}

$query .= " ORDER BY id DESC";

$dataRental = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"><title>Sewa Armada</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body { font-family: 'Inter', sans-serif; background:#f8fafc; }
.bg-navy { background: linear-gradient(135deg,#001F3F,#003366); }

.card {
    background:white;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 4px 15px rgba(0,0,0,0.05);
}

.icon-box {
    width:28px;
    height:28px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#f3f4f6;
    border-radius:8px;
}

.no-scrollbar::-webkit-scrollbar { display:none; }
.no-scrollbar { -ms-overflow-style:none; scrollbar-width:none; }
</style>
</head>

<body>

<div class="max-w-md mx-auto bg-white min-h-screen relative shadow-xl">

<?php include __DIR__ . '/../components/header.php'; ?>

<!-- HEADER -->
<div class="bg-navy text-white px-6 py-5 rounded-b-3xl">
    <h1 class="text-xl font-bold">Sewa Armada</h1>
    <p class="text-xs opacity-70">Data rental dari transaksi</p>
</div>

<!-- FILTER -->
<div class="px-4 -mt-4">
    <div class="flex gap-2 overflow-x-auto py-2 no-scrollbar">

        <?php
        function btn($label,$value,$filter){
            $active = $filter == $value;

            return "<a href='?tipe=$value'
                class='px-4 py-2 rounded-xl text-xs font-bold border whitespace-nowrap
                ".($active ? "bg-yellow-400 text-black border-yellow-400" : "bg-white text-gray-600 border-gray-200")."'>
                $label
            </a>";
        }

        echo btn("Semua","semua",$filter);

        $cap = mysqli_query($conn,"SELECT DISTINCT capacity FROM rentals ORDER BY capacity ASC");
        while($c = mysqli_fetch_assoc($cap)){
            echo btn($c['capacity']." Kursi",$c['capacity'],$filter);
        }
        ?>

    </div>
</div>

<!-- LIST RENTAL -->
<div class="px-4 py-4 space-y-5">

<?php if(mysqli_num_rows($dataRental) > 0): ?>
    <?php while($r = mysqli_fetch_assoc($dataRental)): ?>

        <?php
        $image = !empty($r['image'])
            ? "../assets/bus/".$r['image']
            : "https://via.placeholder.com/400x200?text=No+Image";
        ?>

        <div class="card">

            <img src="<?= $image ?>"
                 class="w-full h-48 object-cover"
                 onerror="this.src='https://via.placeholder.com/400x200'">

            <div class="p-4">

                <div class="flex justify-between">
                    <div>
                        <h3 class="font-bold text-gray-800">
                            <?= $r['bus_name'] ?>
                        </h3>
                        <p class="text-[10px] text-gray-400">
                            <?= $r['customer_name'] ?>
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-[10px] text-gray-400">Total</p>
                        <p class="font-bold text-blue-600">
                            Rp <?= number_format($r['total_price'],0,',','.') ?>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 mt-3">
                    <span class="bg-blue-50 text-blue-600 px-2 py-1 rounded text-[10px] font-bold">
                        <i class="fa-solid fa-users"></i> <?= $r['capacity'] ?> Kursi
                    </span>


                </div>

                <!-- FASILITAS (STATIC) -->
                <div class="flex gap-2 mt-4">
                    <div class="icon-box"><i class="fa-solid fa-snowflake text-blue-400 text-xs"></i></div>
                    <div class="icon-box"><i class="fa-solid fa-wifi text-green-400 text-xs"></i></div>
                    <div class="icon-box"><i class="fa-solid fa-plug text-orange-400 text-xs"></i></div>
                    <div class="icon-box"><i class="fa-solid fa-tv text-purple-400 text-xs"></i></div>
                </div>

                <!-- BUTTON -->
                <div class="mt-5 pt-4 border-t flex justify-between items-center">

                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-green-600 text-[11px] font-bold">Aktif</span>
                    </div>

                    <a href="detail.php?id=<?= $r['id'] ?>"
                       class="bg-navy text-white px-5 py-2 rounded-xl text-xs font-bold">
                        Detail
                    </a>

                </div>

            </div>
        </div>

    <?php endwhile; ?>
<?php else: ?>
    <div class="text-center py-20 text-gray-400">
        <i class="fa-solid fa-bus text-6xl mb-3 opacity-20"></i>
        <p>Tidak ada data rental</p>
    </div>
<?php endif; ?>

</div>

<div class="h-24"></div>

<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white shadow-md">
    <?php include __DIR__ . '/../components/navbar.php'; ?>
</div>

</div>

</body>
</html>