<?php
require_once 'config/koneksi.php';
require_once 'config/helper.php';

syncTravelStatus($conn);

echo "OK";