<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Orang Tua') {
    header("Location: ../html/auth_login.html");
    exit();
}
include 'connection.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM Anak WHERE ID_Orang_Tua = (SELECT ID_Orang_Tua FROM Orang_Tua WHERE ID_User='$user_id')";
$result = $conn->query($sql);
$children = $result->fetch_all(MYSQLI_ASSOC);

$selected_child = null;
$growth_data = [];
$nutrition_status = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $child_id = $_POST['child_id'];
    $weight = isset($_POST['weight']) ? $_POST['weight'] : null;
    $height = isset($_POST['height']) ? $_POST['height'] : null;

    if ($weight && $height) {
        $status_gizi = screenNutrition($weight, $height);

        // Simpan hasil skrining ke dalam tabel hasil_gizi
        $sql_insert_gizi = "INSERT INTO hasil_gizi (ID_Anak, Status_Gizi, Catatan, Tanggal_Pemeriksaan) VALUES ('$child_id', '$status_gizi', '', NOW())";
        $conn->query($sql_insert_gizi);

        // Simpan data pertumbuhan ke dalam tabel riwayat_pertumbuhan
        $sql_insert_pertumbuhan = "INSERT INTO riwayat_pertumbuhan (ID_Anak, Berat_Badan, Tinggi_Badan, Tanggal_Pemeriksaan) VALUES ('$child_id', '$weight', '$height', NOW())";
        $conn->query($sql_insert_pertumbuhan);

        $screening_result = "Status Gizi: " . $status_gizi;
    }
} else {
    $child_id = isset($_POST['child_id']) ? $_POST['child_id'] : null;
}

// Default to first child if none selected
if (empty($child_id) && !empty($children)) {
    $selected_child = $children[0];
    $child_id = $selected_child['ID_Anak'];
} else {
    foreach ($children as $child) {
        if ($child['ID_Anak'] == $child_id) {
            $selected_child = $child;
            break;
        }
    }
}

if ($selected_child) {
    // Ambil data pertumbuhan anak yang dipilih
    $sql_growth_data = "SELECT * FROM riwayat_pertumbuhan WHERE ID_Anak='$child_id' ORDER BY Tanggal_Pemeriksaan DESC";
    $growth_data_result = $conn->query($sql_growth_data);
    if ($growth_data_result) {
        $growth_data = $growth_data_result->fetch_all(MYSQLI_ASSOC);
    }

    // Ambil status gizi anak yang dipilih
    $sql_nutrition_status = "SELECT * FROM hasil_gizi WHERE ID_Anak='$child_id' ORDER BY Tanggal_Pemeriksaan DESC LIMIT 1";
    $nutrition_status_result = $conn->query($sql_nutrition_status);
    if ($nutrition_status_result) {
        $nutrition_status = $nutrition_status_result->fetch_assoc()['Status_Gizi'];
    }
}

function screenNutrition($weight, $height)
{
    // logika untuk skrining gizi
    $bmi = $weight / (($height / 100) ** 2);
    if ($bmi < 18.5) {
        return "Gizi Buruk";
    } elseif ($bmi < 24.9) {
        return "Gizi Baik";
    } else {
        return "Gizi Lebih";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pertumbuhan</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/parent_growth.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<script>
    function showAddChildForm() {
        document.getElementById('addChildForm').style.display = 'block';
    }

    function submitForm() {
        document.getElementById('childForm').submit();
    }

    window.onload = function() {
        document.getElementById('child_id').addEventListener('change', submitForm);
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
            <a href="parent_growth.php" class="active">
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
            <a href="parent_profile.php">
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
        <h2>Skrining Pertumbuhan Anak</h2>
        <div class="skrining">
            <div class="data-anak">
                <h3>Data Anak</h3>
                <?php if ($selected_child) : ?>
                    <p>Nama: <?php echo $selected_child['Nama']; ?></p>
                    <p>Tanggal Lahir: <?php echo $selected_child['Tanggal_Lahir']; ?></p>
                    <p>Jenis Kelamin: <?php echo $selected_child['Jenis_Kelamin']; ?></p>
                <?php endif; ?>
            </div>
            <div class="skrining-card">
                <form id="childForm" method="post" action="parent_growth.php">
                    <label for="child_id">Pilih Anak:</label>
                    <select id="child_id" name="child_id" required>
                        <?php foreach ($children as $child) : ?>
                            <option value="<?php echo $child['ID_Anak']; ?>" <?php echo ($selected_child && $child['ID_Anak'] == $selected_child['ID_Anak']) ? 'selected' : ''; ?>><?php echo $child['Nama']; ?></option>
                        <?php endforeach; ?>
                    </select><br>
                    <label for="weight">Berat Badan (kg):</label>
                    <input type="number" id="weight" name="weight" step="0.1" required><br>
                    <label for="height">Tinggi Badan (cm):</label>
                    <input type="number" id="height" name="height" step="0.1" required><br>
                    <button type="submit">Lakukan Skrining</button>
                </form>
                <?php if (isset($screening_result)) {
                    echo "<p style='display: none;'>$screening_result</p>";
                } ?>
            </div>
        </div>
        <div class="diagram">
            <div class="diagram-tinggi">
                <h3>Diagram Tinggi Badan</h3>
                <canvas id="heightChart"></canvas>
            </div>
            <div class="diagram-berat">
                <h3>Diagram Berat Badan</h3>
                <canvas id="weightChart"></canvas>
            </div>
            <div class="diagram-hasil">
                <h3>Hasil Skrining Gizi</h3>
                <?php if (isset($screening_result)) : ?>
                    <p class="<?php echo 'gizi-' . strtolower(str_replace(' ', '-', $status_gizi)); ?>"><?php echo $screening_result; ?></p>
                <?php elseif ($nutrition_status) : ?>
                    <p class="<?php echo 'gizi-' . strtolower(str_replace(' ', '-', $nutrition_status)); ?>">Status Gizi: <?php echo $nutrition_status; ?></p>
                <?php else : ?>
                    <p>Belum ada hasil skrining gizi.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        // Data for growth charts
        const growthData = <?php echo json_encode($growth_data); ?>;

        // Prepare data for charts
        const labels = growthData.map(data => data.Tanggal_Pemeriksaan);
        const heightData = growthData.map(data => data.Tinggi_Badan);
        const weightData = growthData.map(data => data.Berat_Badan);

        // Height chart
        const heightCtx = document.getElementById('heightChart').getContext('2d');
        new Chart(heightCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tinggi Badan (cm)',
                    data: heightData,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Weight chart
        const weightCtx = document.getElementById('weightChart').getContext('2d');
        new Chart(weightCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Berat Badan (kg)',
                    data: weightData,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>