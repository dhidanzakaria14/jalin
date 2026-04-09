<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$success_msg = ""; // Variabel untuk menampung pesan sukses

// 1. Ambil data user
$stmt = $conn->prepare("SELECT * FROM users WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

// 2. Tentukan halaman tujuan
$role = $userData['role'];
$redirect_url = ($role == 'Vendor' || $role == 'Freelance') ? 'dashboard-vendor.php' : 'dashboard.php';

// 3. Proses Update Data
if (isset($_POST['btn_simpan'])) {
    $nama = $_POST['nama'];
    $whatsapp = $_POST['telepon'];
    $alamat = $_POST['alamat'];
    $nama_toko = isset($_POST['nama_toko']) ? $_POST['nama_toko'] : $userData['nama_toko'];
    $foto_final = $userData['foto_profil']; 

    if (!empty($_FILES['foto']['name'])) {
        $nama_file = $_FILES['foto']['name'];
        $tmp_file = $_FILES['foto']['tmp_name'];
        $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $nama_file_baru = "user_" . $id_user . "_" . time() . "." . $ekstensi;

        if (move_uploaded_file($tmp_file, 'uploads/' . $nama_file_baru)) {
            if ($userData['foto_profil'] != 'default.png' && file_exists('uploads/'.$userData['foto_profil'])) {
                unlink('uploads/'.$userData['foto_profil']);
            }
            $foto_final = $nama_file_baru;
        }
    }

    $query = "UPDATE users SET nama_lengkap = ?, no_whatsapp = ?, alamat = ?, foto_profil = ?, nama_toko = ? WHERE id_user = ?";
    $stmt_upd = $conn->prepare($query);
    $stmt_upd->bind_param("sssssi", $nama, $whatsapp, $alamat, $foto_final, $nama_toko, $id_user);

    if ($stmt_upd->execute()) {
        // Tampilkan pesan sukses (Nanti dihandle JavaScript untuk redirect)
        $success_msg = "Profil berhasil diperbarui! Mengalihkan ke dashboard dalam 3 detik...";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil | JALIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-[#fcfcfc] min-h-screen py-10 px-4">

    <div class="max-w-2xl mx-auto bg-white p-8 md:p-12 rounded-[2.5rem] shadow-xl border border-gray-50">
        <h1 class="text-2xl font-bold text-center text-gray-800">Edit Profil</h1>
        <p class="text-center text-gray-400 text-[10px] tracking-widest font-bold mt-1 uppercase mb-10 italic">
            Role Anda: <?= htmlspecialchars($userData['role']) ?>
        </p>

        <?php if($success_msg): ?>
            <div class="bg-green-50 text-green-600 p-4 rounded-2xl mb-6 text-xs font-bold border border-green-100 text-center animate-pulse">
                ✅ <?= $success_msg ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-6 text-left">
            
            <div class="flex justify-center mb-8">
                <div class="relative group">
                    <div class="w-28 h-28 bg-pink-50 rounded-full flex items-center justify-center border-4 border-white shadow-lg overflow-hidden">
                        <img id="previewImg" src="uploads/<?= $userData['foto_profil'] ?>?t=<?= time() ?>" class="w-full h-full object-cover">
                    </div>
                    <label for="foto" class="absolute bottom-1 right-1 bg-[#d14d72] text-white p-2 rounded-full cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><circle cx="12" cy="13" r="3" /></svg>
                    </label>
                    <input type="file" name="foto" id="foto" class="hidden" accept="image/*" onchange="preview(this)">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <?php if($userData['role'] == 'Vendor' || $userData['role'] == 'Freelance'): ?>
                <div class="md:col-span-2">
                    <label class="text-[10px] font-bold text-[#d14d72] uppercase tracking-widest ml-1">Nama Brand / Toko</label>
                    <input type="text" name="nama_toko" value="<?= htmlspecialchars($userData['nama_toko'] ?? '') ?>" class="w-full px-5 py-4 rounded-2xl bg-gray-50 border-none outline-none focus:ring-2 focus:ring-pink-100 text-sm font-bold">
                </div>
                <?php endif; ?>

                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Nama Lengkap Owner</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($userData['nama_lengkap']) ?>" required class="w-full px-5 py-4 rounded-2xl bg-gray-50 border-none outline-none text-sm">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">No. WhatsApp</label>
                    <input type="text" name="telepon" value="<?= htmlspecialchars($userData['no_whatsapp']) ?>" class="w-full px-5 py-4 rounded-2xl bg-gray-50 border-none outline-none text-sm">
                </div>
            </div>

            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Alamat</label>
                <textarea name="alamat" rows="3" class="w-full px-5 py-4 rounded-2xl bg-gray-50 border-none outline-none text-sm resize-none"><?= htmlspecialchars($userData['alamat']) ?></textarea>
            </div>

            <div class="pt-6 space-y-4">
                <button type="submit" name="btn_simpan" class="w-full bg-[#d14d72] text-white py-4 rounded-2xl font-bold shadow-lg hover:bg-[#b03d5d] transition transform active:scale-95 text-xs uppercase tracking-widest">Simpan Profil</button>
                <div class="flex gap-4">
                    <a href="<?= $redirect_url ?>" class="flex-1 text-center py-3 bg-gray-100 text-gray-500 rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-gray-200 transition">Batal</a>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Fungsi Preview Foto
        function preview(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) { document.getElementById('previewImg').src = e.target.result; }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // LOGIKA REDIRECT SETELAH SUKSES
        <?php if($success_msg): ?>
        setTimeout(function() {
            window.location.href = "<?= $redirect_url ?>";
        }, 3000); // 3000 milidetik = 3 detik
        <?php endif; ?>
    </script>
</body>
</html>