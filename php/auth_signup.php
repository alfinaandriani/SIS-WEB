<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'db_sistun');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $user = $conn->query('SELECT * FROM user ORDER BY ID_User DESC LIMIT 1');
    $user = $user->fetch_assoc();
    $idUser = $user ? $user['ID_User'] + 1 : 1;

    // Prepare and bind
    if ($role === 'Orang Tua') {
        $nama_ayah = $_POST['nama_ayah'];
        $nama_ibu = $_POST['nama_ibu'];
        $alamat = $_POST['alamat'];
        $nomor_telp = $_POST['nomor_telepon'];
        $stmt = $conn->query("INSERT INTO user (nama, email, password, role) VALUES ('$nama_ayah', '$email', '$password', '$role')");
        $stmt = $conn->query("INSERT INTO orang_tua (nama_ayah, nama_ibu, alamat, nomor_telepon, ID_User) VALUES ('$nama_ayah', '$nama_ibu', '$alamat', '$nomor_telp',  $idUser)");
    } elseif ($role === 'Dokter') {
        $nama_dokter = $_POST['nama_dokter'];
        $spesialis = $_POST['spesialis'];
        $nomor_telp = $_POST['nomor_telepon'];
        $stmt = $conn->query("INSERT INTO user (nama, email, password, role) VALUES ('$nama_dokter', '$email', '$password')");
        $stmt = $conn->query("INSERT INTO dokter (nama, spesialisasi, nomor_telepon, ID_User) VALUES ('$nama_dokter', '$spesialis', '$nomor_telp',  $idUser)");
    }



    // Execute the query
    if ($stmt) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $stmtr;
    }

    $conn->close();
}
