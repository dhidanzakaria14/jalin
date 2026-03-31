<?php
include 'koneksi.php';
session_start();

$error_msg = "";

if (isset($_POST['btn_masuk'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Cek password (sesuaikan jika nanti pakai password_hash)
        if ($password == $user['password']) {
            $_SESSION['id_user']   = $user['id_user'];
            $_SESSION['nama_user'] = $user['nama_lengkap'];
            $_SESSION['role']      = $user['role'];

            if ($user['role'] == 'Vendor' || $user['role'] == 'Freelance') {
                header("Location: dashboard-vendor.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error_msg = "Password salah!";
        }
    } else {
        $error_msg = "Email tidak terdaftar!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | JALIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-[#fffafb] min-h-screen flex items-center justify-center p-6 text-gray-800">

    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="text-3xl font-bold text-[#d14d72] cursor-pointer inline-block" onclick="window.location.href='index.php'">
                JALIN<span class="text-[#ffb7c5]">.</span>
            </div>
            <p class="text-gray-500 text-sm mt-2">Wujudkan momen indah sekaligus berbagi dampak sosial.</p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] shadow-2xl shadow-pink-100/50 border border-pink-50">
            <h2 class="text-xl font-bold text-gray-800 mb-6 text-center">Selamat Datang Kembali</h2>
            
            <?php if($error_msg != ""): ?>
                <div class="bg-red-50 text-red-500 p-3 rounded-xl mb-4 text-[11px] font-bold text-center border border-red-100">
                    ⚠️ <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-[#d14d72] uppercase tracking-wider ml-1">Email</label>
                    <input type="email" name="email" required placeholder="Masukkan email Anda" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-100 outline-none focus:ring-2 focus:ring-pink-100 transition text-sm">
                </div>

                <div class="space-y-1.5">
                    <div class="flex justify-between items-center ml-1">
                        <label class="text-[11px] font-bold text-[#d14d72] uppercase tracking-wider">Password</label>
                        <a href="#" class="text-[11px] font-bold text-pink-300 hover:text-[#d14d72]">Lupa Password?</a>
                    </div>
                    <input type="password" name="password" required placeholder="••••••••" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-100 outline-none focus:ring-2 focus:ring-pink-100">
                </div>

                <button type="submit" name="btn_masuk" 
                    class="w-full bg-[#d14d72] text-white py-4 rounded-xl font-bold shadow-lg hover:bg-[#b03d5d] transition transform active:scale-95 mt-4 uppercase tracking-widest text-sm">
                    Masuk Sekarang
                </button>
            </form>
        </div>

        <p class="text-center mt-8 text-sm text-gray-500 font-medium">
            Belum memiliki akun JALIN? 
            <a href="register.php" class="text-[#d14d72] font-bold hover:underline">Daftar di sini</a>
        </p>
    </div>
</body>
</html>