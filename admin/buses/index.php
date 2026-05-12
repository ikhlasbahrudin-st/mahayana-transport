<?php
require_once '../../config/koneksi.php';

// DELETE (opsional langsung di sini biar simpel)
if(isset($_GET['delete'])){
    $id = (int) $_GET['delete'];

    $q = mysqli_query($conn, "SELECT image FROM buses WHERE id=$id");
    $d = mysqli_fetch_assoc($q);

    if($d && !empty($d['image']) && file_exists('../../assets/bus/'.$d['image'])){
        unlink('../../assets/bus/'.$d['image']);
    }

    mysqli_query($conn, "DELETE FROM buses WHERE id=$id");

    header("Location: index.php?success=delete");
    exit;
}

$data = mysqli_query($conn, "SELECT * FROM buses ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Bus</title>
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
    <h1 class="text-lg font-bold text-gray-800">Data Bus</h1>

    <a href="create.php"
       class="bg-yellow-400 hover:bg-yellow-300 px-4 py-2 rounded-lg font-semibold">
       + Tambah Bus
    </a>
</div>

<!-- MAIN -->
<div class="p-6">

<?php if(isset($_GET['success'])): ?>
<div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm">
    <?php
    if($_GET['success']=='delete') echo "Data berhasil dihapus";
    elseif($_GET['success']=='update') echo "Data berhasil diupdate";
    else echo "Data berhasil ditambahkan";
    ?>
</div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow overflow-hidden">

<table class="w-full text-sm">

<thead class="bg-gray-50 text-gray-600 uppercase text-xs">
<tr>
<th class="px-4 py-3">No</th>
<th class="px-4 py-3">Gambar</th>
<th class="px-4 py-3">Nama Bus</th>
<th class="px-4 py-3">Plat</th>
<th class="px-4 py-3">Kapasitas</th>
<th class="px-4 py-3 text-center">Aksi</th>
</tr>
</thead>

<tbody class="divide-y">

<?php if(mysqli_num_rows($data) > 0): ?>
<?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>

<tr class="hover:bg-gray-50">

<td class="px-4 py-3"><?= $no++ ?></td>

<!-- GAMBAR -->
<td class="px-4 py-3">
    <?php if(!empty($row['image'])): ?>
        <img src="../../assets/bus/<?= $row['image'] ?>"
             class="w-16 h-12 object-cover rounded-lg border">
    <?php else: ?>
        <div class="w-16 h-12 bg-gray-200 flex items-center justify-center rounded text-xs text-gray-400">
            No Img
        </div>
    <?php endif; ?>
</td>

<td class="px-4 py-3 font-semibold text-gray-800">
    <?= $row['bus_name'] ?>
</td>

<td class="px-4 py-3 text-gray-600">
    <?= $row['plate_number'] ?>
</td>

<td class="px-4 py-3">
    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold">
        <?= $row['capacity'] ?> Kursi
    </span>
</td>

<td class="px-4 py-3 text-center">
    <div class="flex justify-center gap-2">

        <a href="edit.php?id=<?= $row['id'] ?>"
           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
           Edit
        </a>

        <a href="?delete=<?= $row['id'] ?>"
           onclick="return confirm('Yakin hapus data ini?')"
           class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
           Hapus
        </a>

    </div>
</td>

</tr>

<?php endwhile; ?>
<?php else: ?>

<tr>
<td colspan="6" class="text-center py-10 text-gray-400">
    Data bus belum ada
</td>
</tr>

<?php endif; ?>

</tbody>

</table>

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