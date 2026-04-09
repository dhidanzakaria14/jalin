<?php
include 'koneksi.php';
session_start();

// 1. Ambil data layanan dari database
// Pastikan nama kolom di JOIN sesuai (id_vendor dan id_user)
$query = "SELECT l.*, u.nama_toko 
          FROM layanan l 
          JOIN users u ON l.id_vendor = u.id_user 
          ORDER BY l.id_layanan DESC";
$result = mysqli_query($conn, $query);

// Cek jika query gagal secara teknis
if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JALIN | Katalog Vendor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fffafb; color: #333; }
        .vendor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; margin-top: 20px; }
        .vendor-card { background: white; border-radius: 25px; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.03); border: 1px solid #fce7eb; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .vendor-card:hover { transform: translateY(-12px); box-shadow: 0 25px 30px rgba(209, 77, 114, 0.12); }
        .vendor-img { position: relative; height: 240px; overflow: hidden; }
        .vendor-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
        .vendor-card:hover .vendor-img img { transform: scale(1.1); }
        .price-tag { color: #d14d72; font-weight: 700; font-size: 18px; }
        #vendorModal.hidden { display: none; }
        #vendorModal:not(.hidden) { display: flex; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body class="overflow-x-hidden">

    <nav class="sticky top-0 z-[1000] bg-white/90 backdrop-blur-md py-4 shadow-sm border-b border-pink-50 text-left">
        <div class="w-[85%] mx-auto flex justify-between items-center text-left">
            <div class="text-2xl font-bold text-[#d14d72] cursor-pointer" onclick="window.location.href='index.php'">JALIN<span class="text-[#ffb7c5]">.</span></div>
            <div id="user-nav-container">
                <?php if(isset($_SESSION['nama_lengkap'])): ?>
                    <span class="bg-[#ffb7c5] px-6 py-2.5 rounded-full text-white font-bold text-xs uppercase tracking-widest shadow-md">
                        👤 <?= strtoupper($_SESSION['nama_lengkap']) ?>
                    </span>
                <?php else: ?>
                    <a href="login.php" class="text-sm font-bold text-[#d14d72] hover:underline">Masuk</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="py-20 text-center">
        <div class="container mx-auto px-4 relative z-10 text-center">
            <p class="text-[#ffb7c5] font-bold uppercase tracking-[0.4em] text-[10px] mb-4">Official Partners</p>
            <h2 class="text-4xl md:text-5xl font-extrabold text-gray-800 leading-tight">Temukan Vendor <br><span class="text-[#d14d72]">Impian Anda</span></h2>
        </div>
    </header>

    <section class="container mx-auto px-4 w-[85%] pb-32 text-left">
        <div id="vendor-grid-container" class="vendor-grid">
            <?php 
            // Cek apakah ada datanya
            if(mysqli_num_rows($result) > 0): 
                while($row = mysqli_fetch_assoc($result)): 
            ?>
                <div class="vendor-card text-left">
                    <div class="vendor-img text-left">
                        <img src="uploads/<?= htmlspecialchars($row['gambar']) ?>" onerror="this.src='https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=600&q=80'">
                    </div>
                    <div class="p-8 text-left">
                        <p class="text-[10px] font-bold text-[#ffcad4] uppercase tracking-widest mb-1"><?= strtoupper($row['nama_toko']) ?></p>
                        <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($row['nama_layanan']) ?></h3>
                        <div class="flex justify-between items-center mt-8 pt-5 border-t border-gray-50 text-left">
                            <span class="price-tag">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                            <?php 
                                // Encode data ke base64 agar aman
                                $encodedData = base64_encode(json_encode($row)); 
                            ?>
                            <button onclick="openModal('<?= $encodedData ?>')" class="bg-[#fff0f3] text-[#d14d72] px-5 py-2.5 rounded-xl text-xs font-bold border border-pink-100 hover:bg-[#d14d72] hover:text-white transition">Lihat</button>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile; 
            else: 
            ?>
                <div class="col-span-full py-20 text-center text-gray-400 italic">
                    Belum ada layanan yang ditambahkan oleh vendor.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div id="vendorModal" class="fixed inset-0 z-[2000] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="bg-white w-full max-w-4xl rounded-[3rem] relative z-10 overflow-hidden shadow-2xl flex flex-col md:flex-row max-h-[90vh]">
            <div class="md:w-1/2 h-64 md:h-auto overflow-hidden">
                <img id="modalImg" src="" class="w-full h-full object-cover">
            </div>
            <div class="md:w-1/2 p-10 md:p-14 overflow-y-auto text-left">
                <button onclick="closeModal()" class="absolute top-8 right-10 text-3xl text-gray-300 hover:text-[#d14d72]">&times;</button>
                <p id="modalVendor" class="text-[10px] font-bold text-[#ffcad4] uppercase tracking-widest mb-2"></p>
                <h2 id="modalTitle" class="text-3xl font-bold text-gray-800 mb-2 leading-tight"></h2>
                
                <div class="space-y-4 my-8">
                    <h4 class="font-bold text-gray-700 text-xs uppercase tracking-widest">Deskripsi Layanan:</h4>
                    <p id="modalDesc" class="text-sm text-gray-500 leading-relaxed italic"></p>
                </div>

                <div class="pt-8 border-t border-gray-100">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Harga JALIN</p>
                    <p id="modalPrice" class="text-2xl font-bold text-[#d14d72] mb-8"></p>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button id="btnHubungi" class="flex-1 bg-green-500 text-white px-6 py-4 rounded-2xl font-bold shadow-lg hover:bg-green-600 transition flex items-center justify-center gap-2 text-sm uppercase">
                            <span>💬</span> Hubungi Vendor
                        </button>
                        <button class="flex-1 bg-[#d14d72] text-white px-6 py-4 rounded-2xl font-bold shadow-lg hover:bg-[#b03d5d] transition text-sm uppercase">
                            Booking
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(encodedData) {
            const item = JSON.parse(atob(encodedData));
            const hargaFormat = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(item.harga);

            document.getElementById('modalTitle').innerText = item.nama_layanan;
            document.getElementById('modalVendor').innerText = item.nama_toko;
            document.getElementById('modalImg').src = 'uploads/' + item.gambar;
            document.getElementById('modalPrice').innerText = hargaFormat;
            document.getElementById('modalDesc').innerText = item.deskripsi || "Layanan unggulan dari mitra JALIN.";

            const btnHubungi = document.getElementById('btnHubungi');
            btnHubungi.onclick = function() {
                const nomerWA = "6285808330777"; 
                const pesan = `Halo JALIN! Saya tertarik dengan layanan *${item.nama_layanan}* dari vendor *${item.nama_toko}*.`;
                window.open(`https://wa.me/${nomerWA}?text=${encodeURIComponent(pesan)}`, '_blank');
            };

            document.getElementById('vendorModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('vendorModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    </script>
</body>
</html>