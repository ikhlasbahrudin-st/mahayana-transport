<?php
include '../../config/koneksi.php';

// ================= VALIDASI ID =================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET['id'];

// ================= AMBIL DATA RENTAL =================
$result = mysqli_query($conn, "SELECT * FROM rentals WHERE id = $id");

if (!$result || mysqli_num_rows($result) == 0) {
    die("❌ Data tidak ditemukan");
}

$data = mysqli_fetch_assoc($result);

// ================= HAPUS COVER IMAGE =================
if (!empty($data['image'])) {
    $coverPath = "../../assets/bus/" . $data['image'];

    if (file_exists($coverPath)) {
        unlink($coverPath);
    }
}

// ================= HAPUS GALLERY IMAGE =================
$gallery = mysqli_query($conn, "SELECT image FROM rental_images WHERE rental_id = $id");

if ($gallery && mysqli_num_rows($gallery) > 0) {

    while ($img = mysqli_fetch_assoc($gallery)) {

        if (!empty($img['image'])) {
            $path = "../../assets/bus/" . $img['image'];

            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}

// ================= HAPUS DATA GALLERY DB =================
mysqli_query($conn, "DELETE FROM rental_images WHERE rental_id = $id");

// ================= HAPUS DATA RENTAL UTAMA =================
$delete = mysqli_query($conn, "DELETE FROM rentals WHERE id = $id");

// ================= RESPONSE =================
if ($delete) {
    echo "<script>
        alert('✅ Data rental & semua gambar berhasil dihapus!');
        window.location.href='index.php';
    </script>";
} else {
    echo "❌ Gagal hapus data: " . mysqli_error($conn);
}
?>