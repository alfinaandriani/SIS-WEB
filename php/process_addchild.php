<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Orang Tua') {
    header("Location: ../html/auth_login.html");
    exit();
}

include 'connection.php';

$parent_id = $_SESSION['user_id']; // Asumsikan ID User yang sedang login adalah ID Orang Tua
$nama = $_POST['nama'];
$tanggal_lahir = $_POST['tanggal_lahir'];
$jenis_kelamin = $_POST['jenis_kelamin'];
$foto = $_FILES['foto'];

// Direktori untuk menyimpan file yang diupload
$target_dir = "../uploads/";
$target_file = $target_dir . basename($foto["name"]);
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Check if file is an actual image
$check = getimagesize($foto["tmp_name"]);
if ($check === false) {
    die("File yang diupload bukan gambar.");
}

// Check if file already exists
if (file_exists($target_file)) {
    die("Maaf, file sudah ada.");
}

// Check file size
if ($foto["size"] > 500000) {
    die("Maaf, ukuran file terlalu besar.");
}

// Allow certain file formats
if (
    $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif"
) {
    die("Maaf, hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.");
}

// Try to upload file
if (!move_uploaded_file($foto["tmp_name"], $target_file)) {
    die("Maaf, terjadi kesalahan saat mengupload file.");
}

// Insert data anak ke database
$sql = "INSERT INTO Anak (Nama, Tanggal_Lahir, Jenis_Kelamin, ID_Orang_Tua, Foto)
        VALUES ('$nama', '$tanggal_lahir', '$jenis_kelamin', 
                (SELECT ID_Orang_Tua FROM Orang_Tua WHERE ID_User='$parent_id'), '$target_file')";

if ($conn->query($sql) === TRUE) {
    echo "Data anak berhasil ditambahkan.";
    header("Location: parent_growth.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
