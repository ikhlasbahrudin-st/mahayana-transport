<?php
session_start();
require_once '../../config/koneksi.php';

// ... (Logic delete dan query tetap sama)
$promo = mysqli_query($conn, "SELECT * FROM promos ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Promo - Mahayana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        
        /* CSS FIX UNTUK SIDEBAR & CONTENT */
        @media (min-width: 768px) {
            /* Pastikan angka ini SAMA dengan lebar sidebar di sidebar.php (w-64 = 16rem) */
            .sidebar-fixed { width: 16rem; position: fixed; left: 0; top: 0; height: 100vh; }
            .content-wrapper { margin-left: 16rem; width: calc(100% - 16rem); }
        }
    </style>
</head>
<body class="antialiased">

    <div class="flex flex-col md:flex-row min-h-screen">
        
        <aside class="sidebar-fixed bg-white z-50">
            <?php include '../components/sidebar.php'; ?>
        </aside>

        <div class="content-wrapper flex flex-col flex-1 min-w-0">
            
            <?php include '../components/navbar.php'; ?>

            <main class="p-4 md:p-6 lg:p-8">
                <div class="max-w-full mx-auto">
                    
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Data Promo</h1>
                            <p class="text-slate-500 text-sm">Kelola konten promosi Mahayana Shuttle.</p>
                        </div>
                        <a href="add.php" class="bg-yellow-500 hover:bg-yellow-600 transition-all text-slate-900 px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-yellow-500/20 flex items-center justify-center gap-2 w-fit">
                            <i class="fa-solid fa-plus-circle"></i>
                            Tambah Promo
                        </a>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto no-scrollbar">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold text-[10px] uppercase tracking-wider">
                                        <th class="px-6 py-4">Visual</th>
                                        <th class="px-6 py-4">Judul & Info</th>
                                        <th class="px-6 py-4">Kategori</th>
                                        <th class="px-6 py-4">Benefit</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php if(mysqli_num_rows($promo) > 0): ?>
                                        <?php while($p = mysqli_fetch_assoc($promo)): ?>
                                        <tr class="hover:bg-slate-50/50 transition-colors group">
                                            <td class="px-6 py-4">
                                                <div class="w-16 h-12 rounded-lg overflow-hidden border border-slate-200">
                                                    <img src="../../uploads/promo/<?= $p['image'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" onerror="this.src='https://via.placeholder.com/150'">
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($p['title']) ?></p>
                                                <p class="text-[10px] text-slate-400">ID: #<?= $p['id'] ?></p>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded-md text-[10px] font-bold uppercase italic"><?= htmlspecialchars($p['type']) ?></span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm font-bold text-slate-700"><?= number_format($p['points'], 0, ',', '.') ?> Poin</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex px-2 py-1 rounded-full <?= $p['is_active'] ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-100 text-slate-400 border-slate-200' ?> text-[10px] font-bold border uppercase italic">
                                                    <?= $p['is_active'] ? 'Aktif' : 'Draft' ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex justify-end gap-2">
                                                    <a href="edit.php?id=<?= $p['id'] ?>" class="p-2 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-500 hover:text-white transition-all"><i class="fa-solid fa-pen text-xs"></i></a>
                                                    <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Hapus?')" class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all"><i class="fa-solid fa-trash text-xs"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="p-10 text-center text-slate-400 text-sm">Tidak ada data.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

</body>
</html>