<?php
include 'koneksi.php';
if (!isset($_SESSION['role'])) { header("Location: login.php"); exit; }

// Membaca peran pengguna dan langsung melempar ke halamannya masing-masing secara rahasia
if ($_SESSION['role'] == 'admin') {
    header("Location: admin_verifikasi.php"); exit;
} elseif ($_SESSION['role'] == 'penemu') {
    header("Location: lapor_temuan.php"); exit;
} elseif ($_SESSION['role'] == 'pemilik') {
    header("Location: lapor_hilang.php"); exit;
}
?>
