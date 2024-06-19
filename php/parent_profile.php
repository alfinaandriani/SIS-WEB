<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Orang Tua') {
    header("Location: ../html/auth_login.html");
    exit();
}
include 'connection.php';

$user_id = $_SESSION['user_id'];

// Pastikan nama kolom benar
$sql = "SELECT User.Email, User.Password, User.Foto, Orang_Tua.Nama_Ayah, Orang_Tua.Nama_Ibu, Orang_Tua.Alamat, Orang_Tua.Nomor_Telepon 
        FROM User 
        JOIN Orang_Tua ON User.ID_User = Orang_Tua.ID_User 
        WHERE User.ID_User='$user_id'";

$result = $conn->query($sql);

if ($result) {
    $user = $result->fetch_assoc();
    // Inisialisasi array dengan nilai kosong jika field tidak ada
    $user['Nama_Ayah'] = $user['Nama_Ayah'] ?? '';
    $user['Nama_Ibu'] = $user['Nama_Ibu'] ?? '';
    $user['Alamat'] = $user['Alamat'] ?? '';
    $user['Nomor_Telepon'] = $user['Nomor_Telepon'] ?? '';
    $user['Email'] = $user['Email'] ?? '';
    $user['Foto'] = $user['Foto'] ?? '';
} else {
    die("Error: " . $conn->error);
}

$sql_children = "SELECT * FROM Anak WHERE ID_Orang_Tua = (SELECT ID_Orang_Tua FROM Orang_Tua WHERE ID_User='$user_id')";
$result_children = $conn->query($sql_children);
$children = $result_children->fetch_all(MYSQLI_ASSOC);

