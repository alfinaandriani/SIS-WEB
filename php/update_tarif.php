<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Dokter') {
    header("Location: ../html/auth_login.html");
    exit();
}
include 'connection.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tarif'])) {
    $tarif = htmlspecialchars($_POST['tarif']);
    if (!is_numeric($tarif)) {
        die("Invalid tarif value.");
    }
    $sql = "UPDATE dokter SET Tarif='$tarif' WHERE ID_User='$user_id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: doctor_profile.php");
    } else {
        echo "Error updating record: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
