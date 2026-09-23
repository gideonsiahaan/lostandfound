<?php
include 'koneksi.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pemilik') { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];

// --- PROSES HAPUS LAPORAN OLEH USER ---
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Keamanan: User hanya bisa menghapus laporannya sendiri
    mysqli_query($conn, "DELETE FROM barang WHERE id='$id' AND user_id='$user_id'");
    header("Location: lapor_hilang.php"); exit;
}

// --- PROSES INPUT LAPORAN ---
if (isset($_POST['laporkan'])) {
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $tanggal = $_POST['tanggal'];
    $ciri_ciri = mysqli_real_escape_string($conn, $_POST['ciri_ciri']);

    $foto_nama = $_FILES['foto']['name']; $target_file = "";
    if (!empty($foto_nama)) {
        $target_file = "uploads/" . time() . '_' . basename($foto_nama);
        move_uploaded_file($_FILES['foto']['tmp_name'], $target_file);
    }

    mysqli_query($conn, "INSERT INTO barang (user_id, jenis_laporan, nama_barang, lokasi, tanggal, ciri_ciri, foto, status) VALUES ('$user_id', 'hilang', '$nama_barang', '$lokasi', '$tanggal', '$ciri_ciri', '$target_file', 'menunggu')");
    echo "<script>alert('Aduan kehilangan berhasil dikirim!'); window.location='lapor_hilang.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Panel Pemilik - Lost & Found</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body { background-color: #fff0f2; } /* Background pink lembut sesuai screenshot */
    </style>
</head>
<body class="text-slate-800 min-h-screen p-4 md:p-8 font-sans">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- HEADER -->
        <header class="bg-white p-6 rounded-2xl flex justify-between items-center shadow-sm border border-rose-100">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-rose-500">PANEL KONTROL SISTEM</span>
                <h1 class="text-2xl font-bold text-slate-800 mt-1">Sistem Informasi Barang</h1>
                <p class="text-sm text-slate-500 mt-1">Pengguna: <span class="font-medium text-slate-700"><?= $_SESSION['username']; ?></span> | Hak Akses: <span class="text-[10px] font-bold text-rose-600 bg-rose-100 px-2 py-0.5 rounded uppercase">PEMILIK BARANG</span></p>
            </div>
            <a href="logout.php" class="bg-[#ff0033] hover:bg-red-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm shadow-md transition">Keluar Aplikasi (Logout)</a>
        </header>

        <!-- MAIN LAYOUT (DUAL COLUMN) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- KOLOM KIRI: FORM -->
            <div class="lg:col-span-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-4">
                <h2 class="text-lg font-bold text-rose-600 border-b border-slate-100 pb-2">Laporkan Kehilangan</h2>
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Nama Barang Hilang</label>
                        <input type="text" name="nama_barang" placeholder="Contoh: Dompet, HP" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-rose-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Perkiraan Lokasi Hilang</label>
                        <input type="text" name="lokasi" placeholder="Contoh: Ruang Kelas B" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-rose-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal Kejadian</label>
                        <input type="date" name="tanggal" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-rose-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Ciri-ciri Khusus Kepemilikan</label>
                        <textarea name="ciri_ciri" placeholder="Sebutkan stiker, ciri fisik khusus..." required rows="3" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-rose-400"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Foto Lama Barang (Jika Ada)</label>
                        <input type="file" name="foto" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100">
                    </div>
                    <button type="submit" name="laporkan" class="w-full bg-[#ff0055] hover:bg-rose-700 text-white font-bold py-3 rounded-xl transition shadow-md">Kirim Aduan Kehilangan</button>
                </form>
            </div>

            <!-- KOLOM KANAN: LIST DATA -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- 1. ETALASE BARANG TEMUAN AKTIF -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <h2 class="text-md font-bold text-slate-800 mb-4">Daftar Barang Temuan (Siap Dicocokkan)</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <?php 
                        // HANYA MENAMPILKAN YANG STATUSNYA 'tersedia'. JIKA SUDAH 'selesai' OLEH ADMIN, AKAN OTOMATIS HILANG DARI SINI
                        $q = mysqli_query($conn, "SELECT * FROM barang WHERE jenis_laporan='temuan' AND status='tersedia' ORDER BY id DESC");
                        if(mysqli_num_rows($q) > 0):
                            while($f = mysqli_fetch_assoc($q)):
                        ?>
                        <div class="p-4 rounded-2xl border border-slate-100 flex gap-4 bg-white shadow-xs hover:shadow-sm transition">
                            <img src="<?= !empty($f['foto']) ? $f['foto'] : 'https://placehold.co/100'; ?>" class="w-16 h-16 object-cover rounded-xl border border-slate-200">
                            <div class="flex flex-col justify-between min-w-0">
                                <div>
                                    <h4 class="font-bold text-sm text-slate-900 truncate"><?= $f['nama_barang']; ?></h4>
                                    <p class="text-[11px] text-rose-500 font-medium mt-0.5">📍 <?= $f['lokasi']; ?></p>
                                </div>
                                <button onclick="alert('Bawa bukti kepemilikan Anda ke Kantor Admin untuk klaim barang ini!')" class="bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold px-3 py-1.5 rounded-lg w-fit mt-2 transition">Ajukan Klaim</button>
                            </div>
                        </div>
                        <?php endwhile; else: ?>
                        <div class="col-span-2 text-center text-slate-400 text-sm py-8">Belum ada barang temuan yang dipublikasikan.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. RIWAYAT ADUAN HILANG USER -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <h2 class="text-md font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">Daftar Barang Hilang yang Anda Laporkan</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead>
                                <tr class="text-slate-500 border-b border-slate-200 bg-slate-50">
                                    <th class="px-4 py-2 font-medium">Barang & Lokasi</th>
                                    <th class="px-4 py-2 font-medium">Status Laporan</th>
                                    <th class="px-4 py-2 font-medium text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $q2 = mysqli_query($conn, "SELECT * FROM barang WHERE user_id='$user_id' AND jenis_laporan='hilang' ORDER BY id DESC");
                                if(mysqli_num_rows($q2) > 0):
                                    while($r = mysqli_fetch_assoc($q2)):
                                ?>
                                <tr class="border-b border-slate-100 hover:bg-slate-50">
                                    <td class="px-4 py-3"><span class="font-bold text-slate-800"><?= $r['nama_barang']; ?></span><br><span class="text-slate-400 text-xs">📍 <?= $r['lokasi']; ?></span></td>
                                    <td class="px-4 py-3">
                                        <?php if($r['status'] == 'selesai'): ?>
                                            <span class="text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-md font-bold text-[10px] uppercase tracking-wider">Kasus Ditutup (Selesai)</span>
                                        <?php else: ?>
                                            <span class="text-rose-700 bg-rose-100 px-2.5 py-1 rounded-md font-bold text-[10px] uppercase tracking-wider">Aktif Mencari</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="lapor_hilang.php?action=hapus&id=<?= $r['id']; ?>" onclick="return confirm('Yakin ingin menghapus laporan ini?')" class="text-red-500 hover:text-white hover:bg-red-500 border border-red-200 px-3 py-1 rounded-lg font-bold text-xs transition">Hapus</a>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="3" class="py-6 text-center text-slate-400">Anda belum membuat laporan kehilangan.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
