<?php
include 'koneksi.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name  = mysqli_real_escape_string($conn, $_POST['last_name']);
    $nama_lengkap = $first_name . " " . $last_name;
    
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $password   = $_POST['password']; 
    $no_wa      = mysqli_real_escape_string($conn, $_POST['no_wa']);
    $role       = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Data tambahan (bisa kosong jika bukan vendor)
    $nama_toko  = isset($_POST['nama_toko']) ? mysqli_real_escape_string($conn, $_POST['nama_toko']) : "";
    $alamat     = isset($_POST['alamat']) ? mysqli_real_escape_string($conn, $_POST['alamat']) : "";

    // Cek email ganda
    $cek = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek) > 0) {
        $message = "Email sudah terdaftar!";
    } else {
        $query = "INSERT INTO users (nama_lengkap, nama_toko, email, password, no_whatsapp, alamat, role) 
                  VALUES ('$nama_lengkap', '$nama_toko', '$email', '$password', '$no_wa', '$alamat', '$role')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Pendaftaran Berhasil! Silakan Login.'); window.location='login.php';</script>";
        } else {
            $message = "Gagal mendaftar: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun | JALIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: #fffafb;
            background-image: 
                url('https://www.transparenttextures.com/patterns/dust.png'),
                url('https://images.unsplash.com/photo-1525287714853-a55e109d9415?q=80&w=400&auto=format&fit=crop');
            background-repeat: repeat, no-repeat;
            background-position: center, bottom right;
            background-attachment: fixed;
            background-size: auto, 30%;
        }
        .role-card-selected {
            border-color: #ffb7c5 !important;
            box-shadow: 0 20px 25px -5px rgba(255, 183, 197, 0.4) !important;
            transform: scale(1.05) translateY(-10px);
        }
    </style>
