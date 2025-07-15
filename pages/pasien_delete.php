<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Klinik_Management/login.php');
    exit();
}

if (!isset($_GET['idpasien']) || !is_numeric($_GET['idpasien'])) {
    setFlashMessage('danger', 'ID pasien tidak valid');
    header('Location: /Klinik_Management/index.php?page=pasien');
    exit();
}

$idpasien = (int)$_GET['idpasien'];

// Get patient data for confirmation
$sql = "SELECT * FROM pasien WHERE idpasien = ?";
$patient = fetchOne($sql, [$idpasien]);

if (!$patient) {
    setFlashMessage('danger', 'Data pasien tidak ditemukan');
    header('Location: /Klinik_Management/index.php?page=pasien');
    exit();
}

if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    // Check if patient is referenced in other tables
    $check_rekam_medis = "SELECT COUNT(*) as count FROM rekam_medis WHERE idpasien = ?";
    $rekam_medis_count = fetchOne($check_rekam_medis, [$idpasien]);
    
    $check_tindakan = "SELECT COUNT(*) as count FROM tindakan WHERE idpasien = ?";
    $tindakan_count = fetchOne($check_tindakan, [$idpasien]);
    
    if ($rekam_medis_count['count'] > 0 || $tindakan_count['count'] > 0) {
        setFlashMessage('danger', 'Pasien tidak dapat dihapus karena masih memiliki data terkait di rekam medis atau tindakan');
        header('Location: /Klinik_Management/index.php?page=pasien');
        exit();
    }
    
    // Delete the patient
    $sql = "DELETE FROM pasien WHERE idpasien = ?";
    executeQuery($sql, [$idpasien]);
    setFlashMessage('success', 'Data pasien berhasil dihapus');
    header('Location: /Klinik_Management/index.php?page=pasien');
    exit();
} elseif (isset($_POST['confirm']) && $_POST['confirm'] === 'no') {
    header('Location: /Klinik_Management/index.php?page=pasien');
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Konfirmasi Hapus Data Pasien</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger border-danger">
                        <h6 class="alert-heading">
                            <i class="bi bi-exclamation-triangle-fill"></i> PERINGATAN!
                        </h6>
                        <p class="mb-0">
                            <strong>Tindakan ini tidak dapat dibatalkan!</strong> Menghapus data pasien akan mempengaruhi data rekam medis dan tindakan medis yang terkait.
                        </p>
                    </div>
                    
                    <p>Apakah Anda yakin ingin menghapus data pasien berikut?</p>
                    
                    <div class="alert alert-info">
                        <h6>Detail Pasien:</h6>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Nama:</strong> <?= htmlspecialchars($patient['nama']) ?></li>
                            <li><strong>Jenis Kelamin:</strong> <?= $patient['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></li>
                            <li><strong>Pekerjaan:</strong> <?= htmlspecialchars($patient['pekerjaan'] ?? 'Tidak ada') ?></li>
                            <li><strong>Tempat Lahir:</strong> <?= htmlspecialchars($patient['tmp_lahir'] ?? 'Tidak ada') ?></li>
                            <li><strong>Tanggal Lahir:</strong> <?= $patient['tgl_lahir'] ? date('d/m/Y', strtotime($patient['tgl_lahir'])) : 'Tidak ada' ?></li>
                            <li><strong>Telepon:</strong> <?= htmlspecialchars($patient['telpon']) ?></li>
                            <li><strong>Alamat:</strong> <?= htmlspecialchars($patient['alamat'] ?? 'Tidak ada') ?></li>
                            <li><strong>Tanggal Daftar:</strong> <?= $patient['tgl_daftar'] ? date('d/m/Y', strtotime($patient['tgl_daftar'])) : 'Tidak ada' ?></li>
                        </ul>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="confirm" value="yes">
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                        <a href="/Klinik_Management/index.php?page=pasien" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 