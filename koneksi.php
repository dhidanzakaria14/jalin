<?php
include 'koneksi.php'; // Memanggil file koneksi tadi

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Query SQL untuk memasukkan data
    $sql = "INSERT INTO users (email, no_whatsapp, role) VALUES ('$email', '$phone', 'Vendor')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard-vendor.html");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>