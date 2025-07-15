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

if (!isset($_GET['idrm']) || !is_numeric($_GET['idrm'])) {
    setFlashMessage('danger', 'ID rekam medis tidak valid');
    header('Location: /Klinik_Management/index.php?page=rekam_medis');
    exit();
}

$idrm = (int)$_GET['idrm'];

// Get rekam medis data with related information
$sql = "SELECT rm.*, p.nama AS nama_pasien, d.nama AS nama_dokter, r.nama_ruang, t.tindakan
        FROM rekam_medis rm
        LEFT JOIN pasien p ON rm.idpasien = p.idpasien
        LEFT JOIN dokter d ON rm.iddokter = d.iddokter
        LEFT JOIN ruang r ON rm.idruang = r.idruang
        LEFT JOIN tindakan t ON rm.idtindakan = t.idtindakan
        WHERE rm.idrm = ?";
$rekam_medis = fetchOne($sql, [$idrm]);

if (!$rekam_medis) {
    setFlashMessage('danger', 'Data rekam medis tidak ditemukan');
    header('Location: /Klinik_Management/index.php?page=rekam_medis');
    exit();
}

if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    // Get the room ID before deleting
    $room_id = $rekam_medis['idruang'];
    
    // Delete the rekam medis
    $sql = "DELETE FROM rekam_medis WHERE idrm = ?";
    executeQuery($sql, [$idrm]);
    
    // Update room status if it was assigned
    if ($room_id) {
        updateRoomStatusOnMedicalRecordChange($room_id, null);
    }
    
    setFlashMessage('success', 'Data rekam medis berhasil dihapus');
    header('Location: /Klinik_Management/index.php?page=rekam_medis');
    exit();
} elseif (isset($_POST['confirm']) && $_POST['confirm'] === 'no') {
    header('Location: /Klinik_Management/index.php?page=rekam_medis');
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Konfirmasi Hapus Data Rekam Medis</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger border-danger">
                        <h6 class="alert-heading">
                            <i class="bi bi-exclamation-triangle-fill"></i> PERINGATAN!
                        </h6>
                        <p class="mb-0">
                            <strong>Tindakan ini tidak dapat dibatalkan!</strong> Menghapus data rekam medis akan menghilangkan riwayat medis pasien secara permanen.
                        </p>
                    </div>
                    
                    <p>Apakah Anda yakin ingin menghapus data rekam medis berikut?</p>
                    
                    <div class="alert alert-info">
                        <h6>Detail Rekam Medis:</h6>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Pasien:</strong> <?= htmlspecialchars($rekam_medis['nama_pasien']) ?></li>
                            <li><strong>Dokter:</strong> <?= htmlspecialchars($rekam_medis['nama_dokter']) ?></li>
                            <li><strong>Ruang:</strong> <?= htmlspecialchars($rekam_medis['nama_ruang'] ?? 'Tidak ada') ?></li>
                            <li><strong>Diagnosis:</strong> <?= htmlspecialchars($rekam_medis['diagnosis'] ?? 'Tidak ada') ?></li>
                            <li><strong>Tindakan:</strong> <?= htmlspecialchars($rekam_medis['tindakan'] ?? 'Tidak ada') ?></li>
                        </ul>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="confirm" value="yes">
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                        <a href="/Klinik_Management/index.php?page=rekam_medis" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 