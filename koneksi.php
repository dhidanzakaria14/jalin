<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_jalin"; // Pastikan nama database di phpMyAdmin sama persis

$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi agar tidak bingung kalau error
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>