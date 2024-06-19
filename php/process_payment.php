<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Orang Tua') {
    header("Location: ../html/auth_login.html");
    exit();
}
include 'connection.php';

$parent_id = $_SESSION['user_id'];
$doctor_id = htmlspecialchars($_POST['doctor_id']);
$child_id = htmlspecialchars($_POST['child_id']);
$message = htmlspecialchars($_POST['message']);

// Ambil tarif dokter dari database
$sql = "SELECT Tarif FROM Dokter WHERE ID_Dokter='$doctor_id'";
$result = $conn->query($sql);
$doctor = $result->fetch_assoc();
$payment_amount = $doctor['Tarif'];

// Menyimpan data pembayaran
$sql_payment = "INSERT INTO Pembayaran (ID_Orang_Tua, ID_Dokter, Jumlah_Pembayaran, Tanggal_Pembayaran)
                VALUES ((SELECT ID_Orang_Tua FROM Orang_Tua WHERE ID_User='$parent_id'), '$doctor_id', '$payment_amount', NOW())";
if ($conn->query($sql_payment) === TRUE) {
    $payment_id = $conn->insert_id;
    // Menyimpan data konsultasi
    $sql_consultation = "INSERT INTO Konsultasi (ID_Dokter, ID_Anak, Pesan, Status, Tanggal_Respon)
                         VALUES ('$doctor_id', '$child_id', '$message', 'Pending', NOW())";
    if ($conn->query($sql_consultation) === TRUE) {
        header("Location: user_dashboard.php");
        exit();
    } else {
        echo "Error: " . $sql_consultation . "<br>" . $conn->error;
    }
} else {
    echo "Error: " . $sql_payment . "<br>" . $conn->error;
}

$conn->close();
