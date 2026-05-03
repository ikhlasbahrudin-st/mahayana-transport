<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../auth_admin/login.php");
    exit;
}