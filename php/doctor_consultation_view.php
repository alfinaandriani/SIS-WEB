<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Dokter') {
    header("Location: ../html/auth_login.html");
    exit();
}
include 'connection.php';

$consultation_id = $_GET['consultation_id'];
$sql = "SELECT * FROM Konsultasi WHERE ID_Konsultasi='$consultation_id'";
$result = $conn->query($sql);
$consultation = $result->fetch_assoc();

$child_id = $consultation['ID_Anak'];
$sql_child = "SELECT * FROM Anak WHERE ID_Anak='$child_id'";
$result_child = $conn->query($sql_child);
$child = $result_child->fetch_assoc();

$sql_growth = "SELECT * FROM Riwayat_Pertumbuhan WHERE ID_Anak='$child_id'";
$result_growth = $conn->query($sql_growth);

$conn->query("UPDATE Konsultasi SET IsRead=TRUE WHERE ID_Konsultasi='$consultation_id'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Konsultasi</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<style>
    .content h2 {
        margin: 0;
        font-size: 32px;
        color: #333333;
        grid-column: 1 / -1;
        text-align: center;
        margin-bottom: 20px;
    }

    .profile-container,
    .data-container,
    .growth-container,
    .message-container,
    .response-container {
        background-color: #fefefe;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .profile-container {
        display: flex;
        align-items: center;
        grid-column: 1 / -1;
    }

    .profile-container img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin-right: 20px;
    }

    .profile-container h3 {
        margin: 0;
    }

    .data-container,
    .growth-container,
    .message-container,
    .response-container {
        grid-column: span 1;
    }

    .growth-container table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .growth-container table,
    th,
    td {
        border: 1px solid #ddd;
    }

    th,
    td {
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #fefefe;
    }

    textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-top: 10px;
    }

    button {
        background-color: #418cfd;
        color: #fefefe;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
    }
</style>

