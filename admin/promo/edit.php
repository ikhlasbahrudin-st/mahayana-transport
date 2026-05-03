<?php
session_start();
require_once '../../config/koneksi.php';

date_default_timezone_set('Asia/Jakarta');

// ===================== AMBIL DATA =====================
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "<script>alert('ID tidak valid!'); window.location='index.php';</script>";
    exit;
}

$query = mysqli_query($conn, "SELECT * FROM promos WHERE id = $id LIMIT 1");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// ===================== PROSES UPDATE =====================
if (isset($_POST['submit'])) {

    $title      = mysqli_real_escape_string($conn, $_POST['title']);
    $type       = mysqli_real_escape_string($conn, $_POST['type']);
    $tipe_promo = mysqli_real_escape_string($conn, $_POST['tipe_promo']);
    $points     = (int)$_POST['points'];
    $is_active  = isset($_POST['is_active']) ? 1 : 0;

    $targetDir = "../../uploads/promo/";
    $imageName = $data['image'];

    // ===================== UPLOAD GAMBAR BARU =====================
    if (!empty($_FILES['image']['name'])) {

        $image = $_FILES['image']['name'];
        $tmp   = $_FILES['image']['tmp_name'];
        $ext   = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            echo "<script>alert('Format gambar tidak valid!');</script>";
            exit;
        }

        $newName = "promo_" . time() . "." . $ext;
        $uploadPath = $targetDir . $newName;

        if (move_uploaded_file($tmp, $uploadPath)) {

            // hapus gambar lama jika ada
            if (!empty($imageName) && file_exists($targetDir . $imageName)) {
                unlink($targetDir . $imageName);
            }

            $imageName = $newName;
        }
    }

    // ===================== UPDATE DATABASE (FIX ERROR DETECTION) =====================
    $update = mysqli_query($conn, "
        UPDATE promos SET 
            title = '$title',
            type = '$type',
            tipe_promo = '$tipe_promo',
            points = '$points',
            image = '$imageName',
            is_active = '$is_active'
        WHERE id = $id
        LIMIT 1
    ");

    // 🔥 DEBUG ERROR SQL (INI PENTING)
    if (!$update) {
        die("SQL ERROR: " . mysqli_error($conn));
    }

    echo "<script>
        alert('Promo berhasil diupdate!');
        window.location='index.php';
    </script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Promo</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50">

<div class="max-w-3xl mx-auto p-6">

    <h1 class="text-2xl font-bold mb-6">Edit Promo</h1>

    <form method="POST" enctype="multipart/form-data" class="space-y-5 bg-white p-6 rounded-xl shadow">

        <!-- TITLE -->
        <div>
            <label class="font-semibold">Judul</label>
            <input type="text" name="title" value="<?= htmlspecialchars($data['title']) ?>" required
                   class="w-full border p-3 rounded-lg mt-1">
        </div>

        <!-- TYPE -->
        <div>
            <label class="font-semibold">Type</label>
            <input type="text" name="type" value="<?= htmlspecialchars($data['type']) ?>" required
                   class="w-full border p-3 rounded-lg mt-1">
        </div>

        <!-- TIPE PROMO -->
        <div>
            <label class="font-semibold">Tipe Promo</label>
            <select name="tipe_promo" required class="w-full border p-3 rounded-lg mt-1">

                <option value="Shuttle" <?= $data['tipe_promo']=='Shuttle'?'selected':'' ?>>Shuttle</option>
                <option value="Wisata" <?= $data['tipe_promo']=='Wisata'?'selected':'' ?>>Wisata</option>
                <option value="Sewa Armada" <?= $data['tipe_promo']=='Sewa Armada'?'selected':'' ?>>Sewa Armada</option>

            </select>
        </div>

        <!-- POINTS -->
        <div>
            <label class="font-semibold">Points (%)</label>
            <input type="number" name="points" value="<?= $data['points'] ?>" required
                   class="w-full border p-3 rounded-lg mt-1">
        </div>

        <!-- GAMBAR -->
        <div>
            <label class="font-semibold">Gambar Saat Ini</label><br>
            <img src="../../uploads/promo/<?= $data['image'] ?>" class="h-20 rounded mt-2 mb-2">

            <input type="file" name="image"
                   class="w-full border p-3 rounded-lg mt-1">
        </div>

        <!-- ACTIVE -->
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" <?= $data['is_active'] ? 'checked' : '' ?>>
            <label>Aktif</label>
        </div>

        <!-- SUBMIT -->
        <button type="submit" name="submit"
                class="w-full bg-black text-white py-3 rounded-xl font-bold">
            Update Promo
        </button>

    </form>

</div>

</body>
</html>