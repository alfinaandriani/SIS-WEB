<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Dokter') {
    header("Location: ../html/auth_login.html");
    exit();
}
include 'connection.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto'])) {
    $foto = $_FILES['foto'];
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($foto["name"]);

    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if (!in_array($imageFileType, $allowed_types)) {
        die("Invalid file type.");
    }

    if ($foto["size"] > 500000) {
        die("File size too large.");
    }

    // Memindahkan file yang diupload
    if (move_uploaded_file($foto["tmp_name"], $target_file)) {
        $sql = "UPDATE User SET Foto='$target_file' WHERE ID_User='$user_id'";
        if ($conn->query($sql) === TRUE) {
            header("Location: doctor_profile.php");
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        echo "Error uploading file.";
    }
} else {
    echo "Invalid request.";
}
