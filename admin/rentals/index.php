<?php
include '../../config/koneksi.php';

// AMBIL DATA LANGSUNG DARI RENTALS
$query = "SELECT * FROM rentals ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Sewa - Mahayana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        /* Solusi agar teks tidak merusak layout */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;  
            overflow: hidden;
        }
    </style>
</head>

<body class="bg-[#f8fafc] flex h-screen overflow-hidden text-slate-700">

<div class="w-72 hidden md:block border-r bg-slate-950">
    <?php include '../components/sidebar.php'; ?>
</div>

<div class="flex-1 flex flex-col overflow-hidden">

    <?php include '../components/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 md:p-8">

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Sewa Armada</h1>
                <p class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                    <span class="flex h-2 w-2 rounded-full bg-yellow-400"></span>
                    Manajemen operasional persewaan bus
                </p>
            </div>

            <a href="add_rental.php"
               class="inline-flex items-center justify-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-slate-900 px-6 py-3 rounded-2xl font-bold text-sm shadow-lg shadow-yellow-200 transition-all hover:-translate-y-0.5">
                <i class="fa-solid fa-plus text-xs"></i>
                Tambah Sewa
            </a>
        </div>

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-fixed min-w-[1000px]">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="w-1/6 px-6 py-5 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Penyewa</th>
                            <th class="w-1/6 px-6 py-5 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Armada</th>
                            <th class="w-1/4 px-6 py-5 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Deskripsi/Catatan</th>
                            <th class="w-1/6 px-6 py-5 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Harga & Tanggal</th>
                            <th class="w-32 px-6 py-5 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="w-28 px-6 py-5 text-[11px] font-extrabold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            
                            $statusConfig = [
                                'pending'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'dot' => 'bg-amber-400'],
                                'confirmed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'dot' => 'bg-emerald-400'],
                                'cancelled' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'dot' => 'bg-rose-400'],
                                'completed' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'dot' => 'bg-blue-400'],
                            ];
                            $st = $statusConfig[$row['status']] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'dot' => 'bg-slate-400'];
                        ?>

                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-5 align-top">
                                <div class="font-bold text-slate-900 truncate" title="<?= $row['customer_name'] ?>">
                                    <?= htmlspecialchars($row['customer_name']) ?>
                                </div>
                                <div class="text-xs text-slate-400 mt-1 italic">
                                    <?= htmlspecialchars($row['customer_phone']) ?>
                                </div>
                            </td>

                            <td class="px-6 py-5 align-top">
                                <span class="font-bold text-sm text-slate-800 block truncate"><?= htmlspecialchars($row['bus_name']) ?></span>
                                <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded text-slate-500 font-bold uppercase"><?= $row['capacity'] ?> Seats</span>
                            </td>

                            <td class="px-6 py-5 align-top">
                                <p class="text-sm text-slate-600 leading-relaxed line-clamp-2 hover:line-clamp-none transition-all cursor-help" title="<?= htmlspecialchars($row['description']) ?>">
                                    <?= !empty($row['description']) ? htmlspecialchars($row['description']) : '<span class="text-slate-300 italic">Tidak ada catatan</span>' ?>
                                </p>
                            </td>

                            <td class="px-6 py-5 align-top">
                                <div class="text-sm font-extrabold text-slate-900">
                                    Rp <?= number_format($row['total_price'],0,',','.') ?>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-1">
                                    <i class="fa-regular fa-calendar-check mr-1"></i> <?= date('d/m/y', strtotime($row['rental_date'])) ?>
                                </div>
                            </td>

                            <td class="px-6 py-5 align-top">
                                <span class="<?= $st['bg'] ?> <?= $st['text'] ?> px-3 py-1 rounded-full text-[10px] font-extrabold uppercase flex items-center w-fit gap-1.5">
                                    <span class="w-1 h-1 rounded-full <?= $st['dot'] ?>"></span>
                                    <?= $row['status'] ?>
                                </span>
                            </td>

                            <td class="px-6 py-5 align-top">
                                <div class="flex justify-center items-center gap-2">
                                    <a href="edit_rental.php?id=<?= $row['id'] ?>"
                                       class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                        <i class="fa-solid fa-pen text-[10px]"></i>
                                    </a>
                                    <a href="hapus_rental.php?id=<?= $row['id'] ?>"
                                       onclick="return confirm('Hapus data?')"
                                       class="w-8 h-8 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                        <i class="fa-solid fa-trash text-[10px]"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>