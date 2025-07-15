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

if (!isset($_GET['idtindakan']) || !is_numeric($_GET['idtindakan'])) {
    setFlashMessage('danger', 'ID tindakan tidak valid');
    header('Location: /Klinik_Management/index.php?page=tindakan');
    exit();
}

$idtindakan = (int)$_GET['idtindakan'];

// Get tindakan data with related patient and doctor information
$sql = "SELECT t.*, p.nama AS nama_pasien, d.nama AS nama_dokter
        FROM tindakan t
        LEFT JOIN pasien p ON t.idpasien = p.idpasien
        LEFT JOIN dokter d ON t.iddokter = d.iddokter
        WHERE t.idtindakan = ?";
$tindakan = fetchOne($sql, [$idtindakan]);

if (!$tindakan) {
    setFlashMessage('danger', 'Data tindakan tidak ditemukan');
    header('Location: /Klinik_Management/index.php?page=tindakan');
    exit();
}

if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    // Check if tindakan is referenced in rekam_medis (which will prevent deletion)
    $check_rekam_medis = "SELECT COUNT(*) as count FROM rekam_medis WHERE idtindakan = ?";
    $rekam_medis_count = fetchOne($check_rekam_medis, [$idtindakan]);
    
    if ($rekam_medis_count['count'] > 0) {
        setFlashMessage('danger', 'Tindakan tidak dapat dihapus karena masih memiliki data terkait di rekam medis');
        header('Location: /Klinik_Management/index.php?page=tindakan');
        exit();
    }
    
    // Delete the tindakan
    $sql = "DELETE FROM tindakan WHERE idtindakan = ?";
    executeQuery($sql, [$idtindakan]);
    
    setFlashMessage('success', 'Data tindakan berhasil dihapus');
    header('Location: /Klinik_Management/index.php?page=tindakan');
    exit();
} elseif (isset($_POST['confirm']) && $_POST['confirm'] === 'no') {
    header('Location: /Klinik_Management/index.php?page=tindakan');
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Konfirmasi Hapus Tindakan Medis</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger border-danger">
                        <h6 class="alert-heading">
                            <i class="bi bi-exclamation-triangle-fill"></i> PERINGATAN!
                        </h6>
                        <p class="mb-0">
                            <strong>Tindakan ini tidak dapat dibatalkan!</strong> Menghapus data tindakan medis akan mempengaruhi data rekam medis yang terkait.
                        </p>
                    </div>
                    
                    <p>Apakah Anda yakin ingin menghapus data tindakan medis berikut?</p>
                    
                    <div class="alert alert-info">
                        <h6>Detail Tindakan:</h6>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Pasien:</strong> <?= htmlspecialchars($tindakan['nama_pasien']) ?></li>
                            <li><strong>Dokter:</strong> <?= htmlspecialchars($tindakan['nama_dokter'] ?? 'Tidak ada') ?></li>
                            <li><strong>Kriteria:</strong> <?= htmlspecialchars($tindakan['kriteria'] ?? 'Tidak ada') ?></li>
                            <li><strong>Tindakan:</strong> <?= htmlspecialchars($tindakan['tindakan'] ?? 'Tidak ada') ?></li>
                            <li><strong>Fasilitas:</strong> <?= htmlspecialchars($tindakan['fasilitas'] ?? 'Tidak ada') ?></li>
                            <li><strong>Keputusan Keluarga:</strong> <?= htmlspecialchars($tindakan['keputusan_keluarga'] ?? 'Tidak ada') ?></li>
                        </ul>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="confirm" value="yes">
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                        <a href="/Klinik_Management/index.php?page=tindakan" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 