<body>
    <div class="sidebar">
        <img src="../assets/logo_sis.webp" alt="Logo SIS">
        <div class="menu-container">
            <a href="doctor_dashboard.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" fill="none">
                    <path d="M28.0193 8.86302L18.3599 1.13555C16.4723 -0.36865 13.5229 -0.383397 11.65 1.12081L1.99069 8.86302C0.604461 9.96905 -0.236123 12.1811 0.0588184 13.9213L1.91695 25.0406C2.34462 27.5328 4.65991 29.4942 7.18166 29.4942H22.8136C25.3058 29.4942 27.6654 27.4886 28.093 25.0258L29.9512 13.9065C30.2166 12.1811 29.376 9.96905 28.0193 8.86302ZM16.1036 23.5954C16.1036 24.2 15.6022 24.7014 14.9976 24.7014C14.393 24.7014 13.8916 24.2 13.8916 23.5954V19.1712C13.8916 18.5666 14.393 18.0652 14.9976 18.0652C15.6022 18.0652 16.1036 18.5666 16.1036 19.1712V23.5954Z" fill="#BBBBBB" />
                </svg>
                Beranda</a>
            <a href="doctor_consultation.php" class="active">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="27" viewBox="0 0 30 27" fill="none">
                    <path d="M0.499834 5.11421C0 6.97418 0 9.54013 0 13.5C0 19.1246 0 21.9369 1.43237 23.9084C1.89497 24.5451 2.4549 25.105 3.09161 25.5676C5.0631 27 7.8754 27 13.5 27H16.5C22.1246 27 24.9369 27 26.9084 25.5676C27.5451 25.105 28.105 24.5451 28.5676 23.9084C30 21.9369 30 19.1246 30 13.5C30 9.52693 30 6.9571 29.4952 5.09564L26.341 8.24979C23.8926 10.6982 21.9739 12.617 20.2755 13.9128C18.5371 15.2392 16.898 16.0184 14.9998 16.0184C13.1016 16.0184 11.4625 15.2392 9.72412 13.9128C8.02573 12.617 6.10697 10.6982 3.65856 8.24976L0.725782 5.31698L0.499834 5.11421Z" fill="#BBBBBB" />
                    <path d="M1.5 3L1.66327 3.1351L2.27381 3.68303L5.18869 6.59791C7.71118 9.1204 9.52471 10.9305 11.0889 12.124C12.6272 13.2977 13.7914 13.7684 14.9998 13.7684C16.2082 13.7684 17.3724 13.2977 18.9107 12.124C20.4749 10.9306 22.2884 9.1204 24.8109 6.59791L28.2728 3.13605L28.4612 2.94868C27.9986 2.31197 27.5451 1.89497 26.9084 1.43237C24.9369 0 22.1246 0 16.5 0H13.5C7.8754 0 5.0631 0 3.09161 1.43237C2.4549 1.89497 1.9626 2.36329 1.5 3Z" fill="#BBBBBB" />
                </svg>
                Konsultasi</a>
            <a href="doctor_payment_report.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="34" viewBox="0 0 30 34" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.43512 1.59152C3.61036 1.46421 3.79157 1.34708 3.97989 1.23931C4.04038 1.37404 4.12594 1.50025 4.23658 1.61088C6.38751 3.76172 8.07582 5.44736 9.5641 6.63852C11.0708 7.84443 12.4818 8.63597 14.1075 8.89346C15.1008 9.05078 16.1126 9.05078 17.1059 8.89346C18.6861 8.64317 20.0633 7.88836 21.5229 6.73889C22.9638 5.60408 24.5815 4.00462 26.6178 1.96979C26.6831 1.9045 26.7397 1.83378 26.7876 1.75908C27.4034 2.23876 27.9491 2.80287 28.4085 3.43512C30 5.62567 30 8.75045 30 15V18.3333C30 24.5829 30 27.7077 28.4085 29.8982C27.8945 30.6057 27.2723 31.2278 26.5649 31.7418C24.3743 33.3333 21.2496 33.3333 15 33.3333C8.75045 33.3333 5.62567 33.3333 3.43512 31.7418C2.72767 31.2278 2.10552 30.6057 1.59152 29.8982C0 27.7077 0 24.5829 0 18.3333V15C0 8.75045 0 5.62567 1.59152 3.43512C2.10552 2.72767 2.72767 2.10552 3.43512 1.59152ZM8.33333 12.0833C7.64298 12.0833 7.08333 12.643 7.08333 13.3333C7.08333 14.0237 7.64298 14.5833 8.33333 14.5833H21.6667C22.357 14.5833 22.9167 14.0237 22.9167 13.3333C22.9167 12.643 22.357 12.0833 21.6667 12.0833H8.33333ZM11.6667 18.75C10.9763 18.75 10.4167 19.3096 10.4167 20C10.4167 20.6904 10.9763 21.25 11.6667 21.25H18.3333C19.0237 21.25 19.5833 20.6904 19.5833 20C19.5833 19.3096 19.0237 18.75 18.3333 18.75H11.6667ZM8.33333 25.4167C7.64298 25.4167 7.08333 25.9763 7.08333 26.6667C7.08333 27.357 7.64298 27.9167 8.33333 27.9167H21.6667C22.357 27.9167 22.9167 27.357 22.9167 26.6667C22.9167 25.9763 22.357 25.4167 21.6667 25.4167H8.33333Z" fill="#BBBBBB" />
                    <path d="M15 0C19.4999 0 22.3797 0 24.4573 0.594121C22.59 2.45648 21.1933 3.81624 19.9761 4.77483C18.6935 5.78491 17.7104 6.26655 16.7148 6.42424C15.9806 6.54052 15.2328 6.54052 14.4986 6.42424C13.4744 6.26202 12.4636 5.75708 11.1263 4.6867C9.88105 3.69007 8.44836 2.28405 6.52767 0.366125C8.51456 0 11.1707 0 15 0Z" fill="#BBBBBB" />
                </svg>
                Laporan</a>
            <a href="doctor_profile.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0 15C0 6.71573 6.71573 0 15 0C23.2843 0 30 6.71573 30 15C30 19.2537 28.2281 23.0953 25.3849 25.8238C22.6907 28.4093 19.03 30 15 30C10.97 30 7.30929 28.4093 4.61512 25.8238C1.77194 23.0953 0 19.2537 0 15ZM23.4881 24.7236C22.8866 22.9244 21.1868 21.6279 19.186 21.6279H10.814C8.8132 21.6279 7.11336 22.9244 6.51191 24.7236C8.78166 26.7068 11.7497 27.907 15 27.907C18.2503 27.907 21.2183 26.7068 23.4881 24.7236ZM15 4.18605C11.3395 4.18605 8.37209 7.15346 8.37209 10.814C8.37209 14.4744 11.3395 17.4419 15 17.4419C18.6605 17.4419 21.6279 14.4744 21.6279 10.814C21.6279 7.15346 18.6605 4.18605 15 4.18605Z" fill="#BBBBBB" />
                </svg>
                Profil</a>
            <a href="logout.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="29" viewBox="0 0 30 29" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M23.6 13.2C24.2627 13.2 24.8 13.7373 24.8 14.4C24.8 15.0627 24.2627 15.6 23.6 15.6L10.8 15.6L10.8 19.2C10.8 20.6864 10.8 21.4296 10.8985 22.0515C11.4407 25.4746 14.1254 28.1593 17.5485 28.7015C18.1704 28.8 18.9136 28.8 20.4 28.8C21.8864 28.8 22.6296 28.8 23.2515 28.7015C26.6746 28.1593 29.3593 25.4746 29.9015 22.0515C30 21.4296 30 20.6864 30 19.2L30 9.6C30 8.11359 30 7.37038 29.9015 6.74852C29.3593 3.32538 26.6746 0.640664 23.2515 0.0984923C22.6296 -9.89172e-07 21.8864 -9.56685e-07 20.4 -8.91712e-07C18.9136 -8.26739e-07 18.1704 -7.94253e-07 17.5485 0.0984925C14.1254 0.640665 11.4407 3.32538 10.8985 6.74852C10.8 7.37039 10.8 8.11359 10.8 9.6L10.8 13.2L23.6 13.2ZM10.8 13.2L3.10323 13.2C3.62715 12.6837 4.39836 12.07 5.5248 11.1768L8.34555 8.94029C8.86486 8.52854 8.95205 7.77376 8.5403 7.25445C8.12854 6.73514 7.37376 6.64795 6.85445 7.05971L3.97638 9.34171C2.90041 10.1948 2.01254 10.8987 1.38003 11.5288C0.730954 12.1754 0.192887 12.893 0.0467718 13.8101C0.0156439 14.0055 5.96973e-07 14.2026 6.29444e-07 14.4C6.38073e-07 14.5974 0.0156425 14.7945 0.0467704 14.9899C0.192884 15.9069 0.730953 16.6246 1.38003 17.2712C2.01254 17.9012 2.9004 18.6052 3.97637 19.4583L6.85445 21.7403C7.37376 22.152 8.12854 22.0649 8.5403 21.5456C8.95205 21.0262 8.86486 20.2715 8.34555 19.8597L5.5248 17.6232C4.39836 16.73 3.62715 16.1163 3.10323 15.6L10.8 15.6L10.8 13.2Z" fill="#BBBBBB" />
                </svg>
                Logout</a>
        </div>
    </div>
    <div class="content">
        <h2>Detail Konsultasi</h2>
        <div class="profile-container">
            <img src="../assets/default_profile.png" alt="Foto Anak">
            <div>
                <h3>Nama: <?php echo $child['Nama']; ?></h3>
            </div>
        </div>
        <div class="data-container">
            <h3>Data Anak</h3>
            <p>Tanggal Lahir: <?php echo $child['Tanggal_Lahir']; ?></p>
            <p>Jenis Kelamin: <?php echo $child['Jenis_Kelamin']; ?></p>
        </div>
        <div class="growth-container">
            <h3>Riwayat Pertumbuhan</h3>
            <table>
                <tr>
                    <th>Tanggal Pemeriksaan</th>
                    <th>Berat Badan</th>
                    <th>Tinggi Badan</th>
                </tr>
                <?php
                while ($row = $result_growth->fetch_assoc()) {
                    echo "<tr><td>" . $row['Tanggal_Pemeriksaan'] . "</td><td>" . $row['Berat_Badan'] . "</td><td>" . $row['Tinggi_Badan'] . "</td></tr>";
                }
                ?>
            </table>
        </div>
        <div class="message-container">
            <h3>Pesan dari Orang Tua</h3>
            <p><?php echo $consultation['Pesan']; ?></p>
        </div>
        <div class="response-container">
            <h3>Respon dari Dokter</h3>
            <?php if ($consultation['Respon']) { ?>
                <p><?php echo $consultation['Respon']; ?></p>
            <?php } else { ?>
                <form action="send_response.php" method="post">
                    <input type="hidden" name="consultation_id" value="<?php echo $consultation_id; ?>">
                    <textarea name="response" rows="4" cols="50" required></textarea><br>
                    <button type="submit">Kirim Balasan</button>
                </form>
            <?php } ?>
        </div>
    </div>
</body>

</html>