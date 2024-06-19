<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Dokter') {
    header("Location: ../html/auth_login.html");
    exit();
}
include 'connection.php';

$consultation_id = $_POST['consultation_id'];
$response = $_POST['response'];
$tanggal_respon = date('Y-m-d H:i:s');

$sql = "UPDATE Konsultasi SET Respon='$response', Status='Selesai', Tanggal_Respon='$tanggal_respon', IsRead=FALSE WHERE ID_Konsultasi='$consultation_id'";
if ($conn->query($sql) === TRUE) {
    echo "Respon berhasil dikirim.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