</head>
<body class="min-h-screen py-16 px-6 text-gray-800 relative">

    <img src="https://images.unsplash.com/photo-1533109311681-3e4142f36f56?q=80&w=300" 
         class="absolute top-0 left-0 opacity-20 w-48 h-48 object-cover rounded-full -translate-x-16 -translate-y-16" alt="decor">

    <div class="max-w-6xl mx-auto relative z-10">
        <div class="text-center mb-16">
            <div class="text-4xl font-bold text-[#d14d72] cursor-pointer inline-block transform hover:scale-105 transition" onclick="window.location.href='index.php'">
                JALIN<span class="text-[#ffb7c5]">.</span>
            </div>
            <h2 class="text-3xl font-bold text-gray-800 mt-6">Mulai Perjalanan Anda di JALIN</h2>
            <p class="text-gray-500 mt-3 font-medium">Pilih peran Anda untuk mewujudkan momen indah.</p>
            
            <?php if($message != ""): ?>
                <div class="bg-red-500 text-white p-3 rounded-xl mt-4 max-w-sm mx-auto text-xs font-bold">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16 items-start">
            <div id="cardPengantin" onclick="selectRole('Pengantin')" class="group bg-white p-10 rounded-[2.5rem] shadow-xl hover:shadow-2xl hover:-translate-y-3 transition-all duration-500 border-4 border-transparent cursor-pointer text-center relative overflow-hidden">
                <div class="w-24 h-24 bg-[#fff0f3] rounded-full flex items-center justify-center mx-auto mb-6 border border-pink-100">
                    <img src="https://cdn-icons-png.flaticon.com/512/3656/3656861.png" class="w-14 h-14" alt="ring">
                </div>
                <h3 class="font-bold text-xl text-[#d14d72] mb-3">Calon Pengantin</h3>
                <p class="text-sm text-gray-400">Wujudkan pernikahan impian Anda.</p>
                <div class="absolute top-4 right-4 w-6 h-6 border-2 border-gray-200 rounded-full flex items-center justify-center">
                    <div id="dotPengantin" class="w-3 h-3 bg-[#ffb7c5] rounded-full opacity-0 transition-opacity"></div>
                </div>
            </div>

            <div id="cardVendor" onclick="selectRole('Vendor')" class="group bg-white p-10 rounded-[2.5rem] shadow-xl hover:shadow-2xl hover:-translate-y-3 transition-all duration-500 border-4 border-transparent cursor-pointer text-center relative overflow-hidden">
                <div class="w-24 h-24 bg-[#fff0f3] rounded-full flex items-center justify-center mx-auto mb-6 border border-pink-100">
                    <img src="https://cdn-icons-png.flaticon.com/512/12170/12170068.png" class="w-14 h-14" alt="shop">
                </div>
                <h3 class="font-bold text-xl text-[#d14d72] mb-3">Vendor UMKM</h3>
                <p class="text-sm text-gray-400">Kembangkan bisnis wedding Anda.</p>
                <div class="absolute top-4 right-4 w-6 h-6 border-2 border-gray-200 rounded-full flex items-center justify-center">
                    <div id="dotVendor" class="w-3 h-3 bg-[#ffb7c5] rounded-full opacity-0 transition-opacity"></div>
                </div>
            </div>

            <div id="cardFreelance" onclick="selectRole('Freelance')" class="group bg-white p-10 rounded-[2.5rem] shadow-xl hover:shadow-2xl hover:-translate-y-3 transition-all duration-500 border-4 border-transparent cursor-pointer text-center relative overflow-hidden">
                <div class="w-24 h-24 bg-[#fff0f3] rounded-full flex items-center justify-center mx-auto mb-6 border border-pink-100">
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135810.png" class="w-14 h-14" alt="student">
                </div>
                <h3 class="font-bold text-xl text-[#d14d72] mb-3">Mahasiswa</h3>
                <p class="text-sm text-gray-400">Dapatkan pengalaman & penghasilan.</p>
                <div class="absolute top-4 right-4 w-6 h-6 border-2 border-gray-200 rounded-full flex items-center justify-center">
                    <div id="dotFreelance" class="w-3 h-3 bg-[#ffb7c5] rounded-full opacity-0 transition-opacity"></div>
                </div>
            </div>
        </div>

        <div id="registerFormArea" class="hidden max-w-xl mx-auto backdrop-blur-sm bg-white/90 p-12 rounded-[3rem] shadow-2xl border border-pink-100 relative">
            <h3 class="text-center font-bold text-gray-800 mb-10 uppercase tracking-widest text-sm">Formulir <span id="selectedRoleText" class="text-[#d14d72]"></span></h3>
            
            <form action="" method="POST" class="space-y-6">
                <input type="hidden" name="role" id="roleInput">
                
                <div class="grid grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-[#d14d72] uppercase ml-1">Nama Depan</label>
                        <input type="text" name="first_name" required class="w-full px-5 py-3.5 rounded-xl border border-gray-100 bg-gray-50/50 text-sm outline-none focus:ring-2 focus:ring-pink-100">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-[#d14d72] uppercase ml-1">Nama Belakang</label>
                        <input type="text" name="last_name" required class="w-full px-5 py-3.5 rounded-xl border border-gray-100 bg-gray-50/50 text-sm outline-none focus:ring-2 focus:ring-pink-100">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-[#d14d72] uppercase ml-1">Alamat Email</label>
                    <input type="email" name="email" required placeholder="nama@email.com" class="w-full px-5 py-3.5 rounded-xl border border-gray-100 bg-gray-50/50 text-sm outline-none focus:ring-2 focus:ring-pink-100">
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-[#d14d72] uppercase ml-1">WhatsApp Aktif</label>
                    <input type="number" name="no_wa" required placeholder="628123456789" class="w-full px-5 py-3.5 rounded-xl border border-gray-100 bg-gray-50/50 text-sm outline-none focus:ring-2 focus:ring-pink-100">
                </div>

                <div id="vendorExtra" class="hidden space-y-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-[#d14d72] uppercase ml-1">Nama Toko/Bisnis</label>
                        <input type="text" name="nama_toko" class="w-full px-5 py-3.5 rounded-xl border border-pink-50 bg-pink-50/10 text-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-[#d14d72] uppercase ml-1">Alamat Toko</label>
                        <textarea name="alamat" class="w-full px-5 py-3.5 rounded-xl border border-pink-50 bg-pink-50/10 text-sm outline-none h-20"></textarea>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-[#d14d72] uppercase ml-1">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-5 py-3.5 rounded-xl border border-gray-100 bg-gray-50/50 text-sm outline-none focus:ring-2 focus:ring-pink-100">
                </div>

                <button type="submit" class="w-full bg-[#d14d72] text-white py-4 rounded-full font-bold shadow-lg hover:bg-[#b03d5d] transition transform active:scale-95 mt-6 uppercase tracking-widest text-sm">
                    Buat Akun Sekarang
                </button>
            </form>
        </div>

        <p class="text-center mt-12 text-sm text-gray-500 font-medium">
            Sudah memiliki akun? <a href="login.php" class="text-[#d14d72] font-bold hover:underline">Masuk di sini</a>
        </p>
    </div>

    <script>
        function selectRole(role) {
            document.getElementById('roleInput').value = role;
            document.getElementById('selectedRoleText').innerText = role;

            document.querySelectorAll('[id^="card"]').forEach(card => card.classList.remove('role-card-selected'));
            document.querySelectorAll('[id^="dot"]').forEach(dot => dot.style.opacity = '0');

            const selectedCard = document.getElementById('card' + role);
            selectedCard.classList.add('role-card-selected');
            document.getElementById('dot' + role).style.opacity = '1';

            document.getElementById('registerFormArea').classList.remove('hidden');

            if(role === 'Vendor') {
                document.getElementById('vendorExtra').classList.remove('hidden');
            } else {
                document.getElementById('vendorExtra').classList.add('hidden');
            }

            window.scrollTo({top: document.getElementById('registerFormArea').offsetTop - 50, behavior: 'smooth'});
        }
    </script>
</body>
</html>