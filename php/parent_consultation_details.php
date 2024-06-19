<?php
include 'connection.php';

$consultation_id = isset($_GET['consultation_id']) ? $_GET['consultation_id'] : null;

if ($consultation_id) {
    $sql = "SELECT k.*, a.Nama AS Nama_Anak, a.Tanggal_Lahir, a.Jenis_Kelamin, a.Foto AS Foto_Anak, d.Nama AS Nama_Dokter, d.Spesialisasi, u.Foto AS Foto_Dokter
            FROM konsultasi k 
            JOIN anak a ON k.ID_Anak = a.ID_Anak 
            JOIN dokter d ON k.ID_Dokter = d.ID_Dokter
            JOIN user u ON d.ID_User = u.ID_User
            WHERE k.ID_Konsultasi='$consultation_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
?>
        <div class="modal-header">
            <h2>Detail Konsultasi</h2>
        </div>
        <div class="modal-body">
            <p><strong>Nama Anak:</strong> <?php echo $row['Nama_Anak']; ?></p>
            <p><strong>Tanggal Lahir:</strong> <?php echo $row['Tanggal_Lahir']; ?></p>
            <p><strong>Jenis Kelamin:</strong> <?php echo $row['Jenis_Kelamin']; ?></p>
            <p><strong>Pesan dari Orang Tua:</strong> <?php echo $row['Pesan']; ?></p>
            <p><strong>Tanggal Pengiriman Konsultasi:</strong> <?php echo $row['Tanggal_Konsultasi']; ?></p>
            <p><strong>Respon dari Dokter:</strong> <?php echo $row['Respon']; ?></p>
            <p><strong>Tanggal Pengiriman Respon Dokter:</strong> <?php echo $row['Tanggal_Respon']; ?></p>
            <h3>Informasi Dokter</h3>
            <img src="<?php echo $row['Foto_Dokter']; ?>" alt="Foto Dokter" style="width: 100px; border-radius: 50%;">
            <p><strong>Nama Dokter:</strong> Dr. <?php echo $row['Nama_Dokter']; ?></p>
            <p><strong>Spesialisasi:</strong> <?php echo $row['Spesialisasi']; ?></p>
        </div>
<?php
    } else {
        echo "Detail konsultasi tidak ditemukan.";
    }
} else {
    echo "ID konsultasi tidak ditemukan.";
}
?>