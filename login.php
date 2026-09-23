<?php
include 'koneksi.php';
// Jika sudah login, cek role-nya dan langsung arahkan
if (isset($_SESSION['role'])) { 
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_verifikasi.php");
    } elseif ($_SESSION['role'] == 'penemu') {
        header("Location: lapor_temuan.php");
    } elseif ($_SESSION['role'] == 'pemilik') {
        header("Location: lapor_hilang.php");
    } else {
        header("Location: index.php"); // Fallback jika role tidak diketahui
    }
    exit; 
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Bypass Admin
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_id'] = 1; 
        $_SESSION['username'] = 'admin'; 
        $_SESSION['role'] = 'admin'; 
        $_SESSION['nama_lengkap'] = 'Administrator Utama';
        header("Location: admin_verifikasi.php"); // LANGSUNG KE ADMIN
        exit;
    }

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id']; 
            $_SESSION['username'] = $row['username']; 
            $_SESSION['role'] = $row['role']; 
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
            
            // CEK ROLE LALU LANGSUNG REDIRECT KE HALAMANNYA
            if ($row['role'] == 'penemu') {
                header("Location: lapor_temuan.php");
            } else if ($row['role'] == 'pemilik') {
                header("Location: lapor_hilang.php");
            } else {
                header("Location: dashboard.php"); // Jika tidak ada, baru lempar ke dashboard
            }
            exit;
        }
    }
    echo "<script>alert('Username atau Password salah!');</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Login - Lost & Found</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>body { background: linear-gradient(135deg, #e0e7ff 0%, #fae8ff 50%, #f3e8ff 100%); }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white/85 backdrop-blur-md w-full max-w-md rounded-3xl shadow-2xl p-8 text-slate-800 border border-white/50">
        <div class="text-center mb-6">
            <div class="inline-flex bg-indigo-600 text-white p-3.5 rounded-2xl mb-3 shadow-md shadow-indigo-600/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <h1 class="text-2xl font-black text-slate-900">BARANG HILANG & DITEMUKAN</h1>
            <p class="text-xs text-slate-500 mt-1">Silakan masuk untuk mengakses sistem informasi barang</p>
        </div>
        <form action="" method="POST" class="space-y-4" autocomplete="off">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Nama Pengguna (Username)</label>
                <input type="text" name="username" required placeholder="Contoh: ica" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Kata Sandi</label>
                <input type="password" name="password" required placeholder="Contoh: 12345" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <button type="submit" name="login" class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-bold py-3 rounded-xl text-sm shadow-lg shadow-indigo-600/20 cursor-pointer">Masuk ke Sistem</button>
        </form>
        <div class="text-center pt-4 border-t border-slate-200/80 mt-4">
            <p class="text-xs text-slate-500">Belum memiliki akun? <a href="register.php" class="text-indigo-600 font-bold hover:underline">Daftar Akun Baru</a></p>
        </div>
    </div>
</body>
</html>
