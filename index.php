<?php
include 'koneksi.php';

// Menghapus total template lama dan langsung melempar user ke halaman login buatan kita
if (isset($_SESSION['role'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;
?>
