<?php
require_once '../../config/koneksi.php';

// DELETE
if(isset($_GET['delete'])){
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM schedules WHERE id=$id");
    header("Location: index.php?success=delete");
    exit;
}

// JOIN DATA
$data = mysqli_query($conn, "
    SELECT s.*, b.bus_name, r.departure_city, r.arrival_city
    FROM schedules s
    JOIN buses b ON s.bus_id = b.id
    JOIN routes r ON s.route_id = r.id
    ORDER BY 
        CASE WHEN s.is_daily = 1 THEN 0 ELSE 1 END,
        s.date DESC,
        s.departure_time ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Jadwal</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- SIDEBAR FIX -->
    <div class="w-64 fixed h-full hidden md:block">
        <?php include '../components/sidebar.php'; ?>
    </div>

    <!-- CONTENT -->
    <div class="flex-1 md:ml-64 flex flex-col">

        <!-- HEADER -->
        <div class="bg-white shadow px-6 py-4 flex justify-between items-center sticky top-0 z-10">
            <h1 class="text-lg font-bold text-gray-800">Data Jadwal</h1>

            <a href="create.php"
               class="bg-yellow-400 hover:bg-yellow-300 px-4 py-2 rounded-lg font-semibold">
               + Tambah Jadwal
            </a>
        </div>

        <!-- MAIN -->
        <div class="p-6 flex-1 overflow-y-auto">

            <!-- ALERT -->
            <?php if(isset($_GET['success'])): ?>
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm">
                    <?php
                        if($_GET['success'] == 'delete') echo "Jadwal berhasil dihapus";
                        elseif($_GET['success'] == 'update') echo "Jadwal berhasil diupdate";
                        else echo "Jadwal berhasil ditambahkan";
                    ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow overflow-hidden">

                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">No</th>
                            <th class="px-4 py-3 text-left">Bus</th>
                            <th class="px-4 py-3 text-left">Rute</th>
                            <th class="px-4 py-3 text-left">Jam</th>
                            <th class="px-4 py-3 text-left">Jadwal</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">

                    <?php if(mysqli_num_rows($data) > 0): ?>
                        <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
                        <tr class="hover:bg-gray-50">

                            <td class="px-4 py-3"><?= $no++ ?></td>

                            <td class="px-4 py-3 font-semibold">
                                <?= $row['bus_name'] ?>
                            </td>

                            <td class="px-4 py-3">
                                <?= $row['departure_city'] ?> 
                                <span class="text-gray-400">→</span> 
                                <?= $row['arrival_city'] ?>
                            </td>

                            <td class="px-4 py-3">
                                <span class="font-semibold text-gray-800">
                                    <?= date('H:i', strtotime($row['departure_time'])) ?>
                                </span>
                                <span class="text-gray-400">-</span>
                                <span class="font-semibold text-gray-800">
                                    <?= date('H:i', strtotime($row['arrival_time'])) ?>
                                </span>
                            </td>

                            <!-- JADWAL -->
                            <td class="px-4 py-3">

                                <?php if($row['is_daily'] == 1): ?>

                                    <!-- BADGE HARIAN -->
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">
                                        Setiap Hari
                                    </span>

                                <?php else: ?>

                                    <!-- TANGGAL NORMAL -->
                                    <span class="bg-blue-50 text-blue-600 px-2 py-1 rounded text-xs font-bold">
                                        <?= date('d M Y', strtotime($row['date'])) ?>
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-2">

                                    <!-- EDIT -->
                                    <a href="edit.php?id=<?= $row['id'] ?>"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition"
                                    title="Edit">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>

                                    <!-- DELETE -->
                                    <a href="?delete=<?= $row['id'] ?>"
                                    onclick="return confirm('Yakin hapus jadwal ini?')"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-500 hover:bg-red-600 text-white transition"
                                    title="Hapus">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </a>

                                </div>
                            </td>

                        </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-400">
                                Belum ada jadwal tersedia
                            </td>
                        </tr>
                    <?php endif; ?>

                    </tbody>
                </table>

            </div>

        </div>

        <!-- FOOTER FIX -->
        <div class="bg-white border-t text-center text-xs text-gray-400 py-3">
            © <?= date('Y') ?> Mahayana Admin
        </div>

    </div>

</div>

</body>
</html>