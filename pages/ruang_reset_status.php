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

if (!isset($_GET['idruang']) || !is_numeric($_GET['idruang'])) {
    setFlashMessage('danger', 'ID ruang tidak valid');
    header('Location: /Klinik_Management/index.php?page=ruang');
    exit();
}

$idruang = (int)$_GET['idruang'];

// Get room data for confirmation
$sql = "SELECT * FROM ruang WHERE idruang = ?";
$ruang = fetchOne($sql, [$idruang]);

if (!$ruang) {
    setFlashMessage('danger', 'Data ruang tidak ditemukan');
    header('Location: /Klinik_Management/index.php?page=ruang');
    exit();
}

if ($ruang['status'] !== 'Terisi') {
    setFlashMessage('warning', 'Ruang ini tidak dalam status "Terisi"');
    header('Location: /Klinik_Management/index.php?page=ruang');
    exit();
}

if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    // Check if room is still being used in medical records
    $check_rekam_medis = "SELECT COUNT(*) as count FROM rekam_medis WHERE idruang = ?";
    $rekam_medis_count = fetchOne($check_rekam_medis, [$idruang]);
    
    if ($rekam_medis_count['count'] > 0) {
        setFlashMessage('danger', 'Ruang masih digunakan dalam rekam medis. Status tidak dapat direset.');
        header('Location: /Klinik_Management/index.php?page=ruang');
        exit();
    }
    
    // Reset room status to empty
    updateRoomStatus($idruang, 'Kosong');
    setFlashMessage('success', 'Status ruang berhasil direset menjadi "Kosong"');
    header('Location: /Klinik_Management/index.php?page=ruang');
    exit();
} elseif (isset($_POST['confirm']) && $_POST['confirm'] === 'no') {
    header('Location: /Klinik_Management/index.php?page=ruang');
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Reset Status Ruang</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning border-warning">
                        <h6 class="alert-heading">
                            <i class="bi bi-exclamation-triangle-fill"></i> PERHATIAN!
                        </h6>
                        <p class="mb-0">
                            <strong>Reset status ruang akan mengubah status dari "Terisi" menjadi "Kosong".</strong> 
                            Pastikan ruang benar-benar sudah tidak digunakan.
                        </p>
                    </div>
                    
                    <p>Apakah Anda yakin ingin mereset status ruang berikut?</p>
                    
                    <div class="alert alert-info">
                        <h6>Detail Ruang:</h6>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Nama Ruang:</strong> <?= htmlspecialchars($ruang['nama_ruang']) ?></li>
                            <li><strong>Status Saat Ini:</strong> 
                                <span class="badge bg-danger">Terisi</span>
                            </li>
                            <li><strong>Status Setelah Reset:</strong> 
                                <span class="badge bg-success">Kosong</span>
                            </li>
                        </ul>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="confirm" value="yes">
                        <button type="submit" class="btn btn-warning">Ya, Reset Status</button>
                        <a href="/Klinik_Management/index.php?page=ruang" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 