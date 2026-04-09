<?php
include 'koneksi.php';
session_start();

// 1. Proteksi Halaman: Jika belum login, tendang ke login.php
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// --- [LOGIKA LOGOUT NYATU DI SINI] ---
// Ini akan jalan kalau kamu klik link yang ada ?action=logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// --- [LOGIKA HAPUS AKUN NYATU DI SINI] ---
if (isset($_POST['btn_hapus_akun'])) {
    $cek_foto = mysqli_query($conn, "SELECT foto_profil FROM users WHERE id_user = '$id_user'");
    $f = mysqli_fetch_assoc($cek_foto);
    if ($f['foto_profil'] != 'default.png' && !empty($f['foto_profil']) && file_exists('uploads/' . $f['foto_profil'])) {
        unlink('uploads/' . $f['foto_profil']);
    }
    if (mysqli_query($conn, "DELETE FROM users WHERE id_user = '$id_user'")) {
        session_destroy();
        echo "<script>alert('Akun JALIN Anda telah dihapus.'); window.location='login.php';</script>";
        exit();
    }
}

// 2. Ambil Data Detail User dari Database
$queryUser = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_user'");
$userData = mysqli_fetch_assoc($queryUser);
$userName = $userData['nama_lengkap'];
$userRole = $userData['role'];

// 3. Hitung Statistik Sederhana
$sqlBooking = "SELECT COUNT(*) as total FROM pesanan WHERE id_pengantin = '$id_user'";
$resBooking = mysqli_query($conn, $sqlBooking);
$countBooking = mysqli_fetch_assoc($resBooking)['total'];

// 4. Ambil 5 Pesanan Terbaru
$queryOrders = mysqli_query($conn, "
    SELECT p.tgl_pesan, p.status, l.nama_layanan, u.nama_lengkap as nama_vendor
    FROM pesanan p
    JOIN detail_pesanan dp ON p.id_pesanan = dp.id_pesanan
    JOIN layanan l ON dp.id_layanan = l.id_layanan
    JOIN users u ON l.id_vendor = u.id_user
    WHERE p.id_pengantin = '$id_user'
    ORDER BY p.tgl_pesan DESC LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengantin | JALIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; background-color: #fffafb; }</style>
</head>
<body class="text-gray-800">

    <nav class="bg-white py-4 shadow-sm sticky top-0 z-50 border-b border-pink-50">
        <div class="w-[85%] mx-auto flex justify-between items-center">
            <div class="text-2xl font-bold text-[#d14d72] cursor-pointer" onclick="window.location.href='index.php'">
                JALIN<span class="text-[#ffb7c5]">.</span>
            </div>
            <div class="flex items-center gap-6 text-sm font-semibold text-[#d14d72]">
                <a href="index.php" class="hover:text-pink-400 transition">Beranda</a>
                <div class="flex items-center gap-2 bg-[#ffb7c5] px-4 py-2 rounded-full text-white shadow-md">
                    <span><?php echo strtoupper($userName); ?></span>
                </div>
            </div>
        </div>
    </nav>

    <main class="w-[85%] mx-auto py-12">
        <div class="flex flex-col lg:flex-row gap-8">
            
            <aside class="w-full lg:w-1/4 space-y-6">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-xl text-center border border-pink-50">
                    <div class="w-24 h-24 bg-[#fff0f3] rounded-full mx-auto mb-4 flex items-center justify-center border-2 border-white text-[#d14d72] overflow-hidden">
                        <?php 
                        $path_foto = 'uploads/' . $userData['foto_profil'];
                        if(!empty($userData['foto_profil']) && file_exists($path_foto)): ?>
                            <img src="<?= $path_foto ?>?t=<?= time() ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            👤
                        <?php endif; ?>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 italic"><?php echo $userName; ?></h2>
                    <p class="text-xs text-pink-500 font-bold uppercase tracking-widest mt-1"><?php echo $userRole; ?></p>
                    
                    <hr class="my-6 border-pink-50">
                    
                    <div class="space-y-3 text-left">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-4 ml-1">Menu Profil</div>
                        <a href="dashboard.php" class="block text-sm font-bold text-[#d14d72] bg-[#fff0f3] p-4 rounded-2xl transition border border-pink-100">Dashboard Akun</a>
                        <a href="edit-profile.php" class="block text-sm font-medium text-gray-500 hover:text-[#d14d72] hover:bg-pink-50/50 p-4 rounded-2xl transition">Edit Profil</a>
                        
                        <a href="dashboard.php?action=logout" onclick="return confirm('Yakin ingin keluar?')" class="block text-sm font-medium text-red-400 hover:bg-red-50 p-4 rounded-2xl transition mt-4">Keluar (Logout)</a>
                        
                        <form action="" method="POST" class="mt-2">
                            <button type="submit" name="btn_hapus_akun" onclick="return confirm('Hapus akun permanen?')" class="w-full text-left text-[10px] font-bold text-gray-300 hover:text-red-400 p-4 uppercase tracking-widest transition">Hapus Akun</button>
                        </form>
                    </div>
                </div>
            </aside>

            <section class="flex-1 space-y-8">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-pink-50 flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="text-left w-full">
                        <h1 class="text-2xl font-bold text-gray-800">Halo, <span class="italic text-[#d14d72]"><?php echo $userName; ?></span>! 👋</h1>
                        <p class="text-gray-500 text-sm mt-1">Hari bahagia Anda sedang kami siapkan.</p>
                    </div>
                    <div class="text-center bg-[#fffafb] px-6 py-4 rounded-[1.5rem] border border-pink-100 min-w-[100px]">
                        <div class="text-2xl font-bold text-[#d14d72]"><?php echo $countBooking; ?></div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Bookings</div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-pink-50">
                    <h3 class="font-bold text-gray-400 tracking-widest uppercase text-xs mb-6">Pesanan Terbaru</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-bold text-gray-300 uppercase tracking-[0.2em] border-b border-gray-50">
                                    <th class="pb-5 pl-2">Vendor</th>
                                    <th class="pb-5">Layanan</th>
                                    <th class="pb-5">Tanggal</th>
                                    <th class="pb-5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <?php if(mysqli_num_rows($queryOrders) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($queryOrders)): ?>
                                    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
                                        <td class="py-6 pl-2 font-bold text-gray-700 text-xs"><?php echo $row['nama_vendor']; ?></td>
                                        <td class="py-6 text-gray-500 text-xs"><?php echo $row['nama_layanan']; ?></td>
                                        <td class="py-6 text-gray-500 text-xs"><?php echo date('d M Y', strtotime($row['tgl_pesan'])); ?></td>
                                        <td class="py-6">
                                            <span class="bg-blue-50 text-blue-500 px-4 py-1.5 rounded-full text-[9px] font-bold uppercase tracking-wider italic">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="py-10 text-center text-gray-400 text-xs italic">Belum ada pesanan terbaru.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>