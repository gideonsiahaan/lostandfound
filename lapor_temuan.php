<?php
include 'koneksi.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penemu') { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];

// --- PROSES HAPUS LAPORAN OLEH USER ---
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM barang WHERE id='$id' AND user_id='$user_id'");
    header("Location: lapor_temuan.php"); exit;
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

    mysqli_query($conn, "INSERT INTO barang (user_id, jenis_laporan, nama_barang, lokasi, tanggal, ciri_ciri, foto, status) VALUES ('$user_id', 'temuan', '$nama_barang', '$lokasi', '$tanggal', '$ciri_ciri', '$target_file', 'menunggu')");
    echo "<script>alert('Laporan temuan berhasil terkirim!'); window.location='lapor_temuan.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Panel Penemu - Lost & Found</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body { background-color: #eaf7ed; } /* Background hijau lembut sesuai screenshot */
    </style>
</head>
<body class="text-slate-800 min-h-screen p-4 md:p-8 font-sans">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <header class="bg-white p-6 rounded-2xl flex justify-between items-center shadow-sm border border-emerald-100">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-600">PANEL KONTROL SISTEM</span>
                <h1 class="text-2xl font-bold text-slate-800 mt-1">Sistem Informasi Barang</h1>
                <p class="text-sm text-slate-500 mt-1">Pengguna: <span class="font-medium text-slate-700"><?= $_SESSION['username']; ?></span> | Hak Akses: <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded uppercase">PENEMU BARANG</span></p>
            </div>
            <a href="logout.php" class="bg-[#ff0033] hover:bg-red-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm shadow-md transition">Keluar Aplikasi (Logout)</a>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <div class="lg:col-span-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-4">
                <h2 class="text-lg font-bold text-emerald-600 border-b border-slate-100 pb-2">Laporkan Temuan</h2>
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Nama Barang Temuan</label>
                        <input type="text" name="nama_barang" placeholder="Contoh: Kunci Motor, Tas" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Lokasi Ditemukan</label>
                        <input type="text" name="lokasi" placeholder="Contoh: Kantin, Parkiran Belakang" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal Ditemukan</label>
                        <input type="date" name="tanggal" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Ciri-ciri Khusus Barang</label>
                        <textarea name="ciri_ciri" placeholder="Sebutkan warna, merk, gantungan, dll..." required rows="3" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-400"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Foto Barang Temuan</label>
                        <input type="file" name="foto" required class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                    <button type="submit" name="laporkan" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl transition shadow-md">Kirim Laporan Temuan</button>
                </form>
            </div>

            <div class="lg:col-span-8 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h2 class="text-md font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">Riwayat Laporan Temuan Anda</h2>
                
                <?php
                $cek_setuju = mysqli_query($conn, "SELECT id FROM barang WHERE user_id='$user_id' AND status='tersedia' AND jenis_laporan='temuan'");
                if(mysqli_num_rows($cek_setuju) > 0):
                ?>
                <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl mb-4 flex items-start gap-3 whitespace-normal">
                    <span class="text-xl">📢</span>
                    <div class="text-xs text-amber-950 leading-relaxed">
                        <p class="font-bold text-amber-800 text-sm">Instruksi Penyerahan Barang</p>
                        <p class="mt-1">Terima kasih atas kejujuran Anda! Laporan temuan Anda telah disetujui oleh admin. Mohon kesediaannya untuk segera pergi ke <b>Kantor Admin</b> guna menyimpan/menyerahkan barang fisik tersebut agar aman.</p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr class="text-slate-500 border-b border-slate-200 bg-slate-50">
                                <th class="px-4 py-2 font-medium w-16">Foto</th>
                                <th class="px-4 py-2 font-medium">Nama & Lokasi</th>
                                <th class="px-4 py-2 font-medium">Ciri Fisik</th>
                                <th class="px-4 py-2 font-medium">Status Validasi</th>
                                <th class="px-4 py-2 font-medium text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $q = mysqli_query($conn, "SELECT * FROM barang WHERE user_id='$user_id' AND jenis_laporan='temuan' ORDER BY id DESC");
                            if(mysqli_num_rows($q) > 0):
                                while($r = mysqli_fetch_assoc($q)):
                            ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <img src="<?= $r['foto']; ?>" class="w-10 h-10 object-cover rounded-lg border border-slate-200">
                                </td>
                                <td class="px-4 py-3"><span class="font-bold text-slate-800"><?= $r['nama_barang']; ?></span><br><span class="text-emerald-600 text-xs font-medium">📍 <?= $r['lokasi']; ?></span></td>
                                <td class="px-4 py-3 text-slate-500 text-xs truncate max-w-[150px]" title="<?= $r['ciri_ciri']; ?>">"<?= $r['ciri_ciri']; ?>"</td>
                                
                                <td class="px-4 py-3 align-top">
                                    <?php 
                                    // DISINI SUDAH FIXED FIX: Mengecek status 'diambil' maupun 'selesai'
                                    if($r['status'] == 'diambil' || $r['status'] == 'selesai') {
                                        ?>
                                        <div class="flex flex-col gap-1.5 whitespace-normal max-w-[220px]">
                                            <span class="text-indigo-700 bg-indigo-100 px-2.5 py-1 rounded-md font-bold text-[10px] uppercase tracking-wider w-fit">Kasus Selesai</span>
                                            <div class="text-[11px] text-slate-500 leading-tight mt-1">
                                                🎉 Barang telah dikembalikan ke pemiliknya. <b class="text-indigo-600">Terima kasih atas kejujuran Anda!</b>
                                            </div>
                                        </div>
                                        <?php
                                    } elseif($r['status'] == 'tersedia') {
                                        // Tampilan rapi saat disetujui admin
                                        ?>
                                        <div class="flex flex-col gap-1.5 whitespace-normal max-w-[220px]">
                                            <span class="text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-md font-bold text-[10px] uppercase tracking-wider w-fit">Disetujui Admin</span>
                                            <div class="text-[11px] bg-emerald-50 text-emerald-800 border border-emerald-200/60 p-2 rounded-xl leading-relaxed">
                                                ✨ <b>Langkah Selanjutnya:</b> Silakan pergi ke <b>Kantor Admin</b> untuk menitipkan/menyimpan barang fisik ini agar aman.
                                            </div>
                                        </div>
                                        <?php
                                    } else {
                                        echo '<span class="text-amber-700 bg-amber-100 px-2.5 py-1 rounded-md font-bold text-[10px] uppercase tracking-wider">Menunggu Validasi</span>';
                                    }
                                    ?>
                                </td>
                                
                                <td class="px-4 py-3 text-center">
                                    <a href="lapor_temuan.php?action=hapus&id=<?= $r['id']; ?>" onclick="return confirm('Yakin ingin menghapus laporan ini?')" class="text-red-500 hover:text-white hover:bg-red-500 border border-red-200 px-2.5 py-1 rounded-lg font-bold text-xs transition">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="5" class="py-6 text-center text-slate-400">Belum ada riwayat laporan temuan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
