<?php
include 'koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit; }

// --- BAGIAN LOGIKA AKSI ADMIN ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']); $act = $_GET['action'];
    
    // Jika tombol "Setujui" diklik
    if ($act == 'setuju') { 
        mysqli_query($conn, "UPDATE barang SET status='tersedia' WHERE id='$id'"); 
    } 
    // Jika tombol "Tandai Selesai" diklik (Berlaku untuk Hilang & Temuan)
    elseif ($act == 'selesai') {
        mysqli_query($conn, "UPDATE barang SET status='selesai', tanggal_kembali=NOW() WHERE id='$id'");
    }
    // Jika tombol "Hapus" diklik pada riwayat
    elseif ($act == 'hapus') {
        mysqli_query($conn, "DELETE FROM barang WHERE id='$id'");
    }
    
    header("Location: admin_verifikasi.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Panel Kontrol - Lost & Found</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body { background-color: #f8fafc; }
        dialog::backdrop { background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); }
    </style>
</head>
<body class="text-slate-800 min-h-screen p-4 md:p-8 font-sans">
    
    <dialog id="modalDetail" class="m-auto rounded-2xl shadow-2xl border-0 p-0 w-11/12 max-w-md bg-white overflow-hidden">
        <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center">
            <h3 class="font-bold text-white text-lg">Detail Lengkap Laporan</h3>
            <button onclick="document.getElementById('modalDetail').close()" class="text-indigo-200 hover:text-white font-bold text-xl">&times;</button>
        </div>
        <div class="p-6 space-y-4 text-sm text-slate-700">
            <div><span class="block text-xs font-bold text-slate-400 uppercase">Tanggal Kejadian</span><p id="mdl_tanggal" class="font-medium">-</p></div>
            <div><span class="block text-xs font-bold text-slate-400 uppercase">Lokasi</span><p id="mdl_lokasi" class="font-medium">-</p></div>
            <div><span class="block text-xs font-bold text-slate-400 uppercase">Ciri-Ciri Spesifik</span><p id="mdl_ciri" class="bg-slate-50 p-3 rounded-lg border border-slate-100 mt-1 whitespace-pre-wrap">-</p></div>
        </div>
        <div class="bg-slate-50 px-6 py-4 flex justify-end border-t border-slate-100">
            <button onclick="document.getElementById('modalDetail').close()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg font-bold text-sm transition">Tutup Detail</button>
        </div>
    </dialog>

    <div class="max-w-6xl mx-auto space-y-6">
        
        <header class="bg-white p-6 rounded-2xl flex justify-between items-center shadow-sm border border-slate-200">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-600">PANEL KONTROL SISTEM</span>
                <h1 class="text-2xl font-bold text-slate-800 mt-1">Sistem Informasi Barang</h1>
                <p class="text-sm text-slate-500 mt-1">Pengguna: <span class="font-medium text-slate-700">Administrator Utama</span></p>
            </div>
            <a href="logout.php" class="bg-[#ff0033] hover:bg-red-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm shadow-md transition">Keluar (Logout)</a>
        </header>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 space-y-8">
            <h2 class="text-lg font-bold text-slate-800">Panel Validasi Administrator</h2>

            <div class="space-y-4">
                <h3 class="text-[12px] font-bold text-emerald-700 uppercase bg-emerald-50 px-3 py-2 rounded-lg w-fit tracking-wide border border-emerald-100">1. LAPORAN TEMUAN MASUK (Butuh Persetujuan)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr class="text-slate-500 border-b border-slate-200 bg-slate-50">
                                <th class="px-4 py-3 font-semibold rounded-tl-lg w-20">Foto</th>
                                <th class="px-4 py-3 font-semibold">Nama Barang</th>
                                <th class="px-4 py-3 font-semibold">Pelapor (Penemu)</th>
                                <th class="px-4 py-3 font-semibold rounded-tr-lg w-64 text-center">Tindakan Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $q = mysqli_query($conn, "SELECT b.*, u.nama_lengkap FROM barang b JOIN users u ON b.user_id=u.id WHERE b.jenis_laporan='temuan' AND b.status='menunggu'");
                            if(mysqli_num_rows($q) > 0):
                                while($adm = mysqli_fetch_assoc($q)):
                            ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <a href="<?= $adm['foto']; ?>" target="_blank"><img src="<?= $adm['foto']; ?>" class="w-10 h-10 object-cover rounded-lg border border-slate-200 hover:opacity-75"></a>
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-700"><?= $adm['nama_barang']; ?></td>
                                <td class="px-4 py-3 text-slate-600"><?= $adm['nama_lengkap']; ?></td>
                                <td class="px-4 py-3 flex gap-2 justify-center">
                                    <button type="button" onclick="bukaDetail('<?= date('d M Y', strtotime($adm['tanggal'])); ?>', '<?= htmlspecialchars(str_replace(array("\r", "\n"), ' ', $adm['lokasi']), ENT_QUOTES); ?>', '<?= htmlspecialchars(str_replace(array("\r", "\n"), ' ', $adm['ciri_ciri']), ENT_QUOTES); ?>')" class="bg-slate-100 hover:bg-slate-200 text-slate-600 border border-slate-200 px-3 py-1.5 rounded-lg font-bold text-xs transition">🔍 Detail</button>
                                    <a href="admin_verifikasi.php?action=setuju&id=<?= $adm['id']; ?>" class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg font-bold text-xs shadow-sm transition">✅ Setujui Publikasi</a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="4" class="py-8 text-center text-slate-400 text-sm">Tidak ada laporan temuan baru.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h3 class="text-[12px] font-bold text-rose-700 uppercase bg-rose-50 px-3 py-2 rounded-lg w-fit tracking-wide border border-rose-100">2. LAPORAN KEHILANGAN AKTIF</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr class="text-slate-500 border-b border-slate-200 bg-slate-50">
                                <th class="px-4 py-3 font-semibold rounded-tl-lg w-20">Foto</th>
                                <th class="px-4 py-3 font-semibold">Nama Barang</th>
                                <th class="px-4 py-3 font-semibold">Korban (Pemilik)</th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                                <th class="px-4 py-3 font-semibold rounded-tr-lg w-64 text-center">Tindakan Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $q2 = mysqli_query($conn, "SELECT b.*, u.nama_lengkap FROM barang b JOIN users u ON b.user_id=u.id WHERE b.jenis_laporan='hilang' AND b.status != 'selesai'");
                            if(mysqli_num_rows($q2) > 0):
                                while($los = mysqli_fetch_assoc($q2)):
                            ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <?php if(!empty($los['foto'])): ?>
                                        <a href="<?= $los['foto']; ?>" target="_blank"><img src="<?= $los['foto']; ?>" class="w-10 h-10 object-cover rounded-lg border border-slate-200 hover:opacity-75"></a>
                                    <?php else: ?>
                                        <div class="w-10 h-10 bg-slate-100 text-slate-400 flex items-center justify-center text-[9px] rounded-lg border border-slate-200">No Foto</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-700"><?= $los['nama_barang']; ?></td>
                                <td class="px-4 py-3 text-slate-600"><?= $los['nama_lengkap']; ?></td>
                                <td class="px-4 py-3"><span class="text-rose-600 bg-rose-50 px-2 py-1 border border-rose-100 rounded text-xs font-bold">Aktif Mencari</span></td>
                                <td class="px-4 py-3 flex gap-2 justify-center items-center h-full">
                                    <button type="button" onclick="bukaDetail('<?= date('d M Y', strtotime($los['tanggal'])); ?>', '<?= htmlspecialchars(str_replace(array("\r", "\n"), ' ', $los['lokasi']), ENT_QUOTES); ?>', '<?= htmlspecialchars(str_replace(array("\r", "\n"), ' ', $los['ciri_ciri']), ENT_QUOTES); ?>')" class="bg-slate-100 hover:bg-slate-200 text-slate-600 border border-slate-200 px-3 py-1.5 rounded-lg font-bold text-xs transition mt-1.5">🔍 Detail</button>
                                    <a href="admin_verifikasi.php?action=selesai&id=<?= $los['id']; ?>" onclick="return confirm('Apakah Anda yakin barang ini sudah diambil dan dikembalikan kepada pemilik aslinya?')" class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1.5 rounded-lg font-bold text-xs shadow-sm transition mt-1.5">🏁 Tandai Selesai</a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="5" class="py-8 text-center text-slate-400 text-sm">Belum ada aduan laporan kehilangan yang aktif.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h3 class="text-[12px] font-bold text-indigo-700 uppercase bg-indigo-50 px-3 py-2 rounded-lg w-fit tracking-wide border border-indigo-100">3. LAPORAN TEMUAN AKTIF</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr class="text-slate-500 border-b border-slate-200 bg-slate-50">
                                <th class="px-4 py-3 font-semibold rounded-tl-lg w-20">Foto</th>
                                <th class="px-4 py-3 font-semibold">Nama Barang</th>
                                <th class="px-4 py-3 font-semibold">Penemu</th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                                <th class="px-4 py-3 font-semibold rounded-tr-lg w-64 text-center">Tindakan Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $q4 = mysqli_query($conn, "SELECT b.*, u.nama_lengkap FROM barang b JOIN users u ON b.user_id=u.id WHERE b.jenis_laporan='temuan' AND b.status = 'tersedia' ORDER BY b.id DESC");
                            if(mysqli_num_rows($q4) > 0):
                                while($tmn = mysqli_fetch_assoc($q4)):
                            ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <?php if(!empty($tmn['foto'])): ?>
                                        <a href="<?= $tmn['foto']; ?>" target="_blank"><img src="<?= $tmn['foto']; ?>" class="w-10 h-10 object-cover rounded-lg border border-slate-200 hover:opacity-75"></a>
                                    <?php else: ?>
                                        <div class="w-10 h-10 bg-slate-100 text-slate-400 flex items-center justify-center text-[9px] rounded-lg border border-slate-200">No Foto</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-700"><?= $tmn['nama_barang']; ?></td>
                                <td class="px-4 py-3 text-slate-600"><?= $tmn['nama_lengkap']; ?></td>
                                <td class="px-4 py-3"><span class="text-emerald-600 bg-emerald-50 px-2 py-1 border border-emerald-100 rounded text-xs font-bold">Tersedia</span></td>
                                <td class="px-4 py-3 flex gap-2 justify-center items-center h-full">
                                    <button type="button" onclick="bukaDetail('<?= date('d M Y', strtotime($tmn['tanggal'])); ?>', '<?= htmlspecialchars(str_replace(array("\r", "\n"), ' ', $tmn['lokasi']), ENT_QUOTES); ?>', '<?= htmlspecialchars(str_replace(array("\r", "\n"), ' ', $tmn['ciri_ciri']), ENT_QUOTES); ?>')" class="bg-slate-100 hover:bg-slate-200 text-slate-600 border border-slate-200 px-3 py-1.5 rounded-lg font-bold text-xs transition mt-1.5">🔍 Detail</button>
                                    <a href="admin_verifikasi.php?action=selesai&id=<?= $tmn['id']; ?>" onclick="return confirm('Apakah Anda yakin barang temuan ini sudah diklaim/diserahkan ke pemilik aslinya?')" class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1.5 rounded-lg font-bold text-xs shadow-sm transition mt-1.5">🏁 Tandai Selesai</a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="5" class="py-8 text-center text-slate-400 text-sm">Belum ada barang temuan aktif di etalase.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h3 class="text-[12px] font-bold text-slate-700 uppercase bg-slate-100 px-3 py-2 rounded-lg w-fit tracking-wide border border-slate-200">4. RIWAYAT PENGEMBALIAN BARANG (Kasus Selesai)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr class="text-slate-500 border-b border-slate-200 bg-slate-50">
                                <th class="px-4 py-3 font-semibold rounded-tl-lg w-20">Foto</th>
                                <th class="px-4 py-3 font-semibold">Nama Barang</th>
                                <th class="px-4 py-3 font-semibold">Pemilik/Pelapor</th>
                                <th class="px-4 py-3 font-semibold">Waktu Pengambilan</th>
                                <th class="px-4 py-3 font-semibold rounded-tr-lg w-24 text-center">Aksi Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $q3 = mysqli_query($conn, "SELECT b.*, u.nama_lengkap FROM barang b JOIN users u ON b.user_id=u.id WHERE b.status = 'selesai' ORDER BY b.tanggal_kembali DESC");
                            if(mysqli_num_rows($q3) > 0):
                                while($dn = mysqli_fetch_assoc($q3)):
                            ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <?php if(!empty($dn['foto'])): ?>
                                        <a href="<?= $dn['foto']; ?>" target="_blank"><img src="<?= $dn['foto']; ?>" class="w-10 h-10 object-cover rounded-lg border border-slate-200 grayscale opacity-75"></a>
                                    <?php else: ?>
                                        <div class="w-10 h-10 bg-slate-100 text-slate-400 flex items-center justify-center text-[9px] rounded-lg border border-slate-200">No Foto</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-500"><?= $dn['nama_barang']; ?></td>
                                <td class="px-4 py-3 text-slate-500"><?= $dn['nama_lengkap']; ?></td>
                                <td class="px-4 py-3 text-slate-500"><?= !empty($dn['tanggal_kembali']) ? date('d M Y, H:i', strtotime($dn['tanggal_kembali'])) : '-'; ?></td>
                                <td class="px-4 py-3 text-center">
                                    <a href="admin_verifikasi.php?action=hapus&id=<?= $dn['id']; ?>" onclick="return confirm('Yakin ingin menghapus data riwayat ini permanen?')" class="text-red-500 hover:text-white bg-red-50 hover:bg-red-500 border border-red-200 hover:border-red-500 px-3 py-1.5 rounded-lg font-bold text-xs transition">🗑️ Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="5" class="py-8 text-center text-slate-400 text-sm">Belum ada riwayat pengembalian barang.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        function bukaDetail(tanggal, lokasi, ciri) {
            document.getElementById('mdl_tanggal').innerText = tanggal;
            document.getElementById('mdl_lokasi').innerText = lokasi;
            document.getElementById('mdl_ciri').innerText = ciri;
            document.getElementById('modalDetail').showModal();
        }
    </script>
</body>
</html>
