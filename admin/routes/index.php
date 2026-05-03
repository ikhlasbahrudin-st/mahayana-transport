<?php
require_once '../../config/koneksi.php';

$uri = $_SERVER['REQUEST_URI'];

// DELETE (langsung di index)
if(isset($_GET['delete'])){
    $id = (int) $_GET['delete'];

    mysqli_query($conn, "DELETE FROM routes WHERE id=$id");

    header("Location: index.php?success=delete");
    exit;
}

// AMBIL DATA
$data = mysqli_query($conn, "SELECT * FROM routes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Rute</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <?php include '../components/sidebar.php'; ?>

    <!-- CONTENT -->
    <div class="flex-1 flex flex-col">

        <!-- HEADER -->
        <div class="bg-white shadow px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-gray-800">Data Rute</h1>

            <a href="create.php"
               class="bg-yellow-400 hover:bg-yellow-300 px-4 py-2 rounded-lg font-semibold">
               + Tambah Rute
            </a>
        </div>

        <!-- MAIN -->
        <div class="p-6">

            <!-- ALERT -->
            <?php if(isset($_GET['success'])): ?>
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm">
                    <?php
                        if($_GET['success'] == 'delete') echo "Data berhasil dihapus";
                        else echo "Berhasil";
                    ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow overflow-hidden">

                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">No</th>
                            <th class="px-4 py-3 text-left">Asal</th>
                            <th class="px-4 py-3 text-left">Tujuan</th>
                            <th class="px-4 py-3 text-left">Harga</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">

                    <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
                        <tr class="hover:bg-gray-50">

                            <td class="px-4 py-3"><?= $no++ ?></td>

                            <td class="px-4 py-3 font-semibold">
                                <?= $row['departure_city'] ?>
                            </td>

                            <td class="px-4 py-3">
                                <?= $row['arrival_city'] ?>
                            </td>

                            <td class="px-4 py-3">
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">
                                    Rp <?= number_format($row['base_price'],0,',','.') ?>
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-2">

                                    <a href="edit.php?id=<?= $row['id'] ?>"
                                       class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                                       Edit
                                    </a>

                                    <a href="index.php?delete=<?= $row['id'] ?>"
                                       onclick="return confirm('Yakin hapus data ini?')"
                                       class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                       Hapus
                                    </a>

                                </div>
                            </td>

                        </tr>
                    <?php endwhile; ?>

                    </tbody>
                </table>

                <?php if(mysqli_num_rows($data) == 0): ?>
                    <div class="text-center py-10 text-gray-400">
                        Belum ada data rute
                    </div>
                <?php endif; ?>

            </div>

        </div>

        <!-- FOOTER -->
        <div class="bg-white border-t text-center text-xs text-gray-400 py-3">
            © <?= date('Y') ?> Mahayana Admin
        </div>

    </div>

</div>

</body>
</html>