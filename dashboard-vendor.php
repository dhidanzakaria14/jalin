<?php
include 'koneksi.php';
session_start();

// 1. Proteksi Halaman & Role
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] != 'Vendor' && $_SESSION['role'] != 'Freelance')) {
    header("Location: login.php");
    exit();
}

$id_vendor = $_SESSION['id_user'];

// --- LOGIKA HAPUS AKUN (BARU DITAMBAHKAN) ---
if (isset($_GET['hapus_akun'])) {
    // 1. Hapus Foto Profil User
    $queryFotoUser = mysqli_query($conn, "SELECT foto_profil FROM users WHERE id_user = '$id_vendor'");
    $dataUser = mysqli_fetch_assoc($queryFotoUser);
    if ($dataUser['foto_profil'] != 'default.png' && file_exists('uploads/' . $dataUser['foto_profil'])) {
        unlink('uploads/' . $dataUser['foto_profil']);
    }

    // 2. Hapus Foto-foto Layanan milik vendor ini
    $queryFotoLayanan = mysqli_query($conn, "SELECT gambar FROM layanan WHERE id_vendor = '$id_vendor'");
    while ($layanan = mysqli_fetch_assoc($queryFotoLayanan)) {
        if ($layanan['gambar'] != 'default-layanan.png' && file_exists('uploads/' . $layanan['gambar'])) {
            unlink('uploads/' . $layanan['gambar']);
        }
    }

    // 3. Hapus data dari database
    $delete = mysqli_query($conn, "DELETE FROM users WHERE id_user = '$id_vendor'");
    
    if ($delete) {
        session_destroy();
        echo "<script>alert('Akun Anda berhasil dihapus secara permanen.'); window.location='index.php';</script>";
        exit();
    }
}

// --- LOGIKA MASTER KATEGORI ---
// Tambah Kategori
if (isset($_POST['btn_tambah_kategori'])) {
    $nama_kat = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    mysqli_query($conn, "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kat')");
    header("Location: dashboard-vendor.php"); exit();
}

// Edit Kategori
if (isset($_POST['btn_edit_kategori'])) {
    $id_kat = $_POST['id_kategori'];
    $nama_kat = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    mysqli_query($conn, "UPDATE kategori SET nama_kategori='$nama_kat' WHERE id_kategori='$id_kat'");
    header("Location: dashboard-vendor.php"); exit();
}

// Hapus Kategori
if (isset($_GET['hapus_kat'])) {
    $id_hapus_kat = $_GET['hapus_kat'];
    $cek_pakai = mysqli_query($conn, "SELECT id_layanan FROM layanan WHERE id_kategori = '$id_hapus_kat'");
    if (mysqli_num_rows($cek_pakai) > 0) {
        echo "<script>alert('Gagal! Kategori masih digunakan layanan.'); window.location='dashboard-vendor.php';</script>";
    } else {
        mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori = '$id_hapus_kat'");
        header("Location: dashboard-vendor.php");
    }
    exit();
}

// --- LOGIKA LAYANAN ---
// Hapus Layanan
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    $queryFoto = mysqli_query($conn, "SELECT gambar FROM layanan WHERE id_layanan = '$id_hapus' AND id_vendor = '$id_vendor'");
    $dataFoto = mysqli_fetch_assoc($queryFoto);
    if ($dataFoto) {
        if ($dataFoto['gambar'] != 'default-layanan.png' && file_exists('uploads/' . $dataFoto['gambar'])) {
            unlink('uploads/' . $dataFoto['gambar']);
        }
        mysqli_query($conn, "DELETE FROM layanan WHERE id_layanan = '$id_hapus' AND id_vendor = '$id_vendor'");
        header("Location: dashboard-vendor.php"); exit();
    }
}

