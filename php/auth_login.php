<?php
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM User WHERE Email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['Password'])) {
            session_start();
            $_SESSION['user_id'] = $row['ID_User'];
            $_SESSION['role'] = $row['Role'];

            if ($row['Role'] == 'Orang Tua') {
                header("Location: parent_dashboard.php");
            } else if ($row['Role'] == 'Dokter') {
                header("Location: doctor_dashboard.php");
            }
        } else {
            echo "Password salah";
        }
    } else {
        echo "Email tidak ditemukan";
    }

    $conn->close();
}
