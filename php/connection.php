<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_sistun";

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getUnreadNotifications($user_id, $conn)
{
    $sql = "SELECT COUNT(*) as unread_count FROM Konsultasi k
            JOIN Anak a ON k.ID_Anak = a.ID_Anak
            WHERE a.ID_Orang_Tua = (SELECT ID_Orang_Tua FROM Orang_Tua WHERE ID_User='$user_id')
            AND k.Status = 'Selesai' AND k.IsRead = FALSE";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['unread_count'];
}
