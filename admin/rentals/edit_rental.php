<?php
include '../../config/koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ================= GET DATA =================
$query = mysqli_query($conn, "SELECT * FROM rentals WHERE id = $id");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: index.php");
    exit();
}

// ================= GET GALLERY =================
$gallery = mysqli_query($conn, "SELECT * FROM rental_images WHERE rental_id = $id");

// ================= HANDLE UPDATE (DIRECT HERE) =================
if (isset($_POST['update'])) {

    $customer_name  = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $customer_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $bus_name       = mysqli_real_escape_string($conn, $_POST['bus_name']);
    $capacity       = (int)$_POST['capacity'];
    $rental_date    = $_POST['rental_date'];
    $description    = mysqli_real_escape_string($conn, $_POST['description']);
    $status         = $_POST['status'];

    // ================= COVER IMAGE =================
    $image = $data['image'];

    if (!empty($_FILES['image']['name'])) {

        $tmp = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        $allowed = ['jpg','jpeg','png','webp'];

        if (in_array($ext, $allowed)) {

            $image = time().'_'.rand(1000,9999).'.'.$ext;

            $folder = "../../assets/bus/";
            move_uploaded_file($tmp, $folder.$image);

            // delete old
            if (!empty($data['image']) && file_exists($folder.$data['image'])) {
                unlink($folder.$data['image']);
            }
        }
    }

    // ================= UPDATE RENTAL =================
    mysqli_query($conn, "
        UPDATE rentals SET
            customer_name   = '$customer_name',
            customer_phone  = '$customer_phone',
            bus_name        = '$bus_name',
            capacity        = '$capacity',
            rental_date     = '$rental_date',
            description     = '$description',
            image           = '$image',
            status          = '$status'
        WHERE id = $id
    ");

    // ================= MULTI IMAGE INSERT =================
    if (!empty($_FILES['images']['name'][0])) {

        $folder = "../../assets/bus/";

        foreach ($_FILES['images']['name'] as $key => $img) {

            if ($_FILES['images']['error'][$key] == 0) {

                $tmp = $_FILES['images']['tmp_name'][$key];
                $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));

                $allowed = ['jpg','jpeg','png','webp'];

                if (in_array($ext, $allowed)) {

                    $newName = time().'_'.rand(1000,9999).'.'.$ext;

                    move_uploaded_file($tmp, $folder.$newName);

                    mysqli_query($conn, "
                        INSERT INTO rental_images (rental_id, image)
                        VALUES ('$id', '$newName')
                    ");
                }
            }
        }
    }

    echo "<script>
        alert('Data berhasil diupdate!');
        window.location.href='index.php';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Rental</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body { font-family: sans-serif; }
.input{
    width:100%;
    padding:12px;
    border:1px solid #e5e7eb;
    border-radius:12px;
    outline:none;
    background:#f8fafc;
}
</style>
</head>

<body class="bg-slate-50">

<div class="max-w-6xl mx-auto p-6">

<!-- HEADER -->
<div class="flex items-center gap-4 mb-6">
    <a href="index.php" class="w-10 h-10 flex items-center justify-center bg-white border rounded-xl">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="text-xl font-bold">Edit Rental</h1>
        <p class="text-sm text-gray-500">Direct Update (tanpa proses file)</p>
    </div>
</div>

<form method="POST" enctype="multipart/form-data"
      class="grid lg:grid-cols-3 gap-6">

<!-- COVER -->
<div class="bg-white p-4 rounded-2xl border">

    <label class="text-xs font-bold text-gray-500">Cover Image</label>

    <img src="../../assets/bus/<?= $data['image'] ?>"
         class="w-full h-44 object-cover rounded-xl mt-2">

    <input type="file" name="image" class="mt-3 w-full">
</div>

<!-- GALLERY -->
<div class="bg-white p-4 rounded-2xl border">

    <div class="flex justify-between mb-3">
        <label class="text-xs font-bold text-gray-500">Gallery</label>

        <button type="button" onclick="addImage()"
                class="bg-yellow-400 px-3 py-1 rounded-lg text-xs font-bold">
            + Tambah
        </button>
    </div>

    <div class="grid grid-cols-3 gap-2 mb-3">
        <?php while($g = mysqli_fetch_assoc($gallery)): ?>
            <img src="../../assets/bus/<?= $g['image'] ?>"
                 class="w-full h-20 object-cover rounded-lg border">
        <?php endwhile; ?>
    </div>

    <div id="box"></div>
</div>

<!-- FORM -->
<div class="lg:col-span-2 bg-white p-6 rounded-2xl border space-y-4">

    <input type="text" name="customer_name" value="<?= $data['customer_name'] ?>" class="input">
    <input type="text" name="customer_phone" value="<?= $data['customer_phone'] ?>" class="input">

    <input type="text" name="bus_name" value="<?= $data['bus_name'] ?>" class="input">
    <input type="number" name="capacity" value="<?= $data['capacity'] ?>" class="input">

    <input type="date" name="rental_date" value="<?= $data['rental_date'] ?>" class="input">

    <textarea name="description" class="input"><?= $data['description'] ?></textarea>

    <select name="status" class="input">
        <option value="pending" <?= $data['status']=='pending'?'selected':'' ?>>Pending</option>
        <option value="confirmed" <?= $data['status']=='confirmed'?'selected':'' ?>>Confirmed</option>
        <option value="completed" <?= $data['status']=='completed'?'selected':'' ?>>Completed</option>
    </select>

    <button name="update" class="w-full bg-yellow-400 py-3 rounded-xl font-bold">
        Simpan
    </button>

</div>

</form>
</div>

<script>
function addImage(){
    const box = document.getElementById('box');

    const div = document.createElement('div');
    div.className = "flex gap-2 mt-2";

    div.innerHTML = `
        <input type="file" name="images[]" class="w-full border p-2 rounded-xl">
        <button type="button" onclick="this.parentElement.remove()"
            class="bg-red-500 text-white px-3 rounded-xl">
            X
        </button>
    `;

    box.appendChild(div);
}
</script>

</body>
</html>