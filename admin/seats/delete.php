<?php
include '../../config/koneksi.php';

$id = (int)$_GET['id'];

$seat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM seats WHERE id=$id"));
$bus_id = $seat['bus_id'];

mysqli_query($conn, "DELETE FROM seats WHERE id=$id");

header("Location: index.php?bus_id=$bus_id");