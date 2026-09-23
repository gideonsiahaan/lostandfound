<?php
include 'koneksi.php';
if (isset($_SESSION['role'])) { header("Location: dashboard.php"); exit; }

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah digunakan!');</script>";
    } else {
        $query = "INSERT INTO users (username, password, role, nama_lengkap, no_hp) VALUES ('$username', '$password', '$role', '$nama', '$no_hp')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Pendaftaran Berhasil!'); window.location='login.php';</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Daftar Akun - Lost & Found</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>body { background: linear-gradient(135deg, #e0e7ff 0%, #fae8ff 50%, #f3e8ff 100%); }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white/85 backdrop-blur-md w-full max-w-md rounded-3xl shadow-2xl p-8 text-slate-800 border border-white/50">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-black text-slate-900">DAFTAR AKUN BARU</h1>
            <p class="text-xs text-slate-500 mt-1">Lengkapi formulir hak akses</p>
        </div>
        <form action="" method="POST" class="space-y-3" autocomplete="off">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1 uppercase">Username</label>
                    <input type="text" name="username" required placeholder="ica" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1 uppercase">Password</label>
                    <input type="password" name="password" required placeholder="12345" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required placeholder="Contoh: Ica Lestari" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase">Nomor WhatsApp</label>
                <input type="text" name="no_hp" required placeholder="Contoh: 0812345678" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase">Pilih Jenis Hubungan Akun</label>
                <select name="role" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="penemu">Saya adalah Penemu (Melaporkan barang temuan)</option>
                    <option value="pemilik">Saya adalah Pemilik (Mencari barang hilang)</option>
                </select>
            </div>
            <button type="submit" name="register" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold py-3 rounded-xl text-sm shadow-lg shadow-emerald-600/20 cursor-pointer mt-2">Kirim & Daftarkan Akun</button>
        </form>
        <div class="text-center pt-4 border-t border-slate-200/80 mt-3">
            <p class="text-xs text-slate-500">Sudah punya akun? <a href="login.php" class="text-indigo-600 font-bold hover:underline">Kembali ke Login</a></p>
        </div>
    </div>
</body>
</html>