// Edit Layanan
if (isset($_POST['btn_edit_layanan'])) {
    $id_edit = $_POST['id_layanan'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_layanan']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $id_kat = $_POST['id_kategori'];
    
    $query_upd = "UPDATE layanan SET id_kategori='$id_kat', nama_layanan='$nama', harga='$harga', deskripsi='$deskripsi' WHERE id_layanan='$id_edit' AND id_vendor='$id_vendor'";
    if (!empty($_FILES['foto']['name'])) {
        $foto_baru = "layanan_" . time() . "." . pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $foto_baru);
        $query_upd = "UPDATE layanan SET id_kategori='$id_kat', nama_layanan='$nama', harga='$harga', deskripsi='$deskripsi', gambar='$foto_baru' WHERE id_layanan='$id_edit' AND id_vendor='$id_vendor'";
    }
    mysqli_query($conn, $query_upd);
    header("Location: dashboard-vendor.php"); exit();
}

// Tambah Layanan
if (isset($_POST['btn_tambah_layanan'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_layanan']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $id_kat = $_POST['id_kategori'];
    $foto_nama = "default-layanan.png";
    if (!empty($_FILES['foto']['name'])) {
        $foto_nama = "layanan_" . time() . "." . pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $foto_nama);
    }
    mysqli_query($conn, "INSERT INTO layanan (id_vendor, id_kategori, nama_layanan, harga, deskripsi, gambar) VALUES ('$id_vendor', '$id_kat', '$nama', '$harga', '$deskripsi', '$foto_nama')");
    header("Location: dashboard-vendor.php"); exit();
}

// Data Pendukung
$vendorData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_vendor'"));
$totalLayanan = mysqli_num_rows(mysqli_query($conn, "SELECT id_layanan FROM layanan WHERE id_vendor = '$id_vendor'"));
$queryPesananCount = mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan p JOIN detail_pesanan dp ON p.id_pesanan = dp.id_pesanan JOIN layanan l ON dp.id_layanan = l.id_layanan WHERE l.id_vendor = '$id_vendor'");
$totalPesanan = mysqli_fetch_assoc($queryPesananCount)['total'] ?? 0;
$listKategori = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC"), MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Dashboard | JALIN Partner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fffafb; }
        .sidebar-link-active { background-color: #fff0f3; color: #d14d72; font-weight: bold; border-right: 4px solid #d14d72; }
        .content-section { display: none; }
        .content-section.active { display: block; animation: fadeIn 0.4s ease forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="text-gray-800 text-left">

    <div class="flex min-h-screen">
        <aside class="w-72 bg-white border-r border-pink-50 hidden md:block sticky top-0 h-screen shadow-sm z-50">
            <div class="p-8 text-left">
                <div class="text-3xl font-bold text-[#d14d72] mb-12 cursor-pointer" onclick="window.location.href='index.php'">JALIN<span class="text-[#ffb7c5]">.</span></div>
                <nav class="space-y-4">
                    <div onclick="switchSection('dashboard', this)" class="sidebar-link sidebar-link-active flex items-center gap-4 p-4 text-sm cursor-pointer transition-all"><span>📊</span> Dashboard</div>
                    <div onclick="switchSection('kategori', this)" class="sidebar-link flex items-center gap-4 p-4 text-sm text-gray-500 hover:bg-pink-50 cursor-pointer transition-all"><span>📁</span> Master Kategori</div>
                    <div onclick="switchSection('layanan', this)" class="sidebar-link flex items-center gap-4 p-4 text-sm text-gray-500 hover:bg-pink-50 cursor-pointer transition-all"><span>🛍️</span> Kelola Layanan</div>
                    <div onclick="switchSection('pesanan', this)" class="sidebar-link flex items-center gap-4 p-4 text-sm text-gray-500 hover:bg-pink-50 cursor-pointer transition-all"><span>📩</span> Pesanan Masuk</div>
                    <div class="pt-10">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 ml-4">Pengaturan</p>
                        <a href="edit-profile.php" class="flex items-center gap-4 p-4 text-sm text-gray-500 hover:bg-pink-50 rounded-2xl transition"><span>🏠</span> Edit Profil</a>
                        <a href="logout.php" onclick="return confirm('Keluar?')" class="flex items-center gap-4 p-4 text-sm text-red-400 font-bold rounded-2xl transition"><span>🚪</span> Keluar</a>
                        
                        <a href="?hapus_akun=true" onclick="return confirm('⚠️ PERINGATAN! Akun akan dihapus permanen beserta seluruh layanan. Lanjutkan?')" class="flex items-center gap-4 p-4 text-sm text-red-500 hover:bg-red-50 font-bold rounded-2xl transition mt-10 border border-red-100"><span>🗑️</span> Hapus Akun</a>
                    </div>
                </nav>
            </div>
        </aside>

        <main class="flex-1 p-10 lg:p-16">
            <header class="flex justify-between items-center mb-12 text-left">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 text-left">Halo, <?= explode(' ', $vendorData['nama_lengkap'])[0] ?>! 👋</h1>
                    <p class="text-[10px] text-pink-400 font-bold uppercase tracking-widest mt-1 italic"><?= strtoupper($vendorData['nama_toko'] ?? 'Partner Jalin') ?></p>
                </div>
                <div class="w-12 h-12 bg-pink-100 rounded-full border-2 border-white shadow-md overflow-hidden">
                    <img src="uploads/<?= $vendorData['foto_profil'] ?>" class="w-full h-full object-cover" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($vendorData['nama_lengkap']) ?>'">
                </div>
            </header>

            <section id="section-dashboard" class="content-section active text-left">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                    <div class="bg-white p-8 rounded-[2rem] border border-pink-50 shadow-sm"><p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Layanan</p><h3 class="text-2xl font-bold mt-2 text-[#d14d72]"><?= $totalLayanan ?></h3></div>
                    <div class="bg-white p-8 rounded-[2rem] border border-pink-50 shadow-sm"><p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pesanan</p><h3 class="text-2xl font-bold mt-2"><?= $totalPesanan ?></h3></div>
                    <div class="bg-white p-8 rounded-[2rem] border border-pink-50 shadow-sm"><p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider text-left">Impact Score</p><h3 class="text-2xl font-bold mt-2 italic text-left">850 pts</h3></div>
                </div>
                <div class="bg-white p-10 rounded-[3rem] shadow-xl border border-pink-50 text-left">
                    <h3 class="font-bold text-gray-800 mb-8 flex items-center gap-2 uppercase text-xs tracking-widest text-left">Tren Penjualan</h3>
                    <div class="h-[350px]"><canvas id="vendorChart"></canvas></div>
                </div>
            </section>

            <section id="section-kategori" class="content-section text-left">
                <div class="flex justify-between items-center mb-10 text-left">
                    <h2 class="text-2xl font-bold text-gray-800 italic uppercase text-sm">Master Kategori</h2>
                    <button onclick="openModal('modalTambahKat')" class="bg-[#d14d72] text-white px-8 py-4 rounded-2xl text-[10px] font-bold uppercase shadow-lg hover:bg-[#b03d5d] transition">+ Kategori Baru</button>
                </div>
                <div class="bg-white rounded-[2.5rem] border border-pink-50 overflow-hidden shadow-sm">
                    <table class="w-full text-left">
                        <thead class="bg-pink-50/30 text-[10px] font-bold uppercase text-gray-400 tracking-widest">
                            <tr><th class="p-8">Nama Kategori</th><th class="p-8 text-center">Aksi</th></tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php foreach($listKategori as $kat): ?>
                            <tr class="border-b border-pink-50">
                                <td class="p-8 font-bold"><?= $kat['nama_kategori'] ?></td>
                                <td class="p-8 text-center flex justify-center gap-4">
                                    <button onclick='openEditKatModal(<?= json_encode($kat) ?>)' class="text-blue-500 font-bold uppercase text-[10px] hover:underline">Edit</button>
                                    <a href="?hapus_kat=<?= $kat['id_kategori'] ?>" onclick="return confirm('Hapus kategori?')" class="text-red-400 font-bold uppercase text-[10px] hover:underline">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="section-layanan" class="content-section text-left">
                <div class="flex justify-between items-center mb-10 text-left">
                    <div class="text-left text-left">
                        <h2 class="text-2xl font-bold text-gray-800 italic uppercase text-sm">Katalog Layanan</h2>
                        <p class="text-sm text-gray-400 mt-1">Kelola foto dan harga jasa Anda.</p>
                    </div>
                    <button onclick="openModal('modalTambah')" class="bg-[#d14d72] text-white px-8 py-4 rounded-2xl text-[10px] font-bold uppercase shadow-lg hover:bg-[#b03d5d] transition">+ Tambah Layanan</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left">
                    <?php
                    $layananQuery = mysqli_query($conn, "SELECT l.*, k.nama_kategori FROM layanan l LEFT JOIN kategori k ON l.id_kategori = k.id_kategori WHERE l.id_vendor = '$id_vendor' ORDER BY l.id_layanan DESC");
                    while($l = mysqli_fetch_assoc($layananQuery)):
                    ?>
                    <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-pink-50 flex gap-8 animate-fadeIn text-left">
                        <img src="uploads/<?= $l['gambar'] ?>" class="w-32 h-32 rounded-[2rem] object-cover bg-pink-50 shadow-inner">
                        <div class="flex-1 text-left">
                            <p class="text-[9px] font-bold text-[#ffcad4] uppercase tracking-widest text-left"><?= $l['nama_kategori'] ?? 'General' ?></p>
                            <h4 class="font-bold text-lg text-gray-800 text-left"><?= $l['nama_layanan'] ?></h4>
                            <p class="text-[#d14d72] font-bold mt-1 text-sm text-left">Rp <?= number_format($l['harga'], 0, ',', '.') ?></p>
                            <div class="mt-6 flex gap-2">
                                <button onclick='openEditModal(<?= json_encode($l) ?>)' class="text-[9px] bg-blue-50 text-blue-500 px-4 py-2 rounded-xl font-bold uppercase hover:bg-blue-500 transition">Edit</button>
                                <a href="?hapus=<?= $l['id_layanan'] ?>" onclick="return confirm('Hapus?')" class="text-[9px] bg-red-50 text-red-400 px-4 py-2 rounded-xl font-bold uppercase hover:bg-red-500 transition">Hapus</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <section id="section-pesanan" class="content-section text-left">
                <h2 class="text-2xl font-bold mb-10 text-gray-800 uppercase text-xs tracking-widest text-left">Antrean Proyek</h2>
                <div class="bg-white rounded-[2.5rem] border border-pink-50 overflow-hidden shadow-xl text-center p-20 text-gray-400 italic text-sm">Belum ada pesanan masuk.</div>
            </section>
        </main>
    </div>

    <div id="modalTambahKat" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm text-left"><div class="bg-white w-full max-w-md rounded-[3rem] p-10 shadow-2xl text-left"><h3 class="text-xl font-bold mb-6 text-left">Tambah Kategori</h3><form action="" method="POST" class="space-y-4"><input type="text" name="nama_kategori" required placeholder="Nama Kategori" class="w-full px-6 py-4 rounded-2xl border bg-gray-50 outline-none text-sm"><button type="submit" name="btn_tambah_kategori" class="w-full bg-[#d14d72] text-white py-4 rounded-2xl font-bold uppercase text-[10px]">Simpan</button><button type="button" onclick="closeModal('modalTambahKat')" class="w-full text-gray-400 text-[10px] font-bold uppercase">Batal</button></form></div></div>
    <div id="modalEditKat" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm text-left"><div class="bg-white w-full max-w-md rounded-[3rem] p-10 shadow-2xl text-left"><h3 class="text-xl font-bold mb-6">Edit Kategori</h3><form action="" method="POST" class="space-y-4 text-left"><input type="hidden" name="id_kategori" id="kat_edit_id"><input type="text" name="nama_kategori" id="kat_edit_nama" required class="w-full px-6 py-4 rounded-2xl border bg-gray-50 outline-none text-sm"><button type="submit" name="btn_edit_kategori" class="w-full bg-blue-500 text-white py-4 rounded-2xl font-bold uppercase text-[10px]">Update</button><button type="button" onclick="closeModal('modalEditKat')" class="w-full text-gray-400 text-[10px] font-bold uppercase">Batal</button></form></div></div>
    <div id="modalTambah" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm text-left"><div class="bg-white w-full max-w-lg rounded-[3.5rem] p-12 shadow-2xl overflow-y-auto max-h-[90vh] text-left"><h3 class="text-2xl font-bold italic mb-8">Tambah Katalog</h3><form action="" method="POST" enctype="multipart/form-data" class="space-y-5 text-left"><select name="id_kategori" required class="w-full px-6 py-4 rounded-2xl border bg-gray-50 outline-none text-sm"><option value="">Pilih Kategori</option><?php foreach($listKategori as $k): ?><option value="<?= $k['id_kategori'] ?>"><?= $k['nama_kategori'] ?></option><?php endforeach; ?></select><input type="file" name="foto" required class="w-full text-xs text-gray-400"><input type="text" name="nama_layanan" required placeholder="Nama Layanan" class="w-full px-6 py-4 rounded-2xl border bg-gray-50 outline-none text-sm"><input type="number" name="harga" required placeholder="Harga" class="w-full px-6 py-4 rounded-2xl border bg-gray-50 outline-none text-sm"><textarea name="deskripsi" rows="3" placeholder="Deskripsi" class="w-full px-6 py-4 rounded-2xl border bg-gray-50 outline-none text-sm resize-none"></textarea><button type="submit" name="btn_tambah_layanan" class="w-full bg-[#d14d72] text-white py-5 rounded-3xl font-bold uppercase text-[10px] shadow-lg">Simpan Katalog</button><button type="button" onclick="closeModal('modalTambah')" class="w-full text-gray-400 text-[10px] font-bold uppercase mt-2">Batal</button></form></div></div>
    <div id="modalEdit" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm text-left"><div class="bg-white w-full max-w-lg rounded-[3.5rem] p-12 shadow-2xl overflow-y-auto max-h-[90vh] text-left"><h3 class="text-2xl font-bold italic mb-8">Edit Katalog</h3><form action="" method="POST" enctype="multipart/form-data" class="space-y-5 text-left"><input type="hidden" name="id_layanan" id="edit_id"><select name="id_kategori" id="edit_kat" required class="w-full px-6 py-4 rounded-2xl border bg-gray-50 text-sm"><?php foreach($listKategori as $k): ?><option value="<?= $k['id_kategori'] ?>"><?= $k['nama_kategori'] ?></option><?php endforeach; ?></select><input type="file" name="foto" class="w-full text-xs text-gray-400"><input type="text" name="nama_layanan" id="edit_nama" required class="w-full px-6 py-4 rounded-2xl border bg-gray-50 text-sm"><input type="number" name="harga" id="edit_harga" required class="w-full px-6 py-4 rounded-2xl border bg-gray-50 text-sm"><textarea name="deskripsi" id="edit_deskripsi" rows="3" class="w-full px-6 py-4 rounded-2xl border bg-gray-50 text-sm resize-none"></textarea><button type="submit" name="btn_edit_layanan" class="w-full bg-blue-500 text-white py-5 rounded-3xl font-bold uppercase text-[10px] shadow-lg">Update Katalog</button><button type="button" onclick="closeModal('modalEdit')" class="w-full text-gray-400 text-[10px] font-bold uppercase mt-2">Batal</button></form></div></div>

    <script>
        function switchSection(sectionId, element) {
            document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
            document.getElementById('section-' + sectionId).classList.add('active');
            document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('sidebar-link-active', 'text-[#d14d72]', 'font-bold'));
            if(element) element.classList.add('sidebar-link-active', 'text-[#d14d72]', 'font-bold');
        }
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id_layanan;
            document.getElementById('edit_kat').value = data.id_kategori;
            document.getElementById('edit_nama').value = data.nama_layanan;
            document.getElementById('edit_harga').value = data.harga;
            document.getElementById('edit_deskripsi').value = data.deskripsi;
            openModal('modalEdit');
        }
        function openEditKatModal(data) {
            document.getElementById('kat_edit_id').value = data.id_kategori;
            document.getElementById('kat_edit_nama').value = data.nama_kategori;
            openModal('modalEditKat');
        }
        window.onload = function() {
            const ctx = document.getElementById('vendorChart').getContext('2d');
            new Chart(ctx, { type: 'line', data: { labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'], datasets: [{ label: 'Sales', data: [5, 8, 4, 12, 10, <?= $totalPesanan ?>], borderColor: '#d14d72', backgroundColor: 'rgba(209, 77, 114, 0.1)', fill: true, tension: 0.4 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } } });
        };
    </script>
</body>
</html>