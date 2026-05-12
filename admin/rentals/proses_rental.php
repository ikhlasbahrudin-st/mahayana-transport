<?php
include '../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// ================= DATA =================
$name   = mysqli_real_escape_string($conn, $_POST['customer_name'] ?? '');
$phone  = mysqli_real_escape_string($conn, $_POST['customer_phone'] ?? '');
$bus    = mysqli_real_escape_string($conn, $_POST['bus_name'] ?? '');
$cap    = (int) ($_POST['capacity'] ?? 0);
$date   = $_POST['rental_date'] ?? '';
$desc   = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
$price  = (float) ($_POST['price_per_day'] ?? 0);
$total  = (float) ($_POST['total_price'] ?? 0);
$status = $_POST['status'] ?? 'pending';

// ================= VALIDASI =================
if ($name == '' || $phone == '' || $bus == '' || $date == '') {
    die("Data wajib belum lengkap");
}

// ================= COVER IMAGE =================
$coverImage = '';

if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

    $tmp = $_FILES['image']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ext, $allowed)) {

        $folder = "../../assets/bus/";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $coverImage = time() . "_cover_" . rand(1000, 9999) . "." . $ext;

        move_uploaded_file($tmp, $folder . $coverImage);
    }
}

// ================= INSERT RENTAL =================
mysqli_query($conn, "
INSERT INTO rentals (
    customer_name,
    customer_phone,
    bus_name,
    capacity,
    rental_date,
    description,
    image,
    price_per_day,
    total_price,
    status
) VALUES (
    '$name',
    '$phone',
    '$bus',
    '$cap',
    '$date',
    '$desc',
    '$coverImage',
    '$price',
    '$total',
    '$status'
)
");

$rental_id = mysqli_insert_id($conn);

// ================= MULTI IMAGE GALLERY =================
if (!empty($_FILES['gallery']['name'][0])) {

    $folder = "../../assets/bus/";

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    foreach ($_FILES['gallery']['name'] as $key => $img) {

        if ($_FILES['gallery']['error'][$key] == 0) {

            $tmp = $_FILES['gallery']['tmp_name'][$key];
            $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));

            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed)) {

                $newName = time() . "_gallery_" . rand(1000, 9999) . "." . $ext;

                move_uploaded_file($tmp, $folder . $newName);

                mysqli_query($conn, "
                    INSERT INTO rental_images (rental_id, image)
                    VALUES ('$rental_id', '$newName')
                ");
            }
        }
    }
}

// ================= REDIRECT =================
header("Location: index.php?success=1");
exit;
?>