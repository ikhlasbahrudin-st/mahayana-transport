<?php
require_once '../config/koneksi.php';

$asal = mysqli_real_escape_string($conn, $_POST['asal']);
$tujuan = mysqli_real_escape_string($conn, $_POST['tujuan']);

// Cari tanggal paling awal yang tersedia untuk rute tersebut
$sql = "SELECT s.date 
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        WHERE r.departure_city = '$asal' AND r.arrival_city = '$tujuan'
        ORDER BY s.date ASC LIMIT 1";

$result = mysqli_query($conn, $sql);

if ($row = mysqli_fetch_assoc($result)) {
    echo $row['date'];
} else {
    echo date('Y-m-d'); // Default jika tidak ada
}
?>