$notification = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $nama_ayah = $_POST['nama_ayah'];
        $nama_ibu = $_POST['nama_ibu'];
        $alamat = $_POST['alamat'];
        $nomor_telepon = $_POST['nomor_telp'];
        $email = $_POST['email'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

        $update_sql = "UPDATE User SET Email='$email'";
        if ($password) {
            $update_sql .= ", Password='$password'";
        }
        $update_sql .= " WHERE ID_User='$user_id'";

        $update_sql_ortu = "UPDATE Orang_Tua SET Nama_Ayah='$nama_ayah', Nama_Ibu='$nama_ibu', Alamat='$alamat', Nomor_Telepon='$nomor_telepon' WHERE ID_User='$user_id'";

        if ($conn->query($update_sql) === TRUE && $conn->query($update_sql_ortu) === TRUE) {
            $notification = "Profil berhasil diperbarui.";
            $user['Nama_Ayah'] = $nama_ayah;
            $user['Nama_Ibu'] = $nama_ibu;
            $user['Alamat'] = $alamat;
            $user['Nomor_Telepon'] = $nomor_telepon;
            $user['Email'] = $email;
        } else {
            $notification = "Error: " . $update_sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['update_photo'])) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES["profile_photo"]["name"]);
        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
            $update_sql = "UPDATE User SET Foto='$target_file' WHERE ID_User='$user_id'";
            if ($conn->query($update_sql) === TRUE) {
                $notification = "Foto profil berhasil diperbarui.";
                $user['Foto'] = $target_file;
            } else {
                $notification = "Error: " . $update_sql . "<br>" . $conn->error;
            }
        } else {
            $notification = "Sorry, there was an error uploading your file.";
        }
    } elseif (isset($_POST['add_child'])) {
        $nama_anak = $_POST['nama_anak'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $foto_anak = $_FILES['foto_anak'];

        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($foto_anak["name"]);
        if (move_uploaded_file($foto_anak["tmp_name"], $target_file)) {
            $sql = "INSERT INTO Anak (Nama, Tanggal_Lahir, Jenis_Kelamin, ID_Orang_Tua, Foto)
                    VALUES ('$nama_anak', '$tanggal_lahir', '$jenis_kelamin', 
                            (SELECT ID_Orang_Tua FROM Orang_Tua WHERE ID_User='$user_id'), '$target_file')";
            if ($conn->query($sql) === TRUE) {
                $notification = "Data anak berhasil ditambah.";
                $children = fetch_children($user_id, $conn);
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } elseif (isset($_POST['update_child'])) {
        $child_id = $_POST['child_id'];
        $nama_anak = $_POST['nama_anak'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $foto_anak = $_FILES['foto_anak'];

        $update_sql = "UPDATE Anak SET Nama='$nama_anak', Tanggal_Lahir='$tanggal_lahir', Jenis_Kelamin='$jenis_kelamin' WHERE ID_Anak='$child_id'";
        if (!empty($foto_anak['name'])) {
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($foto_anak["name"]);
            if (move_uploaded_file($foto_anak["tmp_name"], $target_file)) {
                $update_sql = "UPDATE Anak SET Foto='$target_file' WHERE ID_Anak='$child_id'";
                if ($conn->query($update_sql) === TRUE) {
                    $children = fetch_children($user_id, $conn);
                } else {
                    echo "Error: " . $update_sql . "<br>" . $conn->error;
                }
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
        if ($conn->query($update_sql) === TRUE) {
            $children = fetch_children($user_id, $conn);
        } else {
            echo "Error: " . $update_sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['delete_child'])) {
        $child_id = $_POST['child_id'];
        $delete_sql = "DELETE FROM Anak WHERE ID_Anak='$child_id'";
        if ($conn->query($delete_sql) === TRUE) {
            $children = fetch_children($user_id, $conn);
        } else {
            echo "Error: " . $delete_sql . "<br>" . $conn->error;
        }
    }
}

function fetch_children($user_id, $conn)
{
    $sql_children = "SELECT * FROM Anak WHERE ID_Orang_Tua = (SELECT ID_Orang_Tua FROM Orang_Tua WHERE ID_User='$user_id')";
    $result_children = $conn->query($sql_children);
    return $result_children->fetch_all(MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Profile</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/profile.css">
</head>

<script>
    function enableEditing(section) {
        var inputs = document.querySelectorAll('#' + section + ' input, #' + section + ' select');
        inputs.forEach(function(input) {
            input.disabled = false;
        });
        var saveButton = document.querySelector('#' + section + ' .save-button');
        saveButton.style.display = 'block';
        saveButton.classList.add('active');
        saveButton.disabled = false;
    }

    function saveChanges(section) {
        var form = document.getElementById(section + '-form');
        form.submit();
    }

    function showAddChildForm() {
        document.getElementById('addChildForm').style.display = 'block';
    }

    function closeAddChildForm() {
        document.getElementById('addChildForm').style.display = 'none';
    }

    function showNotification(message) {
        var notification = document.getElementById('notification');
        notification.innerText = message;
        notification.style.display = 'block';
        setTimeout(function() {
            notification.style.display = 'none';
        }, 3000);
    }
</script>

<body>
    <div class="sidebar">
        <img src="../assets/logo_sis.webp" alt="Logo SIS">
        <div class="menu-container">
            <a href="parent_dashboard.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" fill="none">
                    <path d="M28.0193 8.86302L18.3599 1.13555C16.4723 -0.36865 13.5229 -0.383397 11.65 1.12081L1.99069 8.86302C0.604461 9.96905 -0.236123 12.1811 0.0588184 13.9213L1.91695 25.0406C2.34462 27.5328 4.65991 29.4942 7.18166 29.4942H22.8136C25.3058 29.4942 27.6654 27.4886 28.093 25.0258L29.9512 13.9065C30.2166 12.1811 29.376 9.96905 28.0193 8.86302ZM16.1036 23.5954C16.1036 24.2 15.6022 24.7014 14.9976 24.7014C14.393 24.7014 13.8916 24.2 13.8916 23.5954V19.1712C13.8916 18.5666 14.393 18.0652 14.9976 18.0652C15.6022 18.0652 16.1036 18.5666 16.1036 19.1712V23.5954Z" fill="#BBBBBB" />
                </svg>
                Beranda
            </a>
            <a href="parent_growth.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.0696 2.03596e-07C17.8951 -1.23592e-05 20.1098 -2.23517e-05 21.867 0.190351C23.6601 0.384625 25.113 0.788191 26.3535 1.68947C27.1045 2.23509 27.7649 2.89552 28.3105 3.64652C29.2118 4.88701 29.6154 6.33991 29.8097 8.13304C30 9.89014 30 12.1049 30 14.9302V15.0697C30 17.895 30 20.1099 29.8097 21.867C29.6154 23.6601 29.2118 25.113 28.3105 26.3535C27.7649 27.1045 27.1045 27.7649 26.3535 28.3105C25.113 29.2118 23.6601 29.6154 21.867 29.8097C20.1099 30 17.8951 30 15.0698 30H14.9303C12.105 30 9.89014 30 8.13304 29.8097C6.33991 29.6154 4.88701 29.2118 3.64652 28.3105C2.89552 27.7649 2.23509 27.1045 1.68947 26.3535C0.788191 25.113 0.384625 23.6601 0.190351 21.867C-2.23517e-05 20.1098 -1.23592e-05 17.8951 2.03596e-07 15.0696V14.9304C-1.23592e-05 12.1049 -2.23517e-05 9.89017 0.190351 8.13304C0.384625 6.33991 0.788191 4.88701 1.68947 3.64652C2.23509 2.89552 2.89552 2.23509 3.64652 1.68947C4.88701 0.788191 6.33991 0.384625 8.13304 0.190351C9.89017 -2.23517e-05 12.1049 -1.23592e-05 14.9304 2.03596e-07H15.0696ZM23.7282 11.1125C24.0089 10.5404 23.7726 9.84911 23.2005 9.56846C22.6283 9.28782 21.937 9.52411 21.6564 10.0962L19.4097 14.6763C18.7133 16.0959 16.6666 16.0266 16.0679 14.5631C14.7154 11.2569 10.0916 11.1003 8.51843 14.3074L6.27177 18.8874C5.99112 19.4596 6.22741 20.1509 6.79954 20.4315C7.37167 20.7122 8.06297 20.4759 8.34362 19.9038L10.5903 15.3237C11.2867 13.9041 13.3334 13.9734 13.9321 15.4369C15.2846 18.7431 19.9084 18.8997 21.4816 15.6926L23.7282 11.1125Z" fill="#BBBBBB" />
                </svg>
                Pertumbuhan
            </a>
            <a href="parent_consultation.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="27" viewBox="0 0 30 27" fill="none">
                    <path d="M0.499834 5.11421C0 6.97418 0 9.54013 0 13.5C0 19.1246 0 21.9369 1.43237 23.9084C1.89497 24.5451 2.4549 25.105 3.09161 25.5676C5.0631 27 7.8754 27 13.5 27H16.5C22.1246 27 24.9369 27 26.9084 25.5676C27.5451 25.105 28.105 24.5451 28.5676 23.9084C30 21.9369 30 19.1246 30 13.5C30 9.52693 30 6.9571 29.4952 5.09564L26.341 8.24979C23.8926 10.6982 21.9739 12.617 20.2755 13.9128C18.5371 15.2392 16.898 16.0184 14.9998 16.0184C13.1016 16.0184 11.4625 15.2392 9.72412 13.9128C8.02573 12.617 6.10697 10.6982 3.65856 8.24976L0.725782 5.31698L0.499834 5.11421Z" fill="#BBBBBB" />
                    <path d="M1.5 3L1.66327 3.1351L2.27381 3.68303L5.18869 6.59791C7.71118 9.1204 9.52471 10.9305 11.0889 12.124C12.6272 13.2977 13.7914 13.7684 14.9998 13.7684C16.2082 13.7684 17.3724 13.2977 18.9107 12.124C20.4749 10.9306 22.2884 9.1204 24.8109 6.59791L28.2728 3.13605L28.4612 2.94868C27.9986 2.31197 27.5451 1.89497 26.9084 1.43237C24.9369 0 22.1246 0 16.5 0H13.5C7.8754 0 5.0631 0 3.09161 1.43237C2.4549 1.89497 1.9626 2.36329 1.5 3Z" fill="#BBBBBB" />
                </svg>
                Konsultasi
            </a>
            <a href="parent_profile.php" class="active">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0 15C0 6.71573 6.71573 0 15 0C23.2843 0 30 6.71573 30 15C30 19.2537 28.2281 23.0953 25.3849 25.8238C22.6907 28.4093 19.03 30 15 30C10.97 30 7.30929 28.4093 4.61512 25.8238C1.77194 23.0953 0 19.2537 0 15ZM23.4881 24.7236C22.8866 22.9244 21.1868 21.6279 19.186 21.6279H10.814C8.8132 21.6279 7.11336 22.9244 6.51191 24.7236C8.78166 26.7068 11.7497 27.907 15 27.907C18.2503 27.907 21.2183 26.7068 23.4881 24.7236ZM15 4.18605C11.3395 4.18605 8.37209 7.15346 8.37209 10.814C8.37209 14.4744 11.3395 17.4419 15 17.4419C18.6605 17.4419 21.6279 14.4744 21.6279 10.814C21.6279 7.15346 18.6605 4.18605 15 4.18605Z" fill="#BBBBBB" />
                </svg>
                Profil
            </a>
            <a href="logout.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="29" viewBox="0 0 30 29" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M23.6 13.2C24.2627 13.2 24.8 13.7373 24.8 14.4C24.8 15.0627 24.2627 15.6 23.6 15.6L10.8 15.6L10.8 19.2C10.8 20.6864 10.8 21.4296 10.8985 22.0515C11.4407 25.4746 14.1254 28.1593 17.5485 28.7015C18.1704 28.8 18.9136 28.8 20.4 28.8C21.8864 28.8 22.6296 28.8 23.2515 28.7015C26.6746 28.1593 29.3593 25.4746 29.9015 22.0515C30 21.4296 30 20.6864 30 19.2L30 9.6C30 8.11359 30 7.37038 29.9015 6.74852C29.3593 3.32538 26.6746 0.640664 23.2515 0.0984923C22.6296 -9.89172e-07 21.8864 -9.56685e-07 20.4 -8.91712e-07C18.9136 -8.26739e-07 18.1704 -7.94253e-07 17.5485 0.0984925C14.1254 0.640665 11.4407 3.32538 10.8985 6.74852C10.8 7.37039 10.8 8.11359 10.8 9.6L10.8 13.2L23.6 13.2ZM10.8 13.2L3.10323 13.2C3.62715 12.6837 4.39836 12.07 5.5248 11.1768L8.34555 8.94029C8.86486 8.52854 8.95205 7.77376 8.5403 7.25445C8.12854 6.73514 7.37376 6.64795 6.85445 7.05971L3.97638 9.34171C2.90041 10.1948 2.01254 10.8987 1.38003 11.5288C0.730954 12.1754 0.192887 12.893 0.0467718 13.8101C0.0156439 14.0055 5.96973e-07 14.2026 6.29444e-07 14.4C6.38073e-07 14.5974 0.0156425 14.7945 0.0467704 14.9899C0.192884 15.9069 0.730953 16.6246 1.38003 17.2712C2.01254 17.9012 2.9004 18.6052 3.97637 19.4583L6.85445 21.7403C7.37376 22.152 8.12854 22.0649 8.5403 21.5456C8.95205 21.0262 8.86486 20.2715 8.34555 19.8597L5.5248 17.6232C4.39836 16.73 3.62715 16.1163 3.10323 15.6L10.8 15.6L10.8 13.2Z" fill="#BBBBBB" />
                </svg>
                Logout
            </a>
        </div>
    </div>
    <div class="content">
        <h2>Profil</h2>
        <?php if ($notification) : ?>
            <div id="notification" class="notification"><?php echo $notification; ?></div>
        <?php endif; ?>
        <div class="section">
            <div class="user">
                <div id="profile-form" class="profile-form">
                    <h3 class="edit-profile">
                        <span>Edit Profil</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" onclick="enableEditing('profile-form')">
                            <path d="M11.4464 18.31C10.9797 18.31 10.6014 18.6883 10.6014 19.155C10.6014 19.6217 10.9797 20 11.4464 20H18.2066C18.6733 20 19.0516 19.6217 19.0516 19.155C19.0516 18.6883 18.6733 18.31 18.2066 18.31H11.4464Z" fill="black" />
                            <path d="M12.3411 1.49344L12.4426 1.55202C13.3193 2.05815 14.0503 2.4802 14.5856 2.8933C15.1507 3.32932 15.5991 3.82979 15.7869 4.53081C15.9748 5.23182 15.8366 5.88947 15.5653 6.54958C15.377 7.00777 15.1001 7.52266 14.7665 8.10997L14.0507 7.68452L14.0414 7.67911L6.23872 3.17422L5.50902 2.74415C5.84713 2.1683 6.15176 1.67646 6.45168 1.28782C6.88771 0.722796 7.38818 0.274339 8.08919 0.0865038C8.7902 -0.101332 9.44786 0.0368065 10.108 0.308115C10.7334 0.565155 11.4644 0.987259 12.3411 1.49344Z" fill="black" />
                            <path d="M4.66239 4.2069L0.743771 10.994C0.408256 11.574 0.142964 12.0327 0.0446764 12.5543C-0.0536116 13.076 0.0265946 13.5997 0.128032 14.2621L0.155361 14.4411C0.342602 15.6707 0.496937 16.6843 0.729713 17.4529C0.97316 18.2567 1.3474 18.9524 2.08344 19.3774C2.81947 19.8023 3.60914 19.7786 4.42699 19.5875C5.20899 19.4048 6.1639 19.0317 7.3224 18.579L7.49112 18.5132C8.11546 18.2698 8.60912 18.0774 9.01175 17.7315C9.41438 17.3855 9.67892 16.9265 10.0135 16.3459L13.9228 9.57457L13.1918 9.14005L5.38385 4.63215L4.66239 4.2069Z" fill="black" />
                        </svg>
                    </h3>
                    <div class="profile-photo">
                        <img src="<?php echo $user['Foto'] ? $user['Foto'] : '../assets/default_profile.png'; ?>" alt="Foto Profil" class="profile-photo-img">
                    </div>
                    <form id="profile-form-form" method="post" action="parent_profile.php" enctype="multipart/form-data">
                        <label for="foto_parent">Update Foto</label>
                        <input type="file" name="foto_parent" accept="image/*" disabled>
                        <input type="hidden" name="update_profile" value="1">
                        <label for="nama_ayah">Nama Ayah</label>
                        <input type="text" name="nama_ayah" value="<?php echo $user['Nama_Ayah']; ?>" required disabled>
                        <label for="nama_ibu">Nama Ibu</label>
                        <input type="text" name="nama_ibu" value="<?php echo $user['Nama_Ibu']; ?>" required disabled>
                        <label for="alamat">Alamat</label>
                        <input type="text" name="alamat" value="<?php echo $user['Alamat']; ?>" required disabled>
                        <label for="nomor_telp">Nomor Telp</label>
                        <input type="text" name="nomor_telp" value="<?php echo $user['Nomor_Telepon']; ?>" required disabled>
                        <label for="email">Email</label>
                        <input type="email" name="email" value="<?php echo $user['Email']; ?>" required disabled>
                        <label for="password">Password</label>
                        <input type="password" name="password" disabled>
                        <button type="button" class="save-button" onclick="saveChanges('profile-form')">Simpan</button>
                    </form>
                </div>
            </div>

            <div class="child">
                <?php foreach ($children as $index => $child) : ?>
                    <div id="child-form-<?php echo $index; ?>" class="child-form">
                        <h3 class="edit-profile">
                            <span>Data Anak</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" onclick="enableEditing('child-form-<?php echo $index; ?>')">
                                <path d="M11.4464 18.31C10.9797 18.31 10.6014 18.6883 10.6014 19.155C10.6014 19.6217 10.9797 20 11.4464 20H18.2066C18.6733 20 19.0516 19.6217 19.0516 19.155C19.0516 18.6883 18.6733 18.31 18.2066 18.31H11.4464Z" fill="black" />
                                <path d="M12.3411 1.49344L12.4426 1.55202C13.3193 2.05815 14.0503 2.4802 14.5856 2.8933C15.1507 3.32932 15.5991 3.82979 15.7869 4.53081C15.9748 5.23182 15.8366 5.88947 15.5653 6.54958C15.377 7.00777 15.1001 7.52266 14.7665 8.10997L14.0507 7.68452L14.0414 7.67911L6.23872 3.17422L5.50902 2.74415C5.84713 2.1683 6.15176 1.67646 6.45168 1.28782C6.88771 0.722796 7.38818 0.274339 8.08919 0.0865038C8.7902 -0.101332 9.44786 0.0368065 10.108 0.308115C10.7334 0.565155 11.4644 0.987259 12.3411 1.49344Z" fill="black" />
                                <path d="M4.66239 4.2069L0.743771 10.994C0.408256 11.574 0.142964 12.0327 0.0446764 12.5543C-0.0536116 13.076 0.0265946 13.5997 0.128032 14.2621L0.155361 14.4411C0.342602 15.6707 0.496937 16.6843 0.729713 17.4529C0.97316 18.2567 1.3474 18.9524 2.08344 19.3774C2.81947 19.8023 3.60914 19.7786 4.42699 19.5875C5.20899 19.4048 6.1639 19.0317 7.3224 18.579L7.49112 18.5132C8.11546 18.2698 8.60912 18.0774 9.01175 17.7315C9.41438 17.3855 9.67892 16.9265 10.0135 16.3459L13.9228 9.57457L13.1918 9.14005L5.38385 4.63215L4.66239 4.2069Z" fill="black" />
                            </svg>
                        </h3>
                        <div class="child-photo">
                            <img src="<?php echo $child['Foto'] ? $child['Foto'] : '../assets/default_profile.png'; ?>" alt="Foto Anak" class="child-photo-img">
                        </div>
                        <form id="child-form-<?php echo $index; ?>-form" method="post" action="parent_profile.php" enctype="multipart/form-data">
                            <label for="foto_anak">Update Foto</label>
                            <input type="file" name="foto_anak" accept="image/*" disabled>
                            <input type="hidden" name="update_child" value="1">
                            <input type="hidden" name="child_id" value="<?php echo $child['ID_Anak']; ?>">
                            <label for="nama_anak">Nama Anak</label>
                            <input type="text" name="nama_anak" placeholder="Nama Anak" value="<?php echo $child['Nama']; ?>" required disabled>
                            <label for="tanggal_lahir">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" value="<?php echo $child['Tanggal_Lahir']; ?>" required disabled>
                            <label for="jenis_kelamin">Jenis Kelamin</label>
                            <select name="jenis_kelamin" required disabled>
                                <option value="Laki-laki" <?php echo $child['Jenis_Kelamin'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="Perempuan" <?php echo $child['Jenis_Kelamin'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                            <button type="button" class="save-button" onclick="saveChanges('child-form-<?php echo $index; ?>')">Simpan</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <button type="button" class="add-button" onclick="showAddChildForm()">+</button>
            </div>
        </div>
    </div>

    <!-- Formulir untuk menambahkan anak baru -->
    <div id="addChildForm" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeAddChildForm()">&times;</span>
            <h3>Tambah Data Anak</h3>
            <form id="add-child-form" method="post" action="parent_profile.php" enctype="multipart/form-data">
                <input type="hidden" name="add_child" value="1">
                <label for="nama_anak">Nama Anak</label>
                <input type="text" name="nama_anak" placeholder="Nama Anak" required>
                <label for="tanggal_lahir">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" required>
                <label for="jenis_kelamin">Jenis Kelamin</label>
                <select name="jenis_kelamin" required>
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
                <label for="foto_anak">Foto Anak</label>
                <input type="file" name="foto_anak" accept="image/*" required>
                <button type="submit">Tambah Anak</button>
            </form>
        </div>
    </div>

    <script>
        function showAddChildForm() {
            document.getElementById('addChildForm').style.display = 'block';
        }

        function closeAddChildForm() {
            document.getElementById('addChildForm').style.display = 'none';
        }

        function showNotification(message) {
            var notification = document.getElementById('notification');
            notification.innerText = message;
            notification.style.display = 'block';
            setTimeout(function() {
                notification.style.display = 'none';
            }, 3000);
        }

        document.getElementById('add-child-form').addEventListener('submit', function(event) {
            event.preventDefault();
            closeAddChildForm();
            showNotification('Data anak berhasil ditambah.');
            this.submit();
        });
    </script>
</body>

</html>